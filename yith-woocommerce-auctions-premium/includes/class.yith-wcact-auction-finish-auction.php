<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Finish_Auction Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * YITH Auction finish auction
 *
 * @class
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Your Inspiration Themes
 */
if ( ! class_exists( 'YITH_Auction_Finish_Auction' ) ) {
	/**
	 * Class YITH_Auction_Finish_Auction
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auction_Finish_Auction {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_Auction_Finish_Auction
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_Auction_Finish_Auction
		 * @since  1.0.0
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			if ( is_null( $self::$instance ) ) {
				$self::$instance = new $self();
			}

			return $self::$instance;
		}

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			add_action( 'wp_loaded', array( $this, 'pay_won_auction' ), 90 );
			add_action( 'woocommerce_order_status_completed', array( $this, 'disable_pay_now_button' ) );
			add_action( 'woocommerce_order_status_processing', array( $this, 'disable_pay_now_button' ) );
			add_action( 'woocommerce_login_form_end', array( $this, 'add_redirect_after_login' ) );

			/* == Genearate order on fly == */
			add_action( 'yith_wcact_auction_winner', array( $this, 'create_order' ), 10, 2 );
		}

		/**
		 * Pay won auction
		 *
		 * Process the winner option when customer click on winner email or product page.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.2
		 */
		public function pay_won_auction() {
			if ( ! empty( $_REQUEST['yith-wcact-pay-won-auction'] ) && is_numeric( $_REQUEST['yith-wcact-pay-won-auction'] ) ) { // phpcs:ignore
				$product_id = intval( wp_unslash( $_REQUEST['yith-wcact-pay-won-auction'] ) ); // phpcs:ignore

				if ( ! is_user_logged_in() ) {
					$account         = wc_get_page_permalink( 'myaccount' );
					$url_to_redirect = add_query_arg(
						array(
							/**
							 * APPLY_FILTERS: yith_wcact_pay_now_redirect_guest
							 *
							 * Filter the URL to redirect guest users after login when trying to pay the auction.
							 *
							 * @param string $redirect_url Redirect URL
							 * @param int    $product_id   Product ID
							 *
							 * @return string
							 */
							'redirect_after_login' => apply_filters( 'yith_wcact_pay_now_redirect_guest', get_permalink( $product_id ), $product_id ),
							'_wpnonce'             => wp_create_nonce( 'yith-wcact-redirect-my-account' ),
						),
						$account
					);

					wp_safe_redirect( $url_to_redirect );
					exit;
				}

				$instance = YITH_Auctions()->bids;
				$max_bid  = $instance->get_max_bid( $product_id );
				$product  = wc_get_product( $product_id );

				if ( $product && 'auction' === $product->get_type() ) {
					$current_user_id = get_current_user_id();

					if ( (int) $current_user_id === (int) $max_bid->user_id && $product->is_closed() ) {  // check if you are the winner user and if the auction is finished.
						$order_id = $product->get_order_id();

						if ( $order_id ) {
							$order = wc_get_order( $order_id );

							$order_url = $order->get_checkout_payment_url();

							/**
							 * APPLY_FILTERS: yith_wcact_get_order_url
							 *
							 * Filter the order URL when paying for the auction.
							 *
							 * @param string   $redirect_url Redirect URL
							 * @param int      $product_id   Product ID
							 * @param WC_Order $order        Order object
							 *
							 * @return string
							 */
							wp_safe_redirect( apply_filters( 'yith_wcact_get_order_url', $order_url, $product_id, $order ) );
							exit;
						} else {
							$where_redirect = ! empty( $_REQUEST['yith-wcact-pay-won-redirect'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith-wcact-pay-won-redirect'] ) ) : get_option( 'yith_wcact_settings_auction_winner_email_redirect', 'checkout' ); // phpcs:ignore

							switch ( $where_redirect ) {
								case 'checkout':
									$url = wc_get_checkout_url();
									break;

								case 'cart':
									$url = wc_get_cart_url();
									break;

								case 'product':
									$url = get_permalink( $product_id );
									break;

								default:
									$url = wc_get_checkout_url();
							}

							if ( 'no' === get_option( 'yith_wcact_settings_tab_auction_show_add_to_cart_in_auction_product', 'no' ) ) {
								WC()->cart->empty_cart();
							}

							WC()->cart->add_to_cart(
								$product_id,
								1,
								0,
								array(),
								array(
									'yith_auction_data' => array(
										'won-auction' => true,
									),
								)
							);

							wc_add_to_cart_message( array( $product_id => 1 ), true );

							/**
							 * APPLY_FILTERS: yith_wcact_get_checkout_url
							 *
							 * Filter the URL to redirect to pay the won auction.
							 *
							 * @param string $url        URL
							 * @param int    $product_id Product ID
							 *
							 * @return string
							 */
							wp_safe_redirect( apply_filters( 'yith_wcact_get_checkout_url', $url, $product_id ) );
							exit;
						}
					} else {
						if ( $product->is_closed() ) {
							wc_add_notice( esc_html__( 'You cannot buy this product', 'yith-auctions-for-woocommerce' ), 'error' );
						}
					}
				}
			}
		}

		/**
		 * Disable pay now button
		 * Set product as paid when the order is on processing or completed.
		 *
		 * @param int $order_id Order id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function disable_pay_now_button( $order_id ) {
			$order = wc_get_order( $order_id );

			foreach ( $order->get_items() as $item ) {
				$_product = $item->get_product();

				if ( $_product && 'auction' === $_product->get_type() ) {
					$_product->set_auction_paid_order( true );
					$_product->save();
				}
			}
		}

		/**
		 * Add a redirect after login
		 * Redirect after login if click in pay now email and the user is not logged in
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.2
		 */
		public function add_redirect_after_login() {
			if ( ! empty( $_GET['redirect_after_login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
					<input type="hidden" name="redirect" value="<?php echo esc_url_raw( wp_unslash( $_GET['redirect_after_login'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
				<?php
			}
		}

		/**
		 * Create an order on fly for auction products
		 *
		 * @param WC_Product $product Auction product.
		 * @param WP_User    $user Winner user.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0.0
		 */
		public function create_order( $product, $user ) {
			/**
			 * APPLY_FILTERS: yith_wcact_automatically_create_order
			 *
			 * Filter whether to automatically create the order for the auction winner.
			 *
			 * @param bool       $create_order Whether to automatically create order or not
			 * @param WC_Product $product      Product object
			 *
			 * @return bool
			 */
			if ( 'auction' === $product->get_type() && ( apply_filters( 'yith_wcact_automatically_create_order', false, $product ) || 'yes' === get_option( 'yith_wcact_auction_winner_create_order', 'no' ) ) ) {
				$order_id = ywcact_create_order( $product, $user->ID );

				if ( $order_id ) {
					$product->set_order_id( $order_id );
					$product->save();

					ywcact_logs( 'The order ' . $order_id . ' is created' );

					/**
					 * DO_ACTION: yith_wcact_after_automatically_create_order
					 *
					 * Allow to fire some action after creating the order for the auction winner.
					 *
					 * @param int        $order_id Order ID
					 * @param WC_Product $product  Product object
					 * @param WP_User    $user     User object
					 */
					do_action( 'yith_wcact_after_automatically_create_order', $order_id, $product, $user );
				}
			}
		}
	}
}

return YITH_Auction_Finish_Auction::get_instance();
