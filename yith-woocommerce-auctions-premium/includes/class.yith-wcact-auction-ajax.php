<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Auction_Ajax Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Auction_Ajax' ) ) {
	/**
	 * YITH_WCACT_Auction_Ajax
	 *
	 * @since 1.0.0
	 */
	class YITH_WCACT_Auction_Ajax {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Auction_Ajax
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Auction_Ajax
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
		 * Constructor
		 *
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function __construct() {
			add_action( 'wp_ajax_yith_wcact_add_bid', array( $this, 'yith_wcact_add_bid' ) );
			add_action( 'wp_ajax_nopriv_yith_wcact_add_bid', array( $this, 'redirect_to_my_account' ) );
		}

		/**
		 * Redirect to user (My account)
		 *
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function redirect_to_my_account() {
			if ( ! is_user_logged_in() ) {
				if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'add-bid' ) ) {
					/**
					 * APPLY_FILTERS: yith_wcact_redirect_url
					 *
					 * Filter the URL to redirect when a guest user tries to place a bid.
					 *
					 * @param string $redirect_url Redirect URL
					 *
					 * @return string
					 */
					$account = apply_filters( 'yith_wcact_redirect_url', wc_get_page_permalink( 'myaccount' ) );

					if ( isset( $_POST['bid'] ) && isset( $_POST['product'] ) ) {
						$product_id = sanitize_key( wp_unslash( $_POST['product'] ) );
						$bid        = sanitize_key( wp_unslash( $_POST['bid'] ) );

						/**
						 * APPLY_FILTERS: yith_wcact_get_product_permalink_redirect_to_my_account
						 *
						 * Filter the product URL to redirect when a guest user tries to place a bid.
						 *
						 * @param string $redirect_url Redirect URL
						 * @param int    $product_id   Product ID
						 *
						 * @return string
						 */
						$get_product_permalink = apply_filters( 'yith_wcact_get_product_permalink_redirect_to_my_account', rawurlencode( get_permalink( $product_id ) ), $product_id );
						$url_to_redirect       = add_query_arg(
							array(
								'redirect_after_login' => $get_product_permalink,
								'_wpnonce'             => wp_create_nonce( 'yith-wcact-redirect-my-account' ),
							),
							$account
						);

						wc_add_notice( esc_html__( 'You must be logged before bidding on a product', 'yith-auctions-for-woocommerce' ), 'error' );

						$array = array(
							'product_id' => $product_id,
							'bid'        => $bid,
							'url'        => $url_to_redirect,
						);
					}

					wp_send_json( $array );
				}

				die();
			}
		}
	}
}
