<?php
/**
 * YITH Vendors Admin Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin' ) ) {
	/**
	 * Vendor admin class
	 *
	 * @class      YITH_Vendors_Admin
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Admin extends YITH_Vendors_Admin_Legacy {

		/**
		 * Admin panel page
		 *
		 * @const string
		 */
		const PANEL_PAGE = 'yith_wpv_panel';

		/**
		 * YITH_Vendors_Admin_Vendor_Dashboard instance
		 *
		 * @var YITH_Vendors_Admin_Vendor_Dashboard | null
		 */
		protected $vendor_dashboard = null;

		/**
		 * YITH_Vendors_Admin_Order instance
		 *
		 * @var YITH_Vendors_Admin_Orders | null
		 */
		protected $orders = null;

		/**
		 * Current tab handler class instance
		 *
		 * @var mixed
		 */
		protected $tab_handler = null;

		/**
		 * AJAX handler class instance
		 *
		 * @var mixed
		 */
		protected $ajax_handler = null;

		/**
		 * Construct
		 */
		public function __construct() {
			$this->init();
			$this->init_vendor_dashboard();
			$this->register_hooks();
		}

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init() {

			YITH_Vendors_Admin_Assets::init();
			YITH_Vendors_Admin_Notices::init();

			$this->ajax_handler = new YITH_Vendors_Admin_Ajax();
			$this->orders       = new YITH_Vendors_Admin_Orders();
			// Load class based on current active tab.
			$tab = $this->get_plugin_current_tab();
			if ( $tab ) {
				$class = 'YITH_Vendors_Admin_' . ucfirst( $tab );

				if ( class_exists( $class ) ) {
					$this->tab_handler = new $class();
				}
			}
		}

		/**
		 * Init vendor dashboard
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function init_vendor_dashboard() {
			$this->vendor_dashboard = new YITH_Vendors_Admin_Vendor_Dashboard();
		}

		/**
		 * Register class hooks and filters
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_hooks() {
			// Plugin Information.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WPV_PATH . '/' . basename( YITH_WPV_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			// Add admin body class.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
			// Panel settings.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'admin_menu', array( $this, 'maybe_register_deprecated_panels' ), 15 );
			add_action( 'admin_init', array( $this, 'handle_deprecated_panel_redirect' ), 1 );
			// Redirect edit term link to the new plugin panel.
			add_action( 'admin_init', array( $this, 'maybe_redirect_to_vendor_section' ) );
			// Support to YITH Themes FW 2.0.
			add_filter( 'yit_layouts_taxonomies_list', array( $this, 'add_taxonomy_to_layouts' ) );
			// Custom manage users columns.
			add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );
			// WooCommerce Status Dashboard Widget.
			add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( $this, 'dashboard_status_widget_top_seller_query' ) );
			// Add filter products by vendor.
			add_filter( 'woocommerce_products_admin_list_table_filters', array( $this, 'products_admin_list_table_filters' ) );
			// Add custom class to plugin list table section.
			add_filter( 'yith_admin_tab_params', array( $this, 'add_list_tables_wrap_class' ) );
			// Manager custom plugin fields.
			add_filter( 'yith_plugin_fw_inline_fields_allowed_types', array( $this, 'customize_inline_fields_allowed' ), 10, 1 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'custom_panel_fields' ), 10, 2 );
			// Handle inline percentage symbol for number fields.
			add_action( 'yith_plugin_fw_get_field_number_after', array( $this, 'add_number_field_inline_description' ), 10, 1 );
		}

		/**
		 * Get plugin admin tabs
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_plugin_tabs() {
			$tabs = array(
				'dashboard'   => _x( 'Dashboard', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ),
				'commissions' => _x( 'Commissions', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ) . $this->get_pending_count_html( 'commissions' ),
				'vendors'     => YITH_Vendors_Taxonomy::get_plural_label( 'ucfirst' ) . $this->get_pending_count_html( 'vendors' ),
			);

			// Check for report permissions.
			if ( ! current_user_can( 'view_woocommerce_reports' ) ) {
				unset( $tabs['dashboard'] );
			}

			return apply_filters( 'yith_wcmv_admin_panel_tabs', $tabs );
		}

		/**
		 * Get pending count object html. Useful for admin views.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $object The object type to count.
		 * @return string
		 */
		protected function get_pending_count_html( $object ) {
			$html  = '';
			$count = 0;

			switch ( $object ) {
				case 'commissions':
					$count = count(
						yith_wcmv_get_commissions(
							array(
								'status' => 'pending',
								'fields' => 'ids',
							)
						)
					);
					break;

				case 'vendors':
					$count = count(
						yith_wcmv_get_vendors(
							array(
								'pending' => 'yes',
								'fields'  => 'ids',
								'number'  => -1,
							)
						)
					);
					break;
			}

			if ( ! empty( $count ) ) {
				$html = '<span class="pending-count">' . $count . '</span>';
			}

			return $html;
		}

		/**
		 * Check current request and maybe redirect to vendors section if is edit term request.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function maybe_redirect_to_vendor_section() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			global $pagenow;

			if ( 'term.php' !== $pagenow || ! isset( $_GET['taxonomy'] ) || YITH_Vendors_Taxonomy::TAXONOMY_NAME !== sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) ) {
				return;
			}

			$args = array( 'tab' => 'vendors' );
			if ( isset( $_GET['tag_ID'] ) ) {
				$args['s'] = absint( $_GET['tag_ID'] );
			}

			wp_safe_redirect( yith_wcmv_get_admin_panel_url( $args ) );
			exit;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get current active tab
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_plugin_current_tab() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! yith_wcmv_is_plugin_panel() ) {
				return '';
			}

			// Get tab from query string if set.
			if ( ! empty( $_GET['tab'] ) ) {
				return sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			}

			$tabs_key = array_keys( $this->get_plugin_tabs() );
			return array_shift( $tabs_key );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Action Links. Add the action links to plugin admin page.
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $links Links plugin array.
		 * @return array
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, self::PANEL_PAGE, false );
			return $links;
		}

		/**
		 * Define plugin row metas.
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array    $new_row_meta_args An array of plugin row meta.
		 * @param string[] $plugin_meta       An array of the plugin's metadata,
		 *                                    including the version, author,
		 *                                    author URI, and plugin URI.
		 * @param string   $plugin_file       Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data       An array of plugin data.
		 * @param string   $status            Status of the plugin. Defaults are 'All', 'Active',
		 *                                    'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                                    'Drop-ins', 'Search', 'Paused'.
		 * @param string   $init_file         Plugin init.
		 * @return array
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WPV_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = 'yith-woocommerce-multi-vendor';
			}

			if ( defined( 'YITH_WPV_FREE_INIT' ) && YITH_WPV_FREE_INIT === $plugin_file ) {
				$new_row_meta_args['support'] = array(
					'url' => 'https://wordpress.org/support/plugin/yith-woocommerce-product-vendors',
				);
			}

			return $new_row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @return   void
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$args = apply_filters(
				'yith_wcmv_plugin_panel_args',
				array(
					'create_menu_page' => true,
					'parent_slug'      => '',
					'page_title'       => 'YITH WooCommerce Multi Vendor',
					'menu_title'       => 'Multi Vendor',
					'capability'       => $this->get_panel_capability(),
					'parent'           => '',
					'parent_page'      => 'yit_plugin_panel',
					'page'             => self::PANEL_PAGE,
					'admin-tabs'       => $this->get_plugin_tabs(),
					'options-path'     => YITH_WPV_PATH . 'plugin-options',
					'class'            => yith_set_wrapper_class(),
					'help_tab'         => array(
						'main_video' => array(
							'desc' => _x( 'Check this video to learn how to <b>create a registration page for vendors</b>', '[HELP TAB] Video title', 'yith-woocommerce-product-vendors' ),
							'url'  => array(
								'en' => 'https://www.youtube.com/watch?v=YjVcpV3fyAA',
								'it' => 'https://www.youtube.com/watch?v=Bhqyhx9tm0s',
								'es' => 'https://www.youtube.com/watch?v=C4Zrj9Q0B7g',
							),
						),
						'playlists'  => array(
							'en' => 'https://www.youtube.com/playlist?list=PLDriKG-6905ml-hTvRAK9XLsGQMJ9FdSC',
							'it' => 'https://www.youtube.com/playlist?list=PL9Ka3j92PYJOgDy7iEYaaa_eQZmToRjNd',
							'es' => 'https://www.youtube.com/playlist?list=PL9c19edGMs08SImdWPM_Y6gPT06WaYucs',
						),
						'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474378-YITH-WOOCOMMERCE-MULTI-VENDOR',
					),
				)
			);

			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WPV_PATH . 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Get admin panel capability
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		protected function get_panel_capability() {
			return apply_filters( 'yith_wcmv_plugin_panel_capability', 'manage_options' );
		}

		/**
		 * Add an extra body classes for vendors dashboard
		 *
		 * @since  1.5.1
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $admin_body_classes Admin body classes.
		 * @return string
		 */
		public function admin_body_class( $admin_body_classes ) {
			global $post, $current_screen;

			$vendor            = yith_wcmv_get_vendor( 'current', 'user' );
			$is_order_details  = is_admin() && ! empty( $current_screen ) && 'shop_order' === $current_screen->id;
			$refund_management = 'yes' === get_option( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' );
			$quote_management  = 'yes' === get_option( 'yith_wpv_vendors_enable_request_quote', 'no' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$admin_body_classes .= ' vendor_limited_access';
				if ( $is_order_details && $refund_management ) {
					$admin_body_classes .= ' vendor_refund_management';
				}

				if ( function_exists( 'YITH_Vendors_Request_Quote' ) && $quote_management && $post instanceof WP_Post && YITH_Vendors_Request_Quote()->has_valid_quote_status( $post->ID ) ) {
					$admin_body_classes .= ' vendor_quote_management';
				}
			} elseif ( current_user_can( 'manage_woocommerce' ) ) {
				$admin_body_classes .= ' vendor_super_user';

				if ( $post && wp_get_post_parent_id( $post->ID ) && 'shop_order' === $post->post_type && $is_order_details ) {
					$admin_body_classes .= ' vendor_suborder_detail';
				}
			}

			if ( yith_wcmv_is_plugin_panel() ) {
				$admin_body_classes .= ' section-' . self::PANEL_PAGE;
			}

			return $admin_body_classes;
		}

		/**
		 * Handles the output of the roles column on the `users.php` screen.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param string  $output  The column output.
		 * @param string  $column  Current column.
		 * @param integer $user_id Current user ID.
		 * @return string
		 */
		public function manage_users_custom_column( $output, $column, $user_id ) {
			if ( 'roles' === $column ) {
				global $wp_roles;

				$user       = new WP_User( $user_id );
				$user_roles = array();
				$output     = esc_html__( 'None', 'yith-woocommerce-product-vendors' );

				if ( is_array( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						$user_roles[] = translate_user_role( $wp_roles->role_names[ $role ] );
					}
					$output = join( ', ', $user_roles );
				}
			}

			return $output;
		}

		/**
		 * Adds custom columns to the `users.php` screen.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param array $columns The table columns array.
		 * @return array
		 */
		public function manage_users_columns( $columns ) {
			// Unset the core WP `role` column.
			if ( isset( $columns['role'] ) ) {
				unset( $columns['role'] );
			}

			// Add our new roles column.
			$columns['roles'] = esc_html__( 'Roles', 'yith-woocommerce-product-vendors' );

			return $columns;
		}

		/**
		 * Add vendor taxonomy to YITH Theme fw 2.0 in layouts section
		 *
		 * @since  1.8.1
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $taxonomies Array of taxonomies.
		 * @return mixed Taxonomies array
		 */
		public function add_taxonomy_to_layouts( $taxonomies ) {
			$taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = get_taxonomy( YITH_Vendors_Taxonomy::TAXONOMY_NAME );

			return $taxonomies;
		}

		/**
		 * Get panel object
		 *
		 * @author Andrea Grillo
		 * @return YIT_Plugin_Panel_Woocommerce|null
		 */
		public function get_panel() {
			return $this->panel;
		}

		/**
		 * Get orders class object
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_Admin_Orders|null
		 */
		public function get_orders_handler() {
			return $this->orders;
		}

		/**
		 * Get orders class object
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_Admin_Vendor_Dashboard|null
		 */
		public function get_vendor_dashboard_handler() {
			return $this->vendor_dashboard;
		}

		/**
		 * Get AJAX handler class object
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_Admin_Ajax|null
		 */
		public function get_ajax_handler() {
			return $this->ajax_handler;
		}

		/**
		 * Get current settings tab class object if any
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return object|null
		 */
		public function get_current_tab_handler() {
			return $this->tab_handler;
		}

		/**
		 * Filter TopSeller query for WooCommerce Dashboard Widget
		 *
		 * @since  1.9.16
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $query Widget query array.
		 * @return array
		 */
		public function dashboard_status_widget_top_seller_query( $query ) {
			$query['where'] .= 'AND posts.post_parent = 0';

			return $query;
		}

		/**
		 * Add a filter by vendor in products list in admin area only if current user can manager WooCommerce.
		 *
		 * @author Francesco Licandro
		 * @param array $filters Array of enabled filters.
		 * @return array
		 */
		public function products_admin_list_table_filters( $filters ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$tax_name             = YITH_Vendors_Taxonomy::TAXONOMY_NAME;
				$filters[ $tax_name ] = array( $this, 'render_products_category_filter' );
			}

			return $filters;
		}

		/**
		 * Filter by Vendor render on products page in admin
		 *
		 * @author Francesco Licandro
		 * @return void
		 */
		public function render_products_category_filter() {
			global $wp_query;

			$taxonomy_label = YITH_Vendors_Taxonomy::get_taxonomy_labels();
			$taxonomy_name  = YITH_Vendors_Taxonomy::TAXONOMY_NAME;

			$args = array(
				'pad_counts'         => 1,
				'show_count'         => 1,
				'hierarchical'       => 1,
				'hide_empty'         => 1,
				'show_uncategorized' => 1,
				'orderby'            => 'name',
				'selected'           => isset( $wp_query->query_vars[ $taxonomy_name ] ) ? $wp_query->query_vars[ $taxonomy_name ] : '',
				// translators: %s stand for the vendor taxonomy singular name.
				'show_option_none'   => sprintf( __( 'No %s' ), strtolower( $taxonomy_label['singular_name'] ) ),
				'option_none_value'  => '',
				'value_field'        => 'slug',
				'taxonomy'           => $taxonomy_name,
				'name'               => $taxonomy_name,
				'class'              => 'dropdown_product_vendor',
			);

			if ( 'order' === $args['orderby'] ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'order'; // phpcs:ignore
			}

			wp_dropdown_categories( $args );
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return 'https://yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/';
		}

		/**
		 * Add list table section wrap class to get plugin FW style
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $args The page arguments.
		 * @return array
		 */
		public function add_list_tables_wrap_class( $args ) {
			if ( self::PANEL_PAGE !== $args['page'] ) {
				return $args;
			}

			if ( in_array( $args['current_tab'], array( 'vendors', 'commissions' ), true ) && ( empty( $args['current_sub_tab'] ) || in_array( $args['current_sub_tab'], array( 'vendors-list', 'commissions-list' ), true ) ) ) {
				$args['wrap_class'] .= ' yith-plugin-ui--classic-wp-list-style yith-plugin-fw-wp-page-wrapper';
			}

			return $args;
		}

		/**
		 * Filter custom plugin panel location
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $field_template THe field default template location.
		 * @param array  $field          The field to load.
		 * @return string
		 */
		public function custom_panel_fields( $field_template, $field ) {
			$allowed_types = apply_filters( 'yith_wcmv_allowed_custom_panel_fields', array( 'ajax-vendors', 'vendor-registration-table', 'options-table', 'price', 'upload-attachment' ) );
			if ( isset( $field['type'] ) && in_array( $field['type'], $allowed_types, true ) ) {
				$field_template = YITH_WPV_PATH . 'includes/admin/views/fields/' . $field['type'] . '.php';
			}
			return $field_template;
		}

		/**
		 * Customize inline fields allowed types
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $allowed An array of allowed types.
		 * @return array
		 */
		public function customize_inline_fields_allowed( $allowed ) {
			if ( yith_wcmv_is_plugin_panel() ) {
				$allowed[] = 'price';
			}
			return $allowed;
		}

		/**
		 * Add percentage symbol for special number fields.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $field The fields' data.
		 * @return void
		 */
		public function add_number_field_inline_description( $field ) {
			if ( ! empty( $field['inline_description'] ) ) {
				echo '<span class="inline-description">' . esc_html( $field['inline_description'] ) . '</span>';
			}
		}
	}
}
