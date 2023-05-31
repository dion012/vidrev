<?php
/**
 * YITH Vendors Admin Vendor Dashboard
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Vendor_Dashboard' ) ) {
	/**
	 * Vendor admin dashboard class
	 *
	 * @class      YITH_Vendors_Admin_Vendor_Dashboard
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Admin_Vendor_Dashboard {

		/**
		 * Panel handler class instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Admin_Vendor_Dashboard_Panel|null
		 */
		protected $panel_handler = null;

		/**
		 * Current vendor
		 *
		 * @var YITH_Vendor | null
		 */
		protected $vendor = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 */
		public function __construct() {
			$this->vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $this->vendor && $this->vendor->is_valid() ) {
				$this->register_common_hooks();

				if ( $this->vendor->has_limited_access() ) {
					$this->register_limited_access_hooks();
				}
			}
		}

		/**
		 * Register common vendor hooks
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_common_hooks() {
			// Remove admin only notices.
			add_filter( 'woocommerce_helper_suppress_connect_notice', array( $this, 'suppress_connect_notice' ) );
			remove_action( 'admin_notices', 'update_nag', 3 );
			// Remove 3rd-part meta-boxes.
			add_action( 'add_meta_boxes_product', array( $this, 'remove_meta_boxes' ), 99 );
			add_filter( 'map_meta_cap', array( $this, 'remove_jetpack_menu_page' ), 10, 4 ); // TODO double check.

			do_action( 'yith_wcmv_vendor_dashboard_hooks', $this->vendor );
		}

		/**
		 * Register limited access hooks
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_limited_access_hooks() {

			// Init panel handler.
			$this->panel_handler = new YITH_Vendors_Admin_Vendor_Dashboard_Panel( $this->vendor );

			// Style and scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'add_style_and_scripts' ), 1 );
			// Hide YITH Licence messages.
			add_action( 'admin_init', array( $this, 'hide_licence_notices' ), 15 );
			// Customize admin menu.
			add_action( 'admin_menu', array( $this, 'filter_menu_items' ), 99 );
			add_action( 'admin_menu', array( $this, 'remove_dashboard_widgets' ) );
			// Filter content.
			add_action( 'pre_get_posts', array( $this, 'filter_content' ), 20, 1 );
			add_filter( 'wp_count_posts', array( $this, 'filter_count_posts' ), 10, 3 );
			add_action( 'current_screen', array( $this, 'restrict_edit_vendor_content' ), 0 );
			// Restrict add products.
			add_action( 'current_screen', array( $this, 'restrict_add_vendor_product' ), 0, 1 );
			// Essential Grid Support.
			add_action( 'add_meta_boxes', array( $this, 'remove_ess_grid_metabox' ), 20 );
			// Associate vendor taxonomy once post type is saved.
			add_action( 'save_post', array( $this, 'add_vendor_taxonomy_to_post_type' ), 10, 2 );
			// Let's limited vendors be able to add product attributes.
			add_action( 'wp_ajax_woocommerce_add_new_attribute', array( $this, 'add_new_attribute' ), 5 );
			// Allow WooCommerce admin access for vendor admins.
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
			// Filter AJAX search products.
			add_filter( 'woocommerce_json_search_found_products', array( $this, 'filter_json_search_found_products' ), 10, 1 );
			// Remove quick edit taxonomy from product.
			add_filter( 'quick_edit_show_taxonomy', array( $this, 'remove_quick_edit_vendor_taxonomy' ), 10, 3 );

			do_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', $this->vendor );
		}

		/**
		 * Get panel handler class instance.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return YITH_Vendors_Admin_Vendor_Dashboard_Panel|null
		 */
		public function get_panel_handler() {
			return $this->panel_handler;
		}

		/**
		 * Hide plugin licence notice to vendor.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function hide_licence_notices() {
			remove_action( 'admin_notices', array( YITH_Plugin_Licence(), 'activation_license_notice' ), 15 );
		}

		/**
		 * Remove quick edit vendor taxonomy for vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param boolean $show_in_quick_edit Whether to show the current taxonomy in Quick Edit.
		 * @param string  $taxonomy_name      Taxonomy name.
		 * @param string  $post_type          Post type of current Quick Edit post.
		 * @return boolean
		 */
		public function remove_quick_edit_vendor_taxonomy( $show_in_quick_edit, $taxonomy_name, $post_type ) {
			if ( YITH_Vendors_Taxonomy::TAXONOMY_NAME === $taxonomy_name && 'product' === $post_type ) {
				return false;
			}
			return $show_in_quick_edit;
		}

		/**
		 * Filter admin repost dashboard content based on current vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $query_args The report query args.
		 * @return array
		 */
		public function filter_reports_content( $query_args ) {
			$query_args['vendor_id'] = $this->vendor->get_id();
			return $query_args;
		}

		/**
		 * Suppress the WooCommerce connect store notice.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $suppress The current value.
		 * @return boolean
		 */
		public function suppress_connect_notice( $suppress ) {
			return true;
		}

		/**
		 * Remove Jetpack pages for Vendor
		 *
		 * @author Francesco Licandro
		 * @param array   $caps    Array of capabilities.
		 * @param string  $cap     Current cap.
		 * @param integer $user_id The user ID.
		 * @param array   $args    An array of arguments.
		 * @return array
		 */
		public function remove_jetpack_menu_page( $caps, $cap, $user_id, $args ) {
			if ( 'jetpack_admin_page' === $cap ) {
				$caps[] = 'manage_options';
			}

			return $caps;
		}

		/**
		 * Enqueue custom style and scripts for vendor dashboard
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function add_style_and_scripts() {

			YITH_Vendors_Admin_Assets::add_css( 'vendor-dashboard-admin', 'vendor-dashboard.css' );
			YITH_Vendors_Admin_Assets::add_js(
				'vendors-admin',
				'vendors-dashboard.js',
				array( 'wc-enhanced-select' ),
				array(
					'yith_wcmv_vendors',
					array(
						'uploadFrameTitle'       => esc_html__( 'Choose an image', 'yith-woocommerce-product-vendors' ),
						'uploadFrameButtonText'  => esc_html__( 'Use image', 'yith-woocommerce-product-vendors' ),
						'countries'              => wp_json_encode( WC()->countries->get_states() ),
						'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ), // Keep the WooCommerce text domain.
						'orderDataToShow'        => array(
							'customer' => get_option( 'yith_wpv_vendors_option_order_show_customer', 'no' ),
							'address'  => get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ),
							'payment'  => get_option( 'yith_wpv_vendors_option_order_show_payment', 'no' ),
						),
						'hideFeaturedProduct'    => ! $this->vendor->can_handle_featured_products(),
					),
				)
			);
		}

		/**
		 * Get a list of allowed post type for vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_allowed_post_type() {
			$allowed_post_types = apply_filters( 'yith_wcmv_vendor_allowed_vendor_post_type', array( 'product' ) );
			return array_unique( $allowed_post_types );
		}

		/**
		 * Get a list of allowed manu items
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_allowed_menu_items() {
			$items = array(
				'index.php',
				'separator1',
				'profile.php',
				'separator-last',
				YITH_Vendors_Admin::PANEL_PAGE,
				// Backward compatibility with modules.
				'yith-plugins_page_pdf_invoice_for_multivendor',
				'yith_woocommerce_subscription',
				'yith_vendor_nyp_settings',
				'yith_wapo_panel',
				'yith_wpv_deprecated_panel_commissions',
				'yith_wpv_deprecated_panel_dashboard',
			);

			if ( current_user_can( 'moderate_comments' ) ) {
				$items[] = 'edit-comments.php';
			}

			// Add allowed post type.
			foreach ( $this->get_allowed_post_type() as $post_type ) {
				$items[] = "edit.php?post_type={$post_type}";
			}

			return apply_filters( 'yith_wcmv_admin_vendor_menu_items', $items );
		}

		/**
		 * Is menu item registered?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $menu_slug The menu item slug to search.
		 * @return boolean
		 */
		protected function is_menu_item_registered( $menu_slug ) {
			global $menu;

			foreach ( $menu as $k => $item ) {
				if ( $menu_slug === $item[2] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Filter WP menu items for vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function filter_menu_items() {
			global $menu;

			$allowed_items = $this->get_allowed_menu_items();
			foreach ( $menu as $page ) {
				if ( ! empty( $page[2] ) && ! in_array( $page[2], $allowed_items, true ) ) {
					remove_menu_page( $page[2] );
				}
			}

			do_action( 'yith_wcmv_filtered_vendor_menu_items', $this->vendor );
		}

		/**
		 * Remove widgets for vendor dashboard
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function remove_dashboard_widgets() {
			add_filter( 'jetpack_just_in_time_msgs', '__return_false', 999 );
			add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false' );

			$to_removes = apply_filters(
				'yith_wcmv_to_remove_dashboard_widgets',
				array(
					array(
						'id'      => 'woocommerce_dashboard_status',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_activity',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'woocommerce_dashboard_recent_reviews',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_right_now',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_quick_press',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'yith_wcmc_dashboard_widget',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'jetpack_summary_widget',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'wpseo-dashboard-overview',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
				)
			);

			foreach ( $to_removes as $widget ) {
				remove_meta_box( $widget['id'], $widget['screen'], $widget['context'] );
			}
		}

		/**
		 * Filter content based on current vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param WP_Query $query The Wp_Query instance.
		 * @return void
		 */
		public function filter_content( &$query ) {

			// If this is not an allowed post type, exit.
			$post_type = $query->get( 'post_type' );

			if ( $post_type && ! in_array( $post_type, $this->get_allowed_post_type(), true ) ) {
				return;
			}

			// Let's third party modify the standard query filter.
			if ( false !== has_action( "yith_wcmv_vendor_filter_content_{$post_type}" ) ) {
				do_action_ref_array( "yith_wcmv_vendor_filter_content_{$post_type}", array( &$query, $this->vendor ) );
			} else {

				$conditions = $query->get( 'tax_query' );
				if ( ! is_array( $conditions ) ) {
					$conditions = array();
				}

				if ( ! $this->vendor_condition_applied( $conditions ) ) {
					$conditions[] = array(
						'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
						'field'    => 'id',
						'terms'    => $this->vendor->get_id(),
					);

					$query->set( 'tax_query', $conditions );
				}
			}
		}

		/**
		 * Check if vendor taxonomy conditions is already applied
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $conditions The conditions to check.
		 * @return boolean
		 */
		protected function vendor_condition_applied( $conditions ) {
			foreach ( $conditions as $condition ) {
				if ( isset( $condition['taxonomy'] ) && YITH_Vendors_Taxonomy::TAXONOMY_NAME === $condition['taxonomy'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Filter WP count posts for vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param object $counts An object containing the current post_type's post
		 *                       counts by status.
		 * @param string $type   Post type.
		 * @param string $perm   The permission to determine if the posts are 'readable'
		 *                       by the current user.
		 * @return object
		 */
		public function filter_count_posts( $counts, $type, $perm ) {

			if ( ! in_array( $type, $this->get_allowed_post_type(), true ) || apply_filters( "yith_wcmv_skip_{$type}_filter_count_post", false ) ) {
				return $counts;
			}

			// Create a cache key.
			$cache_key = _count_posts_cache_key( 'vendor_' . $type, $perm );
			$counts    = wp_cache_get( $cache_key, 'counts' );
			if ( false !== $counts ) {
				// We may have cached this before every status was registered.
				foreach ( get_post_stati() as $status ) {
					if ( ! isset( $counts->{$status} ) ) {
						$counts->{$status} = 0;
					}
				}

				return $counts;
			}

			// Let's third party filter the counts. Useful for module or external plugins.
			$counts = apply_filters( "yith_wcmv_vendor_filter_count_post_{$type}", false, $this->vendor );
			if ( false === $counts ) {
				$posts  = $this->vendor->count_posts( $type );
				$counts = array_fill_keys( get_post_stati(), 0 );

				foreach ( $posts as $post ) {
					$counts[ $post->post_status ] = $post->count;
				}
			}

			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'counts' );

			return $counts;
		}

		/**
		 * Restrict vendors from editing other vendors' posts
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function restrict_edit_vendor_content() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_POST['post_ID'] ) || empty( $_GET['post'] ) || ! apply_filters( 'yith_wcmv_vendor_disabled_manage_other_vendors_posts', true ) ) {
				return;
			}

			$post_id = absint( $_GET['post'] );
			$post    = get_post( $post_id );

			if ( empty( $post ) || ! in_array( $post->post_type, $this->get_allowed_post_type(), true ) ) {
				return;
			}

			$post_type = $post->post_type;
			if ( false !== has_action( "yith_wcmv_restrict_edit_{$post_type}_vendor" ) ) {
				do_action( "yith_wcmv_restrict_edit_{$post_type}_vendor", $post, $this->vendor );
			} else {

				$current_vendor = yith_wcmv_get_vendor( $post_id, 'post' );
				// Let's filter current vendor associated with the post.
				$current_vendor = apply_filters( 'yith_wcmv_vendor_dashboard_vendor_in_post', $current_vendor, $post_id, $post_type );

				if ( $current_vendor && absint( $this->vendor->get_id() ) !== absint( $current_vendor->get_id() ) ) {
					$post_type_obj = get_post_type_object( $post_type );
					// translators: %s stand for the post type name.
					wp_die( sprintf( esc_html__( 'You do not have permission to edit this %s.', 'yith-woocommerce-product-vendors' ), strtolower( $post_type_obj->labels->singular_name ) ) );
				}
			}

			do_action( 'yith_wcmv_restrict_edit_content_vendor', $post, $this->vendor );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Restrict add products for vendors if options is enabled
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param WP_Screen $screen Current screen instance.
		 * @return void
		 */
		public function restrict_add_vendor_product( $screen ) {
			if ( 'add' === $screen->action && 'product' === $screen->post_type && ! $this->vendor->can_add_products() ) {
				$products_limit = apply_filters( 'yith_wcmv_vendors_products_limit', get_option( 'yith_wpv_vendors_product_limit', 25 ), $this->vendor );
				// translators: %1$s is the product number limit for vendor, %2$s and %3$s are open and close html tag for an anchor.
				wp_die( sprintf( __( 'You are not allowed to create more than %1$s products. %2$sClick here to return to your admin area%3$s.', 'yith-woocommerce-product-vendors' ), $products_limit, '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
			}
		}

		/**
		 * Remove 3rd-party plugin meta-boxes
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @retur  void
		 */
		public function remove_meta_boxes() {

			$to_remove = apply_filters(
				'yith_wcmv_remove_product_metaboxes',
				array(
					array(
						'id'      => 'ckwc',
						'screen'  => null,
						'context' => 'side',
					),
				)
			);

			foreach ( $to_remove as $r ) {
				$r = wp_parse_args(
					$r,
					array(
						'id'      => '',
						'screen'  => null,
						'context' => 'advanced',
					)
				);

				if ( ! empty( $r['id'] ) ) {
					remove_meta_box( $r['id'], $r['screen'], $r['context'] );
				}
			}
		}

		/**
		 * Remove Essential Grid Meta-box
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @return   void
		 */
		public function remove_ess_grid_metabox() {
			remove_meta_box( 'eg-meta-box', 'product', 'normal' );
		}

		/**
		 * Add vendor taxonomy to post types
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer|string $post_id The post ID.
		 * @param WP_Post        $post    The post object.
		 * @return void
		 */
		public function add_vendor_taxonomy_to_post_type( $post_id, $post ) {
			global $wp_taxonomies, $yith_wcmv_cache;

			// Let's skip taxonomy association.
			$post_type = $post ? $post->post_type : '';
			if ( ! $post_type || ! in_array( $post_type, $this->get_allowed_post_type(), true ) || ! apply_filters( "yith_wcmv_add_vendor_taxonomy_to_{$post_type}", true, $post, $this->vendor ) || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Double check if post type has the taxonomy registered.
			if ( ! isset( $wp_taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] ) || ! in_array( $post_type, $wp_taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ]->object_type, true ) ) {
				return;
			}

			wp_set_object_terms( $post_id, $this->vendor->get_slug(), YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			// Delete vendor cache for post type.
			$yith_wcmv_cache->delete_vendor_cache( $this->vendor->get_id() );
		}

		/**
		 * Short circuit add new attribute via AJAX function.
		 * Refer to public_html/wp-content/plugins/woocommerce/includes/class-wc-ajax.php:592
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public static function add_new_attribute() {
			check_ajax_referer( 'add-attribute', 'security' );

			if ( isset( $_POST['taxonomy'], $_POST['term'] ) ) {
				$taxonomy = esc_attr( wp_unslash( $_POST['taxonomy'] ) ); // phpcs:ignore
				$term     = wc_clean( wp_unslash( $_POST['term'] ) ); // phpcs:ignore

				if ( taxonomy_exists( $taxonomy ) ) {

					$result = wp_insert_term( $term, $taxonomy );

					if ( is_wp_error( $result ) ) {
						wp_send_json(
							array(
								'error' => $result->get_error_message(),
							)
						);
					} else {
						$term = get_term_by( 'id', $result['term_id'], $taxonomy );
						wp_send_json(
							array(
								'term_id' => $term->term_id,
								'name'    => $term->name,
								'slug'    => $term->slug,
							)
						);
					}
				}
				wp_die( -1 );
			}
		}

		/**
		 * If an user is a vendor admin remove the woocommerce prevent admin access
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $prevent_access Current value: true to prevent admin access, false otherwise.
		 * @return boolean
		 */
		public function prevent_admin_access( $prevent_access ) {
			if ( $this->vendor->is_user_admin() ) {
				return false;
			}

			return $prevent_access;
		}

		/**
		 * Filter JSON found products for current vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param array $products An array of current found products.
		 * @return array
		 */
		public function filter_json_search_found_products( $products ) {
			$vendor_products = $this->vendor->get_products( array( 'post_type' => array( 'product', 'product_variation' ) ) );
			if ( empty( $vendor_products ) ) {
				return array();
			}

			$products = array_intersect_key( $products, array_flip( $vendor_products ) );
			return $products;
		}
	}
}
