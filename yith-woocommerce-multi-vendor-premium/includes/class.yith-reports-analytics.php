<?php
/**
 * YITH_Reports_Analytics Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
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

if ( ! class_exists( 'YITH_Reports_Analytics' ) ) {
	/**
	 * YITH_Reports_Analytics Class
	 */
	class YITH_Reports_Analytics {

		/**
		 * Main Instance
		 *
		 * @since  1.0
		 * @access protected
		 * @var YITH_Reports_Analytics|null
		 */
		protected static $instance = null;

		/**
		 * Main YITH_Reports Instance
		 *
		 * @static
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return YITH_Reports_Analytics Main instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) || is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Clone.
		 * Disable class cloning and throw an error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object. Therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @since 1.0.0
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
		 * @since 1.0.0
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Class construct
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		private function __construct() {
			// WooCommerce Admin Support.
			add_action( 'admin_init', array( $this, 'block_analytics_report_page' ) );
			// Remove WooCommerce Admin for Vendors.
			add_action( 'woocommerce_analytics_menu_capability', array( $this, 'remove_analytics_menu_for_vendors' ) );
			// Orders Report.
			add_filter( 'woocommerce_analytics_clauses_where', array( $this, 'analytics_clauses_where' ), 10, 2 );
		}

		/**
		 * Block Analytics Report Page
		 *
		 * @since  3.11.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function block_analytics_report_page() {
			global $pagenow;
			$vendor                   = yith_wcmv_get_vendor( 'current', 'user' );
			$is_new_analytics_section = ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-admin' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() && $is_new_analytics_section ) {
				// translators: %1$s stand for the open anchor html to admin dashboard, %2$s stand for the close anchor tag.
				wp_die( wp_kses_post( sprintf( __( 'You do not have sufficient permissions to access this page. %1$sClick here to return to your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) ) );
			}
		}

		/**
		 * Remove the WooCommerce admin bar for vendors
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $capability User capability used to show the WooCommerce admin bar.
		 * @return string the allowed capability
		 */
		public function remove_analytics_menu_for_vendors( $capability ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$capability = false;
			}
			return $capability;
		}

		/**
		 * Filter the WooCommerce admin report to remove vendor's information
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array  $clauses The original arguments for the request.
		 * @param string $context The data store context.
		 * @return array the filtered SQL clauses
		 */
		public function analytics_clauses_where( $clauses, $context ) {
			global $wpdb;
			$clauses[] = "AND {$wpdb->prefix}wc_order_stats.parent_id = 0 AND {$wpdb->prefix}wc_order_stats.parent_id NOT IN( SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_created_via' AND {$wpdb->postmeta}.meta_value = 'yith_wcmv_vendor_suborder' )";
			return $clauses;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.0
 * @return YITH_Reports_Analytics
 */
if ( ! function_exists( 'YITH_Reports_Analytics' ) ) {
	/**
	 * Return single instance of the class YITH_Reports_Analytics
	 *
	 * @return YITH_Reports_Analytics
	 */
	function YITH_Reports_Analytics() { // phpcs:ignore
		return YITH_Reports_Analytics::instance();
	}
}

YITH_Reports_Analytics();
