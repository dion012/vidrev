<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_WPML_Compatibility Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_WPML_Compatibility' ) ) {
	/**
	 * WPML Integration Class
	 *
	 * @class   YITH_WCACT_WPML_Compatibility
	 * @package Yithemes
	 * @since   Version 1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WCACT_WPML_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCACT_WPML_Compatibility
		 */
		protected static $instance;

		/**
		 * Sitepress global
		 *
		 * @var SitePress
		 */
		public $sitepress;

		/**
		 * Current language
		 *
		 * @var string
		 */
		public $current_language;

		/**
		 * Default language
		 *
		 * @var string
		 */
		public $default_language;

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			if ( $this->is_active() ) {
				$this->init_wpml_vars();
				$this->load_classes();
			}
		}

		/**
		 * Init the WPML vars
		 */
		protected function init_wpml_vars() {
			if ( $this->is_active() ) {
				global $sitepress;

				$this->sitepress        = $sitepress;
				$this->current_language = $this->sitepress->get_current_language();
				$this->default_language = $this->sitepress->get_default_language();
			}
		}

		/**
		 * Get the class name from slug
		 *
		 * @param string $slug slug.
		 *
		 * @return string
		 */
		public function get_class_name_from_slug( $slug ) {
			$class_slug = str_replace( '-', ' ', $slug );
			$class_slug = ucwords( $class_slug );
			$class_slug = str_replace( ' ', '_', $class_slug );

			return 'YITH_WCACT_WPML_' . $class_slug;
		}

		/**
		 * Init the WPML vars
		 */
		protected function load_classes() {
			$utils = array(
				'auction-product',
			);

			foreach ( $utils as $util ) {
				$filename  = YITH_WCACT_PATH . '/includes/compatibility/wpml/class.yith-wcact-wpml-' . $util . '.php';
				$classname = $this->get_class_name_from_slug( $util );

				$var = str_replace( '-', '_', $util );

				if ( file_exists( $filename ) && ! class_exists( $classname ) ) {
					include_once $filename;
				}

				if ( method_exists( $classname, 'get_instance' ) ) {
					$this->$var = $classname::get_instance( $this );
				}
			}
		}

		/**
		 * Return true if WPML is active
		 *
		 * @return bool
		 */
		public function is_active() {
			global $sitepress;

			return ! empty( $sitepress );
		}

		/**
		 * Restore the current language
		 */
		public function restore_current_language() {
			$this->sitepress->switch_lang( $this->current_language );
		}

		/**
		 * Set the current language to default language
		 */
		public function set_current_language_to_default() {
			$this->sitepress->switch_lang( $this->default_language );
		}
	}

	return new YITH_WCACT_WPML_Compatibility();
}
