<?php
/**
 * Commission paid for vendor email
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_Vendor_Commissions_Paid' ) ) {
	/**
	 * Commission paid (for Vendor) Email
	 * New commissions have been credited to vendor.
	 *
	 * @class      YITH_WC_Email_Vendor_Commissions_Paid
	 * @extends    WC_Email
	 * @package    YITH WooCommerce Multi Vendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_Vendor_Commissions_Paid extends WC_Email {

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'vendor_commissions_paid';
			$this->title       = __( 'Commission paid (for Vendor)', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'New commissions have been credited to the vendor successfully.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'Vendor\'s commission paid', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] - Commission paid', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/vendor-commissions-paid.php';
			$this->template_plain = 'emails/plain/vendor-commissions-paid.php';

			$this->recipient = YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' );

			// Triggers for this email.
			add_action( 'yith_vendors_commissions_paid', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param YITH_Vendors_Commission $commission Commission paid.
		 * @return void
		 */
		public function trigger( $commission ) {
			if ( ! $commission instanceof YITH_Vendors_Commission || ! $this->is_enabled() ) {
				return;
			}

			$this->object = $commission;

			/* Get the user email  */
			$user = $this->object->get_user();

			if ( $user instanceof WP_User ) {
				$this->recipient = $user->user_email;
			}

			if ( ! empty( $this->recipient ) ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
					'commission'    => $this->object,
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
					'commission'    => $this->object,
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
					'label'   => __( 'Enable notification for this email', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
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

return new YITH_WC_Email_Vendor_Commissions_Paid();
