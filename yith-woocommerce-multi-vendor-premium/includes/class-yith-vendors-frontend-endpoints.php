<?php
/**
 * YITH Vendors Frontend Endpoints Class.
 * THis class is useful to manage my account endpoints and actions.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


if ( ! class_exists( 'YITH_Vendors_Frontend_Endpoints' ) ) {
	/**
	 * Class YITH_Vendors_Frontend_Endpoints
	 *
	 * @author Andrea Grillo
	 * @author Francesco Licandro
	 */
	class YITH_Vendors_Frontend_Endpoints {

		/**
		 * The Terms of Service endpoint key
		 *
		 * @var string
		 */
		public $terms_endpoint = '';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 */
		public function __construct() {

			$this->terms_endpoint = YITH_Vendors()->get_account_endpoint();
			$this->init_terms_endpoint_hooks();

			// Customize my account dashboard for vendor.
			add_action( 'woocommerce_account_dashboard', array( $this, 'vendor_dashboard_endpoint' ) );
		}

		/**
		 * Init terms endpoint hooks anf filters
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init_terms_endpoint_hooks() {
			$manage_revision  = get_option( 'yith_wpv_manage_terms_and_privacy_revision', 'no' );
			$privacy_required = get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
			$terms_required   = get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );

			if ( 'yes' === $manage_revision && ( 'yes' === $privacy_required || 'yes' === $terms_required ) ) {
				add_filter( 'woocommerce_account_menu_items', array( $this, 'add_vendor_menu_items' ), 20, 1 );
				add_action( 'woocommerce_account_' . $this->terms_endpoint . '_endpoint', array( $this, 'show_term_of_service_content' ) );
				add_filter( 'woocommerce_endpoint_' . $this->terms_endpoint . '_title', array( $this, 'show_term_of_service_endpoint_title' ) );

				// Handle form submit.
				add_action( 'wp_loaded', array( $this, 'handle_form_submit' ), 20 );
			}
		}

		/**
		 * Add vendor dashboard endpoint in my Account
		 *
		 * @since 4.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param boolean | YITH_Vendor $vendor (Optional) The vendor object instance or false to get current. Default false.
		 * @return void
		 */
		public function vendor_dashboard_endpoint( $vendor = false ) {

			if ( empty( $vendor ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$args = array(
					'is_pending'  => $vendor->is_in_pending(),
					'vendor_name' => $vendor->get_name(),
				);

				yith_wcmv_get_template( 'my-vendor-dashboard', $args, 'woocommerce/myaccount' );
			}
		}

		/**
		 * Add plugin query vars
		 *
		 * @since 1.0.0
		 * @author Andrea Grillo
		 * @param array $query_vars An array of query vars.
		 * @return array
		 * @deprecated
		 */
		public function add_vendor_query_vars( $query_vars ) {
			$query_vars[] = $this->terms_endpoint;

			return $query_vars;
		}

		/**
		 * Add my account menu item
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param array $menu_items The array of My Account menu items.
		 * @return array
		 */
		public function add_vendor_menu_items( $menu_items ) {

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor && $vendor->is_valid() ) {
				if ( isset( $menu_items['customer-logout'] ) ) {
					$logout = $menu_items['customer-logout'];
					unset( $menu_items['customer-logout'] );
				}

				$menu_items['terms-of-service'] = __( 'Terms of Service', 'yith-woocommerce-product-vendors' );

				if ( ! empty( $logout ) ) {
					$menu_items['customer-logout'] = $logout;
				}
			}

			return $menu_items;
		}

		/**
		 * Show my account terms of service endpoint content
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @return void
		 */
		public function show_term_of_service_content() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				add_filter( 'wp_kses_allowed_html', array( $this, 'add_style_to_allowed_post_tags' ), 10, 2 );
				yith_wcmv_get_template( 'terms-of-service.php', array( 'vendor' => $vendor ), 'woocommerce/myaccount' );
				remove_filter( 'wp_kses_allowed_html', array( $this, 'add_style_to_allowed_post_tags' ), 10 );
			}
		}

		/**
		 * Allow </style> html tag for wp_kses_post. Using filter wp_kses_allowed_html.
		 * This is useful when using template builders like elementor
		 *
		 * @since 4.0.4
		 * @author Francesco Licandro
		 * @param array  $tags An array of allowed tags.
		 * @param string $context Allowed tag context.
		 * @return array
		 */
		public function add_style_to_allowed_post_tags( $tags, $context ) {
			if ( 'post' === $context ) {
				$tags['style'] = array();
			}

			return $tags;
		}

		/**
		 * Return my account terms of service endpoint title
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param string $title THe default title value.
		 * @return string
		 */
		public function show_term_of_service_endpoint_title( $title ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				return esc_html__( 'Terms of Service', 'yith-woocommerce-product-vendors' );
			}

			return $title;
		}

		/**
		 * Handle terms form submit
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function handle_form_submit() {

			if ( empty( $_REQUEST['yith_mv_accept_temrs_and_privacy_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['yith_mv_accept_temrs_and_privacy_nonce'] ) ), 'yith-mv-accept-terms-and-privacy' ) ) {
				return;
			}

			$vendor_id = isset( $_REQUEST['yith_vendor_id'] ) ? absint( $_REQUEST['yith_vendor_id'] ) : '';

			$vendor = $vendor_id ? yith_wcmv_get_vendor( $vendor_id, 'vendor' ) : false;
			if ( $vendor && $vendor->is_valid() ) {

				$enable_vendor_selling = true;

				if ( YITH_Vendors()->is_terms_and_conditions_require() ) {
					if ( isset( $_REQUEST['yith_mv_accept_terms'] ) ) {
						$vendor->set_meta( 'data_terms_and_condition', YITH_Vendors()->get_last_modified_data_terms_and_conditions() );
					} else {
						$enable_vendor_selling = false;
					}
				}

				if ( YITH_Vendors()->is_privacy_policy_require() ) {
					if ( isset( $_REQUEST['yith_mv_accept_privacy'] ) ) {
						$vendor->set_meta( 'data_privacy_policy', YITH_Vendors()->get_last_modified_data_privacy_policy() );
					} else {
						$enable_vendor_selling = false;
					}
				}

				if ( $enable_vendor_selling ) {
					$vendor->set_enable_selling( 'yes' );
				}

				$vendor->save();
			}

			wp_safe_redirect( wc_get_account_endpoint_url( 'terms-of-service' ) );
			exit;
		}
	}
}
