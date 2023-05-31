<?php
/**
 * New Vendor Registration email
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_New_Vendor_Registration' ) ) {
	/**
	 * New Vendor Registration Email
	 * An email sent when new Vendor has requested to access to your store.
	 *
	 * @class      YITH_WC_Email_New_Vendor_Registration
	 * @extends    WC_Email
	 * @package    YITH WooCommerce Multi Vendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_New_Vendor_Registration extends WC_Email {

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'new_vendor_registration';
			$this->title       = __( 'New vendor registration', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'A user has registered as a vendor, he/she\'s requesting access to your store', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'New vendor registration', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] - New Vendor Registration', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/new-vendor-registration.php';
			$this->template_plain = 'emails/plain/new-vendor-registration.php';

			// Triggers for this email.
			add_action( 'yith_wcmv_vendor_created', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param YITH_Vendor $vendor Vendor object.
		 */
		public function trigger( $vendor ) {
			if ( ! $vendor instanceof YITH_Vendor || ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->object = $vendor;
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
					'vendor'        => $this->object,
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
					'vendor'        => $this->object,
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
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email recipients comma separated.
					'description' => sprintf( __( 'Enter recipients (comma-separated) for this email. Defaults to <code>%s</code>.', 'yith-woocommerce-product-vendors' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email subject value.
					'description' => sprintf( __( 'This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'yith-woocommerce-product-vendors' ), $this->subject ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email heading value.
					'description' => sprintf( __( 'This controls the main heading contained in the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-product-vendors' ), $this->heading ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose email format.', 'yith-woocommerce-product-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
			);
		}
	}
}

return new YITH_WC_Email_New_Vendor_Registration();
