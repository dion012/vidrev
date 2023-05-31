<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Frontend_Premium Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class Auction Frontend Premium
 *
 * @class   YITH_Auctions_Frontend
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_Auction_Frontend_Premium' ) ) {
	/**
	 * Class YITH_Auctions_Frontend
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auction_Frontend_Premium extends YITH_Auction_Frontend {

		/**
		 * Main Popup Instance
		 *
		 * @var   WC_Product_Auction
		 * @since 2.0
		 */
		public $popup = null;
		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_catalog_orderby', array( $this, 'sort_auctions' ) );
			add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'ordering_auction' ) );

			/* == Cart == */
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10 );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_yith_auction_data' ), 10, 2 );
			add_action( 'pre_get_posts', array( $this, 'modify_query_loop' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'finish_auction' ), 10, 2 );

			/*== Product Page == */
			add_filter( 'yith_wcact_before_form_add_to_cart', array( $this, 'check_closed_for_buy_now' ), 10, 2 );
			add_action( 'yith_wcact_in_to_form_add_to_cart', array( $this, 'print_auction_condition' ), 10 );
			add_action( 'yith_wcact_in_to_form_add_to_cart', array( $this, 'if_reserve_price' ), 15 );
			add_action( 'yith_wcact_in_to_form_add_to_cart', array( $this, 'check_if_max_bid_and_reserve_price' ), 20 );
			add_action( 'yith_wcact_auction_before_set_bid', array( $this, 'add_auction_timeleft' ), 10, 2 );
			add_action( 'yith_wcact_after_add_button_bid', array( $this, 'add_button_buy_now' ), 20 );
			add_filter( 'yith_wcact_actual_bid_add_value', array( $this, 'bid_value_step_for_plugin_buttons' ) );

			/* == Follower list == */
			add_action( 'yith_wcact_after_add_to_cart_form', array( $this, 'add_followers_form' ) );
			add_action( 'wp_loaded', array( $this, 'add_to_followers_list' ), 90 );
			/* == Watchlist feature ==*/
			add_action( 'yith_wcact_after_add_button_bid', array( $this, 'add_watchlist_button' ), 50 );
			add_action( 'yith_wcact_after_no_start_auction', array( $this, 'add_watchlist_button' ), 50 );
			add_action( 'init', array( $this, 'add_to_watchlist_list' ) );
			add_action( 'init', array( $this, 'remove_from_watchlist' ) );
			add_action( 'init', array( $this, 'add_watchlist_notice' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_style' ), 99 );

			/* == Stripe integration show message == */
			add_action( 'woocommerce_credit_card_form_end', array( $this, 'add_auction_notice_on_new_card_stripe' ) );

			$this->popup = new YITH_WCACT_Popup();

			parent::__construct();
		}

		/**
		 * Enqueue Scripts
		 *
		 * Register and enqueue scripts for Frontend
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.9
		 * @return void
		 */
		public function enqueue_scripts() {
			global $post, $wp_locale;

			$product     = isset( $post ) && ! empty( $post ) ? wc_get_product( $post->ID ) : false;
			$format_date = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
			$format_time = get_option( 'yith_wcact_general_time_format', 'h:i:s' );

			$format = $format_date . ' ' . $format_time;

			$date_params = array(
				'format'                => $format,
				'month'                 => $wp_locale->month,
				'month_abbrev'          => $wp_locale->month_abbrev,
				'meridiem'              => $wp_locale->meridiem,
				/**
				 * APPLY_FILTERS: yith_wcact_show_time_in_customer_time
				 *
				 * Filter whether to show time in customer time.
				 *
				 * @param bool $show_time_in_customer_time Whether to show time in customer time or not
				 *
				 * @return bool
				 */
				'show_in_customer_time' => apply_filters( 'yith_wcact_show_time_in_customer_time', true ),
				/**
				 * APPLY_FILTERS: yith_wcact_actual_bid_add_value
				 *
				 * Filter the increment value when adding a new bid.
				 *
				 * @param int $bid_value Bid value
				 *
				 * @return int
				 */
				'actual_bid_add_value'  => apply_filters( 'yith_wcact_actual_bid_add_value', 1 ),
			);

			$countdown_color = get_option( 'yith_wcact_countdown_color', array() );

			// Localize scripts for ajax call.
			wp_localize_script(
				'yith-wcact-frontend-js-premium',
				'ywcact_frontend_object',
				array(
					'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
					'live_auction_product_page'     => 'yes' === get_option( 'yith_wcact_ajax_refresh_auction_product_page', 'no' ) ? (int) get_option( 'yith_wcact_settings_live_auction_product_page', 0 ) * 1000 : 0,
					'add_bid'                       => wp_create_nonce( 'add-bid' ),
					'bid_empty_error'               => esc_html__( 'Please insert a value for your bid.', 'yith-auctions-for-woocommerce' ),
					'update_list_bids'              => wp_create_nonce( 'update-list-bids' ),
					'small_blocks_background_color' => isset( $countdown_color['blocks'] ) ? $countdown_color['blocks'] : 'rgb(255,255,255)',
					/**
					 * APPLY_FILTERS: yith_wcact_ajax_activated
					 *
					 * Filter whether the AJAX is activated.
					 *
					 * @param bool $ajax_activated Whether the AJAX is activated or not
					 *
					 * @return bool
					 */
					'ajax_activated'                => apply_filters( 'yith_wcact_ajax_activated', true ),
				)
			);

			// localize script for date format.
			wp_localize_script( 'yith-wcact-frontend-js-premium', 'date_params', $date_params );

			/**
			 * APPLY_FILTERS: yith_wcact_load_script_everywhere
			 *
			 * Filter whether to load the script everywhere.
			 *
			 * @param bool $load_script Whether to load the script everywhere
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_load_script_everywhere', false ) || is_shop() || is_archive() ) {
				/* === CSS === */
				wp_enqueue_style( 'yith-wcact-frontend-css' );
				/* === Script === */
				wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
			}

			/**
			 * APPLY_FILTERS: yith_wcact_load_script_widget_everywhere
			 *
			 * Filter whether to load the widget script everywhere.
			 *
			 * @param bool $load_script Whether to load the widget script everywhere
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_load_script_widget_everywhere', false ) || ( is_active_widget( false, false, 'yith_woocommerce_auctions', true ) ||
				is_active_widget( false, false, 'yith-wcact-auction-watchlist', true ) ||
				is_active_widget( false, false, 'yith-woocommerce-auctions-future', true ) ||
				is_active_widget( false, false, 'yith-woocommerce-auctions-ended', true )
				) ) {
				/* === CSS === */
				wp_enqueue_style( 'yith-wcact-widget-css' );

				if ( ! wp_script_is( 'yith_wcact_frontend_shop_premium' ) && ( ! $product || ( $product && 'auction' !== $product->get_type() ) ) ) {
					wp_enqueue_script( 'yith_wcact_frontend_shop_premium', YITH_WCACT_ASSETS_URL . 'js/frontend_shop-premium.js', array( 'jquery', 'jquery-ui-sortable' ), YITH_WCACT_VERSION, true );
					wp_localize_script(
						'yith_wcact_frontend_shop_premium',
						'object',
						array(
							'ajaxurl' => admin_url( 'admin-ajax.php' ),
							'add_bid' => wp_create_nonce( 'add-bid' ),
						)
					);

					wp_localize_script( 'yith_wcact_frontend_shop_premium', 'date_params', $date_params );
					wp_enqueue_style( 'yith-wcact-frontend-css' );
				}
			}

			if ( is_product() ) {
				$product = wc_get_product( $post->ID );

				if ( $product && 'auction' === $product->get_type() ) {
					/* === CSS === */
					wp_enqueue_style( 'yith-wcact-frontend-css' );
					wp_enqueue_style( 'dashicons' );
					/* === Script === */
					wp_enqueue_script( 'yith-wcact-frontend-js-premium' );
				}
			}

			$endpoint = YITH_Auctions()->endpoint;

			if ( 'my-auction' === $endpoint->get_current_endpoint() ) {
				wp_enqueue_script( 'yith_wcact_frontend_endpoint', YITH_WCACT_ASSETS_URL . '/js/frontend-endpoint-premium.js', array( 'jquery', 'jquery-ui-sortable' ), YITH_WCACT_VERSION, true );
				wp_localize_script(
					'yith_wcact_frontend_endpoint',
					'yith_wcact_frontend_endpoint',
					array(
						'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
						'time_check'                    => 'yes' === get_option( 'yith_wcact_ajax_refresh_auction_my_acutions_page', 'no' ) ? get_option( 'yith_wcact_settings_live_auction_my_auctions', 0 ) * 1000 : 0,
						'add_bid'                       => wp_create_nonce( 'add-bid' ),
						'update_template'               => wp_create_nonce( 'update-template' ),
						'small_blocks_background_color' => isset( $countdown_color['blocks'] ) ? $countdown_color['blocks'] : 'rgb(255,255,255)',
					)
				);
				wp_enqueue_style( 'yith-wcact-frontend-css' );
			}

			/**
			 * DO_ACTION: yith_wcact_enqueue_fontend_scripts
			 *
			 * Allow to fire some action after the scripts has been enqueued.
			 */
			do_action( 'yith_wcact_enqueue_fontend_scripts' );
		}

		/**
		 * Enqueue Custom Style
		 *
		 * Enqueue style dynamic generated by the plugin
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0.0
		 * @return void
		 */
		public function enqueue_custom_style() {
			$custom_css = $this->build_custom_css();

			if ( $custom_css ) {
				$handle = wp_style_is( 'yith-wcact-frontend-css' ) ? 'yith-wcact-frontend-css' : false;

				if ( $handle ) {
					wp_add_inline_style( $handle, $custom_css );
				}
			}
		}

		/**
		 * Sort Auction
		 *
		 * ​Add to WooCommerce sorting select (in shop page) Sort auctions
		 *
		 * @param array $options Sorting options.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return array
		 */
		public function sort_auctions( $options ) {
			$sort    = array(
				'auction_asc'  => esc_html__( 'Sort auctions by end date (asc)', 'yith-auctions-for-woocommerce' ),
				'auction_desc' => esc_html__( 'Sort auctions by end date (desc)', 'yith-auctions-for-woocommerce' ),
			);
			$options = array_merge( $options, $sort );

			return $options;
		}

		/**
		 * Sort Auction
		 *
		 * ​Add to WooCommerce sorting select (in shop page) Sort auctions
		 *
		 * @param array $args Args.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return array
		 */
		public function ordering_auction( $args ) {
			$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'auction_asc' === $orderby_value ) {
				$args['orderby']  = 'meta_value';
				$args['order']    = 'ASC';
				$args['meta_key'] = '_yith_auction_to'; // phpcs:ignore WordPress.DB.SlowDBQuery
			}

			if ( 'auction_desc' === $orderby_value ) {
				$args['orderby']  = 'meta_value';
				$args['order']    = 'DESC';
				$args['meta_key'] = '_yith_auction_to'; // phpcs:ignore WordPress.DB.SlowDBQuery
			}

			/**
			 * APPLY_FILTERS: yith_wcact_ordering_auction
			 *
			 * Filter the array with the arguments to sort auctions.
			 *
			 * @param array  $args          Ordering args
			 * @param string $orderby_value Order by value
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_ordering_auction', $args, $orderby_value );
		}

		/**
		 * Auction end
		 *
		 * ​Show the Auction end or show the auction start if the auction start after today's date (in shop page)
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function auction_end_start() {
			global $product;

			/**
			 * APPLY_FILTERS: yith_wcact_get_auction_product
			 *
			 * Filter the auction product.
			 *
			 * @param WC_Product_Auction_Premium $product Auction product
			 *
			 * @return WC_Product_Auction_Premium
			 */
			$product = apply_filters( 'yith_wcact_get_auction_product', $product );

			if ( $product && 'auction' === $product->get_type() ) {
				if ( ! $product->is_in_stock() || $product->is_closed() || $product->get_is_closed_by_buy_now() ) {
					echo '<div class=" auction_end_start ywcact-auction-ended-loop" >';
					echo '<span class="ywcact_auction_end_start_label">' . esc_html__( 'Auction ended', 'yith-auctions-for-woocommerce' ) . '</span>';
					echo '</div>';
				} else {
					$auction_start = $product->get_start_date();
					$auction_end   = $product->get_end_date();
					$date          = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

					$time_zone = get_option( 'yith_wcact_general_time_zone', '' );

					if ( $date < $auction_start ) {
						$this->timeleft_loop( $product, $auction_end, $date );

						echo '<div class="auction_end_start">';

						echo '<span class="ywcact_auction_end_start_label">' . sprintf( esc_html_x( 'Auction start:', 'Auction ends: 10 Jan 2016 10:00', 'yith-auctions-for-woocommerce' ) ) . '</span>';
						echo '<span class="date_auction" data-yith-product="' . esc_attr( $product->get_id() ) . '" data-yith-auction-time="' . esc_attr( $auction_start ) . '"></span><span>' . wp_kses_post( $time_zone ) . '</span>';
						echo '</div>';
					} else {
						if ( ! empty( $auction_end ) && ! $product->is_closed() && ! $product->get_is_closed_by_buy_now() ) {
							$this->timeleft_loop( $product, $auction_end, $date, 'yith_end_type' );
							echo '<div class="auction_end_start">';
							echo '<span class="ywcact_auction_end_start_label">' . sprintf( esc_html_x( 'Auction ends:', 'Auction ends: 10 Jan 2016 10:00', 'yith-auctions-for-woocommerce' ) ) . '</span>';
							echo '<span class="date_auction" data-yith-product="' . esc_attr( $product->get_id() ) . '" data-yith-auction-time="' . esc_attr( $auction_end ) . '"></span><span>' . wp_kses_post( $time_zone ) . '</span>';

							/**
							 * DO_ACTION: yith_wcact_add_fields_after_end_label_inside_div
							 *
							 * Allow to render some content after the auction data in the loop.
							 *
							 * @param WC_Product $product Product object
							 */
							do_action( 'yith_wcact_add_fields_after_end_label_inside_div', $product );
							echo '</div>';
						}
					}
				}

				/**
				 * DO_ACTION: yith_wcact_auction_end_start
				 *
				 * Allow to render some content after the auction data in the loop.
				 *
				 * @param WC_Product $product Product object
				 */
				do_action( 'yith_wcact_auction_end_start', $product );
			}
		}

		/**
		 * Change text button
		 *
		 * Change text Auction button (in shop page)
		 *
		 * @param string     $text Text.
		 * @param WC_Product $product Product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 *
		 * @return string
		 */
		public function change_button_auction_shop( $text, $product ) {
			$product = apply_filters( 'yith_wcact_get_auction_product', $product );

			if ( 'auction' === $product->get_type() ) {
				if ( ! $product->is_closed() && ! $product->get_is_closed_by_buy_now() ) {
					/**
					 * APPLY_FILTERS: yith_wcact_change_button_auction_shop_text
					 *
					 * Filter the text for the add to cart button in the shop page for auction products.
					 *
					 * @param string     $text    Button text
					 * @param WC_Product $product Product
					 * @param string     $text    Original text
					 *
					 * @return string
					 */
					$text = apply_filters( 'yith_wcact_change_button_auction_shop_text', esc_html__( 'Bid now', 'yith-auctions-for-woocommerce' ), $product, $text );
				} else {
					$text = apply_filters( 'yith_wcact_change_button_auction_shop_text', esc_html__( 'View', 'yith-auctions-for-woocommerce' ), $product, $text );
				}
			}

			return $text;
		}

		/**
		 * Badge Shop
		 *
		 * Add a badge if product type is: auction (in shop page)
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function auction_badge_shop() {
			global $product;

			if ( $product && 'auction' === $product->get_type() && 'yes' === get_option( 'yith_wcact_show_auction_badge', 'yes' ) ) {
				$img = get_option( 'yith_wcact_appearance_button' );
				$img = ( $img ) ? $img : YITH_WCACT_ASSETS_URL . '/images/badge.svg';

				echo '<span class="yith-wcact-aution-badge"><img src="' . esc_url( $img ) . '"></span>';
			}
		}

		/**
		 *  Add cart item data
		 *
		 *  Create a new array yith_auction_data in $cart_item_data
		 *
		 * @param array $cart_item_data Cart item data.
		 * @param int   $product_id     Product ID.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return $cart_tem_data
		 */
		public function add_cart_item_yith_auction_data( $cart_item_data, $product_id ) {
			/**
			 * APPLY_FILTERS: yith_wcact_auction_product_id
			 *
			 * Filter the auction product ID.
			 *
			 * @param int $product_id Auction product ID
			 *
			 * @return int
			 */
			$product_id   = apply_filters( 'yith_wcact_auction_product_id', $product_id );
			$terms        = get_the_terms( $product_id, 'product_type' );
			$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

			if ( 'auction' === $product_type && ! isset( $cart_item_data['yith_auction_data'] ) ) {
				$cart_item_data['yith_auction_data'] = array(
					'buy-now' => true,
				);
			}

			return $cart_item_data;
		}

		/**
		 *  Change price in cart item
		 *
		 *  If the product_type = 'auction' and click in buy_now change price to buy_now_price
		 *  This code also check if the product is a fee product, change the price.
		 *
		 * @param array $cart_item_data Cart item data.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return $cart_tem_data
		 */
		public function add_cart_item( $cart_item_data ) {
			// Check if add buy now product.
			if ( isset( $cart_item_data['yith_auction_data'] ) && isset( $cart_item_data['yith_auction_data']['buy-now'] ) ) {
				$product  = apply_filters( 'yith_wcact_get_auction_product', $cart_item_data['data'] );
				$no_added = false;

				if ( ! $product->is_closed() ) {
					$buy_now_price = $product->get_buy_now();

					if ( ! $buy_now_price ) {
						$no_added = true;
					}

					$product->set_price( $buy_now_price );
					$cart_item_data['data'] = $product;
				} else {
					$user_id = get_current_user_id();

					if ( $user_id ) {
						$instance             = YITH_Auctions()->bids;
						$auction_product_type = $product->get_auction_type();
						$max_bid              = $auction_product_type && 'reverse' === $auction_product_type ? $instance->get_min_bid( $product->get_id() ) : $instance->get_max_bid( $product->get_id() );

						if ( ! $max_bid || (int) $max_bid->user_id !== (int) $user_id ) {
							$no_added = true;
						}
					} else {
						$no_added = true;
					}
				}

				if ( $no_added ) {
					wc_add_notice( esc_html__( 'You cannot purchase this product because it is an Auction!', 'yith-auctions-for-woocommerce' ), 'error' );

					return false;
				}
			}

			// Check fee product section.
			if ( isset( $cart_item_data['yith_wcact_if_fee_product'] ) && isset( $cart_item_data['yith_wcact_fee_value'] ) ) {
				$product = apply_filters( 'yith_wcact_get_auction_product', $cart_item_data['data'] );
				$product->set_price( $cart_item_data['yith_wcact_fee_value'] );
				$cart_item_data['data'] = $product;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_add_cart_item
			 *
			 * Filter the array with the cart item data for the auction product.
			 *
			 * @param array $cart_item_data Cart item data
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_add_cart_item', $cart_item_data );
		}

		/**
		 *  Load cart from session
		 *
		 *  Change auction or fee product price on session.
		 *
		 * @param array $session_data Session data.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return $cart_tem_data
		 */
		public function get_cart_item_from_session( $session_data ) {
			if ( isset( $session_data['yith_auction_data'] ) && isset( $session_data['yith_auction_data']['buy-now'] ) ) {
				$product       = $session_data['data'];
				$buy_now_price = $product->get_buy_now();
				$product->set_price( $buy_now_price );
				$session_data['data'] = $product;
			}

			if ( isset( $session_data['yith_wcact_if_fee_product'] ) && isset( $session_data['yith_wcact_fee_value'] ) ) {
				$product = $session_data['data'];
				$product->set_price( $session_data['yith_wcact_fee_value'] );
				$session_data['data'] = $product;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_get_cart_item_from_session
			 *
			 * Filter the array with the session data for the auction product.
			 *
			 * @param array $session_data Session data
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_get_cart_item_from_session', $session_data );
		}

		/**
		 *  Modify main query loop
		 *
		 *  Modify the shop query loop in order to hide auction products based on General options
		 *
		 * @param WP_Query $q the query.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function modify_query_loop( $q ) {
			if ( ! is_admin() && ( is_post_type_archive( 'product' ) || is_product_category() ) && $q->is_main_query() ) {
				if ( 'no' === get_option( 'yith_wcact_show_auctions_shop_page', 'yes' ) ) {
					$taxquery = $q->get( 'tax_query' );

					if ( is_array( $taxquery ) ) {
						$taxquery[] = array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => 'auction',
							'operator' => 'NOT IN',
						);
						$q->set( 'tax_query', $taxquery );
					}
				} else {
					$meta            = array();
					$terms           = array();
					$out_of_stock    = 'yes' === get_option( 'yith_wcact_hide_auctions_out_of_stock', 'no' );
					$hide_closed     = 'yes' === get_option( 'yith_wcact_hide_auctions_closed', 'no' );
					$hide_no_started = 'yes' === get_option( 'yith_wcact_hide_auctions_not_started', 'no' );

					$auction_status_ids = yith_wcact_get_auction_status_term_ids();

					$q->get( 'meta_query' );

					if ( $out_of_stock ) {
						$meta[] = array(
							'key'     => '_stock_status',
							'value'   => 'outofstock',
							'compare' => '!=',
						);
					}

					if ( ! empty( $meta ) ) {
						if ( count( $meta ) > 1 ) {
							$meta = array_merge(
								array(
									'relation' => 'AND',
								),
								$meta
							);
						}

						$meta_auction = array(
							'relation' => 'OR',
							array(
								'key'     => '_yith_auction_to',
								'compare' => 'NOT EXISTS',
							),
							$meta,
						);

						$q->set( 'meta_query', $meta_auction );
					}

					/* == TAX QUERY SECTION */
					if ( $hide_closed ) {
						$terms[] = $auction_status_ids['finished'];
					}

					if ( $hide_no_started ) {
						$terms[] = $auction_status_ids['scheduled'];
					}

					if ( ! empty( $terms ) ) {
						$tax_query   = $q->get( 'tax_query' );
						$tax_query[] = array(
							'taxonomy' => 'yith_wcact_auction_status',
							'field'    => 'term_taxonomy_id',
							'terms'    => $terms,
							'operator' => 'NOT IN',
						);

						$q->set( 'tax_query', $tax_query );
					}
				}
			}
		}

		/**
		 *  Finish auction
		 *
		 *  If the product_type = 'auction', //The auction end because the user click in buy_now and place order
		 *
		 * @param int     $order_id Order id.
		 * @param WP_Post $post Post id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function finish_auction( $order_id, $post ) {
			$order = wc_get_order( $order_id );

			foreach ( $order->get_items() as $item ) {
				$_product = $item->get_product();

				if ( $_product && 'auction' === $_product->get_type() ) {
					$_product->set_stock_status( 'outofstock' );

					if ( ! $_product->is_closed() ) {
						$_product->set_is_closed_by_buy_now( true );
						$_product->update_auction_status( true );
						$_product->save();

						/**
						 * DO_ACTION: yith_wcact_after_set_closed_by_buy_now
						 *
						 * Allow to fire some action after the auction has been closed by 'Buy now'.
						 *
						 * @param WC_Product            $product Product object
						 * @param WC_Order_Item_Product $item Order item object
						 * @param WC_Order              $order Order object
						 */
						do_action( 'yith_wcact_after_set_closed_by_buy_now', $_product, $item, $order );
					}
				}
			}
		}

		/**
		 *  Check if auction is closed for buy now
		 *
		 * @param string     $status Auction status.
		 * @param WC_Product $product Product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 *
		 * @return string
		 */
		public function check_closed_for_buy_now( $status, $product ) {
			$product = apply_filters( 'yith_wcact_get_auction_product', $product );

			if ( $product->get_is_closed_by_buy_now() ) {
				return false;
			}

			return $status;
		}

		/**
		 * Show auction info
		 *
		 * @param WC_Product $product Product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.14
		 */
		public function check_if_max_bid_and_reserve_price( $product ) {
			$args = array(
				'product'  => $product,
				'currency' => get_woocommerce_currency(),
			);

			wc_get_template( 'max-bidder.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
		}

		/**
		 *  Show auction timeleft on product page
		 *
		 * @param  WC_Product $product Product.
		 * @param  string     $auction_status Auction status.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.14
		 */
		public function add_auction_timeleft( $product, $auction_status = 'started' ) {
			$auction_date   = $product->is_start() ? $product->get_end_date() : $product->get_start_date();
			$datetime       = $auction_date;
			$auction_finish = $datetime ? $datetime : null;
			$date           = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

			$countdown_style = get_option( 'yith_wcact_countdown_style', 'default' );

			$countdown_blocks = ( 'big-blocks' === $countdown_style ) ? 'yith-wcact-blocks' : '';

			$yith_wcact_class = 'yith-wcact-timeleft-' . $countdown_style . ' yith-wcact-timeleft-product-page';

			$time_end_auction  = $auction_finish;
			$color_number      = get_option( 'yith_wcact_customization_countdown_color_numbers', 24 );
			$color_unit        = get_option( 'yith_wcact_customization_countdown_color_unit', 'hours' );
			$time_change_color = strtotime( ( sprintf( '-%d %s', $color_number, $color_unit ) ), (int) $time_end_auction );

			if ( $date > $time_change_color ) {
				$yith_wcact_class .= ' yith-wcact-countdown-last-minute';
				$time_change_color = 0;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_add_auction_timeleft_args
			 *
			 * Filter the array with the arguments for the auction timeleft.
			 *
			 * @param array      $args    Array of arguments
			 * @param WC_Product $product Product
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_wcact_add_auction_timeleft_args',
				array(
					'product'          => $product,
					'product_id'       => $product->get_id(),
					'auction_finish'   => $auction_finish,
					'date'             => $date,
					'last_minute'      => isset( $time_change_color ) ? $auction_finish - $time_change_color : 0,
					'total'            => $auction_finish - time(),
					'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default',
					'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',
				),
				$product
			);

			$show_countdown = ( 'yes' === get_option( 'yith_wcact_show_general_countdown', 'yes' ) );
			$show_end_date  = ( 'yes' === get_option( 'yith_wcact_show_end_date_auctions', 'yes' ) );

			if ( $show_countdown || $show_end_date ) {
				?>
					<div class="yith-wcact-container-timeleft <?php echo 'yith-wcact-container-timeleft-' . esc_attr( $countdown_style ); ?>">
				<?php

				if ( $show_countdown ) {
					?>
					<p for="yith_time_left" class="ywcact-time-left">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcact_time_left_to_start_text
						 *
						 * Filter the text that will state the time left to start the auction.
						 *
						 * @param string $text Text
						 *
						 * @return string
						 */
						echo 'started' === $auction_status ? esc_html__( 'Time left:', 'yith-auctions-for-woocommerce' ) : esc_html( apply_filters( 'yith_wcact_time_left_to_start_text', __( 'Time left to start auction:', 'yith-auctions-for-woocommerce' ) ) );
						?>
					</p>
					<?php

					wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
				}

				if ( $show_end_date && 'started' === $auction_status ) {
					wc_get_template( 'auction-end.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
				}

				?>
					</div>

				<?php
			}
		}

		/**
		 *  Show reserve price and overtime info
		 *
		 * @param WC_Product $product Product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function if_reserve_price( $product ) {
			$args = array(
				'product' => $product,
			);

			wc_get_template( 'reserve_price_and_overtime.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
		}

		/**
		 * Add buy now button
		 *
		 * @param WC_Product_Auction $product Product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function add_button_buy_now( $product ) {
			$buy_now_onoff = yith_wcact_field_onoff_value( 'buy_now_onoff', 'buy_now', $product );
			$buy_now       = $product->get_buy_now();

			/**
			 * APPLY_FILTERS: yith_wcact_show_buy_now_button
			 *
			 * Filter whether to show the 'Buy now' button.
			 *
			 * @param bool       $show_buy_now_button Whether to show the 'Buy now' button or not
			 * @param WC_Product $product             Product object
			 * @param string     $buy_now             Buy now price
			 *
			 * @return bool
			 */
			/**
			 * APPLY_FILTERS: yith_wcact_show_always_buy_now_button
			 *
			 * Filter whether to show always the 'Buy now' button.
			 *
			 * @param bool       $show_buy_now_button Whether to show always the 'Buy now' button or not
			 * @param WC_Product $product             Product object
			 * @param string     $buy_now             Buy now price
			 *
			 * @return bool
			 */
			if ( ( 'reverse' !== $product->get_auction_type() && 'yes' === $buy_now_onoff && ! ! $buy_now && $buy_now > 0 && apply_filters( 'yith_wcact_show_buy_now_button', true, $product, $buy_now ) ) || apply_filters( 'yith_wcact_show_always_buy_now_button', false, $product, $buy_now ) ) {
				$display = true;

				if ( 'yes' === get_option( 'yith_wcact_settings_hide_buy_now_price_exceed', 'no' ) && $product->get_price() >= $buy_now ) {
					$display = false;
				} elseif ( $display && 'yes' === get_option( 'yith_wcact_settings_hide_buy_now_after_first_bid' ) && YITH_Auctions()->bids->get_max_bid( $product->get_id() ) ) {
					$display = false;
				}

				if ( $display ) {
					?>
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
					<button type="submit" class="auction_add_to_cart_button ywcact-auction-buy-now-button button alt" id="yith-wcact-auction-add-to-cart-button">
					<?php
						/**
						 * APPLY_FILTERS: yith_wcact_get_price_for_customers_buy_now
						 *
						 * Filter the price to 'Buy now' the auction product.
						 *
						 * @param string     $buy_now Buy now price
						 * @param WC_Product $product Product object
						 *
						 * @return string
						 */
						// translators: %s is the 'Buy now' price.
						echo wp_kses_post( sprintf( _x( 'Buy now for %s', 'Purchase it now for $ 50.00', 'yith-auctions-for-woocommerce' ), wc_price( apply_filters( 'yith_wcact_get_price_for_customers_buy_now', $buy_now, $product ) ) ) );
					?>
					</button>
					<?php
				}
			}
		}

		/**
		 *  Display message on product page when auction finnish
		 *
		 * @param WC_Product_Auction $product Auction product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function auction_end( $product ) {
			$instance         = YITH_Auctions()->bids;
			$max_bid          = $instance->get_max_bid( $product->get_id() );
			$show_reason      = 'yes' === get_option( 'yith_wcact_how_auction_ended', 'yes' );
			$no_reserve_price = ! ( $product->has_reserve_price() && $product->get_price() < $product->get_reserve_price() );
			$current_user     = wp_get_current_user();

			$args = array(
				'product'          => $product,
				'show_reason'      => $show_reason,
				'max_bid'          => $max_bid,
				'current_user'     => $current_user,
				'no_reserve_price' => $no_reserve_price,
			);

			if ( $no_reserve_price && $max_bid && $product->is_in_stock() && (int) $current_user->ID === (int) $max_bid->user_id ) { // Winner bidder loading the auction page.
				$stripe_checked = false;

				if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) && 'yes' === get_option( 'yith_wcact_verify_payment_method', 'no' ) ) {
					$tokens = WC_Payment_Tokens::get_customer_tokens( $current_user->ID );

					if ( empty( $tokens ) ) {
						$stripe_checked = true;
					}
				}

				$winner_message = ywcact_generate_content_winner( $product, $current_user );

				$img = get_option( 'yith_wcact_appearance_button', YITH_WCACT_ASSETS_URL . '/images/badge.svg' );

				if ( 'yes' === get_option( 'yith_wcact_auction_winner_show_winner_badge', 'no' ) ) {
					$img = get_option( 'yith_wcact_winner_badge_custom', YITH_WCACT_ASSETS_URL . '/images/icon/winner-logo.svg' );
				}

				$order_id = $product->get_order_id();

				$args['winner_message']     = $winner_message;
				$args['img']                = $img;
				$args['stripe_checked']     = $stripe_checked;
				$args['order_id']           = $order_id && $order_id > 0 ? $order_id : false;
				$args['payment_method_url'] = ! $stripe_checked ? yith_wcact_get_payment_method_url() : '';
			}

			wc_get_template( 'single-product/add-to-cart/auction-ended.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );
		}

		/**
		 * Show form to subscribe to this auction product
		 *
		 * @param WC_Product $product Product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function add_followers_form( $product ) {
			if ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_allow_subscribe' ) ) {
				$display_followers_form = true;
				$current_user_id        = get_current_user_id();

				if ( $current_user_id ) {
					$customer = get_userdata( $current_user_id );

					if ( $product->is_in_followers_list( $customer->data->user_email ) ) {
						$display_followers_form = false;
					}
				}

				/**
				 * APPLY_FILTERS: yith_wcact_display_follow_auction_form
				 *
				 * Filter whether to display the form to follow the auction.
				 *
				 * @param bool $display_followers_form Whether to show the form to follow the auction or not
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcact_display_follow_auction_form', $display_followers_form ) ) {
					?>
					<div class="yith-wcact-watchlist-button">
						<form class="yith-wcact-watchlist" method="post" enctype='multipart/form-data'>
							<p class="yith-wcact-follow-auction"><?php esc_html_e( 'Follow this auction', 'yith-auctions-for-woocommerce' ); ?></p>
							<div class="yith-wcact-watchlist-button">
								<input type="hidden" name="yith-wcact-auction-id" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
								<p>
									<?php
									/**
									 * APPLY_FILTERS: yith_wcact_follow_auction_message
									 *
									 * Filter the message shown in the form to follow the auction.
									 *
									 * @param string $message Message
									 *
									 * @return string
									 */
									echo esc_html( apply_filters( 'yith_wcact_follow_auction_message', __( 'We will keep you updated about this auction', 'yith-auctions-for-woocommerce' ) ) );
									?>
								</p>
								<input type="email" name="yith-wcact-watchlist-input-email" id="yith-wcact-watchlist-email" value="<?php echo esc_attr( ( $current_user_id ) ? $customer->data->user_email : '' ); ?>"
									placeholder="<?php esc_html_e( 'Enter your email', 'yith-auctions-for-woocommerce' ); ?>">
								<input type="submit" class="button button-primary yith-wcact-watchlist"
									value="<?php esc_html_e( 'Stay updated', 'yith-auctions-for-woocommerce' ); ?>">
								<?php

								if ( 'yes' === get_option( 'yith_wcact_show_privacy_field' ) ) {
									/**
									 * APPLY_FILTERS: yith_wcact_privacy_label
									 *
									 * Filter the privacy label.
									 *
									 * @param string $privacy_label Privacy label
									 *
									 * @return string
									 */
									$label = apply_filters( 'yith_wcact_privacy_label', get_option( 'yith_wcact_privacy_checkbox_text', '' ) );

									/**
									 * APPLY_FILTERS: yith_wcact_privacy_required
									 *
									 * Filter whether the privacy checkbox is required in the form to follow the auction.
									 *
									 * @param bool $privacy_required Whether the privacy checkbox is required or not
									 *
									 * @return bool
									 */
									$required = apply_filters( 'yith_wcact_privacy_required', true );
									?>
									<p class="form-row form-row-wide">
										<label for="privacy">
											<input type="checkbox" name="yith-wcact-privacy" id="yith-wcact-privacy" value="yes" <?php checked( isset( $_POST['privacy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?> />
											<?php echo wp_kses_post( $label ); ?> <?php echo $required ? '<span class="required">*</span>' : ''; ?>
										</label>
									</p>
									<?php
								}
								?>
							</div>

						</form>
					</div>
					<?php
				}
			}
		}

		/**
		 *  Validate email and insert into followers product list
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function add_to_followers_list() {
			if ( isset( $_REQUEST['yith-wcact-watchlist-input-email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$email = sanitize_email( wp_unslash( $_REQUEST['yith-wcact-watchlist-input-email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( 0 === strlen( $email ) ) {
					wc_add_notice(
						sprintf(
							esc_html__(
								'The required email field is empty.',
								'yith-auctions-for-woocommerce'
							)
						),
						'error'
					);
				} elseif ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					wc_add_notice(
						sprintf(
							esc_html__(
								'The format of the email address entered for the followers list is not correct.',
								'yith-auctions-for-woocommerce'
							)
						),
						'error'
					);
				} elseif ( 'yes' === get_option( 'yith_wcact_show_privacy_field' ) && ! isset( $_REQUEST['yith-wcact-privacy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					wc_add_notice(
						sprintf(
							esc_html__(
								'You must check the privacy checkbox before following the auction.',
								'yith-auctions-for-woocommerce'
							)
						),
						'error'
					);
				} else {
					$product_id = isset( $_REQUEST['yith-wcact-auction-id'] ) ? intval( $_REQUEST['yith-wcact-auction-id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$product    = wc_get_product( $product_id );

					if ( $product && ! $product->is_in_followers_list( $email ) ) {
						// Create the hash for the customer. Check first is email registered on the database and give the hash.
						$user = get_user_by( 'email', $email );

						if ( ! empty( $user ) ) {
							$hash = get_user_meta( $user->ID, '_yith_wcact_security_hash', true );

							if ( empty( $hash ) ) {
								$hash = wp_generate_password( 12, false );

								update_user_meta( $user->ID, '_yith_wcact_security_hash', $hash );
							}
						} else {
							$hash = wp_generate_password( 12, false );
						}

						$product->add_user_in_followers_list( $email, $hash );

						wc_add_notice(
							sprintf(
								// translators: %s is the email address.
								esc_html__( 'Your email "%s" was successfully added to the followers list.', 'yith-auctions-for-woocommerce' ),
								$email
							),
							'success'
						);

						// Send email successfully follow auction.
						WC()->mailer();

						/**
						 * DO_ACTION: yith_wcact_successfully_follow
						 *
						 * Allow to fire some action when a user follows an auction.
						 *
						 * @param WC_Email $email      Email object
						 * @param int      $product_id Product ID
						 * @param string   $hash       Hash
						 */
						do_action( 'yith_wcact_successfully_follow', $email, $product_id, $hash );
					} else {
						if ( apply_filters( 'yith_wcact_display_follow_auction_form', true ) ) {
							wc_add_notice(
								sprintf(
									// translators: %s is the email address.
									esc_html__( 'Your email "%s" is already in the followers list.', 'yith-auctions-for-woocommerce' ),
									$email
								),
								'error'
							);
						}
					}
				}
			}
		}

		/**
		 *  Print timeleft on loop
		 *
		 * @param WC_Product $product     Product.
		 * @param string     $auction_end Auction end.
		 * @param int        $date        Date.
		 * @param string     $type        Type.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function timeleft_loop( $product, $auction_end, $date, $type = '' ) {
			$show_auction_on_loop = get_option( 'yith_wcact_show_countdown_in_loop', 'no' );
			$auction_date         = $product->is_start() ? $product->get_end_date() : $product->get_start_date();

			/**
			 * APPLY_FILTERS: yith_wcact_display_timeleft_loop
			 *
			 * Filter whether to show the auction countdown in the loop.
			 *
			 * @param bool       $show_auction_on_loop Whether to show the auction countdown in the loop or not
			 * @param WC_Product $product              Product object
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_display_timeleft_loop', 'yes' === $show_auction_on_loop, $product ) ) {
				if ( $type ) {
					$time_end_auction  = $auction_end;
					$color_number      = get_option( 'yith_wcact_customization_countdown_color_numbers', 24 );
					$color_unit        = get_option( 'yith_wcact_customization_countdown_color_unit', 'hours' );
					$time_change_color = strtotime( ( sprintf( '-%d %s', $color_number, $color_unit ) ), (int) $time_end_auction );

					if ( $date > $time_change_color ) {
						$yith_wcact_class  = 'yith-wcact-timeleft-compact yith-wcact-countdown-last-minute';
						$time_change_color = 0;
					}
				}

				/**
				 * APPLY_FILTERS: yith_wcact_timeleft_loop_args
				 *
				 * Filter the array with the arguments for the auction timeleft in the loop.
				 *
				 * @param array $args Array of arguments
				 *
				 * @return array
				 */
				$args = apply_filters(
					'yith_wcact_timeleft_loop_args',
					array(
						'product'          => $product,
						'auction_finish'   => $auction_end,
						'date'             => $date,
						'last_minute'      => isset( $time_change_color ) ? $auction_end - $time_change_color : 0,
						'total'            => $auction_date - $date,
						'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-compact',
						'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',
					)
				);

				?>
				<div class="yith-wcact-timeleft-loop">
				<?php wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Generate CSS code to append to each page, to apply custom style to auction timeleft
		 *
		 * @param array $rules Array of additional rules to add to default ones.
		 * @return string Generated CSS code
		 */
		protected function build_custom_css( $rules = array() ) {
			$generated_style_code = '';

			$countdown_color = get_option( 'yith_wcact_countdown_color', array() );

			if ( empty( $countdown_color ) ) {
				$countdown_color['section'] = '#f5f5f5';
			}

			/**
			 * APPLY_FILTERS: yith_wcact_build_custom_css_loop
			 *
			 * Filter whether to apply the custom CSS rules in the loop.
			 *
			 * @param bool $apply_loop Whether to apply the custom CSS rules in the loop or not
			 *
			 * @return bool
			 */
			if ( ( is_product() && is_array( $countdown_color ) && ! empty( $countdown_color ) ) || apply_filters( 'yith_wcact_build_custom_css_loop', false ) ) {
				$color_section = isset( $countdown_color['section'] ) && ! empty( $countdown_color['section'] ) ? $countdown_color['section'] : '#f5f5f5';
				$color_blocks  = isset( $countdown_color['blocks'] ) && ! empty( $countdown_color['blocks'] ) ? $countdown_color['blocks'] : '#ffffff';
				$color_text    = isset( $countdown_color['text'] ) && ! empty( $countdown_color['text'] ) ? $countdown_color['text'] : '';

				$generated_style_code .= '
                    .yith-wcact-time-left-main{ background-color:' . $color_section . ';}
					.yith-wcact-timeleft.yith-wcact-blocks { background-color:' . $color_blocks . ';}
			        .yith-wcact-timer-auction  { color:' . $color_text . ';}
                ';
			}

			$countdown_finalize_color = get_option( 'yith_wcact_customization_countdown_color_style' );

			if ( $countdown_finalize_color ) {
				$generated_style_code .= '
					.yith-wcact-time-left-main .yith-wcact-countdown-last-minute { color:' . $countdown_finalize_color . ';}
                    .yith-wcact-timeleft-loop .yith-wcact-countdown-last-minute { color:' . $countdown_finalize_color . ';}
                ';
			}

			/**
			 * APPLY_FILTERS: yith_wcact_build_custom_css_rules
			 *
			 * Filter the custom CSS rules generated.
			 *
			 * @param string $generated_style_code Generated CSS rules
			 * @param array  $countdown_color      Countdown color options
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcact_build_custom_css_rules', $generated_style_code, $countdown_color );
		}

		/**
		 *  Print Auction condition
		 *
		 * @param WC_Product $product Product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function print_auction_condition( $product ) {
			$condition = $product->get_item_condition();

			if ( $product && 'auction' === $product->get_type() && 'yes' === get_option( 'yith_wcact_show_item_condition', 'no' ) && $condition ) {
				?>
					<div class="yith-wcact-item-condition">
					<?php
					// translators: %1$s is the auction condition.
					echo esc_html( sprintf( __( 'Condition: %1$s', 'yith-auctions-for-woocommerce' ), $condition ) );
					?>
					</div>
				<?php
			}
		}

		/**
		 *  Print watchlist button
		 *
		 * @param WC_Product $product Product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function add_watchlist_button( $product ) {
			/**
			 * APPLY_FILTERS: yith_wcact_show_watchlist_button_on_product
			 *
			 * Filter whether to show the button to add to watchlist in the auction product page.
			 *
			 * @param bool       $show_watchlist_button Whether to show the button to add to watchlist in the auction product page
			 * @param WC_Product $product               Product object
			 *
			 * @return bool
			 */
			if ( $product && 'auction' === $product->get_type() && apply_filters( 'yith_wcact_show_watchlist_button_on_product', 'yes' === get_option( 'yith_wcact_settings_enable_watchlist', 'no' ), $product ) ) {
				?>
				<div class="ywcact-add-to-watchlist-container">
					<?php echo do_shortcode( '[yith_wcact_add_to_watchlist]' ); ?>
				</div>
				<?php
			}
		}

		/**
		 *  Add product to watchlist
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function add_to_watchlist_list() {
			if ( isset( $_GET['add_to_watchlist'] ) && isset( $_GET['user_id'] ) && apply_filters( 'yith_wcact_show_watchlist_button_on_product', 'yes' === get_option( 'yith_wcact_settings_enable_watchlist', 'no' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$product_id      = intval( $_GET['add_to_watchlist'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$user_id         = intval( $_GET['user_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$product         = wc_get_product( $product_id );
				$current_user_id = get_current_user_id();

				$user_id = $user_id === $current_user_id ? $user_id : $current_user_id;

				if ( $product && 'auction' === $product->get_type() ) {
					$instance             = YITH_Auctions()->bids;
					$product_in_watchlist = $instance->is_product_in_watchlist( $product_id, $user_id );

					if ( ! $product_in_watchlist ) {
						$added = $instance->add_product_to_watchlist( $product_id, $user_id );

						if ( $added ) {
							wc_add_notice(
								sprintf(
									// translators: %s is the name of the product added to the watchlist.
									esc_html__( 'Product "%s" was successfully added to your watchlist.', 'yith-auctions-for-woocommerce' ),
									$product->get_name()
								),
								'success'
							);
						}
					}
				}
			}
		}

		/**
		 *  Remove prdoduct to watchlist
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function remove_from_watchlist() {
			if ( isset( $_GET['remove_from_watchlist'] ) && isset( $_GET['user_id'] ) && apply_filters( 'yith_wcact_show_watchlist_button_on_product', 'yes' === get_option( 'yith_wcact_settings_enable_watchlist', 'no' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$product_id      = intval( $_GET['remove_from_watchlist'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$user_id         = intval( $_GET['user_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$product         = wc_get_product( $product_id );
				$current_user_id = get_current_user_id();

				$user_id = $user_id === $current_user_id ? $user_id : $current_user_id;

				if ( $product && 'auction' === $product->get_type() ) {
					$instance             = YITH_Auctions()->bids;
					$product_in_watchlist = $instance->is_product_in_watchlist( $product_id, $user_id );

					if ( $product_in_watchlist ) {
						$removed = $instance->remove_product_to_watchlist( $product_id, $user_id );

						if ( $removed ) {
							wc_add_notice(
								sprintf(
									// translators: %s is the name of the product removed from the watchlist.
									esc_html__( 'Product "%s" was successfully removed from your watchlist.', 'yith-auctions-for-woocommerce' ),
									$product->get_name()
								),
								'success'
							);
						}
					}
				}
			}
		}

		/**
		 * Add watchlist customer when user is redirected to my account page
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function add_watchlist_notice() {
			if ( 'yes' === get_option( 'yith_wcact_settings_enable_watchlist', 'no' ) && isset( $_GET['watchlist_notice'] ) && true === boolval( $_GET['watchlist_notice'] ) && ! isset( $_POST['login'] ) && ! isset( $_POST['register'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( function_exists( 'wc_add_notice' ) ) {
					/**
					 * APPLY_FILTERS: yith_wcact_watchlist_register_user_message
					 *
					 * Filter the message of the notice when an unlogged user tries to use the watchlist.
					 *
					 * @param string $message Message
					 *
					 * @return string
					 */
					wc_add_notice( apply_filters( 'yith_wcact_watchlist_register_user_message', __( 'Please, log in to use the watchlist feature', 'yith-auctions-for-woocommerce' ) ), 'error' );
				}
			}
		}

		/**
		 * Value step for increment and decrement plugin buttons
		 *
		 * @param float $value Bid value.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0.4
		 */
		public function bid_value_step_for_plugin_buttons( $value ) {
			global $post;

			$product = wc_get_product( $post );

			if ( is_product() && $product && 'auction' === $product->get_type() ) {
				$minimun_increment_amount = (int) $product->get_minimum_increment_amount();

				if ( isset( $minimun_increment_amount ) && $minimun_increment_amount ) {
					$value = $minimun_increment_amount;
				}
			}

			return $value;
		}

		/**
		 * Auction notice on new card registration on YITH Stripe
		 *
		 * @param string $gateway_id Gateway id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0.0
		 */
		public function add_auction_notice_on_new_card_stripe( $gateway_id ) {
			if ( 'yith-stripe' === $gateway_id && is_account_page() ) {
				$automatic_charge_notice = get_option( 'yith_wcact_stripe_note_automatic_charge', '' );
				$force_payment_method    = get_option( 'yith_wcact_stripe_note_force_users_notice', '' );

				if ( 'yes' === get_option( 'yith_wcact_verify_payment_method', 'no' ) ) {
					?>
					<div class="clear"></div>
					<div class="yith-wcact-auction-force-payment-method-stripe"><?php echo nl2br( $force_payment_method ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></div>
					<?php
					if ( 'yes' === get_option( 'yith_wcact_stripe_charge_automatically_price', 'no' ) ) {
						?>
						<div class="yith-wcact-auction-automatic-charge-notice-stripe"><?php echo nl2br( $automatic_charge_notice ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></div>
						<?php
					}
				}
			}
		}
	}
}
