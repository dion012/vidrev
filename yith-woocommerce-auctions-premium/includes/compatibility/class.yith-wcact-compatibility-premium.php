<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Compatibility_Premium Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Compatibility_Premium' ) ) {
	/**
	 * Class handle integrations
	 *
	 * @class   YITH_WCACT_Compatibility_Premium
	 * @package Yithemes
	 * @since   Version 1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WCACT_Compatibility_Premium extends YITH_WCACT_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Compatibility_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugins added.
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		protected $plugins = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Compatibility_Premium
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
			$this->plugins = array(
				'multivendor' => 'Multivendor',
				'wpml'        => 'WPML',
				'elementor'   => 'Elementor',
				'stripe'      => 'Stripe',
			);

			$this->load();
		}

		/**
		 * Load integration class.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		private function load() {
			foreach ( $this->plugins as $slug => $class_slug ) {
				$filename  = YITH_WCACT_PATH . 'includes/compatibility/class.yith-wcact-' . $slug . '-compatibility.php';
				$classname = 'YITH_WCACT_' . $class_slug . '_Compatibility';

				$var = str_replace( '-', '_', $slug );

				if ( $this::has_plugin( $slug ) && file_exists( $filename ) && ! function_exists( $classname ) ) {
					include_once $filename;
				}

				if ( function_exists( $classname ) ) {
					$this->$var = $classname();
				}
			}
		}

		/**
		 * Check if plugin exists and it's activated.
		 *
		 * @param string $slug plugin slug.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return bool
		 */
		public static function has_plugin( $slug ) {
			$has_plugin = false;

			switch ( $slug ) {
				case 'multivendor':
					/**
					 * APPLY_FILTERS: yith_wcact_multivendor_min_version
					 *
					 * Filter the minimum version of YITH WooCommerce Multi Vendor to use for the integration.
					 *
					 * @param string $min_version Minimum version
					 *
					 * @return string
					 */
					$has_plugin = defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM && defined( 'YITH_WPV_VERSION' ) && version_compare( YITH_WPV_VERSION, apply_filters( 'yith_wcact_multivendor_min_version', '1.5.0' ), '>' );
					break;

				case 'wpml':
					global $sitepress;
					$has_plugin = ! empty( $sitepress );
					break;

				case 'elementor':
					$has_plugin = defined( 'ELEMENTOR_VERSION' ) && ELEMENTOR_VERSION;
					break;

				case 'stripe':
					$has_plugin = defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM;
					break;

				default:
					$has_plugin = false;
			}

			return $has_plugin;
		}
	}
}
