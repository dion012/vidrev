<?php
/**
 * YITH Vendors Frontend Premium Class
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

if ( ! class_exists( 'YITH_Vendors_Frontend_Premium' ) ) {
	/**
	 * Class YITH_Vendors_Frontend_Premium
	 *
	 * @author Andrea Grillo
	 * @author Francesco Licandro
	 */
	class YITH_Vendors_Frontend_Premium extends YITH_Vendors_Frontend {

		/**
		 * Endpoints class instance
		 *
		 * @since 4.0.0
		 * @var null | YITH_Vendors_Frontend_Endpoints
		 */
		public $endpoints = null;

		/**
		 * Constructor
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function __construct() {
			$this->load_classes();
			parent::__construct();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			// Shop Page.
			add_action( 'woocommerce_product_query', array( $this, 'hide_vendors_product' ), 15, 1 );
			add_filter( 'woocommerce_show_page_title', array( $this, 'remove_store_page_title' ) );
			add_action( 'woocommerce_archive_description', array( $this, 'remove_woocommerce_term_description' ), 5 );
			add_filter( 'yith_wcmv_single_product_vendor_tab_args', array( $this, 'vendor_tab_priority' ) );
			add_action( 'woocommerce_archive_description', array( $this, 'add_store_page_header' ) );
			// Store page header parts.
			add_action( 'yith_wcmv_vendor_header_store_info', array( $this, 'print_store_info' ) );
			add_action( 'yith_wcmv_vendor_header_store_socials', array( $this, 'print_store_socials' ) );
			add_action( 'yith_wcmv_vendor_header_store_description', array( $this, 'print_store_description' ) );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'print_product_list_title' ), 5 );
			// Related Product Management.
			add_filter( 'woocommerce_related_products', array( $this, 'get_vendor_related_product' ), 10, 3 );
			// Cart and Checkout.
			add_filter( 'woocommerce_cart_item_name', array( $this, 'add_sold_by_vendor' ), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'add_sold_by_vendor' ), 10, 3 );

			add_filter( 'body_class', array( $this, 'body_class' ) );

			// Handle vendor registration.
			add_action( 'yith_wcmv_before_became_a_vendor_form', array( $this, 'output_wc_notices' ), 10 );
			add_action( 'woocommerce_register_form', array( $this, 'register_form' ) );
			add_filter( 'woocommerce_process_registration_errors', array( $this, 'process_registration' ), 10 );
			add_action( 'woocommerce_created_customer', array( $this, 'create_vendor' ), 10, 1 );
			add_action( 'wp_loaded', array( $this, 'switch_customer_to_vendor' ) );

			// Check for enabled vendor.
			add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );
		}

		/**
		 * Load sub classes for main Frontend
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function load_classes() {
			YITH_Vendors_Shortcodes::load();
			$this->endpoints = new YITH_Vendors_Frontend_Endpoints();
		}

		/**
		 * Enqueue Style and Scripts
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author Francesco Licandro
		 * @return void
		 */
		public function enqueue_scripts() {
			global $post;

			// Main stylesheet.
			wp_enqueue_style( 'yith-wc-product-vendors' );
			wp_add_inline_style( 'yith-wc-product-vendors', $this->get_inline_css() );

			// Main script.
			wp_register_script( 'product-vendors', YITH_WPV_ASSETS_URL . 'js/frontend/' . yit_load_js_file( 'multi-vendor.js' ), array( 'jquery', 'imagesloaded' ), YITH_WPV_VERSION, true );
			wp_localize_script( 'product-vendors', 'field_check', array( 'is_vat_require' => YITH_Vendors()->is_vat_require() ) );

			// Theme stylesheet.
			$paths   = apply_filters( 'yith_wpv_stylesheet_paths', array( WC()->template_path() . 'product-vendors.css', 'product-vendors.css' ) );
			$located = locate_template( $paths, false, false );
			$search  = array( get_stylesheet_directory(), get_template_directory() );
			$replace = array( get_stylesheet_directory_uri(), get_template_directory_uri() );

			if ( ! empty( $located ) ) {
				$theme_stylesheet = str_replace( $search, $replace, $located );
				wp_enqueue_style( 'yith-wc-product-vendors-theme', $theme_stylesheet, array(), YITH_WPV_VERSION );
			}

			$gmaps_api_key = get_option( 'yith_wpv_frontpage_gmaps_key', '' );
			$gmaps_api_uri = '//maps.google.com/maps/api/js?language=en';

			if ( ! empty( $gmaps_api_key ) ) {
				$gmaps_api_uri .= "&key={$gmaps_api_key}";
			}

			wp_register_script( 'gmaps-api', $gmaps_api_uri, array( 'jquery' ) );
			wp_register_script( 'gmap3', YITH_WPV_ASSETS_URL . 'third-party/gmap3/gmap3.min.js', array( 'jquery', 'gmaps-api' ), '6.0.0' );

			if ( yith_wcmv_is_vendor_page() ) {
				wp_enqueue_style( 'yith-wcmv-font-awesome' );
				wp_enqueue_script( 'gmap3' );
				wp_enqueue_script( 'product-vendors' );
			}

			$is_frontend_manager_page = function_exists( 'YITH_Frontend_Manager' ) && ! empty( YITH_Frontend_Manager()->gui ) && YITH_Frontend_Manager()->gui->is_main_page();

			if ( is_account_page() || $this->is_become_a_vendor_page() || $is_frontend_manager_page || ( ! empty( $post ) && has_shortcode( $post->post_content, 'yith_wcmv_become_a_vendor' ) ) ) {
				wp_enqueue_script( 'product-vendors' );
				if ( ! is_user_logged_in() ) {
					wp_enqueue_script( 'wc-password-strength-meter' );
				}
			}

			if ( ! empty( $post ) && has_shortcode( $post->post_content, 'yith_wcmv_list' ) ) {
				wp_enqueue_style( 'yith-wcmv-font-awesome' );
			}
		}

		/**
		 * Get inline CSS rules
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		protected function get_inline_css() {

			// Build custom CSS.
			$custom_css = array();

			// Vendor name color.
			$name_default                   = array(
				'normal' => '#bc360a',
				'hover'  => '#ea9629',
			);
			$name_colors                    = get_option( 'yith_wpv_vendor_color_name', $name_default );
			$custom_css['name-color']       = ! empty( $name_colors['normal'] ) ? $name_colors['normal'] : $name_default['normal'];
			$custom_css['name-color-hover'] = ! empty( $name_colors['hover'] ) ? $name_colors['hover'] : $name_default['hover'];

			// Process header.
			$header_default                        = array(
				'text'       => '#ffffff',
				'background' => 'rgba(255, 255, 255, 0.8)',
			);
			$header_colors                         = get_option( 'yith_wpv_header_color', $header_default );
			$custom_css['header-text-color']       = ! empty( $header_colors['text'] ) ? $header_colors['text'] : $header_default['text'];
			$custom_css['header-background-color'] = ! empty( $header_colors['background'] ) ? $header_colors['background'] : $header_default['background'];

			$custom_css = apply_filters( 'yith_wcmv_get_inline_custom_css_rules', $custom_css );

			$css = ':root {';
			foreach ( $custom_css as $key => $value ) {
				$css .= "--ywcmv-{$key}:{$value};";
			}
			$css .= '}';

			return $css;
		}

		/**
		 * Check if the product listing options is enabled and filter the product list
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author Francesco Licandro
		 * @use    woocommerce_product_query action
		 * @param WP_Query $query The WP_Query object.
		 * @return void
		 */
		public function hide_vendors_product( $query ) {

			if ( 'yes' === get_option( 'yith_wpv_hide_vendor_products', 'no' ) && ! is_product_taxonomy() ) {
				$vendor_ids = yith_wcmv_get_vendors(
					array(
						'fields' => 'ids',
						'number' => -1,
					)
				);
				if ( ! empty( $vendor_ids ) ) {
					$tax_query = array(
						array(
							'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
							'field'    => 'id',
							'terms'    => $vendor_ids,
							'operator' => 'NOT IN',
						),
					);

					$query->set( 'tax_query', $tax_query );
				}
			}
		}

		/**
		 * Remove the page title in Vendor store page
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param boolean $title If true print the page title.
		 * @return boolean
		 */
		public function remove_store_page_title( $title ) {
			return yith_wcmv_is_vendor_page() ? false : $title;
		}

		/**
		 * Remove woocommerce term description in vendor store page
		 *
		 * @author Andrea Grillo
		 * @return void
		 */
		public function remove_woocommerce_term_description() {
			if ( yith_wcmv_is_vendor_page() ) {
				remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description' );
			}
		}

		/**
		 * Print vendor store header in archive-product template
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author   Francesco Licandro
		 * @return void
		 */
		public function add_store_page_header() {

			if ( ! yith_wcmv_is_vendor_page() ) {
				return;
			}

			// Backward compatibility with old template.
			if ( $this->is_template_overridden( 'loop/store-header' ) ) {
				parent::add_store_page_header();
			}

			$term   = get_queried_object();
			$vendor = yith_wcmv_get_vendor( $term->slug );
			if ( ! $vendor || ! $vendor->is_valid() || ! $vendor->is_selling_enabled() ) {
				return;
			}

			$template_args = array( 'vendor' => $vendor );
			$header_skin   = get_option( 'yith_wpv_store_header_style', 'small-box' );
			if ( 'double-box' === $header_skin ) {
				$template_args['header_image'] = $vendor->get_header_image();
			} else {
				$header_image = $vendor->get_header_image( false );
				if ( ! empty( $header_image ) ) {
					$template_args['header_image']  = $header_image[0];
					$template_args['header_height'] = $header_image[2];
				}
			}

			if ( yith_wcmv_show_gravatar( $vendor, 'frontend' ) ) {
				$template_args['avatar'] = $vendor->get_avatar();
			}

			// Let's filter template args.
			$template_args = apply_filters( 'yith_wcmv_store_header_template_arg', $template_args, $header_skin );

			do_action( 'yith_wcmv_before_vendor_header', $template_args, $vendor );

			yith_wcmv_get_template( "store-header-{$header_skin}", $template_args, 'woocommerce/loop' );

			do_action( 'yith_wcmv_after_vendor_header', $template_args, $vendor );
		}

		/**
		 * Print store header products list title
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function print_product_list_title() {

			if ( ! yith_wcmv_is_vendor_page() ) {
				return;
			}

			$title = get_option( 'yith_wpv_store_products_list_title', __( 'Our products', 'yith-woocommerce-product-vendors' ) );
			if ( ! empty( $title ) ) {
				echo '<h2 class="store-product-list-title">' . esc_html( $title ) . '</h2>';
			}
		}

		/**
		 * Print vendor store header info.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return void
		 */
		public function print_store_info( $vendor ) {
			$template_args = array();
			$info_to_show  = get_option( 'yith_wpv_vendor_info_to_show', array( 'vat-ssn', 'rating' ) );
			foreach ( $info_to_show as $info ) {
				switch ( $info ) {
					case 'vat-ssn':
						$template_args['vat'] = $vendor->get_meta( 'vat' );
						break;
					case 'sales':
						$template_args['total_sales'] = count( $vendor->get_orders( 'suborder' ) );
						break;
					case 'website':
						$website_url = $vendor->get_meta( 'website' );
						if ( ! empty( $website_url ) ) {
							$website_label = str_ireplace( array( 'http://', 'https://' ), '', $website_url );
							$parsed        = wp_parse_url( $website_url );
							if ( empty( $parsed['scheme'] ) ) {
								$website_url = 'http://' . ltrim( $website_url, '/' );
							}

							$template_args['website'] = array(
								'url'   => $website_url,
								'label' => $website_label,
							);
						}

						break;
					case 'rating':
						$template_args['vendor_reviews'] = $vendor->get_reviews_average_and_product();
						break;
					case 'location':
						$template_args['location'] = $vendor->get_formatted_address();
						break;
					case 'telephone':
						$template_args['telephone'] = $vendor->get_meta( 'telephone' );
						break;
					case 'store_email':
						$template_args['store_email'] = $vendor->get_meta( 'store_email' );
						break;
				}
			}

			if ( empty( $template_args ) ) {
				return;
			}

			$template_args['icons'] = yith_wcmv_get_font_awesome_icons();
			yith_wcmv_get_template( 'store-info', $template_args, 'woocommerce/loop' );
		}

		/**
		 * Print vendor store header socials list.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return void
		 */
		public function print_store_socials( $vendor ) {

			$info_to_show = get_option( 'yith_wpv_vendor_info_to_show', array( 'vat-ssn', 'rating' ) );
			if ( ! in_array( 'socials', $info_to_show, true ) ) {
				return;
			}

			$socials      = array();
			$socials_list = YITH_Vendors()->get_social_fields();
			foreach ( $vendor->get_socials() as $social => $uri ) {
				if ( empty( $uri ) ) {
					continue;
				}
				// Format URI.
				$uri = str_replace( 'http://', '', $uri );
				$uri = str_replace( 'https://', '', $uri );
				$uri = ( '#yith-live-chat' !== $uri ? '//' . $uri : $uri );

				$socials[] = array(
					'uri'  => $uri,
					'icon' => isset( $socials_list['social_fields'][ $social ] ) ? $socials_list['social_fields'][ $social ]['icon'] : '',
				);
			}

			if ( empty( $socials ) ) {
				return;
			}

			yith_wcmv_get_template( 'store-socials', array( 'socials' => $socials ), 'woocommerce/loop' );
		}


		/**
		 * Print vendor store header description.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return void
		 */
		public function print_store_description( $vendor ) {

			$info_to_show = get_option( 'yith_wpv_vendor_info_to_show', array( 'vat-ssn', 'rating' ) );
			if ( ! in_array( 'description', $info_to_show, true ) ) {
				return;
			}

			$store_description_class = apply_filters( 'yith_wcmv_store_descritpion_class', 'store-description-wrapper' );
			$vendor_description      = do_shortcode( $vendor->get_description() );
			$vendor_description      = call_user_func( '__', $vendor_description, 'yith-woocommerce-product-vendors' );

			yith_wcmv_get_template(
				'store-description',
				array(
					'store_description_class' => $store_description_class,
					'vendor_description'      => $vendor_description,
				),
				'woocommerce/loop'
			);
		}

		/**
		 * Change vendor tab priority
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param array $args The vendor tab params array.
		 * @return array
		 */
		public function vendor_tab_priority( $args ) {
			$args['priority'] = absint( get_option( 'yith_vendors_tab_position', 99 ) );

			return $args;
		}

		/**
		 * Exclude the not enable vendors to Related products with woocommerce 3.x
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array   $related_products An array of related products ID.
		 * @param integer $product_id       Current product ID.
		 * @param array   $args             An array of arguments.
		 * @return array
		 */
		public function get_vendor_related_product( $related_products, $product_id, $args = array() ) {

			// Related Product Management.
			$related = get_option( 'yith_vendors_related_products', 'vendor' );
			if ( 'disabled' === $related ) {
				return array();
			} elseif ( 'vendor' === $related ) {

				$product = wc_get_product( $product_id );
				$vendor  = yith_wcmv_get_vendor( $product, 'product' );

				if ( $vendor && $vendor->is_valid() ) {
					$product_ids = $vendor->get_products();

					$related_product_ids = ! empty( $args['excluded_ids'] ) ? array_diff( $product_ids, $args['excluded_ids'] ) : $product_ids;
					$related_products    = array_intersect( $related_product_ids, $related_products );
				}
			}

			return $related_products;
		}

		/**
		 * Add sold by information to product in cart and in checkout order review details
		 * The follow args are documented in woocommerce\templates\cart\cart.php:74
		 *
		 * @since  1.6
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author Francesco Licandro
		 * @param string         $product_title The product title HTML.
		 * @param array          $cart_item     The cart item array.
		 * @param boolean|string $cart_item_key (Optional) The cart item key or false.
		 * @return string The product title HTML.
		 */
		public function add_sold_by_vendor( $product_title, $cart_item, $cart_item_key = false ) {
			if ( isset( $cart_item['product_id'] ) ) {
				$vendor = yith_wcmv_get_vendor( $cart_item['product_id'], 'product' );

				if ( $vendor && $vendor->is_valid() ) {
					$product_title .= '<span class="yith_wcmv_sold_by_wrapper">';
					$product_title .= ' ';
					$vendor_name    = $vendor->get_url();
					$a_open         = is_cart() ? "<a href='{$vendor_name}'>" : '';
					$a_close        = is_cart() ? '</a>' : '';
					$product_title .= '<small>(' . apply_filters( 'yith_wcmv_sold_by_string_frontend', _x( 'Sold by', 'Cart details: Product sold by', 'yith-woocommerce-product-vendors' ) ) . ': ' . $a_open . $vendor->get_name() . $a_close . ')</small>';
					$product_title .= '</span>';
				}
			}

			return $product_title;
		}

		/**
		 * Check if current page is the become a vendor page
		 *
		 * @since    1.9.16
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  boolean  True if the current page is the become a vendor page, false otherwise.
		 */
		public function is_become_a_vendor_page() {
			return is_page( get_option( 'yith_wpv_become_a_vendor_page_id', 0 ) );
		}

		/**
		 * Add a body class(es)
		 *
		 * @since  1.9.16
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @author Francesco Licandro
		 * @param array $classes The array of body classes.
		 * @return array
		 */
		public function body_class( $classes ) {
			if ( $this->is_become_a_vendor_page() ) {
				$classes[] = 'become-a-vendor';
				$classes[] = 'multi-vendor-style'; // Leave for backward compatibility.
			}

			return $classes;
		}

		/**
		 * Add Vendor registration form
		 *
		 * @since    1.0.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @return   void
		 */
		public function register_form() {

			if ( 'yes' !== get_option( 'yith_wpv_vendors_my_account_registration', 'no' ) ) {
				return;
			}

			$fields = yith_wcmv_get_vendor_registration_fields_frontend();
			if ( empty( $fields ) ) {
				return;
			}

			$args = apply_filters(
				'yith_wcmv_register_form_args',
				array(
					'fields'                          => $fields,
					'is_become_a_vendor_page'         => $this->is_become_a_vendor_page(),
					// Leave for backward compatibility.
					'is_vat_require'                  => YITH_Vendors()->is_vat_require(),
					'is_terms_and_conditions_require' => YITH_Vendors()->is_terms_and_conditions_require(),
					'is_privacy_policy_require'       => YITH_Vendors()->is_privacy_policy_require(),
					'is_paypal_email_required'        => YITH_Vendors()->is_paypal_email_required(),
					'is_paypal_email_enabled'         => YITH_Vendors()->is_paypal_email_enabled(),
					'become_a_vendor_style'           => 'multivendor',
				)
			);

			yith_wcmv_get_template( 'vendor-registration', $args, 'woocommerce/myaccount' );
		}

		/**
		 * Get error message in registration form
		 *
		 * @since    1.7
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @param string $error_type  The error type.
		 * @param string $field_label ( Optional ) The field label to use in message. Default empty string.
		 * @return string Error message.
		 */
		protected function get_field_error_message( $error_type, $field_label = '' ) {

			$error_message = '';
			switch ( $error_type ) {
				case 'antispam':
					$error_message = _x( 'Please, no spam here!', '[Frontend]Became vendor error message', 'yith-woocommerce-product-vendors' );
					break;

				case 'empty_field':
					// translators: %s stand for the field label.
					$error_message = sprintf( _x( '%s is a required field.', '[Frontend]Became vendor error message', 'yith-woocommerce-product-vendors' ), $field_label );
					break;

				case 'invalid_email':
					// translators: %s stand for the field label.
					$error_message = sprintf( _x( '%s must be a valid email address.', '[Frontend]Became vendor error message', 'yith-woocommerce-product-vendors' ), $field_label );
					break;

				case 'duplicated':
					$error_message = _x( 'A vendor with this name already exists.', '[Frontend]Became vendor error message', 'yith-woocommerce-product-vendors' );
					break;
			}

			return $error_message;
		}

		/**
		 * Process Vendor registration form
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param WP_Error $validation_error WP_Error class instance.
		 * @param string   $deprecated       (Optional) The type of registration to process. Default is new_vendor.
		 * @return WP_Error
		 */
		public function process_registration( $validation_error, $deprecated = 'new_vendor' ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( ! empty( $_POST['vendor-register'] ) ) {

				if ( ! empty( $_POST['vendor-antispam'] ) ) {
					$validation_error->add( 'antispam', $this->get_field_error_message( 'antispam' ) );
				} else {

					$fields = yith_wcmv_get_vendor_registration_fields_frontend();
					foreach ( $fields as $key => $field ) {

						$label = isset( $field['label'] ) ? $field['label'] : str_replace( '-', ' ', $key );

						// Check if field is required and is empty.
						if ( $field['required'] && empty( $_POST[ $key ] ) ) {
							$validation_error->add( "empty_{$key}", $this->get_field_error_message( 'empty_field', $label ) );
						} elseif ( ! empty( $_POST[ $key ] ) ) {

							if ( isset( $field['type'] ) && 'email' === $field['type'] && ! is_email( sanitize_email( wp_unslash( $_POST[ $key ] ) ) ) ) {
								$validation_error->add( "invalid_email_{$key}", $this->get_field_error_message( 'invalid_email' ) );
							}

							// Vendor name must be unique!
							if ( 'vendor_name' === $key && yith_wcmv_check_duplicate_term_name( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) ) {
								$validation_error->add( 'duplicated', $this->get_field_error_message( 'duplicated' ) );
							}
						}
					}
				}
			}

			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return apply_filters( 'yith_wcmv_process_registration', $validation_error, $deprecated );
		}

		/**
		 * Create the vendor profile after Vendor registration
		 *
		 * @since    1.0.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @param integer $customer_id The new customer_id.
		 * @return string The vendor ID.
		 */
		public function create_vendor( $customer_id ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $_POST['vendor-register'] ) || empty( $_POST['vendor-name'] ) ) {
				return false;
			}

			$data = array( 'name' => sanitize_text_field( wp_unslash( $_POST['vendor-name'] ) ) );
			// Collect posted fields.
			$fields = yith_wcmv_get_vendor_registration_fields_frontend();
			foreach ( $fields as $key => $field ) {

				if ( 'vendor-terms' === $key && isset( $_POST['vendor-terms'] ) ) {
					$data['data_terms_and_condition'] = YITH_Vendors()->get_last_modified_data_terms_and_conditions();
					continue;
				}

				if ( 'vendor-privacy' === $key && isset( $_POST['vendor-privacy'] ) ) {
					$data['data_privacy_policy'] = YITH_Vendors()->get_last_modified_data_privacy_policy();
					continue;
				}

				$data_key = ! empty( $field['connected_to'] ) ? $field['connected_to'] : yith_wcmv_sanitize_custom_meta_key( $key );
				if ( 'checkbox' === $field['type'] ) {
					$data[ $data_key ] = isset( $_POST[ $key ] ) ? 'yes' : 'no';
				} elseif ( 'email' === $field['type'] ) {
					$data[ $data_key ] = ! empty( $_POST[ $key ] ) ? sanitize_email( wp_unslash( $_POST[ $key ] ) ) : '';
				} else {
					$data[ $data_key ] = ! empty( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
				}
			}

			// Set additional data.
			$data['owner'] = intval( $customer_id );

			if ( 'yes' === get_option( 'yith_wpv_vendors_my_account_registration_auto_approve', 'no' ) ) {
				$data['enable_selling'] = 'yes';
			} else {
				$data['enable_selling'] = 'no';
				$data['pending']        = 'yes';
			}

			return YITH_Vendors_Factory::create( $data );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle submit of became a vendor form
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @return void
		 */
		public function switch_customer_to_vendor() {
			$current_user_id = get_current_user_id();
			if ( ! empty( $_POST['vendor-register'] ) && ! empty( $current_user_id ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing

				do_action( 'yith_wcmv_before_become_a_vendor', $current_user_id );

				$validation = new WP_Error();
				$validation = $this->process_registration( $validation );
				$errors     = $validation->get_error_messages();

				if ( ! empty( $errors ) ) {
					foreach ( $errors as $error ) {
						wc_add_notice( $error, 'error' );
					}
					return;
				}

				$vendor_id = $this->create_vendor( $current_user_id );
				if ( is_wp_error( $vendor_id ) ) {
					wc_add_notice( __( 'An error occurred while creating a vendor. Please, try again!', 'yith-woocommerce-product-vendors' ), 'error' );
					return;
				}

				do_action( 'yith_wcmv_after_become_a_vendor', $vendor_id, $current_user_id );
				wp_safe_redirect( apply_filters( 'yith_wcmv_after_become_a_vendor_redirect_uri', get_permalink( wc_get_page_id( 'myaccount' ) ) ) );
				exit;
			}
		}


		/**
		 * Check if vendor is in pending to enable or disable admin access
		 *
		 * @since  1.6
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param boolean $show Tru to show, false otherwise.
		 * @return string  The product title HTML
		 */
		public function show_admin_bar( $show ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() && $vendor->is_in_pending() ) {
				$show = false;
			}

			return $show;
		}

		/**
		 * Output WooCommerce notices for became-a-vendor shortcode
		 * This double check is needed for Gutenberg block.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function output_wc_notices() {
			if ( ! function_exists( 'wc_print_notices' ) || ! function_exists( 'woocommerce_output_all_notices' ) ) {
				return;
			}

			woocommerce_output_all_notices();
		}
	}
}
