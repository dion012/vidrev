<?php
/**
 * YITH_WordPress_Yoast_SEO_Support class
 *
 * @since      1.11.4
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WordPress_Yoast_SEO_Support' ) ) {
	/**
	 * Yoast SEO plugin support
	 *
	 * @class      YITH_WordPress_Yoast_SEO_Support
	 * @package    YITH WooCommerce Multi Vendor
	 * @since      1.11.4
	 * @author     YITH
	 */
	class YITH_WordPress_Yoast_SEO_Support {

		/**
		 * Main instance
		 *
		 * @var YITH_WordPress_Yoast_SEO_Support|null
		 */
		private static $instance = null;

		/**
		 * Clone.
		 * Disable class cloning and throw an error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object. Therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @since 1.9.8
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
		 * @since 1.9.8
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Construct
		 */
		private function __construct() {
			add_action( 'wpseo_register_extra_replacements', array( $this, 'register_plugin_replacements' ) );
		}

		/**
		 * Register a var replacement for vendor name
		 *
		 * @author Alessio Torrisi <alessio.torrisi@yithemes.com>
		 * @return void
		 */
		public function register_plugin_replacements() {
			wpseo_register_var_replacement( '%%vendor_name%%', 'YITH_WordPress_Yoast_SEO_Support::retrieve_vendor_name', 'basic', __( 'This is the name of the vendor product', 'yith-woocommerce-product-vendors' ) );
		}

		/**
		 * Get the vendor name
		 *
		 * @author Alessio Torrisi <alessio.torrisi@yithemes.com>
		 * @return string Store name
		 */
		public static function retrieve_vendor_name( $var, $post ) {
			if ( isset( $post->ID ) ) {
				$vendor = yith_wcmv_get_vendor( $post->ID, 'product' );
				$var    = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : $var;
			}

			return $var;
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_WordPress_Yoast_SEO_Support
		 * @since  1.11.4
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @return /YITH_WordPress_Yoast_SEO_Support
 * @since  1.11.4
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 */
if ( ! function_exists( 'YITH_WordPress_Yoast_SEO_Support' ) ) {
	function YITH_WordPress_Yoast_SEO_Support() { // phpcs:ignore
		return YITH_WordPress_Yoast_SEO_Support::instance();
	}
}

YITH_WordPress_Yoast_SEO_Support();
