<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Compatibility Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Compatibility' ) ) {
	/**
	 *  Class handle integrations
	 *
	 * @class   YITH_WCACT_Compatibility
	 * @package Yithemes
	 * @since   Version 1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WCACT_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Compatibility
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugins added
		 *
		 * @var   \array
		 * @since 1.0.0
		 */
		protected $plugins = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Compatibility
		 * @since  1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->plugins = array(
				'wpml' => 'WPML',
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
				case 'wpml':
					global $sitepress;

					$has_plugin = ! empty( $sitepress );
					break;

				default:
					$has_plugin = false;
			}

			return $has_plugin;
		}
	}
}
