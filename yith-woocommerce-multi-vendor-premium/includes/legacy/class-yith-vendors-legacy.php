<?php
/*
 * Legacy class for YITH Vendors. This class includes all deprecated methods and arguments that are going to be removed on future release.
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
if ( ! class_exists( 'YITH_Vendors_Legacy' ) ) {
	/**
	 * Class YITH_Vendors
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	abstract class YITH_Vendors_Legacy {

		/**
		 * User Meta Key
		 *
		 * @since 1.0
		 * @access protected
		 * @var string
		 */
		protected $user_meta_key = 'yith_product_vendor';

		/**
		 * User Meta Key
		 *
		 * @since 1.0
		 * @access protected
		 * @var string
		 */
		protected $user_meta_owner = 'yith_product_vendor_owner';

		/**
		 * Taxonomy handler Class
		 *
		 * @since 1.9.17
		 * @var YITH_Vendors_Taxonomy | null
		 */
		public $taxonomy = null;

		/**
		 * Main Shipping Class
		 *
		 * @var YITH_Vendors_Shipping
		 * @since 1.9.17
		 * @deprecated
		 */
		public $shipping = null;

		/**
		 * YITH_WCMV_Addons class instance
		 *
		 * @var YITH_Vendors_Modules_Handler
		 * @deprecated
		 */
		public $addons = null;

		/**
		 * YITH_Vendors_Gateways class instance
		 *
		 * @var YITH_Vendors_Gateways|null
		 * @deprecated
		 */
		public $gateways = null;

		/**
		 * Required classes
		 *
		 * @since 1.0
		 * @var array
		 */
		public $require = array(
			'admin'    => array(),
			'frontend' => array(),
			'common'   => array(),
		);

		/**
		 * Magic __get method
		 *
		 * @param string $key The key requested.
		 * @since 4.0.0
		 */
		public function __get( $key ) {

			switch ( $key ) {
				case 'termmeta_table':
					global $wpdb;
					return $wpdb->termmeta;

				case 'termmeta_term_id':
					return 'term_id';

				case 'is_wc_lower_2_6':
					return false;

				case 'gateways':
					return function_exists( 'YITH_Vendors_Gateways' ) ? YITH_Vendors_Gateways() : null;

				case 'shipping':
					return function_exists( 'YITH_Vendors_Shipping' ) ? YITH_Vendors_Shipping() : null;

				case 'addons':
					return YITH_Vendors_Modules_Handler::instance();
			}
		}

		/**
		 * Register taxonomy for vendors
		 *
		 * @since  1.0
		 * @author Andrea Grillo
		 * @return void
		 * @deprecated
		 */
		public function register_vendors_taxonomy() {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::register_taxonomy();
		}

		/**
		 * Get the vendors taxonomy label
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $arg The string to return. Default empty. If is empty return all taxonomy labels.
		 * @return Array The taxonomy label
		 * @deprecated
		 */
		public function get_vendors_taxonomy_label( $arg = '' ) {
			// _deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_taxonomy_labels( $arg );
		}

		/**
		 * Get the vendor singular label
		 *
		 * @author Andrea Grilllo <andrea.grillo@yithemes.com>
		 * @param string $callback
		 * @return string
		 * @deprecated
		 */
		public function get_singular_label( $callback = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_singular_label( $callback );
		}

		/**
		 * Set the vendor singular label
		 *
		 * @author Andrea Grilllo <andrea.grillo@yithemes.com>
		 * @param string $singular_label The vendor singular label.
		 * @return void
		 * @deprecated
		 */
		public function set_singular_label( $singular_label = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::set_singular_label( $singular_label );
		}

		/**
		 * Get the vendor  plural  label
		 *
		 * @author Andrea Grilllo <andrea.grillo@yithemes.com>
		 * @param string $callback
		 * @return string
		 * @deprecated
		 */
		public function get_plural_label( $callback = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_plural_label( $callback );
		}

		/**
		 * Set the vendor plural label
		 *
		 * @author Andrea Grilllo <andrea.grillo@yithemes.com>
		 * @param string $plural_label The vendor plural label.
		 * @return void
		 * @deprecated
		 */
		public function set_plural_label( $plural_label = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::set_plural_label( $plural_label );
		}

		/**
		 * Update the term meta
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param int    $term_id Term ID.
		 * @param string $meta_key Metadata key.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param mixed  $prev_value Optional. Previous value to check before updating.
		 * @return int|bool|WP_Error
		 * @deprecated
		 */
		public function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'update_term_meta' );

			return update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
		}

		/**
		 * Delete the term meta
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param int    $term_id Term ID.
		 * @param string $meta_key Metadata name.
		 * @param mixed  $meta_value Optional. Metadata value. If provided,
		 *                           rows will only be removed that match the value.
		 *                           Must be serializable if non-scalar. Default empty.
		 * @return bool True on success, false on failure.
		 * @deprecated
		 */
		public function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'delete_term_meta' );

			return delete_term_meta( $term_id, $meta_key, $meta_value );
		}

		/**
		 * Add the term meta
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param int    $term_id Term ID.
		 * @param string $meta_key Metadata name.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param bool   $unique Optional. Whether the same key should not be added. Default false.
		 * @return int|false|WP_Error
		 * @deprecated
		 */
		public function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
			_deprecated_function( __METHOD__, '4.0.0', 'add_term_meta' );

			return add_term_meta( $term_id, $meta_key, $meta_value, $unique );
		}

		/**
		 * Get the term meta
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param int    $term_id Term ID.
		 * @param string $key The meta key to retrieve.
		 * @param bool   $single Optional. Whether to return a single value. Default true.
		 * @return mixed
		 * @deprecated
		 */
		public function get_term_meta( $term_id, $key, $single = true ) {
			_deprecated_function( __METHOD__, '4.0.0', 'get_term_meta' );

			return get_term_meta( $term_id, $key, $single );
		}

		/**
		 * Select the termeta table.
		 * The table woocommerce_termeta was removed in WooCommerce 2.6
		 *
		 * @since  1.9.8
		 * @author Andrea Grillo <andrea.grillo@yitheme.com>
		 * @return void
		 * @deprecated
		 */
		public function select_termmeta_table() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Get the protected attribute taxonomy name
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string The taxonomy name
		 * @deprecated
		 */
		public function get_taxonomy_name() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Taxonomy::TAXONOMY_NAME' );
			return YITH_Vendors_Taxonomy::TAXONOMY_NAME;
		}

		/**
		 * Add Vendor Role.
		 *
		 * @fire register_activation_hook
		 * @since 1.6.5
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 * @deprecated
		 */
		public static function add_vendor_role() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::add_role' );
			YITH_Vendors_Capabilities::add_role();
		}

		/**
		 * Remove Vendor Role.
		 *
		 * @fire register_deactivation_hook
		 * @since 1.6.5
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 * @deprecated
		 */
		public static function remove_vendor_role() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::remove_role' );
			YITH_Vendors_Capabilities::remove_role();
		}

		/**
		 * Set up array of vendor admin capabilities
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return array Vendor capabilities
		 * @deprecated
		 */
		public function vendor_enabled_capabilities() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::get_capabilities' );
			return YITH_Vendors_Capabilities::get_capabilities();
		}

		/**
		 * Get protected attribute role_name
		 *
		 * @since 1.6.5
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string
		 * @deprecated
		 */
		public function get_role_name() {
			_deprecated_function( __METHOD__, '4.0.0', 'const YITH_Vendors_Capabilities::ROLE_NAME' );
			return YITH_Vendors_Capabilities::ROLE_NAME;
		}

		/**
		 * Plugin Setup
		 *
		 * @fire register_activation_hook
		 * @since 1.6.5
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $method
		 * @return void
		 * @deprecated
		 */
		public static function setup( $method = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::setup' );
			YITH_Vendors_Capabilities::setup( $method );
		}

		/**
		 * Get vendors list
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $args
		 * @return Array Vendor Objects
		 * @deprecated
		 */
		public function get_vendors( $args = array() ) {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_get_vendors' );
			return yith_wcmv_get_vendors( $args );
		}

		/**
		 * Load plugin modules
		 *
		 * @since  4.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function load_admin_modules() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$required = array();

			// WooCommerce Customer/Order CSV Export.
			if ( function_exists( 'wc_customer_order_csv_export' ) ) {
				$required['admin'][] = 'includes/modules/module.yith-wc-customer-order-export-support.php';
			}

			! empty( $required ) && $this->load_required( $required );
		}

		/**
		 * Remove new post and comments wp bar admin menu for vendor
		 *
		 * @since  1.5.1
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function remove_wp_bar_admin_menu() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );
			}
		}

		/**
		 * Return if PayPal Email is required or not
		 *
		 * @since 1.7
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return bool
		 * @deprecated
		 */
		public function is_paypal_email_enabled() {
			return 'yes' === get_option( 'yith_wpv_vendors_registration_show_paypal_email', 'yes' );
		}

		/**
		 * Return if PayPal Email is required or not
		 *
		 * @since 1.7
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string
		 * @deprecated
		 */
		public function is_paypal_email_required() {
			return $this->is_paypal_email_enabled() ? 'yes' === get_option( 'yith_wpv_vendors_registration_required_paypal_email', 'no' ) : false;
		}

		/**
		 * Locate core template file
		 *
		 * @since  1.0
		 * @param $core_file
		 * @param $template
		 * @param $template_base
		 * @return array Vendor capabilities
		 */
		public function locate_core_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				// HTML Email
				'emails/commissions-paid.php',
				'emails/commissions-unpaid.php',
				'emails/vendor-commissions-paid.php',
				'emails/new-vendor-registration.php',
				'emails/vendor-new-account.php',
				'emails/vendor-new-order.php',
				'emails/vendor-cancelled-order.php',
				'emails/commissions-bulk.php',

				// Plain Email
				'emails/plain/commissions-paid.php',
				'emails/plain/commissions-unpaid.php',
				'emails/plain/vendor-commissions-paid.php',
				'emails/plain/new-vendor-registration.php',
				'emails/plain/vendor-new-account.php',
				'emails/plain/vendor-new-order.php',
				'emails/plain/vendor-cancelled-order.php',
				'emails/plain/commissions-bulk.php',
			);

			if ( in_array( $template, $custom_template ) ) {
				$core_file = YITH_WPV_TEMPLATE_PATH . $template;
			}

			return $core_file;
		}

		/**
		 * Save extra taxonomy fields for product vendors taxonomy
		 *
		 * @since  1.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param float       $commission The vendor commission.
		 * @param integer     $vendor_id  The vendor id.
		 * @param YITH_Vendor $vendor     The vendor instance.
		 * @param integer     $product_id The product id.
		 * @return string The vendor commissions
		 * @deprecated
		 */
		public function get_commission( $commission, $vendor_id, $vendor, $product_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $commission;
		}

		/**
		 * Gets the message of the privacy to display.
		 * To be overloaded by the implementor.
		 *
		 * @return string
		 * @deprecated
		 */
		public function get_privacy_message() {
			_deprecated_function( __METHOD__, '4.0.0' );
			$content = '
			<div contenteditable="false">' .
				'<p class="wp-policy-help">' .
				__( 'This sample language includes the basics around what personal data your store may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store may vary. We recommend consulting with a lawyer when deciding what information to disclose on your Privacy Policy.', 'yith-woocommerce-product-vendors' ) .
				'</p>' .
				'</div>' .
				'<p>' . __( 'We collect information about you during the checkout process on our store.', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<h2>' . __( 'What we collect and store', 'yith-woocommerce-product-vendors' ) . '</h2>' .
				'<p>' . __( 'While you visit our site, we’ll track:', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<ul>' .
				'<li>' . __( 'Vendors data: we will use this information to create vendor profiles and allow them to sell their products on the site in exchange for a commission on sales. ', 'yith-woocommerce-product-vendors' ) . '</li>' .
				'<li>' . __( 'Data required to create a store: store name and description, header image, store logo, address, email address, phone number, VAT/SSN, legal notes, social network links (Facebook, Twitter, LinkedIn, YouTube, Vimeo, Instagram, Pinterest, Flickr, Behance, Tripadvisor), payment information (IBAN and/or PayPal email address), and information related to commissions and payments made.', 'yith-woocommerce-product-vendors' ) . '</li>' .
				'</ul>' .
				'<div contenteditable="false">' .
				'<h2>' . __( 'Who on our team has access', 'yith-woocommerce-product-vendors' ) . '</h2>' .
				'<p>' . __( 'Members of our team have access to the information you provide to us. For example, both Administrators and Shop Managers can access:', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'</div>';

			return $content;
		}

		/**
		 * Add or Remove  publish_products capabilities to vendor admins when global option change
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $vendors An array of vendors.
		 * @return   void|string
		 * @deprecated
		 */
		public function force_skip_review_option( $vendors = array() ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json( 'complete' );
			}
		}

		/**
		 * Return the user meta key
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string The protected attribute User Meta Key
		 * @deprecated
		 */
		public function get_user_meta_key() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_user_meta_key' );
			return yith_wcmv_get_user_meta_key();
		}

		/**
		 * Return the user meta key
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string The protected attribute User Meta Key
		 * @deprecated
		 */
		public function get_user_meta_owner() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_user_meta_owner' );
			return yith_wcmv_get_user_meta_owner();
		}

		/**
		 * Get the vendor commission
		 *
		 * @since 1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @return string The vendor commission.
		 * @deprecated
		 */
		public function get_base_commission() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_base_commission' );
			return yith_wcmv_get_base_commission();
		}
	}
}
