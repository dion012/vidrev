<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auctions_Premium Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * YITH Auction Class
 *
 * @class   YITH_AUCTIONS
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_Auctions_Premium' ) ) {
	/**
	 * Class YITH_AUCTIONS
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auctions_Premium extends YITH_Auctions {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			add_filter( 'yith_wcact_require_class', array( $this, 'load_premium_classes' ) );

			add_filter( 'woocommerce_product_class', array( $this, 'return_premium_product_class' ) );

			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'handle_custom_query_var' ), 10, 2 );

			add_action( 'woocommerce_product_get_minimum_increment_amount', array( $this, 'override_min_increment_amount_on_automatic_bids' ), 10, 2 );

			parent::__construct();
		}

		/**
		 * Main Init classes
		 *
		 * @return void
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function init_classes() {
			$this->bids          = YITH_WCACT_Bids::get_instance();
			$this->ajax          = YITH_WCACT_Auction_Ajax_Premium::get_instance();
			$this->compatibility = YITH_WCACT_Compatibility_Premium::get_instance();
			$this->shortcode     = YITH_WCACT_Auction_Shortcodes::init();
			$this->endpoint      = YITH_Auctions_My_Auctions::get_instance();
			$this->migration     = YITH_WCACT_Migration::get_instance();

			YITH_WCACT_Fee_Product();
		}

		/**
		 * Return premium classes
		 *
		 * @param string $classname Class name.
		 *
		 * @return string
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function return_premium_product_class( $classname ) {
			if ( 'WC_Product_Auction' === $classname ) {
				return $classname . '_Premium';
			}

			return $classname;
		}

		/**
		 * Add premium files to Require array
		 *
		 * @param array $require The require files array.
		 *
		 * @return array
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function load_premium_classes( $require ) {
			$frontend            = array(
				'includes/class.yith-wcact-auction-frontend-premium.php',
				'includes/class-yith-wcact-popup.php',
			);
			$common              = array(
				'includes/class.yith-wcact-auction-product-premium.php',
				'includes/class.yith-wcact-auction-ajax-premium.php',
				'includes/class.yith-wcact-auction-widget.php',
				'includes/widget/class.yith-wcact-auction-widget-ended.php',
				'includes/widget/class.yith-wcact-auction-widget-future.php',
				'includes/widget/class.yith-wcact-auction-widget-watchlist.php',
				'includes/class.yith-wcact-auction-cron.php',
				'includes/class.yith-wcact-auction-notify.php',
				'includes/class.yith-wcact-auction-shortcodes.php',
				'includes/compatibility/class.yith-wcact-compatibility-premium.php',
				'includes/data-stores/class.yith-wcact-product-auction-data-store-cpt.php',
				'includes/class-yith-wcact-fee-product.php',
				'includes/functions-ywcact.php',
				'includes/functions-ywcact-product.php',
				'includes/migration/class-yith-wcact-migration.php',
			);
			$require['admin'][]  = 'includes/class.yith-wcact-auction-admin-premium.php';
			$require['frontend'] = array_merge( $require['frontend'], $frontend );
			$require['common']   = array_merge( $require['common'], $common );

			return $require;
		}

		/**
		 * Handle a custom 'customvar' query var to get products with the 'customvar' meta.
		 *
		 * @param  array $query      - Args for WP_Query.
		 * @param  array $query_vars - Query vars from WC_Product_Query.
		 * @return array modified $query
		 */
		public function handle_custom_query_var( $query, $query_vars ) {
			if ( ! empty( $query_vars['ywcact_auction_type'] ) ) {
				switch ( $query_vars['ywcact_auction_type'] ) {
					case 'non-started':
						$query['meta_query'][] = array(
							array(
								'key'     => '_yith_auction_for',
								'value'   => strtotime( 'now' ),
								'compare' => '>',
							),
						);
						break;

					case 'started':
						$query['meta_query'][] = array(
							'relation' => 'AND',
							array(
								'key'     => '_yith_auction_for',
								'value'   => strtotime( 'now' ),
								'compare' => '<',
							),
							array(
								'key'     => '_yith_auction_to',
								'value'   => strtotime( 'now' ),
								'compare' => '>',
							),
						);
						break;

					case 'finished':
						$query['meta_query'][] = array(
							array(
								'key'     => '_yith_auction_to',
								'value'   => strtotime( 'now' ),
								'compare' => '<',
							),
						);
						break;
				}
			}

			if ( ! empty( $query_vars['ywcact_meta_query_shortcode'] ) ) {
				$meta_query_shortcode = $query_vars['ywcact_meta_query_shortcode'];

				if ( 'current' === $meta_query_shortcode ) {
					$query['meta_query'][] = array(
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
					);
				}
			}

			return $query;
		}

		/**
		 * Override min increment amount for auctions with automatic mode enabled.
		 *
		 * @param  float      $value   - Current min increment amount.
		 * @param  WC_Product $product Product.
		 * @return float
		 */
		public function override_min_increment_amount_on_automatic_bids( $value, $product ) {
			if ( $product && 'auction' === $product->get_type() ) {
				$bid_increment = $product->calculate_bid_up_increment();

				if ( $bid_increment && $bid_increment > 0 ) {
					$value = $bid_increment;
				}
			}

			return $value;
		}

		/**
		 * Function init()
		 *
		 * Instance the admin or frontend classes
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 * @access protected
		 */
		public function init() {
			if ( is_admin() ) {
				$this->admin = YITH_Auction_Admin_Premium::get_instance();
			}

			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				$this->frontend = YITH_Auction_Frontend_Premium::get_instance();
			}
		}
	}
}
