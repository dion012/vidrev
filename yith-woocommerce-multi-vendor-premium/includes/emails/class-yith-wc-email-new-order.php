<?php
/**
 * New Vendor Order email
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_New_Order' ) ) {
	/**
	 * New Vendor Order Email
	 * New order emails are sent to vendor(s) when an order is received.
	 *
	 * @class      YITH_WC_Email_New_Order
	 * @extends    WC_Email
	 * @package    YITH WooCommerce Multi Vendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_New_Order extends WC_Email {

		/**
		 * Order number
		 *
		 * @var string
		 */
		public $order_number = '';

		/**
		 * Order customer name
		 *
		 * @var string
		 */
		public $customer_name = '';

		/**
		 * Construct
		 */
		public function __construct() {
			$this->id          = 'new_order_to_vendor';
			$this->title       = __( 'New order (to vendor)', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'New order emails are sent to vendor(s) when an order is received.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'New customer order', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] New customer order ({order_number}) - {order_date}', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/vendor-new-order.php';
			$this->template_plain = 'emails/plain/vendor-new-order.php';

			$this->recipient = YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' );

			// Triggers for this email.
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'trigger' ) );
			// Notification for Stripe Multibanco and PayPal eCheck.
			add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $this, 'trigger' ) );

			// Force to send email if no Syn option enabled.
			add_action( 'yith_wcmv_new_order_email', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param string|integer $order_id The order ID.
		 * @return bool
		 */
		public function trigger( $order_id ) {

			if ( ! $this->is_enabled() || empty( $order_id ) || apply_filters( 'yith_wcmv_skip_new_order_email_to_vendor', false, $order_id ) ) {
				return false;
			}

			// Is a parent order?
			if ( ! wp_get_post_parent_id( $order_id ) ) {
				$suborder_ids   = apply_filters( 'yith_wcmv_order_action_new_order_to_vendor', 'woocommerce_order_action_new_order_to_vendor' ) === current_action() ? YITH_Vendors_Orders::get_suborder( $order_id ) : array();
				$suborder_ids[] = $order_id;
			} else {
				$suborder_ids = array( $order_id );
			}

			if ( empty( $suborder_ids ) ) {
				return false;
			}

			foreach ( $suborder_ids as $suborder_id ) {
				$this->object = wc_get_order( $suborder_id );
				$vendor_id    = $this->object ? yith_wcmv_get_vendor_id_for_order( $this->object ) : false;
				$this->vendor = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

				if ( ! $this->vendor || ! $this->vendor->is_valid() ) {
					return false;
				}

				$this->order_number = yith_wcmv_get_email_order_number( $this->object, 'yes' === $this->get_option( 'show_parent_order_id', 'no' ) );
				// Get customer name.
				$billing_first_name  = $this->object->get_billing_first_name();
				$billing_last_name   = $this->object->get_billing_last_name();
				$this->customer_name = ( $billing_first_name && $billing_last_name ) ? $billing_first_name . ' ' . $billing_last_name : $this->object->get_billing_email();

				$this->placeholders['{order_number}'] = $this->order_number;
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );

				$vendor_email = $this->vendor->get_meta( 'store_email' );

				if ( empty( $vendor_email ) ) {
					$vendor_owner = get_user_by( 'id', absint( $this->vendor->get_owner() ) );
					$vendor_email = $vendor_owner instanceof WP_User ? $vendor_owner->user_email : false;
				}

				$headers = $this->get_headers();

				if ( 'yes' === $this->get_option( 'send_cc_to_admin', 'no' ) ) {
					$admin_name  = get_option( 'woocommerce_email_from_name' );
					$admin_email = get_option( 'woocommerce_email_from_address' );
					if ( $admin_email && $admin_name ) {
						$headers .= "Cc: {$admin_name} <{$admin_email}>";
					}
				}

				// Send Email to Vendor.
				$to = apply_filters( 'yith_wcmv_email_address_recipients_new_order_vendor_email', $vendor_email, $this->vendor, $this );
				do_action( 'wpml_switch_language_for_email', $to );
				$this->send( $to, $this->get_subject(), $this->get_content(), $headers, $this->get_attachments() );
				do_action( 'wpml_restore_language_from_email' );
			}
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_html,
				array(
					'order'         => $this->object,
					'order_number'  => $this->order_number,
					'vendor'        => $this->vendor,
					'customer'      => $this->customer_name,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Get the email content in plain text format.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_plain,
				array(
					'order'         => $this->object,
					'order_number'  => $this->order_number,
					'vendor'        => $this->vendor,
					'customer'      => $this->customer_name,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'              => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'subject'              => array(
					'title'       => __( 'Subject', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email subject value.
					'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-woocommerce-product-vendors' ), $this->subject ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'              => array(
					'title'       => __( 'Email heading', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email heading value.
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-product-vendors' ), $this->heading ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type'           => array(
					'title'       => __( 'Email type', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose email format.', 'yith-woocommerce-product-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
				'show_parent_order_id' => array(
					'title'   => __( 'Order ID', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Show the parent order ID instead of the vendor suborder ID.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
				'send_cc_to_admin'     => array(
					'title'   => __( 'CC to Admin', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Send a copy of this email to the website admin.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
			);
		}
	}
}

return new YITH_WC_Email_New_Order();
