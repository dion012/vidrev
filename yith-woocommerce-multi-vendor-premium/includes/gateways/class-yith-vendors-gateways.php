<?php
/**
 * YITH_Vendors_Gateways
 * Define methods and properties for class that manages admin payments
 *
 * @class      YITH_Vendors_Gateways
 * @since      Version 2.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateways' ) ) {

	class YITH_Vendors_Gateways {
		/**
		 * List of available gateways
		 *
		 * @since 1.0.0
		 * @var array Array of available gateways
		 */
		protected static $available_gateways = array();

		/**
		 * List of all gateway instance
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected static $gateways = array();

		/**
		 * Array of instances of the class, one for each available gateway
		 *
		 * @since 1.0.0
		 * @var mixed Array of instances of the class
		 */
		public static $instance = null;

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
		 * Returns instance of the class,
		 *
		 * @since 1.0.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 * @return YITH_Vendors_Gateway Unique instance of the class for the passed gateway slug
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Alias for get_instance
		 *
		 * @since 1.0.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 * @return YITH_Vendors_Gateway Unique instance of the class for the passed gateway slug
		 */
		public static function instance() {
			return self::get_instance();
		}

		/**
		 * Constructor Method
		 *
		 * @since 1.0.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 * @return void
		 */
		private function __construct() {
			self::$available_gateways = self::get_available_gateways_ids(); // Leave for backward compatibility.

			foreach ( self::get_available_gateways() as $gateway_id => $gateway_class ) {
				if ( class_exists( $gateway_class ) && empty( self::$gateways[ $gateway_id ] ) ) {
					self::$gateways[ $gateway_id ] = new $gateway_class();
				}
			}
		}

		/**
		 * Return the gateway instance if loaded based on gateway ID
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param string $gateway_id The gateway ID to retrieve.
		 * @return null|YITH_Vendors_Gateway
		 */
		public static function get_gateway( $gateway_id ) {
			return isset( self::$gateways[ $gateway_id ] ) ? self::$gateways[ $gateway_id ] : null;
		}

		/**
		 * Returns list of gateways that results enabled
		 *
		 * @since 1.0.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 * @param string $return If return a list of ids or object.
		 * @return array Array of enabled gateway, as slug => Class_Name.
		 */
		public static function get_available_gateways( $return = 'ids' ) {
			$gateways = array();
			foreach ( self::get_available_gateways_ids() as $gateway_id ) {
				$gateways[ $gateway_id ] = 'ids' === $return ? self::get_gateway_class_from_slug( $gateway_id ) : self::get_gateway( $gateway_id );
			}
			return apply_filters( 'yith_wcmv_get_available_gateways', $gateways, $return );
		}

		/**
		 * Returns list of gateways available on checkout
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @return array Array of enabled gateway, as slug => name.
		 */
		public static function get_available_gateways_on_checkout() {
			$gateways = array();
			foreach ( self::get_available_gateways( 'object' ) as $gateway_slug => $gateway ) {
				if ( $gateway->get_is_available_on_checkout() && $gateway->is_enabled() ) {
					$gateways[ $gateway_slug ] = $gateway->get_method_title();
				}
			}
			return apply_filters( 'yith_wcmv_get_available_gateways_on_checkout', $gateways );
		}

		/**
		 * Returns list of gateways ids
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return array Array of gateways ids
		 */
		public static function get_available_gateways_ids() {
			$gateways_ids = array(
				'stripe-connect',
				'paypal-payouts',
				'account-funds',
			);

			if ( apply_filters( 'yith_deprecated_paypal_service_support', false ) ) {
				$gateways_ids[] = 'paypal-masspay';
			}

			return apply_filters( 'yith_wcmv_available_gateways', $gateways_ids );
		}

		/**
		 * Returns class name of a gateway calculated from slug
		 *
		 * @since 1.0.0
		 * @author Antonio La Rocca <antonio.larocca@yithemes.it>
		 * @param string $slug Gateway slug.
		 * @return string Name of the gateway class.
		 */
		public static function get_gateway_class_from_slug( $slug ) {
			return 'YITH_Vendors_Gateway_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', strtolower( $slug ) ) ) );
		}

		/**
		 * Show html content for yith_wcmv_gateways_list option type
		 *
		 * @since 1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public static function show_gateways_list() {
			$gateways = apply_filters( 'yith_wcmv_show_enabled_gateways_table', self::get_available_gateways() );

			if ( empty( $gateways ) || ! function_exists( 'yith_wcmv_include_admin_template' ) ) {
				return;
			}

			$columns = apply_filters(
				'yith_wcmv_payment_gateways_setting_columns',
				array(
					'name'   => __( 'Gateway', 'yith-woocommerce-product-vendors' ),
					'id'     => __( 'Gateway ID', 'yith-woocommerce-product-vendors' ),
					'status' => __( 'Active', 'yith-woocommerce-product-vendors' ),
				)
			);

			// Prepare the gateways array.
			$filtered_gateways = array();
			foreach ( $gateways as $slug => $class ) {
				// Get gateway instance.
				$gateway = self::get_gateway( $slug );
				if ( empty( $gateway ) ) {
					$gateway = apply_filters( "yith_wcmv_external_gateway_{$slug}", $gateway, $slug );
				}

				if ( empty( $gateway ) ) {
					continue;
				}

				// Short circuit plugin FW for backward options compatibility.
				$options = $gateway->get_options();
				foreach ( $options as &$option ) {
					$option['yith-type'] = isset( $option['type'] ) ? $option['type'] : 'text';
				}

				$filtered_gateways[ $gateway->get_id() ] = array(
					'name'      => $gateway->get_method_title(),
					'enabled'   => $gateway->is_enabled(),
					'available' => ! $gateway->get_is_external() || ( $gateway->is_external_plugin_enabled() && $gateway->is_external_plugin_configured() ),
					'options'   => $options,
				);
			}

			yith_wcmv_include_admin_template(
				'gateways-list',
				array(
					'gateways' => $filtered_gateways,
					'columns'  => $columns,
				)
			);
		}
	}
}
