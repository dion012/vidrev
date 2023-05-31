<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auctions Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Main Class YITH_Auctions
 *
 * @class   YITH_AUCTIONS
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_Auctions' ) ) {
	/**
	 * Class YITH_AUCTIONS
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auctions {

		/**
		 * Plugin version
		 *
		 * @var   string
		 * @since 1.0
		 */
		public $version = YITH_WCACT_VERSION;
		/**
		 * Main Instance
		 *
		 * @var    YITH_Auctions
		 * @since  1.0
		 * @access protected
		 */
		protected static $instance = null;
		/**
		 * Main Admin Instance
		 *
		 * @var   YITH_Auction_Admin
		 * @since 1.0
		 */
		public $admin = null;
		/**
		 * Main Frontpage Instance
		 *
		 * @var   YITH_Auction_Frontend
		 * @since 1.0
		 */
		public $frontend = null;
		/**
		 * Main Product Instance
		 *
		 * @var   WC_Product_Auction
		 * @since 1.0
		 */
		public $product = null;

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			/* === Require Main Files === */
			/**
			 * APPLY_FILTERS: yith_wcact_require_class
			 *
			 * Filter the required files to be loaded in the plugin.
			 *
			 * @param array $files Required files
			 *
			 * @return array
			 */
			$require = apply_filters(
				'yith_wcact_require_class',
				array(
					'common'   => array(
						'includes/legacy/abstract.yith-wcact-legacy-auction-product.php',
						'includes/class.yith-wcact-auction-product.php',
						'includes/class.yith-wcact-auction-db.php',
						'includes/class.yith-wcact-auction-bids.php',
						'includes/class.yith-wcact-auction-ajax.php',
						'includes/class.yith-wcact-auction-my-auctions.php',
						'includes/class.yith-wcact-auction-finish-auction.php',
						'includes/compatibility/class.yith-wcact-compatibility.php',
					),
					'admin'    => array(
						'includes/class.yith-wcact-auction-admin.php',
					),
					'frontend' => array(
						'includes/class.yith-wcact-auction-frontend.php',
					),
				)
			);

			$this->require( $require );

			$this->init_classes();

			/* === Load Plugin Framework === */
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			/* === Register data store for Auction Products === */
			add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ) );

			/* Register plugin to licence/update system */
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			$this->set_init_values_for_2_0_version();
			$this->set_init_values_for_3_0_version();

			/* == Register taxonomy */

			add_action( 'init', array( $this, 'register_taxonomies' ) );
			add_action( 'init', array( $this, 'register_scripts' ) );

			/* == Plugins Init === */
			$this->init();

		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Auctions Main instance
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public static function instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			if ( is_null( $self::$instance ) ) {
				$self::$instance = new $self();
			}

			return $self::$instance;
		}

		/**
		 * Init classes
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function init_classes() {
			$this->bids          = YITH_WCACT_Bids::get_instance();
			$this->ajax          = YITH_WCACT_Auction_Ajax::get_instance();
			$this->compatibility = YITH_WCACT_Compatibility::get_instance();
		}

		/**
		 * Add the main classes file
		 *
		 * Include the admin and frontend classes
		 *
		 * @param array $main_classes array The require classes file path.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 *
		 * @return void
		 * @access protected
		 */
		protected function require( $main_classes ) {
			foreach ( $main_classes as $section => $classes ) {
				foreach ( $classes as $class ) {
					if ( 'common' === $section || ( 'frontend' === $section && ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) || ( 'admin' === $section && is_admin() ) && file_exists( YITH_WCACT_PATH . $class ) ) {
						include_once YITH_WCACT_PATH . $class;
					}
				}
			}
		}

		/**
		 * Load plugin framework
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					include_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Register data stores for bookings.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.3.4
		 * @param  array $data_stores array of data stores.
		 * @return array
		 */
		public function register_data_stores( $data_stores = array() ) {
			$data_stores['product-auction'] = 'YITH_WCACT_Product_Auction_Data_Store_CPT';
			return $data_stores;
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
				$this->admin = YITH_Auction_Admin::get_instance();
			}

			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				$this->frontend = YITH_Auction_Frontend::get_instance();
			}
		}
		/**
		 * Function __set_init_values_for_2_0_version()
		 *
		 * Set old values on new options
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 * @return void
		 * @access protected
		 */
		protected function set_init_values_for_2_0_version() {

			$already_processed = get_option( 'yith_wcact_set_values_2_0', false );

			if ( $already_processed ) {
				return;
			}
			$old_date_format = get_option( 'yith_wcact_settings_date_format' ); // It should not be removed.

			if ( $old_date_format ) {
				$new_format = explode( ' ', $old_date_format );
				if ( 2 === count( $new_format ) ) {
					update_option( 'yith_wcact_general_date_format', $new_format[0] );
					update_option( 'yith_wcact_general_time_format', $new_format[1] );
				}
			}

			// Reschedule auction ended without bids general option.
			$reschedule_number = get_option( 'yith_wcact_settings_automatic_reschedule_auctions_number', 0 );

			if ( $reschedule_number && $reschedule_number > 0 ) {
				update_option( 'yith_wcact_settings_reschedule_auctions_without_bids', 'yes' );
			}

			update_option( 'yith_wcact_set_values_2_0', true );

		}

		/**
		 * Function __set_init_values_for_3_0_version()
		 *
		 * Set old values on new options for version 3.0
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return void
		 * @access protected
		 */
		protected function set_init_values_for_3_0_version() {

			$already_processed = get_option( 'yith_wcact_set_values_3_0', false );

			if ( $already_processed ) {
				return;
			}

			/* == Hide auction badge == */

			$hide_auction_badge = get_option( 'yith_wcact_hide_badge_product_page', 'no' );
			$show_auction_bade  = 'yes' === $hide_auction_badge ? 'no' : 'yes';

			update_option( 'yith_wcact_show_badge_product_page', $show_auction_bade );

			/* == Privacy options == */
			$privacy_label       = get_option( 'yith_wcact_privacy_field_name' );
			$privacy_anchor_url  = get_option( 'yith_wcact_privacy_field_anchor_url' );
			$privacy_anchor_text = get_option( 'yith_wcact_privacy_field_anchor_text' );
			$privacy_link        = sprintf( '<a target="_blank" href="%s">%s</a>', $privacy_anchor_url, $privacy_anchor_text );

			/**
			 * APPLY_FILTERS: yith_wcact_privacy_label
			 *
			 * Filter the privacy label.
			 *
			 * @param string $privacy_label Privacy label
			 *
			 * @return string
			 */
			$label = apply_filters( 'yith_wcact_privacy_label', str_replace( '%PRIVACY%', $privacy_link, $privacy_label ) );

			if ( $label ) {
				update_option( 'yith_wcact_privacy_checkbox_text', $label );
			}
			/* ======================= */

			$old_instalation = get_option( 'yith_wcact_set_values_2_0', false );

			if ( $old_instalation ) {

				/* == New email options == */
				$email_about_to_end = get_option( 'yith_wcact_settings_cron_auction_send_emails', 'no' );

				if ( 'yes' === $email_about_to_end ) {
					update_option( 'yith_wcact_notify_followers_auction_about_to_end', 'yes' );
				} else {
					update_option( 'yith_wcact_notify_followers_auction_about_to_end', 'no' );
				}

				update_option( 'yith_wcact_notify_followers_on_new_bids', 'no' );
				update_option( 'yith_wcact_email_bidders_new_bid', 'no' );

				/* == Limit auction to suggest ==  */
				update_option( 'yith_wcact_ended_suggest_other_auction_number', 3 );

				/* == Migration == */
				$migration = YITH_WCACT_Migration::get_instance();

				/* == Create Process to move follow auctions == */
				$migration->followers();

				/* == Register terms for each auction product  == */
				$migration->init_action_status();
			}

			update_option( 'yith_wcact_set_values_3_0', true );
		}

		/**
		 * Register auction status taxonomy.
		 */
		public function register_taxonomies() {
			$taxonomy_exists = taxonomy_exists( 'yith_wcact_auction_status' );

			if ( $taxonomy_exists ) {
				return;
			}

			$taxonomy = 'yith_wcact_auction_status';

			register_taxonomy(
				$taxonomy,
				/**
				 * APPLY_FILTERS: yith_wcact_auction_status_visibility
				 *
				 * Filter the object type with witch the taxonomy will be associated.
				 *
				 * @param array $object_type Object type
				 *
				 * @return array
				 */
				apply_filters( 'yith_wcact_auction_status_visibility', array( 'product' ) ),
				/**
				 * APPLY_FILTERS: yith_wcact_auction_status_taxonomy_args
				 *
				 * Filter the array with the parameters used in the taxonomy creation.
				 *
				 * @param array $taxonomy_args Taxonomy args
				 *
				 * @return array
				 */
				apply_filters(
					'yith_wcact_auction_status_taxonomy_args',
					array(
						'hierarchical'      => false,
						'show_ui'           => false,
						'show_in_nav_menus' => false,
						'query_var'         => is_admin(),
						'rewrite'           => false,
						'public'            => false,
						'label'             => _x( 'Auction status', 'Taxonomy name', 'yith-auctions-for-woocommerce' ),
					)
				)
			);

			/**
			 * APPLY_FILTERS: yith_wcact_auction_status_terms
			 *
			 * Filter the array with the taxonomy terms.
			 *
			 * @param array $taxonomy_terms Taxonomy terms
			 *
			 * @return array
			 */
			$terms = apply_filters(
				'yith_wcact_auction_status_terms',
				array(
					'scheduled',
					'started',
					'finished',
				)
			);

			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'name', $term, $taxonomy ) ) { // @codingStandardsIgnoreLine.
					wp_insert_term( $term, $taxonomy );
				}
			}
		}

		/**
		 * Register scripts for the application
		 *
		 * @return void
		 */
		public function register_scripts() {

			wp_register_style( 'yith-wcact-frontend-css', YITH_WCACT_ASSETS_URL . 'css/frontend.css', array(), YITH_WCACT_VERSION );
			wp_register_style( 'yith-wcact-widget-css', YITH_WCACT_ASSETS_URL . 'css/yith-wcact-widget.css', array(), YITH_WCACT_VERSION );

			/* === Script === */
			wp_register_script( 'yith-wcact-frontend-js-premium', YITH_WCACT_ASSETS_URL . 'js/frontend-premium.js', array( 'jquery', 'jquery-ui-datepicker', 'accounting' ), YITH_WCACT_VERSION, 'true' );

			/* === Script frontend shop premium === */
			wp_register_script( 'yith_wcact_frontend_shop_premium', YITH_WCACT_ASSETS_URL . 'js/fontend_shop-premium.js', array( 'jquery', 'jquery-ui-sortable' ), YITH_WCACT_VERSION, true );

			global $wp_locale;
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

			wp_localize_script(
				'yith_wcact_frontend_shop_premium',
				'object',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'add_bid' => wp_create_nonce( 'add-bid' ),
				)
			);

			wp_localize_script( 'yith_wcact_frontend_shop_premium', 'date_params', $date_params );

		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since  2.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				include_once YITH_WCACT_PATH . '/plugin-fw/licence/lib/yit-licence.php';
				include_once YITH_WCACT_PATH . '/plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_WCACT_INIT, YITH_WCACT_SECRETKEY, YITH_WCACT_SLUG );

		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since  2.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				include_once YITH_WCACT_PATH . '/plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_WCACT_SLUG, YITH_WCACT_INIT );
		}
	}
}
