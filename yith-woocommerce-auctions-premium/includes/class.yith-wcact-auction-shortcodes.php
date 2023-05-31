<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Auction_Shortcodes Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_PATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Shortcode class
 *
 * @class   YITH_Auction_Shortcodes
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodriguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Auction_Shortcodes' ) ) {
	/**
	 * Class YITH_Auction_Shortcodes
	 *
	 * @author Carlos Rodriguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Auction_Shortcodes {

		/**
		 * Init class that create the shortcodes.
		 *
		 * @author Carlos Rodriguez <carlos.rodriguez@yithemes.com>
		 */
		public static function init() {
			$shortcodes = array(
				'yith_auction_products'          => __CLASS__ . '::yith_auction_products', // print auction products.
				'yith_auction_out_of_date'       => __CLASS__ . '::yith_auction_out_of_date',
				'yith_auction_show_list_bid'     => __CLASS__ . '::yith_auction_show_list_bid',
				'yith_auction_current'           => __CLASS__ . '::yith_auction_current',
				'yith_auction_non_started'       => __CLASS__ . '::yith_auction_non_started',
				'yith_auction_form'              => __CLASS__ . '::yith_auction_form',
				'yith_wcact_add_to_watchlist'    => __CLASS__ . '::add_to_watchlist',
				'yith_wcact_other_auctions'      => __CLASS__ . '::other_auctions',
				'yith_wcact_out_of_stock'        => __CLASS__ . '::auctions_out_of_stock',
				'yith_wcact_unsubscribe_auction' => __CLASS__ . '::unsubscribe_auction_list',
			);

			foreach ( $shortcodes as $shortcode => $function ) {
				add_shortcode( $shortcode, $function );
			}

			add_action( 'yith_wcact_pagination_nav', array( __CLASS__, 'pagination_nav' ) );
			shortcode_atts( array( 'id' => '' ), array(), 'yith_auction_show_list_bid' );
		}

		/**
		 * Loop over found products.
		 *
		 * @param  array  $query_args Query args.
		 * @param  array  $atts Shortcode attributes.
		 * @param  string $loop_name shortcode name.
		 * @return string
		 */
		private static function product_loop( $query_args, $atts, $loop_name ) {
			global $woocommerce_loop;

			if ( isset( $query_args['category'] ) && $query_args['category'] ) {
				$query_args['cat_operator'] = 'IN';
				$query_args                 = self::set_categories_query_args( $query_args );
			}

			$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $query_args, $atts, $loop_name ) );

			$columns                     = absint( $atts['columns'] );
			$woocommerce_loop['columns'] = $columns;
			$woocommerce_loop['name']    = $loop_name;
			$orderby                     = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ); // phpcs:ignore

			/**
			 * APPLY_FILTERS: yith_wcact_shortcode_catalog_orderby
			 *
			 * Filter the "Order by" options for the shortcodes.
			 *
			 * @param array  $order_by_options Order by options
			 * @param array  $query_args       Query args
			 * @param array  $atts             Shortcode attributes
			 * @param string $loop_name        Loop name
			 *
			 * @return array
			 */
			$catalog_orderby_options = apply_filters(
				'yith_wcact_shortcode_catalog_orderby',
				array(
					'menu_order'   => esc_html__( 'Default sorting', 'yith-auctions-for-woocommerce' ),
					'price'        => esc_html__( 'Sort by price: low to high', 'yith-auctions-for-woocommerce' ),
					'price_desc'   => esc_html__( 'Sort by price: high to low', 'yith-auctions-for-woocommerce' ),
					'auction_asc'  => esc_html__( 'Sort auctions by end date (asc)', 'yith-auctions-for-woocommerce' ),
					'auction_desc' => esc_html__( 'Sort auctions by end date (desc)', 'yith-auctions-for-woocommerce' ),
				),
				$query_args,
				$atts,
				$loop_name
			);

			ob_start();

			if ( is_array( $catalog_orderby_options ) ) {
				/**
				 * APPLY_FILTERS: yith_wcact_showing_auction_count
				 *
				 * Filter whether to show the auctions count in the shortcode.
				 *
				 * @param bool $show_count Whether to show auction count or not
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcact_showing_auction_count', true ) ) : ?>
					<p class="woocommerce-result-count">
						<?php
						if ( 1 === intval( $products->post_count ) ) {
							echo esc_html__( 'Showing the single auction', 'yith-auctions-for-woocommerce' );
						} else {
							/* translators: %d: post count */
							printf( esc_html__( 'Showing all %d results', 'yith-auctions-for-woocommerce' ), esc_attr( $products->post_count ) );
						}
						?>
					</p>
				<?php endif; ?>
				<form class="woocommerce-ordering " method="get">
					<select name="orderby" class="orderby">
						<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php wc_query_string_form_fields( null, array( 'orderby', 'submit' ) ); ?>
				</form>

				<?php
			}

			if ( $products->have_posts() ) {
				do_action( "woocommerce_shortcode_before_{$loop_name}_loop" );

				woocommerce_product_loop_start();

				while ( $products->have_posts() ) {
					$products->the_post();

					wc_get_template_part( 'content', 'product' );
				}

				woocommerce_product_loop_end();

				do_action( "woocommerce_shortcode_after_{$loop_name}_loop" );

				if ( ! isset( $atts['pagination'] ) || 'no' !== $atts['pagination'] ) {
					/**
					 * DO_ACTION: yith_wcact_pagination_nav
					 *
					 * Allow to render some content in the pagination section for the shortcodes.
					 *
					 * @param int $max_pages Number of max pages
					 */
					do_action( 'yith_wcact_pagination_nav', $products->max_num_pages );
				}
			} else {
				do_action( "woocommerce_shortcode_{$loop_name}_loop_no_results" );
			}

			wc_reset_loop();
			wp_reset_postdata();
			return woocommerce_catalog_ordering() . '<div class="woocommerce columns-' . $columns . ' ' . $loop_name . ' yith-wcact-loop ">' . ob_get_clean() . '</div>';
		}

		/**
		 * ShortCode for auction products
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 * @since  1.0.0
		 */
		public static function yith_auction_products( $atts ) {
			$atts = shortcode_atts(
				array(
					'columns'        => '4',
					'orderby'        => '',
					'order'          => 'ASC',
					'ids'            => '',
					'skus'           => '',
					'posts_per_page' => '-1',
					'pagination'     => 'yes',
					'category'       => '',
				),
				$atts,
				'yith_auction_products'
			);

			$ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );

			$query_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => $atts['posts_per_page'],
				'paged'               => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
				'meta_query'          => WC()->query->get_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			);

			$query_args = array_merge( $atts, $query_args );

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$auction_term = get_term_by( 'slug', 'auction', 'product_type' );

			if ( $auction_term ) {
				$posts_in = array_unique( (array) get_objects_in_term( $auction_term->term_id, 'product_type' ) );

				if ( ! empty( $posts_in ) ) {
					$query_args['post__in'] = array_map( 'trim', $posts_in );

					// Ignore catalog visibility.
					$query_args['meta_query'] = array_merge( $query_args['meta_query'], isset( $ordering_args['meta_query'] ) ? $ordering_args['meta_query'] : array() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

					if ( ! is_product() ) {
						wp_enqueue_style( 'yith-wcact-frontend-css' );
						wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
					}

					return self::product_loop( $query_args, $atts, 'yith_auction_products' );
				}
			}

			return '';
		}

		/**
		 * Catalog ordering args
		 *
		 * @param string $orderby Order by value.
		 * @param string $order Order.
		 * @return string
		 * @since  1.0.0
		 */
		public static function get_catalog_ordering_args( $orderby = '', $order = '' ) {
			if ( isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$orderby = wc_clean( (string) sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order   = '';
			}

			if ( ! $orderby ) {
				/**
				 * APPLY_FILTERS: yith_wcact_shortcode_default_catalog_orderby
				 *
				 * Filter the default "Order by" option in the shortcodes.
				 *
				 * @param string $default_option Default order by option
				 *
				 * @return string
				 */
				$orderby_value = apply_filters( 'yith_wcact_shortcode_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
				$orderby_value = explode( '-', $orderby_value );
				$orderby       = esc_attr( $orderby_value[0] );
				$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : $order;
			}

			$orderby = strtolower( $orderby );
			$order   = strtoupper( $order );
			$args    = array();

			// default - menu_order.
			$args['orderby']  = $orderby;
			$args['order']    = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
			$args['meta_key'] = ''; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['join']     = '';

			switch ( $orderby ) {
				case 'price':
					$args['meta_key'] = 'current_bid'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'ASC';
					break;

				case 'price_desc':
					$args['meta_key'] = 'current_bid'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'DESC';
					break;

				case 'auction_asc':
					$args['orderby']  = 'meta_value';
					$args['order']    = 'ASC';
					$args['meta_key'] = '_yith_auction_to'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;

				case 'auction_desc':
					$args['orderby']  = 'meta_value';
					$args['order']    = 'DESC';
					$args['meta_key'] = '_yith_auction_to'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;

				case 'rand':
					$args['orderby'] = 'rand';
					break;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_shortcode_get_catalog_ordering_args
			 *
			 * Filter the array with the arguments to order products in the shortcode.
			 *
			 * @param array  $args    Array of arguments
			 * @param string $orderby Order by option
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_shortcode_get_catalog_ordering_args', $args, $orderby );
		}

		/**
		 * ShortCode for auction products
		 *
		 * @param array $atts shortcode attributes.
		 * @return string
		 * @since  1.0.0
		 */
		public static function yith_auction_out_of_date( $atts ) {
			$atts = shortcode_atts(
				array(
					'columns'        => '4',
					'orderby'        => '',
					'order'          => '',
					'ids'            => '',
					'skus'           => '',
					'posts_per_page' => '-1',
					'pagination'     => 'yes',
					'category'       => '',
				),
				$atts,
				'yith_auction_out_of_date'
			);

			$ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );

			$query_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => $atts['posts_per_page'],
				'paged'               => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
				'meta_query'          => WC()->query->get_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			);

			$query_args = array_merge( $atts, $query_args );

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$auction_term = get_term_by( 'slug', 'auction', 'product_type' );

			if ( $auction_term ) {
				$posts_in = array_unique( (array) get_objects_in_term( $auction_term->term_id, 'product_type' ) );

				if ( ! empty( $posts_in ) ) {
					$query_args['post__in'] = array_map( 'trim', $posts_in );

					$query_args['meta_query'][] = array(
						'key'     => '_yith_auction_to',
						'value'   => strtotime( 'now' ),
						'compare' => '<',
					);

					// Ignore catalog visibility.
					$query_args['meta_query'] = array_merge( $query_args['meta_query'], WC()->query->stock_status_meta_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

					if ( ! is_product() ) {
						wp_enqueue_style( 'yith-wcact-frontend-css' );
						wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
					}

					return self::product_loop( $query_args, $atts, 'yith_auction_out_of_date' );
				}
			}

			return '';
		}

		/**
		 * ShortCode for show non started auction products
		 *
		 * @param array $atts shortcode attributes.
		 * @return string
		 * @since  1.0.0
		 */
		public static function yith_auction_non_started( $atts ) {
			$atts          = shortcode_atts(
				array(
					'columns'        => '4',
					'orderby'        => '',
					'order'          => 'ASC',
					'ids'            => '',
					'skus'           => '',
					'posts_per_page' => '-1',
					'pagination'     => 'yes',
					'category'       => '',
				),
				$atts,
				'yith_auction_non_started'
			);
			$ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
			$query_args    = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => $atts['posts_per_page'],
				'paged'               => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
				'meta_query'          => WC()->query->get_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			);

			$query_args = array_merge( $atts, $query_args );

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$auction_term = get_term_by( 'slug', 'auction', 'product_type' );

			if ( $auction_term ) {
				$posts_in = array_unique( (array) get_objects_in_term( $auction_term->term_id, 'product_type' ) );

				if ( ! empty( $posts_in ) ) {
					$query_args['post__in'] = array_map( 'trim', $posts_in );

					$query_args['meta_query'][] = array(
						'key'     => '_yith_auction_for',
						'value'   => strtotime( 'now' ),
						'compare' => '>',
					);

					// Ignore catalog visibility.
					$query_args['meta_query'] = array_merge( $query_args['meta_query'], WC()->query->stock_status_meta_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

					if ( ! is_product() ) {
						wp_enqueue_style( 'yith-wcact-frontend-css' );
						wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
					}

					return self::product_loop( $query_args, $atts, 'yith_auction_non_started' );
				}
			}

			return '';
		}

		/**
		 * ShortCode show current auctions
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 * @since  1.0.0
		 */
		public static function yith_auction_current( $atts ) {
			$atts = shortcode_atts(
				array(
					'columns'        => '4',
					'orderby'        => '',
					'order'          => '',
					'ids'            => '',
					'skus'           => '',
					'posts_per_page' => '-1',
					'pagination'     => 'yes',
					'category'       => '',
				),
				$atts,
				'yith_auction_current'
			);

			$ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );

			$query_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => $atts['posts_per_page'],
				'paged'               => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
				'meta_query'          => WC()->query->get_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			);

			$query_args = array_merge( $atts, $query_args );

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$auction_term = get_term_by( 'slug', 'auction', 'product_type' );

			if ( $auction_term ) {
				$posts_in = array_unique( (array) get_objects_in_term( $auction_term->term_id, 'product_type' ) );

				if ( ! empty( $posts_in ) ) {
					$query_args['post__in'] = array_map( 'trim', $posts_in );

					$query_args['meta_query'][] = array(
						'relation' => 'AND',
						array(
							'key'     => '_yith_auction_to',
							'value'   => strtotime( 'now' ),
							'compare' => '>=',
						),
						array(
							'key'     => '_yith_auction_for',
							'value'   => strtotime( 'now' ),
							'compare' => '<=',
						),
						array(
							'key'     => '_yith_auction_closed_buy_now',
							'value'   => 'yes',
							'compare' => '!=',
						),
					);

					// Ignore catalog visibility.
					$query_args['meta_query'] = array_merge( $query_args['meta_query'], WC()->query->stock_status_meta_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

					if ( ! is_product() ) {
						wp_enqueue_style( 'yith-wcact-frontend-css' );
						wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
					}

					return self::product_loop( $query_args, $atts, 'yith_auction_current' );
				}
			}

			return '';
		}

		/**
		 * ShortCode show list bids
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 * @since  1.0.0
		 */
		public static function yith_auction_show_list_bid( $atts ) {
			global $product;

			$auction_id = isset( $atts['id'] ) ? $atts['id'] : 0;

			if ( ! $auction_id && $product && $product->get_id() ) {
				$auction_id = $product->get_id();
			}

			if ( $auction_id ) {
				$auction_product = wc_get_product( $auction_id );

				if ( $auction_product && 'auction' === $auction_product->get_type() ) {
					$args = array(
						'product'  => $auction_product,
						'currency' => get_woocommerce_currency(),
					);

					ob_start();
					wc_get_template( 'list-bids.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
					return ob_get_clean();
				}
			}
		}

		/**
		 * Print Auction add to cart form
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 * @since  1.3.4
		 */
		public static function yith_auction_form( $atts ) {
			global $product, $wp;

			ob_start();

			$auction_id = isset( $atts['id'] ) ? $atts['id'] : 0;

			if ( ! $auction_id && $product && $product->get_id() ) {
				$auction_id = $product->get_id();
			}

			if ( $auction_id ) {
				$auction_product = wc_get_product( $auction_id );

				if ( $auction_product && 'auction' === $auction_product->get_type() ) {
					global $product, $post;

					$old_product = $product;
					$old_post    = $post;
					$post        = get_post( $auction_product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$product     = $auction_product;

					wc_get_template( 'single-product/add-to-cart/auction.php', array(), '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );

					$product = $old_product;
					$post    = $old_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
			}

			/* === Script === */
			wp_enqueue_script( 'yith-wcact-frontend-js-premium', YITH_WCACT_ASSETS_URL . 'js/frontend-premium.js', array( 'jquery', 'jquery-ui-datepicker', 'accounting' ), YITH_WCACT_VERSION, 'true' );
			wp_localize_script(
				'yith-wcact-frontend-js-premium',
				'ywcact_frontend_object',
				array(
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'live_auction_product_page' => 'yes' === get_option( 'yith_wcact_ajax_refresh_auction_product_page', 'no' ) ? (int) get_option( 'yith_wcact_settings_live_auction_product_page', 0 ) * 1000 : 0,
					'add_bid'                   => wp_create_nonce( 'add-bid' ),
					'bid_empty_error'           => esc_html__( 'Please insert a value for your bid.', 'yith-auctions-for-woocommerce' ),
					'return_form_url'           => home_url( $wp->request ),
					/**
					 * APPLY_FILTERS: yith_wcact_ajax_activated
					 *
					 * Filter whether the AJAX is activated.
					 *
					 * @param bool $ajax_activated Whether the AJAX is activated or not
					 *
					 * @return bool
					 */
					'ajax_activated'            => apply_filters( 'yith_wcact_ajax_activated', true ),
				)
			);

			return ob_get_clean();
		}

		/**
		 * Prints template for displaying navigation panel for pagination
		 *
		 * @param int $max_num_pages Max page number.
		 */
		public static function pagination_nav( $max_num_pages ) {
			ob_start();
			wc_get_template( 'frontend/yith-auction-pagination-nav.php', array( 'max_num_pages' => $max_num_pages ), '', YITH_WCACT_TEMPLATE_PATH );
			echo ob_get_clean(); // phpcs:ignore
		}

		/**
		 * Return "Add to Watchlist" button.
		 *
		 * @param array  $atts    Array of parameters for the shortcode.
		 * @param string $content Shortcode content (usually empty).
		 * @return string
		 * @since 2.0.0
		 */
		public static function add_to_watchlist( $atts, $content = null ) {
			global $product;

			// product object.
			$current_product = ( isset( $atts['product_id'] ) ) ? wc_get_product( $atts['product_id'] ) : false;
			$current_product = $current_product ? $current_product : $product;

			if ( ! $current_product ) {
				return '';
			}

			$current_product_id = yit_get_product_id( $current_product );
			$instance           = YITH_Auctions()->bids;
			$user_id            = get_current_user_id();

			$product_in_watchlist = ( $user_id ) ? $instance->is_product_in_watchlist( $current_product_id, get_current_user_id() ) : false;

			$exists = isset( $product_in_watchlist ) && $product_in_watchlist ? true : false;

			$browse_watchlist_text = esc_html__( 'view your watchlist', 'yith-auctions-for-woocommerce' );
			$add_watchlist_text    = esc_html__( 'Add to watchlist', 'yith-auctions-for-woocommerce' );
			$already_in_watchlist  = esc_html__( 'The auction is in your watchlist!', 'yith-auctions-for-woocommerce' );
			$product_added         = esc_html__( 'Product added!', 'yith-auctions-for-woocommerce' );

			/**
			 * APPLY_FILTERS: yith_wcact_watchlist_icon
			 *
			 * Filter the watchlist icon.
			 *
			 * @param string $watchlist_icon Watchlist icon
			 *
			 * @return string
			 */
			$icon              = apply_filters( 'yith_wcact_watchlist_icon', YITH_WCACT_ASSETS_URL . 'images/icon/auctionheart.png' );
			$container_classes = $exists ? 'exists' : false;

			$template_part = $exists ? 'browse' : 'button';

			$ajax_loading = 'yes' === get_option( 'yith_wcact_ajax_enable', 'no' );

			$login_popup = 'yes' === get_option( 'yith_wcact_enable_login_popup', 'no' ) && ( defined( 'YITH_WELRP' ) && YITH_WELRP );

			$link_class = ( ! $user_id ) ? 'ywcact-user-no-logged' : false;
			$link_class = ( $login_popup && $link_class ) ? $link_class . ' ywcact-auction-login-popup' : $link_class;

			$additional_params = array(
				'base_url'                  => yith_wcact_get_current_url(),
				'watchlist_url'             => yith_wcact_get_watchlist_url(),
				'exists'                    => $exists,
				'container_classes'         => $container_classes,
				'product_id'                => $current_product_id,
				'user_id'                   => $user_id,
				/**
				 * APPLY_FILTERS: yith_wcact_add_watchlist_text
				 *
				 * Filter the text to add the auction to the watchlist.
				 *
				 * @param string $add_watchlist_text 'Add to watchlist' text
				 *
				 * @return string
				 */
				'add_watchlist_text'        => apply_filters( 'yith_wcact_add_watchlist_text', $add_watchlist_text ),
				/**
				 * APPLY_FILTERS: yith_wcact_browse_watchlist_label
				 *
				 * Filter the text to browse your watchlist.
				 *
				 * @param string $browse_watchlist_text 'Browse your watchlist' text
				 *
				 * @return string
				 */
				'browse_watchlist_text'     => apply_filters( 'yith_wcact_browse_watchlist_label', $browse_watchlist_text ),
				/**
				 * APPLY_FILTERS: yith_wcact_product_already_in_watchlist_text
				 *
				 * Filter the text when the product is already in the watchlist.
				 *
				 * @param string $already_in_watchlist 'Already in watchlist' text
				 *
				 * @return string
				 */
				'already_in_watchlist_text' => apply_filters( 'yith_wcact_product_already_in_watchlist_text', $already_in_watchlist ),
				/**
				 * APPLY_FILTERS: yith_wcact_product_added_to_watchlist_message
				 *
				 * Filter the text when the product has been added to the watchlist.
				 *
				 * @param string $product_added 'Product added to watchlist' text
				 *
				 * @return string
				 */
				'product_added_text'        => apply_filters( 'yith_wcact_product_added_to_watchlist_message', $product_added ),
				'users_has_product'         => $instance->get_users_count_product_on_watchlist( $current_product_id ),
				'icon'                      => $icon,
				'disable_wishlist'          => false,
				/**
				 * APPLY_FILTERS: show_watchlist_count
				 *
				 * Filter whether to show the count of the products in the watchlist.
				 *
				 * @param bool $show_watchlist_count Whether to show the watchlist count or not
				 *
				 * @return bool
				 */
				'show_count'                => apply_filters( 'yith_wcact_show_watchlist_count', true ),
				'ajax_loading'              => $ajax_loading,
				'template_part'             => $template_part,
				'enable_popup'              => 0 === (int) $user_id && $login_popup,
				'link_class'                => $link_class,
			);

			// let third party developer filter options.
			/**
			 * APPLY_FILTERS: yith_wcact_add_to_watchlist_params
			 *
			 * Filter the array with the parameters needed to add the product to the watchlist.
			 *
			 * @param array $additional_params Parameters
			 * @param array $atts              Array of attributes
			 *
			 * @return array
			 */
			$additional_params = apply_filters( 'yith_wcact_add_to_watchlist_params', $additional_params, $atts );

			$atts = shortcode_atts(
				$additional_params,
				$atts
			);

			ob_start();

			wc_get_template( 'frontend/add-to-watchlist.php', $atts, '', YITH_WCACT_TEMPLATE_PATH );

			return ob_get_clean();
		}

		/**
		 * Return Other Auction section.
		 *
		 * @param array  $atts    Array of parameters for the shortcode.
		 * @param string $content Shortcode content (usually empty).
		 * @return string.
		 * @since 2.0.0
		 */
		public static function other_auctions( $atts, $content ) {
			$atts = shortcode_atts(
				array(
					'color'                       => '#F5F5F5',
					'type'                        => 'auctions',
					'order'                       => 'ASC',
					'ids'                         => '',
					'type'                        => 'auction',
					'limit'                       => 3,
					'exclude'                     => false,
					'category'                    => array(),
					'ywcact_meta_query_shortcode' => 'current',
				),
				$atts,
				'yith_wcact_other_auctions'
			);

			if ( ! $atts['exclude'] ) {
				global $product;

				if ( $product ) {
					$product_id         = $product->get_id();
					$atts['exclude']    = array( $product_id );
					$atts['auction_id'] = $product_id;

					$show_auction_categories = get_option( 'yith_wcact_ended_suggest_active_auctions', 'all' );

					// Display only auctions from the same category.
					if ( 'all' !== $show_auction_categories ) {
						$terms = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'object_ids' => $product_id,
								'fields'     => 'slugs',

							)
						);

						if ( $terms && empty( $atts['category'] ) ) {
							$atts['category'] = $terms;
						}
					}

					// Display only auctions from the same category.
					$limit_auction = get_option( 'yith_wcact_ended_suggest_other_auction_number', '' );
					$atts['limit'] = ( ! $limit_auction || $limit_auction >= 0 ) ? $limit_auction : '';
				}
			} else {
				$atts['exclude'] = array( $atts['exclude'] );
			}

			/**
			 * APPLY_FILTERS: yith_wcact_other_auctions_atts
			 *
			 * Filter the array with the attributes used for the 'Other auctions' shortcode.
			 *
			 * @param array $atts Array of attributes
			 *
			 * @return array
			 */
			$items = wc_get_products( apply_filters( 'yith_wcact_other_auctions_atts', $atts ) );

			if ( is_array( $items ) && ! empty( $items ) ) {
				/**
				 * APPLY_FILTERS: yith_wcact_other_auctions_args
				 *
				 * Filter the array with the arguments sent to the template.
				 *
				 * @param array $args Array of arguments
				 *
				 * @return array
				 */
				$args = apply_filters(
					'yith_wcact_other_auctions_args',
					array(
						'items'           => $items,
						'heading_message' => esc_html__( 'Others auctions you may like:', 'yith-auctions-for-woocommerce' ),
						'color'           => $atts['color'],
					)
				);

				ob_start();

				wc_get_template( 'frontend/shortcodes/other-auctions.php', $args, '', YITH_WCACT_TEMPLATE_PATH );

				return ob_get_clean();
			}
		}

		/**
		 * Set categories args
		 *
		 * @param array $query_args  Query args.
		 * @return array.
		 * @since 2.0.0
		 */
		private static function set_categories_query_args( $query_args ) {
			$categories = array_map( 'sanitize_title', explode( ',', $query_args['category'] ) );
			$field      = 'slug';

			if ( is_numeric( $categories[0] ) ) {
				$field      = 'term_id';
				$categories = array_map( 'absint', $categories );

				// Check numeric slugs.
				foreach ( $categories as $cat ) {
					$the_cat = get_term_by( 'slug', $cat, 'product_cat' );

					if ( false !== $the_cat ) {
						$categories[] = $the_cat->term_id;
					}
				}
			}

			$query_args['tax_query'][] = array(
				'taxonomy'         => 'product_cat',
				'terms'            => $categories,
				'field'            => $field,
				'operator'         => $query_args['cat_operator'],

				/*
				* When cat_operator is AND, the children categories should be excluded,
				* as only products belonging to all the children categories would be selected.
				*/
				'include_children' => 'AND' === $query_args['cat_operator'] ? false : true,
			);

			/**
			 * APPLY_FILTERS: yith_wcact_set_categories_query_args
			 *
			 * Filter the query arguments when setting categories in the shortcode loops.
			 *
			 * @param array $query_args Query args
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_set_categories_query_args', $query_args );
		}

		/**
		 * Auction out of stock shortcode.
		 *
		 * @param array $atts    Array of parameters for the shortcode.
		 * @return string.
		 * @since 2.0.0
		 */
		public static function auctions_out_of_stock( $atts ) {
			$atts = shortcode_atts(
				array(
					'columns'        => '4',
					'orderby'        => '',
					'order'          => 'ASC',
					'ids'            => '',
					'skus'           => '',
					'posts_per_page' => '-1',
					'pagination'     => 'yes',
					'category'       => '',
				),
				$atts,
				'yith_wcact_out_of_stock'
			);

			$ordering_args = self::get_catalog_ordering_args( $atts['orderby'], $atts['order'] );

			$query_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => $atts['posts_per_page'],
				'paged'               => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
				'meta_query'          => WC()->query->get_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			);

			$query_args = array_merge( $atts, $query_args );

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$auction_term = get_term_by( 'slug', 'auction', 'product_type' );

			if ( $auction_term ) {
				$posts_in = array_unique( (array) get_objects_in_term( $auction_term->term_id, 'product_type' ) );

				if ( ! empty( $posts_in ) ) {
					$query_args['post__in'] = array_map( 'trim', $posts_in );

					$query_args['meta_query'][] = array(
						array(
							'key'   => '_stock_status',
							'value' => 'outofstock',
						),

					);

					// Ignore catalog visibility.
					$query_args['meta_query'] = array_merge( $query_args['meta_query'], WC()->query->stock_status_meta_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

					if ( ! is_product() ) {
						wp_enqueue_style( 'yith-wcact-frontend-css' );
						wp_enqueue_script( 'yith_wcact_frontend_shop_premium' );
					}

					return self::product_loop( $query_args, $atts, 'yith_wcact_out_of_stock' );
				}
			}

			return '';
		}

		/**
		 * Print unsubscribe auction list template
		 *
		 * @param array $atts    Array of parameters for the shortcode.
		 * @since 3.0.0
		 * @return string
		 */
		public static function unsubscribe_auction_list( $atts ) {
			/* == Enqueue Scripts and Styles == */
			wp_print_scripts( array( 'yith_wcact_unsubscribe_auction_list' ) );
			wp_print_styles( array( 'yith_wcact_unsubscribe_auction_list' ) );

			$email = isset( $_REQUEST['yith-wcact-email'] ) ? sanitize_email( wp_unslash( $_REQUEST['yith-wcact-email'] ) ) : ''; // phpcs:ignore

			$email = isset( $atts['email'] ) ? sanitize_email( wp_unslash( $atts['email'] ) ) : $email;

			$instance = YITH_Auctions()->bids;

			$follower_list = $instance->get_auction_follower_list( $email );

			$args = array(
				'auction_list' => array_keys( $follower_list ),
				'email'        => $email,
				'button_label' => esc_html__( 'Unsubscribe', 'yith-auctions-for-woocommerce' ),
			);

			ob_start();

			wc_get_template( 'frontend/shortcodes/follower-list.php', $args, '', YITH_WCACT_TEMPLATE_PATH );

			return ob_get_clean();
		}
	}
}
