<?php
/**
 * YITH Vendors Frontend Class
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */
/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Frontend' ) ) {

	/**
	 * Class YITH_Vendors_Frontend
	 *
	 * @author Andrea Grillo
	 * @author Francesco Licandro
	 */
	class YITH_Vendors_Frontend extends YITH_Vendors_Frontend_Legacy {

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 */
		public function __construct() {

			// Shop Page.
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'shop_loop_item_vendor_name' ), 6 );
			add_action( 'woocommerce_product_query', array( $this, 'check_vendors_selling_capabilities' ), 10, 1 );

			// Single Product.
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_vendor_tab' ) );
			add_action( 'woocommerce_single_product_summary', array( $this, 'single_product_vendor_name' ), 5 );
			add_action( 'template_redirect', array( $this, 'exit_direct_access_no_selling_capabilities' ) );

			// Ajax Product Filter Support.
			add_filter( 'yith_wcan_product_taxonomy_type', array( $this, 'add_taxonomy_page' ) );

			// MyAccount -> My Order: Disable suborder view.
			add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'my_account_my_orders_query' ) );
			add_filter( 'woocommerce_customer_get_downloadable_products', array( $this, 'get_downloadable_products' ) );

			// Support to Adventure Tours Product Type.
			class_exists( 'WC_Tour_WP_Query' ) && add_filter( 'yith_wcmv_vendor_get_products_query_args', array( $this, 'add_wc_tour_query_type' ) );

			// Body Classes.
			add_filter( 'body_class', array( $this, 'body_class' ), 20 );

			// Support to YITH Theme FW 2.0 - Sidebar Layout.
			add_filter( 'yit_layout_option_is_product_tax', array( $this, 'show_sidebar_in_vendor_store_page' ) );
		}

		/**
		 * Add product vendor tabs in single product page
		 * check if the product is property of a specific vendor and add a new tab "Vendor" with the vendor information
		 *
		 * @since  1.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param array $tabs The single product tabs array.
		 * @return array The tab array
		 */
		public function add_product_vendor_tab( $tabs ) {
			global $product;

			$vendor   = yith_wcmv_get_vendor( $product, 'product' );
			$show_tab = defined( 'YITH_WPV_FREE_INIT' ) || 'yes' === get_option( 'yith_wpv_show_vendor_tab_in_single', 'yes' );

			if ( $vendor->is_valid() && $show_tab ) {

				$tab_title = apply_filters( 'yith_wcmv_single_product_vendor_tab_name', get_option( 'yith_wpv_vendor_tab_text_text', YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) ) );

				$args = array(
					'title'    => empty( $tab_title ) ? YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) : $tab_title,
					'priority' => 99,
					'callback' => array( $this, 'get_vendor_tab' ),
				);

				// Use yith_wc_vendor as array key. Not use vendor to prevent conflict with wc vendor extension.
				$tabs['yith_wc_vendor'] = apply_filters( 'yith_wcmv_single_product_vendor_tab_args', $args );
			}

			return $tabs;
		}

		/**
		 * Get Vendor product tab template
		 *
		 * @since    1.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @return   void
		 */
		public function get_vendor_tab() {
			global $product;

			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( $vendor && $vendor->is_valid() ) {
				$args = array(
					'vendor'             => $vendor,
					'vendor_name'        => $vendor->get_name(),
					'vendor_description' => $vendor->get_description(),
					'vendor_url'         => $vendor->get_url(),
				);

				$args = apply_filters( 'yith_wcmv_single_product_vendor_tab_template_args', $args );

				yith_wcmv_get_template( 'vendor-tab', $args, 'woocommerce/single-product' );
			}
		}

		/**
		 * Check if vendor name must be shown in current section.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		protected function show_vendor_name() {
			global $product;

			if (
				yith_wcmv_is_vendor_page() ||
				( empty( $product ) || ! $product instanceof WC_Product ) ||
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_single', 'yes' ) && is_product() ) ||
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_categories', 'yes' ) && is_product_taxonomy() ) ||
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_loop', 'yes' ) && is_shop() ) ||
				! apply_filters( 'yith_wcmv_show_vendor_name_template', true )
			) {
				return false;
			}

			return true;
		}

		/**
		 * Add vendor name in shop loop product and single product page.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function shop_loop_item_vendor_name() {
			global $product;

			if ( ! $this->show_vendor_name() ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( $vendor && $vendor->is_valid() ) {
				$args = apply_filters(
					'yith_wcmv_shop_loop_vendor_name_template_args',
					array(
						'vendor' => $vendor,
					)
				);

				yith_wcmv_get_template( 'vendor-name', $args, 'woocommerce/loop' );
			}
		}

		/**
		 * Add vendor name in single product page.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function single_product_vendor_name() {
			global $product;

			if ( ! $this->show_vendor_name() ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( $vendor && $vendor->is_valid() ) {

				$args = array(
					'vendor' => $vendor,
				);

				// Add item sold.
				if ( 'no' !== get_option( 'yith_wpv_vendor_show_item_sold', 'no' ) ) {
					// translators: %itemsold% is a placeholder for the number of items sold.
					$label              = get_option( 'yith_wpv_vendor_item_sold_label', __( '%itemsold% orders', 'yith-woocommerce-product-vendors' ) );
					$sales              = $product instanceof WC_Product ? $product->get_total_sales() : 0;
					$args['sales_info'] = str_replace( '%itemsold%', $sales, $label );
				}

				yith_wcmv_get_template( 'vendor-name', apply_filters( 'yith_wcmv_shop_loop_vendor_name_template_args', $args ), 'woocommerce/single-product' );
			}
		}

		/**
		 * Check if vendor has selling capabilities, if not exclude it from WC Product Query
		 *
		 * @since    1.0.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @param WP_Query $query Query object.
		 * @param boolean  $set   (Optional) True to set vendor tax query in main query, false to return it.
		 * @return   mixed|void
		 */
		public function check_vendors_selling_capabilities( $query, $set = true ) {
			// TODO: add cache
			$exclude_temp_1 = yith_wcmv_get_vendors(
				array(
					'enabled_selling' => false,
					'fields'          => 'ids',
					'number'          => -1,
				)
			);

			$exclude_temp_2 = yith_wcmv_get_vendors(
				array(
					'owner'  => false,
					'fields' => 'ids',
					'number' => -1,
				)
			);

			$exclude = array_unique( array_merge( $exclude_temp_1, $exclude_temp_2 ) );
			$exclude = apply_filters( 'yith_wcmv_to_exclude_terms_in_loop', $exclude );
			if ( ! empty( $exclude ) ) {
				$vendor_tax_query = array(
					'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
					'field'    => 'id',
					'terms'    => $exclude,
					'operator' => 'NOT IN',
				);

				if ( $set ) {
					$current_tax_query   = isset( $query->query_vars['tax_query'] ) ? $query->query_vars['tax_query'] : array();
					$current_tax_query[] = $vendor_tax_query;
					$query->set( 'tax_query', $current_tax_query );
				} else {
					return array( $vendor_tax_query );
				}
			}
		}

		/**
		 * Exit if the vendor account hasn't selling capabilities
		 *
		 * @since    1.0.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @return   void
		 */
		public function exit_direct_access_no_selling_capabilities() {
			global $post;

			$vendor = false;
			if ( yith_wcmv_is_vendor_page() ) {
				$term   = get_queried_object();
				$vendor = yith_wcmv_get_vendor( $term->slug );
			} elseif ( ! empty( $post->post_type ) && is_singular( 'product' ) ) {
				$vendor = yith_wcmv_get_vendor( $post, 'product' );
			}

			if ( $vendor && $vendor->is_valid() && ! $vendor->is_selling_enabled() ) {
				if ( apply_filters( 'yith_wcmv_do_404_redirect', true ) ) {
					$this->redirect_404();
				} else {
					do_action( 'yith_wcmv_404_redirect', $vendor );
				}
			}
		}

		/**
		 * Exit if the vendor account hasn't selling capabilities
		 *
		 * @since    1.0.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @param boolean $exit (Optional) Default: true. If true call exit function.
		 * @return   void
		 */
		public function redirect_404( $exit = true ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );

			include get_query_template( '404' );

			if ( $exit ) {
				exit;
			}
		}

		/**
		 * Add vendor taxonomy page to Ajax Product Filter plugin
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @param array $pages The widget taxonomy pages.
		 * @return mixed|array The allowed taxonomy
		 */
		public function add_taxonomy_page( $pages ) {
			$pages[] = YITH_Vendors_Taxonomy::TAXONOMY_NAME;

			return $pages;
		}

		/**
		 * Filter the My account -> My Order page
		 * Disable suborder view
		 *
		 * @since  1.6.0
		 * @author Andrea Grillo
		 * @param array $query_args Unfiltered query args.
		 * @return array
		 */
		public function my_account_my_orders_query( $query_args ) {
			$query_args['post_parent'] = 0;

			return $query_args;
		}

		/**
		 * Filter download permission (show only parent order)
		 *
		 * @author Salvatore Strano
		 * @author Francesco Licandro
		 * @param array $downloads An array of available downloads.
		 * @return array
		 */
		public function get_downloadable_products( $downloads ) {

			$new_downloads = array();

			foreach ( $downloads as $download ) {
				$order = wc_get_order( absint( $download['order_id'] ) );
				// Show only parent order download.
				if ( $order && ! $order->get_parent_id() ) {
					$new_downloads[] = $download;
				}
			}

			return $new_downloads;
		}

		/**
		 * Add Support to Adventure Tours Product Type
		 * Add the correct wc_query arg to get_posts array
		 *
		 * @since  1.9.13
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $args The WP_Query array.
		 * @return array The WP_Query array.
		 */
		public function add_wc_tour_query_type( $args ) {
			$args['wc_query'] = 'tours';
			return $args;
		}

		/**
		 * Add body classes on frontend
		 *
		 * @since  1.9.18
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $classes Current array of body classes.
		 * @return array body classes array
		 */
		public function body_class( $classes ) {
			if ( is_user_logged_in() ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor->is_valid() ) {
					$classes[] = 'yith_wcmv_user_is_vendor';
				} else {
					$classes[] = 'yith_wcmv_user_is_not_vendor';
				}
			}

			return $classes;
		}

		/**
		 * Support to single vendor sidebar for YITH FW 2.0 theme
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param boolean $is_product_taxonomy Tru of is product tax, false otherwise.
		 * @return  bool
		 */
		public function show_sidebar_in_vendor_store_page( $is_product_taxonomy ) {
			if ( yith_wcmv_is_vendor_page() ) {
				$is_product_taxonomy = true;
			}

			return $is_product_taxonomy;
		}

		/**
		 * Check if given template is overridden. Useful for backward compatibility.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $template The template to check.
		 * @return boolean
		 */
		protected function is_template_overridden( $template ) {
			$template = WC()->template_path() . '/' . $template . '.php';
			if ( file_exists( get_stylesheet_directory() . '/' . $template ) || file_exists( get_template_directory() . '/' . $template ) ) {
				return true;
			}

			return false;
		}
	}
}
