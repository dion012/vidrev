<?php
/**
 * YITH_Vendors_Modules_Handler
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

if ( ! class_exists( 'YITH_Vendors_Modules_Handler' ) ) {
	/**
	 * Handle plugin modules.
	 *
	 * @class      YITH_Vendors_Modules_Handler
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Modules_Handler {

		/**
		 * Single instance of the class
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Modules_Handler
		 */
		protected static $instance;

		/**
		 * An array of available modules
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules = array();

		/**
		 * An array of available modules
		 *
		 * @since 4.0.0
		 * @var array module => boolean | True if available, false otherwise
		 */
		protected $available_modules = array();

		/**
		 * An array of active modules
		 *
		 * @since 4.0.0
		 * @var array array module => boolean | True if active, false otherwise
		 */
		protected $active_modules = array();

		/**
		 * An array of modules post types to handle
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules_post_types = array();

		/**
		 * An array of modules capabilities to add
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules_capabilities = array();

		/**
		 * Modules admin sub-tabs
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules_admin_sub_tabs = array();

		/**
		 * Modules files list
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $mapped_files = array();

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
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_Modules_Handler
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		private function __construct() {
			$this->modules = include YITH_WPV_PATH . 'plugin-options/modules/modules.php';
			if ( ! empty( $this->modules ) ) {
				$this->init_hooks();
				$this->load_modules();
			}
		}

		/**
		 * Init modules hooks
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init_hooks() {
			// Add mapped files to autoload.
			add_action( 'yith_wcmv_autoload_mapped_files', array( $this, 'add_mapped_files' ), 10, 1 );
			// Handle AJAX activation.
			add_action( 'yith_wcmv_admin_ajax_module_active_switch', array( $this, 'activation_handler' ) );
			// Add capabilities.
			add_filter( 'yith_wcmv_vendor_additional_capabilities', array( $this, 'add_modules_capabilities' ), 10 );
			// Add post types.
			add_filter( 'yith_wcmv_vendor_allowed_vendor_post_type', array( $this, 'add_module_post_types' ), 10, 1 );
			// Add vendor taxonomy to module post types.
			add_filter( 'yith_wcmv_register_taxonomy_object_type', array( $this, 'add_module_post_types' ), 10, 1 );
			// Admin tab settings.
			add_filter( 'yith_wcmv_have_active_add_ons_settings', array( $this, 'has_active_modules_settings' ), 10, 1 );
			add_filter( 'yith_wcmv_add_ons_settings_sub_tabs', array( $this, 'add_modules_sub_tab' ), 10, 1 );
		}

		/**
		 * Load available modules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function load_modules() {
			foreach ( $this->modules as $key => $module ) {
				$ukey = str_replace( '-', '_', $key );

				// Always register module capabilities to clean-up caps array.
				if ( ! empty( $module['capabilities'] ) ) {
					$this->modules_capabilities[ $key ] = (array) apply_filters( "yith_wcmv_{$ukey}_module_capabilities", $module['capabilities'] );
				}

				// Then check if module is active. If not, do not go further.
				if ( ! $this->is_module_active( $key ) ) {
					continue;
				}

				if ( ! empty( $module['post_types'] ) ) {
					$this->modules_post_types[ $key ] = apply_filters( "yith_wcmv_{$ukey}_module_post_types", $module['post_types'] );
				}

				if ( ! empty( $module['admin_sub_tabs'] ) ) {
					if ( count( $module['admin_sub_tabs'] ) > 1 ) {
						foreach ( $module['admin_sub_tabs'] as $s_key => $sub_tab ) {
							$s_key                                  = empty( $s_key ) ? 'modules-' . $key : 'modules-' . $key . '-' . $s_key;
							$this->modules_admin_sub_tabs[ $s_key ] = $sub_tab;
						}
					} else {
						$this->modules_admin_sub_tabs[ 'modules-' . $key ] = $module['admin_sub_tabs'];
					}
				}

				// Add mapped files if any.
				if ( ! empty( $module['autoload'] ) ) {
					$this->mapped_files = array_merge( $this->mapped_files, $module['autoload'] );
				}

				// Includes file if any.
				if ( ! empty( $module['includes'] ) ) {
					// Base path.
					$path = YITH_WPV_PATH . 'includes/modules/';

					foreach ( $module['includes'] as $section => $files ) {
						if ( ! $this->is_section( $section ) ) {
							continue;
						}

						if ( ! is_array( $files ) ) {
							$files = explode( ',', $files );
						}

						// Include files.
						foreach ( $files as $file ) {
							if ( file_exists( $path . $file ) ) {
								include_once $path . $file;
							}
						}
					}
				}
			}
		}

		/**
		 * Return the module active option name. Get backward compatibility with the old system.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $key The module key.
		 * @return string
		 */
		protected function get_module_option_name( $key ) {
			$ukey   = str_replace( '-', '_', $key );
			$option = "yith_wpv_vendors_option_{$ukey}_management";
			// Check if module have a dedicated option. Backward compatibility.
			if ( isset( $this->modules[ $key ] ) && isset( $this->modules[ $key ]['option_name'] ) ) {
				$option = $this->modules[ $key ]['option_name'];
			}

			return $option;
		}

		/**
		 * Check current section active for modules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $section The section to check.
		 * @return boolean
		 */
		protected function is_section( $section ) {
			// Handle admin request.
			switch ( $section ) {
				case 'admin':
					$value = yith_wcmv_is_admin_request();
					break;
				case 'frontend':
					$value = yith_wcmv_is_frontend_request();
					break;
				default:
					$value = true;
					break;
			}

			return $value;
		}

		/**
		 * Is module version required valid?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $module The module data.
		 * @return boolean
		 */
		protected function is_version_valid( $module ) {
			$compare           = isset( $module['compare'] ) ? $module['compare'] : '>=';
			$installed_version = ( isset( $module['installed_version'] ) && defined( $module['installed_version'] ) ) ? constant( $module['installed_version'] ) : 0;

			return ! isset( $module['min_version'] ) || version_compare( $installed_version, $module['min_version'], $compare );
		}

		/**
		 * Get all modules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_all_modules() {
			return $this->modules;
		}

		/**
		 * Is module available?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $module The module to check.
		 * @return boolean
		 */
		public function is_module_available( $module ) {
			if ( ! isset( $this->available_modules[ $module ] ) ) {
				// Search for module.
				$available = isset( $this->modules[ $module ] );
				// If available search for deps or min requirements.
				if ( $available &&
					( isset( $this->modules[ $module ]['premium'] ) && ( ! defined( $this->modules[ $module ]['premium'] ) || ! constant( $this->modules[ $module ]['premium'] ) ) ) ||
					! $this->is_version_valid( $this->modules[ $module ] ) ) {
					$available = false;
				}

				$this->available_modules[ $module ] = $available;
			}

			return $this->available_modules[ $module ];
		}

		/**
		 * Get available modules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_available_modules() {
			return array_keys( array_filter( $this->available_modules ) );
		}

		/**
		 * Is module active?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $module The module to check.
		 * @return boolean
		 */
		public function is_module_active( $module ) {
			if ( ! isset( $this->active_modules[ $module ] ) ) {
				// First of all check if module is available.
				$active = $this->is_module_available( $module );
				if ( $active ) {
					// Then check if module is active.
					$option = $this->get_module_option_name( $module );
					$active = 'yes' === get_option( $option, 'no' );
				}

				$this->active_modules[ $module ] = $active;
			}
			return $this->active_modules[ $module ];
		}

		/**
		 * Get active modules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_active_modules() {
			return array_keys( array_filter( $this->active_modules ) );
		}

		/**
		 * Check if user has a plugin module. This is an alias for is_module_available method.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $plugin_name The module to check.
		 * @return boolean
		 */
		public function has_plugin( $plugin_name ) {
			return $this->is_module_available( $plugin_name );
		}

		/**
		 * Handle AJAX module activation
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function activation_handler() {

			$module = isset( $_POST['module'] ) ? sanitize_text_field( wp_unslash( $_POST['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$status = ( isset( $_POST['status'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['status'] ) ) ) ? 'yes' : 'no';  // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $module ) || ! $this->is_module_available( $module ) ) {
				wp_send_json_error();
			}

			// Update option.
			$option = $this->get_module_option_name( $module );
			update_option( $option, $status );
			// Unset current stored status to let check again option.
			unset( $this->active_modules[ $module ] );

			// Update capabilities.
			YITH_Vendors_Capabilities::update_capabilities();

			$module = $this->modules[ $module ];
			$data   = ! empty( $module['admin_sub_tabs'] ) ? array( 'reload' => true ) : null;

			wp_send_json_success( $data );
		}

		/**
		 * Add modules capabilities.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $capabilities The capabilities array.
		 * @return array;
		 */
		public function add_modules_capabilities( $capabilities ) {
			if ( ! empty( $this->modules_capabilities ) && is_array( $this->modules_capabilities ) ) {
				// Filter capabilities for disabled module.
				$module_capabilities = array_filter(
					$this->modules_capabilities,
					function( $module ) {
						return $this->is_module_active( $module );
					},
					ARRAY_FILTER_USE_KEY
				);

				$capabilities = array_merge( $capabilities, $module_capabilities );
			}

			return $capabilities;
		}

		/**
		 * Add vendor post type to default array
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $post_types The default array value.
		 * @return array
		 */
		public function add_module_post_types( $post_types ) {
			foreach ( $this->modules_post_types as $module => $module_post_types ) {
				$post_types = array_merge( $post_types, $module_post_types );
			}

			return array_unique( $post_types ); // Avoid duplicated.
		}

		/**
		 * Check if there is at least a modules with admin tab
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function has_active_modules_settings() {
			return ! empty( $this->modules_admin_sub_tabs );
		}

		/**
		 * Add admin module sub-tabs to add-ons panel
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $sub_tabs An array of sub-tabs.
		 * @return array
		 */
		public function add_modules_sub_tab( $sub_tabs ) {
			return array_merge( $sub_tabs, $this->modules_admin_sub_tabs );
		}

		/**
		 * Add mapped files
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $files An array of mapped files for autoload.
		 * @return array
		 */
		public function add_mapped_files( $files ) {
			return array_merge( $files, $this->mapped_files );
		}
	}
}
