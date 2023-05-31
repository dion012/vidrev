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

if ( ! class_exists( 'YITH_Vendors_WPML' ) ) {
	/**
	 * Handle compatibility with WPML
	 *
	 * @class      YITH_Vendors_WPML
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_WPML {

		/**
		 * Main Instance
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var YITH_Vendors_WPML|null
		 */
		protected static $instance = null;

		/**
		 * The default language code
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var string
		 */
		protected $default_language = '';

		/**
		 * Vendors translations
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var string
		 */
		protected $translations = array();

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
		 * Main class instance
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_WPML Main instance
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
			if ( apply_filters( 'wpml_setting', false, 'setup_complete' ) ) {
				$this->init();
			}
		}

		/**
		 * Class initialization.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init() {

			$this->default_language = wpml_get_default_language();

			add_filter( 'yith_wcmv_get_vendor_meta_data', array( $this, 'filter_vendor_meta_data' ), 10, 3 );
			add_filter( 'yith_wcmv_vendor_dashboard_vendor_in_post', array( $this, 'filter_vendor_in_post' ), 10, 3 );
			add_filter( 'yith_wcmv_vendors_factory_read_vendor_id', array( $this, 'filter_vendor_id_factory' ), 10, 3 );
			// Extend taxonomy meta-box.
			add_filter( 'yith_wcmv_single_taxonomy_meta_box_vendor_slug', array( $this, 'filter_taxonomy_meta_box_vendor_slug' ), 10, 2 );
			// Filter default args for vendor shortcode.
			add_filter( 'yith_wcmv_shortcode_vendor_products_default_args', array( $this, 'filter_vendor_products_default_args' ), 10, 1 );
		}

		/**
		 * Get an array of translatable keys
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_translatable_keys() {
			return apply_filters(
				'yith_wcmv_get_translation_keys',
				array(
					'shipping_policy',
					'shipping_refund_policy',
				)
			);
		}

		/**
		 * Check if given vendor is a translation
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $vendor_id The vendor ID to check
		 * @return boolean
		 */
		public function is_vendor_a_translation( $vendor_id ) {
			$original_vendor = $this->get_vendor_translated( $vendor_id, $this->default_language );
			return $original_vendor && $original_vendor->get_id() !== $vendor_id;
		}

		/**
		 * Check if current vendor is a translation
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function is_current_vendor_a_translation() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				return $this->is_vendor_a_translation( $vendor->get_id() );
			}

			return false;
		}

		/**
		 * Get original vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $vendor_id The vendor ID.
		 * @param string  $language (Optional) The language to get translation for. The default is the current one.
		 * @return YITH_Vendor|false The vendor instance if found, false otherwise.
		 */
		protected function get_vendor_translated( $vendor_id, $language = '' ) {
			global $yith_wcmv_cache;

			if ( empty( $language ) ) {
				$language = wpml_get_current_language();
			}

			$cache_key            = "translation_{$language}";
			$translated_vendor_id = $yith_wcmv_cache->get_vendor_cache( $vendor_id, $cache_key );
			if ( false === $translated_vendor_id ) {
				$translated_vendor_id = yit_wpml_object_id( $vendor_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME, false, $language );
				// Store on cache.
				$yith_wcmv_cache->set_vendor_cache( $vendor_id, $cache_key, $translated_vendor_id );
			}

			$translated_vendor = ! empty( $translated_vendor_id ) ? yith_wcmv_get_vendor( $translated_vendor_id ) : false;

			return ( $translated_vendor && $translated_vendor->is_valid() ) ? $translated_vendor : false;
		}

		/**
		 * Filter vendor meta data
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param mixed       $value  The meta value.
		 * @param string      $key    The meta key.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return mixed
		 */
		public function filter_vendor_meta_data( $value, $key, $vendor ) {
			$translatable_keys = $this->get_translatable_keys();
			$current_vendor_id = $vendor->get_id();
			if ( ! in_array( $key, $translatable_keys, true ) && $this->is_vendor_a_translation( $current_vendor_id ) ) {
				$original_vendor = $this->get_vendor_translated( $current_vendor_id, $this->default_language );
				// Double check for original vendor.
				if ( $original_vendor ) {
					$value = $original_vendor->get_meta( $key );
				}
			}

			return $value;
		}

		/**
		 * Filter vendor associated with the given post ID.
		 * Always get the vendor associated with the original post.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param mixed   $vendor    Current vendor associated with given post id.
		 * @param integer $post_id   The post ID.
		 * @param string  $post_type (Optional) The post type. Default is post.
		 * @return mixed
		 */
		public function filter_vendor_in_post( $vendor, $post_id, $post_type = 'post' ) {
			$original_post_id = yit_wpml_object_id( $post_id, $post_type, true, $this->default_language );
			if ( $original_post_id !== $post_id ) {
				$vendor = yith_wcmv_get_vendor( $original_post_id, $post_type ); // If false, the product hasn't any vendor set.
			}

			return $vendor;
		}

		/**
		 * Filter vendor id associated with a post.
		 * Always get the original vendor for translated products or post.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $vendor_id   Current vendor ID.
		 * @param mixed   $object      The vendor object.
		 * @param string  $object_type What object is if is numeric (vendor|user|post).
		 * @return mixed
		 */
		public function filter_vendor_id_factory( $vendor_id, $object, $object_type ) {
			if ( 'post' === $object_type || 'product' === $object_type ) {
				$vendor = $this->get_vendor_translated( $vendor_id, $this->default_language );
				if ( $vendor ) {
					$vendor_id = $vendor->get_id();
				}
			}
			return $vendor_id;
		}

		/**
		 * Filter taxonomy meta-box vendor slug value.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string      $slug   Current vendor slug value.
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string
		 */
		public function filter_taxonomy_meta_box_vendor_slug( $slug, $vendor ) {

			global $pagenow;

			if ( $vendor && $vendor->is_valid() ) {
				$translated_vendor = $this->get_vendor_translated( $vendor->get_id() );
				if ( $translated_vendor ) {
					$slug = $translated_vendor->get_slug();
				}
			} elseif ( current_user_can( 'manage_woocommerce' ) && 'post-new.php' === $pagenow && ! empty( $_GET['trid'] ) ) {
				// Get original product from trid.
				$original_product_id = SitePress::get_original_element_id_by_trid( $_GET['trid'] );
				$original_vendor     = yith_wcmv_get_vendor( $original_product_id, 'product' );
				if ( $original_vendor && $original_vendor->is_valid() ) {
					$slug = $this->filter_taxonomy_meta_box_vendor_slug( '', $original_vendor );
				}
			}

			return $slug;
		}

		/**
		 * Filter default args for [yith_wcmv_vendor_products] shortcode.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param array $args Current default shortcode arguments.
		 * @return array
		 */
		public function filter_vendor_products_default_args( $args ) {
			if ( ! empty( $args['vendor_id'] ) ) {
				$original_vendor = $this->get_vendor_translated( $args['vendor_id'], $this->default_language );
				if ( $original_vendor ) {
					$args['vendor_id'] = $original_vendor->get_id();
				}
			}
			return $args;
		}
	}
}

if ( ! function_exists( 'YITH_Vendors_WPML' ) ) {
	/**
	 * Get single instance if the class
	 *
	 * @since  4.0.0
	 * @author Francesco Licandro
	 * @return YITH_Vendors_WPML
	 */
	function YITH_Vendors_WPML() {
		return YITH_Vendors_WPML::instance();
	}
}
