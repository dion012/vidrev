<?php
/**
 * Class YITH_Vendors_Premium
 *
 * @since      4.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
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

/**
 * Class YITH_Vendors_Premium
 *
 * @class      YITH_Vendors_Premium
 * @since      Version 4.0.0
 * @author     YITH WooCommerce Multi Vendor
 * @package    YITH
 */
if ( ! class_exists( 'YITH_Vendors_Premium' ) ) {
	/**
	 * Class YITH_Vendors_Premium
	 *
	 * @class      YITH_Vendors_Premium
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Premium extends YITH_Vendors {

		/**
		 * YITH_Vendors_Payments class instance
		 *
		 * @var YITH_Vendors_Payments|null
		 */
		public $payments = null;

		/**
		 * YITH_Vendors_Request_Quote class instance
		 *
		 * @var YITH_Vendors_Request_Quote|null
		 */
		public $quote = null;

		/**
		 * Construct
		 *
		 * @return void
		 */
		protected function __construct() {

			add_action( 'init', array( $this, 'terms_revision_hooks' ) );
			// Register common scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 );
			// Register plugin account endpoint.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'add_endpoint' ), 20, 1 );
			// Load modules.
			add_action( 'wp_loaded', array( $this, 'load_common_modules' ) );
			// Plugin emails.
			add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ) );
			add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );
			add_action( 'yith_wcmv_email_order_items_table', array( $this, 'email_order_items_table' ), 10, 8 );
			// Plugin widget.
			add_filter( 'yith_wcmv_register_widgets', array( $this, 'register_premium_widgets' ) );

			// Customize WooCommerce product block.
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'customize_blocks_product_html' ), 0, 3 );

			$this->load_frontend_manager_files();
			parent::__construct();
		}

		/**
		 * Hooks to handle terms revision for vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function terms_revision_hooks() {
			$revision_management = get_option( 'yith_wpv_manage_terms_and_privacy_revision', 'no' );
			$privacy_required    = get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
			$terms_required      = get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );
			if ( 'yes' !== $revision_management || ( 'no' === $privacy_required && 'no' === $terms_required ) ) {
				return;
			}

			add_action( 'save_post', array( $this, 'update_acceptance_details_for_vendors' ), 30, 1 );
			add_action( 'yith_wcmv_disable_vendor_to_sale', array( $this, 'disable_vendors_to_sale' ) );
			add_action( 'yith_wcmv_disable_vendor_to_sale_cron', array( $this, 'disable_vendors_to_sale' ) );
			if ( ! empty( $this->admin ) ) {
				add_action( 'admin_notices', array( $this->admin, 'print_check_revision_message' ), 20 );
			}
		}

		/**
		 * Class initialization. Instance the admin or frontend classes
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 * @access protected
		 */
		public function init() {

			if ( ! doing_action( 'init' ) ) {
				_doing_it_wrong( __METHOD__, 'This method should be called only once on init!', '4.0.0' );
				return;
			}

			// WPML Compatibility.
			YITH_Vendors_WPML::instance();
			// Init classes.
			$this->orders      = new YITH_Vendors_Orders_Premium();
			$this->coupons     = new YITH_Vendors_Coupons();
			$this->commissions = new YITH_Vendors_Commissions();
			$this->payments    = new YITH_Vendors_Payments();
			YITH_Vendors_Modules_Handler::instance();
			YITH_Vendors_Gateways::instance();

			// Load admin if admin request.
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Admin_Premium();
				YITH_Vendors_Privacy_Premium();
			}
			// Load frontend if frontend request.
			if ( yith_wcmv_is_frontend_request() ) {
				$this->frontend = new YITH_Vendors_Frontend_Premium();
			}
		}

		/**
		 * Add the premium classes to required array
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro <francesco.licandro@yithemes.com>
		 * @return array The required files.
		 */
		protected function get_required_files() {
			return apply_filters(
				'yith_wcmv_required_files',
				array_merge_recursive(
					parent::get_required_files(),
					array(
						'common' => array(
							'includes/yith-vendors-gateways-functions.php', // Use this file for special action/filter that I can't trigger from gateway class.
							'includes/widgets/class-yith-vendor-store-location.php',
							'includes/widgets/class-yith-vendor-quick-info.php',
						),
						'admin'  => array(
							'includes/class.yith-reports.php',
						),
					)
				)
			);
		}

		/**
		 * Register common style and scripts
		 *
		 * @since    1.0
		 * @author   Andrea Grillo
		 * @author   Francesco Licandro
		 * @return   void
		 */
		public function enqueue_scripts() {
			wp_register_style( 'yith-wcmv-font-icons', YITH_WPV_ASSETS_URL . 'third-party/fontello/css/fontello-embedded.min.css', array(), YITH_WPV_VERSION );
			wp_register_style( 'yith-wc-product-vendors', YITH_WPV_ASSETS_URL . 'css/' . yit_load_css_file( 'product-vendors.css' ), array( 'yith-wcmv-font-icons' ), YITH_WPV_VERSION );
		}

		/**
		 * Register my account endpoint
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $endpoints An array of WooCommerce endpoints.
		 * @return mixed
		 */
		public function add_endpoint( $endpoints ) {
			$endpoints['terms-of-service'] = $this->get_account_endpoint();

			return $endpoints;
		}

		/**
		 * Get my account endpoint slug
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_account_endpoint() {
			return apply_filters( 'yith_wcmv_terms_of_service_endpoint', 'terms-of-service' );
		}

		/**
		 * Load common plugin modules
		 *
		 * @since  4.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function load_common_modules() {
			$required = array();

			// WooCommerce Points and Rewards.
			if ( class_exists( 'WC_Points_Rewards' ) ) {
				$required['common'][] = 'includes/modules/class-yith-wc-points-and-rewards.php';
			}
			// WooCommerce Cost of Goods.
			if ( class_exists( 'WC_COG' ) ) {
				$required['common'][] = 'includes/modules/class-yith-wc-cog.php';
			}

			// YOAST SEO.
			if ( defined( 'WPSEO_VERSION' ) ) {
				$required['common'][] = 'includes/modules/class-yith-yoast-seo.php';
			}

			// YITH Cost of Goods for WooCommerce.
			if ( class_exists( 'YITH_COG' ) ) {
				$required['common'][] = 'includes/modules/class-yith-cost-of-goods-support.php';
			}

			if ( function_exists( 'autoptimize' ) ) {
				$required['frontend'][] = 'includes/modules/class-yith-wp-autoptimize.php';
			}

			! empty( $required ) && $this->load_required( $required );
		}

		/**
		 * Register Emails for Vendors
		 *
		 * @since  1.0.0
		 * @param array $emails An array of registered emails.
		 * @return array
		 */
		public function register_emails( $emails ) {
			$emails['YITH_WC_Email_Commissions_Unpaid']             = include 'emails/class-yith-wc-email-commissions-unpaid.php';
			$emails['YITH_WC_Email_Commissions_Paid']               = include 'emails/class-yith-wc-email-commissions-paid.php';
			$emails['YITH_WC_Email_Vendor_Commissions_Paid']        = include 'emails/class-yith-wc-email-vendor-commissions-paid.php';
			$emails['YITH_WC_Email_New_Vendor_Registration']        = include 'emails/class-yith-wc-email-new-vendor-registration.php';
			$emails['YITH_WC_Email_Vendor_New_Account']             = include 'emails/class-yith-wc-email-vendor-new-account.php';
			$emails['YITH_WC_Email_New_Order']                      = include 'emails/class-yith-wc-email-new-order.php';
			$emails['YITH_WC_Email_Cancelled_Order']                = include 'emails/class-yith-wc-email-cancelled-order.php';
			$emails['YITH_WC_Email_Vendor_Commissions_Bulk_Action'] = include 'emails/class-yith-wc-email-vendor-commissions-bulk-action.php';
			$emails['YITH_WC_Email_Product_Set_In_Pending_Review']  = include 'emails/class-yith-wc-email-product-set-in-pending-review.php';

			return $emails;
		}

		/**
		 * Loads WC Mailer when needed
		 *
		 * @since  1.0
		 * @author andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function load_wc_mailer() {
			add_action( 'yith_wcmv_vendor_account_approved', array( 'WC_Emails', 'send_transactional_email' ), 10 );
		}

		/**
		 * Get the email vendor order table
		 *
		 * @param YITH_Vendor $vendor              Vendor object.
		 * @param WC_Order    $order               Order object.
		 * @param boolean     $show_download_links (Optional) True to show item download link, false otherwise. Default false.
		 * @param boolean     $show_sku            (Optional) True to show item sku, false otherwise. Default false.
		 * @param boolean     $show_purchase_note  (Optional) True to show purchase note, false otherwise. Default false.
		 * @param boolean     $show_image          (Optional) True to show item image, false otherwise. Default false.
		 * @param array       $image_size          (Optional) The item image size. Default array(32,32).
		 * @param boolean     $plain_text          (Optional) True if is a plain email, false otherwise. Default false.
		 * @return void
		 */
		public function email_order_items_table( $vendor, $order, $show_download_links = false, $show_sku = false, $show_purchase_note = false, $show_image = false, $image_size = array( 32, 32 ), $plain_text = false ) {

			$template = ! empty( $plain_text ) ? 'emails/plain/vendor-email-order-items.php' : 'emails/vendor-email-order-items.php';

			yith_wcmv_get_template(
				$template,
				array(
					'order'                  => $order,
					'vendor'                 => $vendor,
					'items'                  => $order->get_items(),
					'show_download_links'    => $show_download_links,
					'show_sku'               => $show_sku,
					'show_purchase_note'     => $show_purchase_note,
					'show_image'             => $show_image,
					'image_size'             => $image_size,
					'tax_credited_to_vendor' => 'vendor' === get_option( 'yith_wpv_commissions_tax_management', 'website' ),
				)
			);
		}

		/**
		 * Register premium widgets
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $widgets The widgets to register.
		 * @return array The widgets array.
		 */
		public function register_premium_widgets( $widgets ) {
			$widgets[] = 'YITH_Vendor_Store_Location_Widget';
			$widgets[] = 'YITH_Vendor_Quick_Info_Widget';

			return $widgets;
		}

		/**
		 * Get the social fields array
		 *
		 * @since  1.8.4
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return array
		 */
		public function get_social_fields() {
			$socials = array(
				'social_fields' => array(
					'facebook'    => array(
						'label' => __( 'Facebook', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__facebook',
					),
					'twitter'     => array(
						'label' => __( 'Twitter', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__twitter',
					),
					'linkedin'    => array(
						'label' => __( 'LinkedIn', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__linkedin',
					),
					'youtube'     => array(
						'label' => __( 'YouTube', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__youtube',
					),
					'vimeo'       => array(
						'label' => __( 'Vimeo', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__vimeo',
					),
					'instagram'   => array(
						'label' => __( 'Instagram', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__instagram',
					),
					'pinterest'   => array(
						'label' => __( 'Pinterest', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__pinterest',
					),
					'flickr'      => array(
						'label' => __( 'Flickr', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__flickr',
					),
					'behance'     => array(
						'label' => __( 'Behance', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__behance',
					),
					'tripadvisor' => array(
						'label' => __( 'Tripadvisor  ', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'yith-wcmv-icon__tripadvisor',
					),
				),
			);

			if ( YITH_Vendors_Modules_Handler::instance()->is_module_active( 'live-chat' ) ) {
				$socials['social_fields']['live_chat'] = array(
					'label' => sprintf(
						'%s<br/><small>%s: <em>%s</em></small>',
						__( 'YITH Live Chat', 'yith-woocommerce-product-vendors' ),
						_x( 'Use this value to show live chat button', 'option description', 'yith-woocommerce-product-vendors' ),
						'#yith-live-chat'
					),
					'icon'  => 'yith-wcmv-icon__chat',
				);
			}

			return apply_filters( 'yith_wcmv_vendor_social_fields', $socials );
		}

		/**
		 * Update policy and term post revision
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param integer $post_id Post ID.
		 * @return void
		 */
		public function update_acceptance_details_for_vendors( $post_id ) {
			$privacy_page_id = absint( get_option( 'yith_wpv_privacy_page', 0 ) );
			$terms_page_id   = absint( get_option( 'yith_wpv_terms_and_conditions_page_id', 0 ) );

			$terms_req   = $this->is_terms_and_conditions_require();
			$privacy_req = $this->is_privacy_policy_require();

			if ( ( ( $terms_req && $post_id === $terms_page_id ) || ( $privacy_req && $post_id === $privacy_page_id ) ) && 'publish' === get_post_status( $post_id ) ) {

				$action = get_option( 'yith_wpv_manage_terms_and_privacy_revision_actions', 'no_action' );
				if ( 'disable_now' === $action ) {
					do_action( 'yith_wcmv_disable_vendor_to_sale' );
				} elseif ( 'disable_after' === $action ) {

					$days = get_option( 'yith_wpv_manage_terms_and_privacy_revision_disable_after', 3 );

					if ( ! wp_next_scheduled( 'yith_wcmv_disable_vendor_to_sale_cron' ) ) {
						wp_clear_scheduled_hook( 'yith_wcmv_disable_vendor_to_sale_cron' );
					}

					$timestamp = time() + ( $days * DAY_IN_SECONDS );
					wp_schedule_single_event( $timestamp, 'yith_wcmv_disable_vendor_to_sale_cron' );
				}
			}
		}

		/**
		 * Disable vendor to sale callback.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function disable_vendors_to_sale() {

			$vendors = yith_wcmv_get_vendors(
				array(
					'enabled_selling' => true,
					'number'          => -1,
				)
			);

			foreach ( $vendors as $vendor ) {
				$terms_check = $this->is_terms_and_conditions_require();
				$terms_check = ! $terms_check || ( $terms_check && $vendor->has_terms_and_conditions_accepted() );

				$privacy_check = $this->is_privacy_policy_require();
				$privacy_check = ! $privacy_check || ( $privacy_check && $vendor->has_privacy_policy_accepted() );

				if ( ! $terms_check || ! $privacy_check ) {
					$vendor->set_enable_selling( 'no' );
					$vendor->save();
				}
			}
		}

		/**
		 * Load YITH Frontend Manager for WooCommerce files.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function load_frontend_manager_files() {
			if ( defined( 'YITH_WCFM_CLASS_PATH' ) && file_exists( YITH_WCFM_CLASS_PATH . 'module/multi-vendor/module.yith-multi-vendor.php' ) ) {
				YITH_Vendors_Frontend_Manager::instance();
			}
		}

		/**
		 * Filters the HTML for products in the grid.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param string     $html Product grid item HTML.
		 * @param object     $data Product data passed to the template.
		 * @param WC_Product $product Product object.
		 * @return string Updated product grid item HTML.
		 */
		public function customize_blocks_product_html( $html, $data, $product ) {
			if ( 'yes' !== get_option( 'yith_wpv_vendor_name_in_loop', 'yes' ) || ! apply_filters( 'yith_wcmv_show_vendor_name_template', true ) ) {
				return $html;
			}

			// Set vendor if any.
			$data->vendor_name = '';
			$vendor            = yith_wcmv_get_vendor( $product, 'product' );
			if ( $vendor && $vendor->is_valid() ) {
				ob_start();
				yith_wcmv_get_template( 'vendor-name', array( 'vendor' => $vendor ), 'woocommerce/loop' );
				$name_html = ob_get_clean();

				$data->vendor_name = sprintf( '<div class="wc-block-grid__product-vendor-name">%s</div>', $name_html );
			}

			// Remove add to cart for vendor in vacation.
			if ( function_exists( 'YITH_Vendors_Vacation' ) && YITH_Vendors_Vacation()->vendor_is_on_vacation( $vendor ) ) {
				$data->button = '';
			}

			return "<li class=\"wc-block-grid__product\">
				<a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
					{$data->image}
					{$data->title}
				</a>
				{$data->badge}
				{$data->price}
				{$data->rating}
				{$data->vendor_name}
				{$data->button}
			</li>";
		}
	}
}
