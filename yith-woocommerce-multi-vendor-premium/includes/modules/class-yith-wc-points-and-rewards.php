<?php
/**
 * YITH_WooCommerce_Points_And_Rewards_Support class
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

if ( ! class_exists( 'YITH_WooCommerce_Points_And_Rewards_Support' ) ) {
	/**
	 * Handle support to YITH WooCommerce Points and Rewards
	 *
	 * @class      YITH_WooCommerce_Points_And_Rewards_Support
	 * @since      1.7
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_WooCommerce_Points_And_Rewards_Support {

		/**
		 * Main instance
		 *
		 * @var YITH_WooCommerce_Points_And_Rewards_Support|null
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
			add_action( 'woocommerce_order_status_pending_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_failed_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_payment_complete', array( $this, 'prevent_double_points' ), 5, 1 );
		}

		/**
		 * Prevent double points from vendor suborder
		 * If a vendor suborder change their status no points are assign to customer
		 *
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param integer  $order_id The order id.
		 * @param WC_Order $order (Optional) The order object.
		 * @return void
		 */
		public function prevent_double_points( $order_id, $order = null ) {
			global $wc_points_rewards;

			if ( ! is_null( $order ) ) {
				$order = wc_get_order( $order_id );
			}

			// Skip guest user.
			if ( ! $order || ! $order->get_user_id() ) {
				return;
			}

			$parent_order_id = $order->get_parent_id();
			if ( $parent_order_id ) {
				remove_action( current_action(), array( $wc_points_rewards->order, 'add_points_earned' ) );
			}
		}


		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @since  1.7
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return YITH_WooCommerce_Points_And_Rewards_Support Main instance
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
 * @since  1.7
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 * @return YITH_WooCommerce_Points_And_Rewards_Support
 */
if ( ! function_exists( 'YITH_WooCommerce_Points_And_Rewards_Support' ) ) {
	function YITH_WooCommerce_Points_And_Rewards_Support() { // phpcs:ignore
		return YITH_WooCommerce_Points_And_Rewards_Support::instance();
	}
}

YITH_WooCommerce_Points_And_Rewards_Support();
