<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Frontend Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class Auction Frontend.
 *
 * @class   YITH_Auctions_Frontend
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_Auction_Frontend' ) ) {

	/**
	 * Class YITH_Auction_Frontend
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auction_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_Auction_Frontend
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_Auction_Frontend
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
			add_action( 'woocommerce_auction_add_to_cart', array( $this, 'print_add_to_cart_template' ) );

			add_action( 'woocommerce_auction_add_to_cart', array( $this, 'add_currency_section' ), 15 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'create_bid_tab' ), 999 );

			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'auction_end_start' ), 8 );
			add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'change_button_auction_shop' ), 10, 2 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'change_product_price_display' ), 10, 2 );
			add_filter( 'woocommerce_empty_price_html', array( $this, 'set_empty_product_price' ), 10, 2 );
			add_filter( 'woocommerce_free_price_html', array( $this, 'set_empty_product_price' ), 10, 2 );

			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'auction_badge_shop' ), 10 );

			add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'add_badge_single_product' ) );

			add_action( 'woocommerce_login_form_end', array( $this, 'add_redirect_after_login' ) );
			add_action( 'woocommerce_register_form_end', array( $this, 'add_redirect_after_login' ) );

			add_action( 'yith_wcact_auction_end', array( $this, 'auction_end' ) );

			/* == Auction commission fee == */

			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'calculate_auction_commission_fee' ) );
		}

		/**
		 * Auction template
		 *
		 * Add the auction template
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function print_add_to_cart_template() {
			wc_get_template( 'single-product/add-to-cart/auction.php', array(), '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );
		}

		/**
		 * Auction currency section
		 *
		 * Add the auction template
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function add_currency_section() {
			global $product;

			?>
			<div class="yith-wcact-currency">
				<input type="hidden" id="yith_wcact_currency" name="yith_wcact_currency" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>">
				<input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php echo esc_attr( $product->get_id() ); ?>">
			</div>
			<?php
		}

		/**
		 * Bid tab
		 *
		 * Create the "Bid" tab to show the all bids of the product
		 *
		 * @param array $tabs Product tabs.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.0
		 * @return array
		 */
		public function create_bid_tab( $tabs ) {
			global $product;

			if ( $product && 'auction' === $product->get_type() ) {
				// Adds the new tab.
				$tabs['yith-wcact-bid-tab'] = array(
					/**
					 * APPLY_FILTERS: yith_wcact_bid_tab_title
					 *
					 * Filter the title of the Bids tab.
					 *
					 * @param string $tab_title Tab title
					 *
					 * @return string
					 */
					'title'    => apply_filters( 'yith_wcact_bid_tab_title', esc_html__( 'Bids', 'yith-auctions-for-woocommerce' ) ),
					/**
					 * APPLY_FILTERS: yith_wcact_priority_bid_tab
					 *
					 * Filter the priority of the Bids tab.
					 *
					 * @param int $tab_priority Tab priority
					 *
					 * @return int
					 */
					'priority' => apply_filters( 'yith_wcact_priority_bid_tab', 1 ),
					'callback' => array( $this, 'bids_content' ),
				);
				// set "tab bid" at the first tab in product tabs.
				$priority = array();

				foreach ( $tabs as $clave => $valor ) {
					$priority[] = $valor['priority'];
				}

				array_multisort( $priority, SORT_ASC, $tabs );
				array_multisort( $priority, SORT_DESC );

				$size = 0;

				foreach ( $tabs as $clave => $valor ) {
					$tabs[ $clave ]['priority'] = $priority[ $size ];

					$size++;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcact_bid_tab
			 *
			 * Filter the array with the product tabs after the Bids tab has been added.
			 *
			 * @param array $tabs Tabs
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_bid_tab', $tabs );
		}

		/**
		 * Template bids content
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.0
		 * @return void
		 */
		public function bids_content() {
			global $product;

			$args = array(
				'product'  => $product,
				'currency' => get_woocommerce_currency(),
			);

			wc_get_template( 'list-bids.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
		}

		/**
		 * Change text button
		 *
		 * Change text Auction button (in shop page)
		 *
		 * @param string     $text button label.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return string
		 */
		public function change_button_auction_shop( $text, $product ) {
			if ( $product && 'auction' === $product->get_type() && ! $product->is_closed() ) {
				return esc_html__( 'Bid now', 'yith-auctions-for-woocommerce' );
			}

			return $text;
		}

		/**
		 * Change display product
		 *
		 * Change text product price in shop and cart item
		 *
		 * @param string     $price Auction price.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.11
		 * @return string
		 */
		public function change_product_price_display( $price, $product ) {
			/**
			 * APPLY_FILTERS: yith_wcact_load_acution_price_html
			 *
			 * Filter whether to show the auction price.
			 *
			 * @param bool $show_auction_price Whether to show the auction price or not.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_load_acution_price_html', false ) || is_shop() || is_product() || is_archive() ) {
				if ( $product && 'auction' === $product->get_type() ) {
					$auction_secret = $product->get_auction_sealed();

					if ( 'yes' === $auction_secret ) {
						$price_html = '<span class="ywcact-sealed-auction">' . esc_html__( 'This is a sealed auction.', 'yith-auctions-for-woocommerce' ) . '</span><span>' . esc_html__( 'Current bid is hidden.', 'yith-auctions-for-woocommerce' ) . '</span>';
					} else {
						if ( $product->is_start() && ! $product->is_closed() && ! (bool) $product->get_is_closed_by_buy_now() ) {
							/**
							 * APPLY_FILTERS: yith_wcact_frontend_current_bid_message
							 *
							 * Filter the current bid label.
							 *
							 * @param string     $current_bid_label Current bid label
							 * @param string     $price             Current bid price
							 * @param WC_Product $product           Product object
							 *
							 * @return string
							 */
							/* translators: %s Current bid price */
							$price_html = apply_filters( 'yith_wcact_frontend_current_bid_message', sprintf( esc_html__( 'Current bid: %s', 'yith-auctions-for-woocommerce' ), $price ), $price, $product );

							if ( is_product() && ! did_action( 'woocommerce_after_single_product_summary' ) ) { // Prevent show this information on related products.
								$show_commission_fee_in_product_page = 'all' === get_option( 'yith_wcact_general_show_commission_fee', 'all' );

								/**
								 * APPLY_FILTERS: yith_wcact_get_commission_fee_display_for_product
								 *
								 * Filter whether to display the commission fee for the auction.
								 *
								 * @param bool       $show_commission_fee Whether to display the commission fee or not
								 * @param WC_Product $product             Product object
								 * @param string     $price               Current bid price
								 *
								 * @return bool
								 */
								if ( $show_commission_fee_in_product_page && apply_filters( 'yith_wcact_get_commission_fee_display_for_product', true, $product, $price ) ) {
									$commision_fee_display = yith_wcact_get_commission_fee_display( $product );

									if ( $commision_fee_display ) {
										$price_html .= '<br/>' . $commision_fee_display;
									}
								}

								/**
								 * APPLY_FILTERS: yith_wcact_show_auction_type_text
								 *
								 * Filter whether to show the auction type in the product page.
								 *
								 * @param bool $show_auction_type Whether to show the auction type or not
								 *
								 * @return bool
								 */
								if ( apply_filters( 'yith_wcact_show_auction_type_text', true ) && $product->get_auction_type() && 'normal' !== $product->get_auction_type() ) {
									$price_html .= '<br/>' . esc_html__( 'This is a reverse auction.', 'yith-auctions-for-woocommerce' );
								}
							}
						} else {
							$price_html = '';
						}
					}

					/**
					 * APPLY_FILTERS: yith_wcact_auction_price_html
					 *
					 * Filter the price HTML for the auction product.
					 *
					 * @param string     $price_html Price HTML
					 * @param WC_Product $product    Product object
					 * @param string     $price      Current bid price
					 *
					 * @return string
					 */
					$price = apply_filters( 'yith_wcact_auction_price_html', $price_html, $product, $price );
				}
			}

			return $price;
		}

		/**
		 * Change empty product price
		 *
		 * If not product price, set product price = 0
		 *
		 * @param string     $price Auction price.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return string
		 */
		public function set_empty_product_price( $price, $product ) {
			if ( $product && 'auction' === $product->get_type() ) {
				$price = wc_price( 0 );
			}

			return $price;
		}

		/**
		 * Badge single product
		 *
		 * Add a badge if product type is: auction (in simple product)
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @param  string $output image section on single product page.
		 * @since  1.0
		 * @return string
		 */
		public function add_badge_single_product( $output ) {
			global $product;

			if ( 'yes' === get_option( 'yith_wcact_show_badge_product_page', 'yes' ) && $product && 'auction' === $product->get_type() ) {
				$img = get_option( 'yith_wcact_appearance_button', '' );
				$img = ( $img ) ? $img : YITH_WCACT_ASSETS_URL . '/images/badge.svg';

				/**
				 * APPLY_FILTERS: yith_wcact_show_max_winner_badge_max_bidder
				 *
				 * Filter whether to show the auction winner badge.
				 *
				 * @param bool $show_auction_winner_badge Whether to show the auction winner badge or not
				 *
				 * @return bool
				 */
				if ( 'yes' === get_option( 'yith_wcact_auction_winner_show_winner_badge', 'no' ) && $product->is_closed() || apply_filters( 'yith_wcact_show_max_winner_badge_max_bidder', false ) ) {
					$instance   = YITH_Auctions()->bids;
					$max_bidder = $instance->get_max_bid( $product->get_id() );

					$user = wp_get_current_user();

					if ( $max_bidder && $user && $user->exists() && (int) $max_bidder->user_id === (int) $user->ID ) {
						$wimg = get_option( 'yith_wcact_winner_badge_custom', '' );
						$img  = ( $wimg ) ? $wimg : $img;
					}
				}

				$output .= '<span class="yith-wcact-aution-badge"><img src="' . $img . '"></span>';
			}

			return $output;
		}

		/**
		 *  Add redirect after login
		 *
		 *  Add custom $_GET parameters in form for redirect to single product page.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function add_redirect_after_login() {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'yith-wcact-redirect-my-account' ) ) {
				if ( ! empty( $_GET['redirect_after_login'] ) ) {
					?>
						<input type = "hidden" name = "redirect" value = "<?php echo esc_url_raw( wp_unslash( $_GET['redirect_after_login'] ) ); ?>" />
					<?php
				}
			}

		}

		/**
		 * Badge single product
		 *
		 * Add a badge if product type is: auction (in simple product)
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @param  WC_Cart $cart Cart.
		 * @since  3.0
		 */
		public function calculate_auction_commission_fee( $cart ) {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			$auction_commission_fee_array = array();
			$commission_fee_total         = 0;

			foreach ( $cart->get_cart() as $cart_item ) {
				$product = $cart_item['data'];

				if ( $product && 'auction' === $product->get_type() ) {
					$price          = $cart_item['line_total'];
					$commission_fee = yith_wcact_calculate_commission_fee( $product, $price );

					if ( $commission_fee && $commission_fee['value'] > 0 ) {
						$auction_commission_fee_array[] = $commission_fee;
						$commission_fee_total          += $commission_fee['value'];
					}
				}
			}

			if ( ! empty( $auction_commission_fee_array ) ) {
				if ( count( $auction_commission_fee_array ) > 1 ) {
					$label = get_option( 'yith_wcact_general_multiple_commissions_label', '' );
					$label = ! $label ? yith_wcact_get_label( 'multiple_commissions_fee' ) : $label;
				} else {
					/**
					 * APPLY_FILTERS: yith_wcact_commission_fee_cart_checkout_label
					 *
					 * Filter the label for the commission fee.
					 *
					 * @param bool    $label                        Label
					 * @param array   $auction_commission_fee_array Commission fee data
					 * @param WC_Cart $cart                         Cart object
					 *
					 * @return string
					 */
					$label = apply_filters( 'yith_wcact_commission_fee_cart_checkout_label', $auction_commission_fee_array[0]['label'], $auction_commission_fee_array, $cart );
					$label = ! $label ? yith_wcact_get_label( 'default_commission_fee' ) : $label;
				}

				$cart->add_fee( $label, $commission_fee_total, false, '' );
			}
		}
	}
}
