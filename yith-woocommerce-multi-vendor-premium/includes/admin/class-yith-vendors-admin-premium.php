<?php
/**
 * YITH Vendors Admin Premium Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Premium' ) ) {
	/**
	 * Premium extension of YITH_Vendors_Admin class
	 *
	 * @class      YITH_Vendors_Admin_Premium
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Admin_Premium extends YITH_Vendors_Admin {

		/**
		 * Products helper class instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Admin_Products|null
		 */
		protected $products = null;

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init() {
			parent::init();
			// Init products helper class.
			$this->products = new YITH_Vendors_Admin_Products();
		}

		/**
		 * Init vendor dashboard
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init_vendor_dashboard() {
			$this->vendor_dashboard = new YITH_Vendors_Admin_Vendor_Dashboard_Premium();
		}

		/**
		 * Register class hooks and filters
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_hooks() {
			parent::register_hooks();

			// Register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// Customize plugin panel.
			add_filter( 'yith_wcmv_admin_panel_tabs', array( $this, 'admin_tabs' ) );
			add_action( 'yith_wcmv_vendors_modules_tab', array( $this, 'modules_tab_content' ), 99 );

			// Regenerate Permalink after panel update.
			add_action( 'yit_panel_wc_before_update', array( $this, 'check_rewrite_rules' ) );
			add_action( 'yit_panel_wc_before_reset', array( $this, 'check_rewrite_rules' ) );

			// Check for vendor's owner.
			add_action( 'admin_notices', array( $this, 'check_vendors_owner' ) );
			add_action( 'yith_wcmv_empty_vendor_object_cache', array( $this, 'delete_check_vendors_owner_transient' ) );

			// JSON Search Vendors using direct request using WooCommerce AJAX system.
			add_action( 'wp_ajax_yith_json_search_vendors', array( $this, 'json_search_vendors' ) );

			$this->register_gutenberg_block();
		}

		/**
		 * Get products class object
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return object|null
		 */
		public function get_products_handler() {
			return $this->products;
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function register_plugin_for_activation() {
			if ( function_exists( 'YIT_Plugin_Licence' ) ) {
				YIT_Plugin_Licence()->register( YITH_WPV_INIT, YITH_WPV_SECRET_KEY, YITH_WPV_SLUG );
			}
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function register_plugin_for_updates() {
			if ( function_exists( 'YIT_Upgrade' ) ) {
				YIT_Upgrade()->register( YITH_WPV_SLUG, YITH_WPV_INIT );
			}
		}

		/**
		 * Add options tab
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $tabs An array of panel tabs.
		 * @return array
		 */
		public function admin_tabs( $tabs ) {
			$tabs['frontend-pages'] = __( 'Store & Product Pages', 'yith-woocommerce-product-vendors' );
			$tabs['other']          = __( 'Other', 'yith-woocommerce-product-vendors' );
			$tabs['modules']        = __( 'Modules', 'yith-woocommerce-product-vendors' );

			return $tabs;
		}

		/**
		 * Modules tab content
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function modules_tab_content() {

			$modules   = YITH_Vendors_Modules_Handler::instance()->get_all_modules();
			$available = YITH_Vendors_Modules_Handler::instance()->get_available_modules();

			ksort( $modules );
			// Move available module first.
			$modules = array_merge( array_flip( $available ), $modules );

			// Prepare modules.
			foreach ( $modules as &$module ) {
				if ( isset( $module['landing_uri'] ) ) {
					$module['landing_uri'] = yith_plugin_fw_add_utm_data( $module['landing_uri'], 'yith-woocommerce-multi-vendor', 'add-ons', 'wp-premium-dashboard' );
				}
			}

			yith_wcmv_include_admin_template( 'modules-list', array( 'modules' => $modules ) );
		}

		/**
		 * Register plugin gutenberg block
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @return void
		 */
		protected function register_gutenberg_block() {
			$product_cat           = get_terms( array( 'taxonomy' => 'product_cat' ) );
			$product_cat_gutenberg = array( 'none' => esc_html_x( 'All categories', 'Short Label', 'yith-woocommerce-product-vendors' ) );
			if ( ! is_wp_error( $product_cat ) ) {
				foreach ( $product_cat as $term ) {
					$product_cat_gutenberg[ $term->slug ] = $term->name;
				}
			}

			$blocks = array(
				'yith-wcmv-list'            => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Vendors List', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'Show a list of vendors.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_list',
					'keywords'       => array(
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Vendor list', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),
					'attributes'     => array(
						'per_page'                => array(
							'type'    => 'number',
							'label'   => _x( 'Number of vendors to show per page', '[gutenberg]: attributes description', 'yith-woocommerce-product-vendors' ),
							'default' => -1,
							'min'     => -1,
							'max'     => 50,
						),
						'include'                 => array(
							'type'    => 'text',
							'label'   => _x( 'Add the vendors\' IDs, comma-separated, to be included. I.E.: 16, 34, 154, 78', '[gutenberg]: attributes description', 'yith-woocommerce-product-vendors' ),
							'default' => '',
						),
						'hide_no_products_vendor' => array(
							'type'    => 'toggle',
							'label'   => _x( 'Vendors without products', '[gutenberg]: attribute description', 'yith-woocommerce-product-vendors' ),
							'default' => false,
							'helps'   => array(
								'checked'   => _x( 'Hide vendors', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
								'unchecked' => _x( 'Show all', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
							),
						),
						'show_description'        => array(
							'type'    => 'toggle',
							'label'   => _x( 'Description', '[gutenberg]: attribute description', 'yith-woocommerce-product-vendors' ),
							'default' => false,
							'helps'   => array(
								'checked'   => _x( 'Show vendor description', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
								'unchecked' => _x( 'Hide vendor description', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
							),
						),
						'description_lenght'      => array(
							'type'    => 'number',
							'default' => 40,
							'min'     => 5,
							'max'     => apply_filters( 'yith_wcmv_vendor_list_description_max_lenght', 400 ),
						),
						'vendor_image'            => array(
							'type'    => 'select',
							'label'   => _x( 'Which image do you want to use?', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'store'    => _x( 'Store image', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'gravatar' => _x( 'Vendor logo', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'store',
						),
						'orderby'                 => array(
							'type'    => 'select',
							'label'   => _x( 'Order by: defines the parameter for vendors organization within the list', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'id'          => _x( 'ID', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'name'        => _x( 'Name', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'slug'        => _x( 'Slug', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'description' => _x( 'Description', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'name',
						),
						'order'                   => array(
							'type'    => 'select',
							'label'   => _x( 'Ascending or descending order?', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'ASC' => _x( 'Ascending', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'DSC' => _x( 'Descending', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'ASC',
						),
					),
				),
				'yith-wcmv-become-a-vendor' => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Become a vendor', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'Add a form for users to register as vendors.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_become_a_vendor',
					'keywords'       => array(
						_x( 'Become a vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Registration form', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),
				),
				'yith-wcmv-vendor-name'     => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Vendor name', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'This shows the name of one vendor. It can also link to the vendor\'s page.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_vendor_name',
					'keywords'       => array(
						_x( 'Vendor name', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),

					'attributes'     => array(
						'show_by'  => array(
							'type'    => 'select',
							'default' => 'vendor',
							'label'   => _x( 'Get vendor by', '[gutenberg]: Get vendor by name, by products, by user', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'product' => _x( 'Product ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
								'user'    => _x( 'Owner ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
								'vendor'  => _x( 'Vendor ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
							),
						),
						'value'    => array(
							'type'    => 'text',
							'default' => 0,
							'label'   => _x( 'ID', '[gutenberg]: Option title', 'yith-woocommerce-product-vendors' ),
						),
						'type'     => array(
							'type'    => 'select',
							'label'   => _x( 'Make the vendor name clickable', '[gutenberg]: Option description', 'yith-woocommerce-product-vendors' ),
							'default' => true,
							'options' => array(
								'no'   => _x( 'No', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'link' => _x( 'Yes', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
						),
						'category' => array(
							'type'    => 'select',
							'label'   => _x( 'Filter products by category', '[gutenberg]: Option description', 'yith-woocommerce-product-vendors' ),
							'default' => '',
							'options' => $product_cat_gutenberg,
						),
					),
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * Check if needs to refresh rewrite rules for frontpage
		 *
		 * @since    4.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author   Francesco Licandro
		 * @return void
		 */
		public function check_rewrite_rules() {
			if ( ! empty( $_POST['yith_wpv_vendor_taxonomy_rewrite'] ) && get_option( 'yith_wpv_vendor_taxonomy_rewrite', '' ) !== sanitize_text_field( wp_unslash( $_POST['yith_wpv_vendor_taxonomy_rewrite'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				update_option( 'yith_wcmv_flush_rewrite_rules', true );
			}
		}

		/**
		 * Check for vendor without owner
		 *
		 * @since    1.6
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @return  void
		 */
		public function check_vendors_owner() {
			if ( ! yith_wcmv_is_plugin_panel() || ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$vendors = get_transient( 'yith_wcmv_check_vendors_owner_cache' );
			if ( false === $vendors ) {
				$vendors = YITH_Vendors_Factory::query( array( 'owner' => '' ) );
				set_transient( 'yith_wcmv_check_vendors_owner_cache', $vendors );
			}

			if ( empty( $vendors ) ) {
				return;
			}

			?>
			<div class="notice notice-warning">
				<p>
					<?php
					// translators: %d is the number of vendors with no owner.
					echo wp_kses_post( sprintf( __( '<strong>Warning</strong>: no owner(s) set on %d vendor shop(s). Please, set an owner for each vendor shop in order to enable the shop(s).', 'yith-woocommerce-product-vendors' ), count( $vendors ) ) );
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Delete transient on vendor object cache reset
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function delete_check_vendors_owner_transient() {
			delete_transient( 'yith_wcmv_check_vendors_owner_cache' );
		}

		/**
		 * Check and get the revision message for vendors
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author Francesco Licandro
		 * @param boolean|YITH_Vendor $vendor Current vendor object.
		 * @param boolean             $terms If terms must be accepted.
		 * @param boolean             $privacy If privacy policy must be accepted.
		 * @return string
		 */
		public function get_revision_message( $vendor = false, $terms = false, $privacy = false ) {

			if ( ! $vendor ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( ! $vendor || ! $vendor->is_valid() ) {
					return '';
				}

				$terms   = YITH_Vendors()->is_terms_and_conditions_require() && $vendor->has_terms_and_conditions_accepted();
				$privacy = YITH_Vendors()->is_privacy_policy_require() && $vendor->has_privacy_policy_accepted();

			}

			if ( ! $terms && ! $privacy ) {
				return '';
			}

			$action       = get_option( 'yith_wpv_manage_terms_and_privacy_revision_actions', 'no_action' );
			$endpoint_url = wc_get_account_endpoint_url( 'terms-of-service' );
			// Get pages title.
			$terms_page_title   = $terms ? get_the_title( get_option( 'yith_wpv_terms_and_conditions_page_id', 0 ) ) : '';
			$privacy_page_title = $privacy ? get_the_title( get_option( 'yith_wpv_privacy_page', 0 ) ) : '';

			switch ( $action ) {
				case 'disable_now':
					if ( $terms && $privacy ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the privacy policy page title.
						$message = sprintf( __( 'The %1$s and %2$s have been modified and your profile has been disabled for sale. To reactivate it, please accept our terms of service and privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $terms_page_title );
					} elseif ( $terms ) {
						// translators: %s stand for terms and conditions page title.
						$message = sprintf( __( 'The %s have been modified and your profile has been disabled for sale. To reactivate it, please accept our terms of service again from this page', 'yith-woocommerce-product-vendors' ), $terms_page_title );
					} elseif ( $privacy ) {
						// translators: %s stand for privacy policy page title.
						$message = sprintf( __( 'The %s has been modified and your profile has been disabled for sale. To reactivate it, please accept our privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title );
					}
					break;

				case 'disable_after':
					// Get last modified.
					$last_terms_modified   = $terms ? strtotime( YITH_Vendors()->get_last_modified_data_terms_and_conditions() ) : 0;
					$last_privacy_modified = $privacy ? strtotime( YITH_Vendors()->get_last_modified_data_privacy_policy() ) : 0;
					$days                  = 'disable_after' === $action ? get_option( 'yith_wpv_manage_terms_and_privacy_revision_disable_after', 3 ) : 0;

					$max_last_modified  = max( $last_terms_modified, $last_privacy_modified );
					$today              = current_time( 'Y-m-d' );
					$max_last_modified += ( $days * DAY_IN_SECONDS );

					$datetime1     = new DateTime( $today );
					$datetime2 		= new DateTime( date( 'Y-m-d', $max_last_modified ) ); // phpcs:ignore
					$disable_after = $datetime1->diff( $datetime2 )->d;

					if ( $terms && $privacy ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the privacy policy page title, %3$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s and %2$s have been modified and your profile will be disabled in %3$s days. To reactivate it, please accept our terms of service and privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $terms_page_title, $disable_after );
					} elseif ( $terms ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s have been modified and your profile will be disabled in %2$s days. To reactivate it, please accept our terms of service again from this page', 'yith-woocommerce-product-vendors' ), $terms_page_title, $disable_after );
					} elseif ( $privacy ) {
						// translators: %1$s stand for privacy policy page title, %2$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s has been modified and your profile will be disabled in %2$s days. To reactivate it, please accept our privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $disable_after );
					}
					break;

				case 'no_action':
					break;
			}

			return ! empty( $message ) ? sprintf( '%s <a href="%s">%s</a>', $message, $endpoint_url, $endpoint_url ) : '';
		}


		/**
		 * Print the error message on admin area for vendors
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 */
		public function print_check_revision_message() {

			// Get the current vendor.
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}

			$terms_must_be_accepted   = YITH_Vendors()->is_terms_and_conditions_require() && ! $vendor->has_terms_and_conditions_accepted();
			$privacy_must_be_accepted = YITH_Vendors()->is_privacy_policy_require() && ! $vendor->has_privacy_policy_accepted();
			if ( ! $terms_must_be_accepted && ! $privacy_must_be_accepted ) {
				return;
			}

			$message = $this->get_revision_message( $vendor, $terms_must_be_accepted, $privacy_must_be_accepted );
			if ( ! empty( $message ) ) {
				?>
				<div class="notice notice-error">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * JSON search for vendors using the WooCommerce AJAX system.
		 * Backward compatibility with old system.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function json_search_vendors() {
			check_ajax_referer( 'search-products', 'security' );

			$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $term ) ) {
				die();
			}

			$vendors = YITH_Vendors_Factory::search( $term );
			wp_send_json( apply_filters( 'yith_wcmv_json_search_found_vendors', $vendors ) );
		}
	}
}
