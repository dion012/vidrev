<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

/**
 * @class      YITH_Vendors
 * @since      4.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */
if ( ! class_exists( 'YITH_Vendors' ) ) {
	/**
	 * Class YITH_Vendors
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YITH_Vendors extends YITH_Vendors_Legacy {

		/**
		 * Main Instance
		 *
		 * @since  1.0
		 * @access protected
		 * @var YITH_Vendors|null
		 */
		protected static $instance = null;

		/**
		 * Main Admin Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Admin | YITH_Vendors_Admin_Premium
		 */
		public $admin = null;

		/**
		 * Main Frontpage Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Frontend | YITH_Vendors_Frontend_Premium
		 */
		public $frontend = null;

		/**
		 * Main Orders Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Orders | YITH_Vendors_Orders_Premium
		 */
		public $orders = null;

		/**
		 * Main Commissions Instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Commissions
		 */
		public $commissions = null;

		/**
		 * Clone.
		 * Disable class cloning and throw an error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object. Therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Wakeup.
		 * Disable unserializing of the class.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Main plugin Instance
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return YITH_Vendors Main instance
		 */
		public static function instance() {
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
		 * @access public
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		protected function __construct() {

			// Load required files.
			$this->load_required( $this->get_required_files() );
			// Register plugin image size.
			$this->register_image_size();
			// Theme support classes.
			$this->theme_support_includes();

			YITH_Vendors_Install::install();

			add_action( 'init', array( $this, 'init' ), 5 );
			add_action( 'init', array( $this, 'flush_rewrite_rules' ), 20 );
			// Load widget.
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			// Remove wp admin bar for vendor.
			add_action( 'admin_bar_menu', array( $this, 'customize_wp_admin_bar' ), 50 );
			// Maybe block admin access.
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
			add_filter( 'show_admin_bar', array( $this, 'customize_wp_admin_bar_visibility' ), 99, 1 );

			// Listen option change that needs capabilities to be updated.
			add_action( 'update_option_yith_wpv_vendors_option_coupon_management', 'YITH_Vendors_Capabilities::update_capabilities' );
			add_action( 'update_option_yith_wpv_vendors_option_review_management', 'YITH_Vendors_Capabilities::update_capabilities' );
			add_action( 'update_option_yith_wpv_vendors_option_order_management', 'YITH_Vendors_Capabilities::update_capabilities' );
			add_action( 'update_option_yith_wpv_vendors_option_staff_management', array( $this, 'handle_vendor_admins_capabilities' ), 10, 3 );
		}

		/**
		 * Class initialization. Instance the admin or frontend classes.
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 * @access protected
		 */
		public function init() {

			if ( ! doing_action( 'init' ) ) {
				_doing_it_wrong( __METHOD__, 'This method should be called only once on init!', '4.0.0' );

				return;
			}

			// Load admin if admin request.
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Admin();
				YITH_Vendors_Privacy();
			}
			// Load frontend if frontend request.
			if ( yith_wcmv_is_frontend_request() ) {
				$this->frontend = new YITH_Vendors_Frontend();
			}

			// Common classes.
			$this->orders      = new YITH_Vendors_Orders();
			$this->commissions = new YITH_Vendors_Commissions();
		}

		/**
		 * Include classes for theme support.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 */
		protected function theme_support_includes() {

			$theme    = wp_get_theme();
			$template = $theme instanceof WP_Theme ? $theme->get_template() : '';

			switch ( $template ) {
				case 'hello-elementor':
					// Support for Hello Elementor theme.
					include_once YITH_WPV_PATH . 'includes/theme-support/class-yith-vendors-hello-elementor.php';
					break;
			}
		}

		/**
		 * Get default required classes
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_required_files() {
			$required = array(
				'common'   => array(
					// Deprecated hooks handler.
					'includes/class-yith-vendors-deprecated-filter-hooks.php',
					'includes/class-yith-vendors-deprecated-action-hooks.php',
					// Legacy.
					'includes/legacy/yith-vendors-legacy-functions.php',
					'includes/class.yith-reports-analytics.php',
				),
				'admin'    => array(
					'includes/admin/yith-vendors-admin-functions.php',
				),
				'frontend' => array(),
			);

			return $required;
		}

		/**
		 * Load the required plugin files.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 * @param array $required_files an array of required files to load.
		 * @return void
		 * @access protected
		 */
		protected function load_required( $required_files ) {

			// Load first common functions to be immediately available.
			$this->require_file( 'includes/yith-vendors-functions.php' );

			$is_admin = function_exists( 'yith_wcmv_is_admin_request' ) ? yith_wcmv_is_admin_request() : is_admin();
			foreach ( $required_files as $section => $files ) {
				if ( 'common' === $section || ( 'frontend' === $section && ! $is_admin ) || ( 'admin' === $section && $is_admin ) ) {
					$this->require_file( $required_files[ $section ] );
				}
			}
		}

		/**
		 * Require s plugin file.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 * @param string $files A single file or an array of files to require .
		 * @return void
		 * @access protected
		 */
		protected function require_file( $files ) {
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					$this->require_file( $file );
				}
			} else {
				if ( file_exists( YITH_WPV_PATH . $files ) ) {
					require_once YITH_WPV_PATH . $files;
				}
			}
		}

		/**
		 * Widgets initialization
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @return void
		 */
		public function widgets_init() {

			if ( ! class_exists( 'YITH_Woocommerce_Vendors_Widget' ) ) {
				require_once YITH_WPV_PATH . 'includes/widgets/class-yith-woocommerce-vendors-widget.php';
			}

			$widgets = apply_filters( 'yith_wcmv_register_widgets', array( 'YITH_Woocommerce_Vendors_Widget' ) );

			foreach ( $widgets as $widget ) {
				register_widget( $widget );
			}
		}

		/**
		 * Replace the Visit Store link from WooCommerce with the vendor store page link.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar object.
		 * @return void
		 */
		public function customize_wp_admin_bar( $wp_admin_bar ) {

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );

				// Remove Yoast SEO admin icon.
				$wp_admin_bar->remove_menu( 'wpseo-menu' );
				// Remove my sites for multisite installation.
				$wp_admin_bar->remove_menu( 'my-sites' );

				if ( apply_filters( 'woocommerce_show_admin_bar_visit_store', true ) ) {
					$wp_admin_bar->add_node(
						array(
							'parent' => 'site-name',
							'id'     => 'view-store',
							'title'  => __( 'Visit Store', 'yith-woocommerce-product-vendors' ),
							'href'   => $vendor->get_url( 'frontend' ),
						)
					);
				}
			}
		}

		/**
		 * Return if VAT/SSN is required or not
		 *
		 * @since  1.7
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return bool
		 */
		public function is_vat_require() {
			return 'yes' === get_option( 'yith_wpv_vendors_my_account_required_vat', 'no' );
		}

		/**
		 * Return if terms and conditions is required or not
		 *
		 * @since  1.7
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return bool
		 */
		public function is_terms_and_conditions_require() {
			return 'yes' === get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );
		}

		/**
		 * Check if privacy policy is required for vendors.
		 *
		 * @return bool
		 */
		public function is_privacy_policy_require() {
			return 'yes' === get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
		}

		/**
		 * Refresh rewrite rules for frontpage
		 *
		 * @since    1.6.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function flush_rewrite_rules() {
			if ( get_option( 'yith_wcmv_flush_rewrite_rules', false ) ) {
				flush_rewrite_rules();
				update_option( 'yith_wcmv_flush_rewrite_rules', false );
			}
		}

		/**
		 * Add image size
		 *
		 * @since  1.11.4
		 * @author Andrea Grillo <andrea.grillo@yitheme.com>
		 * @return void
		 */
		protected function register_image_size() {

			$gravatar_size = get_option( 'yith_vendors_gravatar_image_size', 128 );
			$header_size   = get_option(
				'yith_wpv_header_image_size',
				array(
					'width'  => 1400,
					'height' => 460,
				)
			);

			$images = array(
				'yith_vendors_avatar' => array(
					'width'  => $gravatar_size,
					'height' => 0,
					'crop'   => false,
				),
				'yith_vendors_header' => array(
					'width'  => $header_size['width'],
					'height' => $header_size['height'],
					'crop'   => true,
				),
			);

			foreach ( $images as $image_name => $image_size ) {
				add_image_size( $image_name, intval( $image_size['width'] ), intval( $image_size['height'] ), $image_size['crop'] );
			}
		}

		/**
		 * Get the image size name
		 *
		 * @since  1.11.4
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $image_type The image type name to retrieve.
		 * @return string
		 */
		public function get_image_size( $image_type ) {
			return 'yith_vendors_' . $image_type;
		}

		/**
		 * Get social feed array - Not available on SuperClass
		 *
		 * @author Andrea Grilllo <andrea.grillo@yithemes.com>
		 * @return array
		 */
		public function get_social_fields() {
			return array();
		}

		/**
		 * Get the post datetime, ( Y-m-d H:i:s ) format, for the privacy policy page.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_last_modified_data_privacy_policy() {
			$privacy_page_id    = get_option( 'yith_wpv_privacy_page', 0 );
			$data_last_modified = $privacy_page_id ? get_post_datetime( $privacy_page_id, 'modified' ) : false;

			return $data_last_modified instanceof DateTimeImmutable ? $data_last_modified->format( 'Y-m-d H:i:s' ) : '';
		}

		/**
		 * Get the post datetime, ( Y-m-d H:i:s ) format, for the terms and conditions page.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_last_modified_data_terms_and_conditions() {
			$terms_page_id      = get_option( 'yith_wpv_terms_and_conditions_page_id', 0 );
			$data_last_modified = $terms_page_id ? get_post_datetime( $terms_page_id, 'modified' ) : false;

			return $data_last_modified instanceof DateTimeImmutable ? $data_last_modified->format( 'Y-m-d H:i:s' ) : '';
		}

		/**
		 * Listen option module yith_wpv_vendors_option_staff_management change, and cleanup admins capabilities
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param mixed  $old_value The old option value.
		 * @param mixed  $value     The new option value.
		 * @param string $option    Option name.
		 * @return void
		 */
		public function handle_vendor_admins_capabilities( $old_value, $value, $option ) {
			$vendors = yith_wcmv_get_vendors( array( 'number' => -1 ) );
			foreach ( $vendors as $vendor ) {
				$admins = $vendor->get_meta( 'admins' );
				if ( empty( $admins ) ) {
					continue;
				}

				foreach ( $admins as $admin ) {
					if ( 'yes' === $value ) {
						YITH_Vendors_Capabilities::set_vendor_capabilities_for_user( $admin, $vendor );
					} else {
						YITH_Vendors_Capabilities::remove_vendor_capabilities_for_user( $admin );
					}
				}
			}
		}

		/**
		 * If current user has role vendor but no vendor associated.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		protected function is_user_owner_without_vendor() {
			$user            = wp_get_current_user();
			$has_vendor_role = in_array( YITH_Vendors_Capabilities::ROLE_NAME, $user->roles, true );
			$vendor          = yith_wcmv_get_vendor( 'current', 'user' );

			return $has_vendor_role && ( empty( $vendor ) || ! $vendor->is_valid() );
		}

		/**
		 * If an user has role vendor but no vendor store associated, block admin access.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $prevent_access Current value: true to prevent admin access, false otherwise.
		 * @return boolean
		 */
		public function prevent_admin_access( $prevent_access ) {
			return $prevent_access || $this->is_user_owner_without_vendor();
		}

		/**
		 * Render or not tha admin bar based on current user
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $visible True if the admin bar is visible, false otherwise.
		 * @return boolean
		 */
		public function customize_wp_admin_bar_visibility( $visible ) {
			return $visible && ! $this->is_user_owner_without_vendor();
		}
	}
}
