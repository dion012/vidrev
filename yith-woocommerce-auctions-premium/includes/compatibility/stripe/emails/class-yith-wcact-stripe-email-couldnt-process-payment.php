<?php
/**
 * YITH_WCACT_Stripe_Email_Couldnt_Process_Payment Class.
 *
 * @package YITH\Auctions\Includes\Compatibility\Stripe\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class Stripe couldn't process payment ( first attempt )
 *
 * @class   YITH_WCACT_Stripe_Email_Couldnt_Process_Payment
 * @package Yithemes
 * @since   Version 3.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Stripe_Email_Couldnt_Process_Payment' ) ) {
	/**
	 * Class YITH_WCACT_Stripe_Email_Couldnt_Process_Payment
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Stripe_Email_Couldnt_Process_Payment extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_stripe_email_couldnt_process_payment';

			$this->title = esc_html__( 'Auctions - Stripe couldn\'t process your payment', 'yith-auctions-for-woocommerce' );

			$this->description = esc_html__( 'Email sent when Stripe cannot process the first payment attempt.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'We couldn\'t process your payment', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - We couldn\'t process your payment', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/stripe/couldnt-process-payment.php';

			$this->customer_email = true;

			add_action( 'yith_wcact_failed_attempts_1', array( $this, 'trigger' ), 10, 3 );

			parent::__construct();
		}

		/**
		 * Trigger the email
		 *
		 * @param WC_Order   $order Order.
		 * @param WC_Product $product Product.
		 * @param array      $gateway_response Gateway response.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function trigger( $order, $product, $gateway_response ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$user         = $order->get_user();
			$this->object = array(
				'user_email'       => $user->data->user_email,
				'user_name'        => $user->user_login,
				'product_name'     => $product->get_title(),
				'product'          => $product,
				'url_product'      => $product->get_permalink(),
				'gateway_response' => $gateway_response,
				'order'            => $order,
			);
			$this->send(
				$this->object['user_email'],
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);

			ywcact_logs( 'Sent email attempt 1 for product ' . $product->get_id() . ' on order ' . $order->get_id() );
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				),
				'',
				YITH_WCACT_TEMPLATE_PATH
			);
		}

		/**
		 * Get the email content in plain text format.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				),
				'',
				YITH_WCACT_TEMPLATE_PATH
			);
		}

		/**
		 * Initialise Settings Form Fields
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-auctions-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-auctions-for-woocommerce' ),
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => esc_html__( 'Subject', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* Translators: %s: Subject */
					'description' => sprintf( esc_html__( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-auctions-for-woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => esc_html__( 'Email Heading', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* Translators: %s: Heading */
					'description' => sprintf( esc_html__( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yith-auctions-for-woocommerce' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'email_type' => array(
					'title'       => esc_html__( 'Email type', 'yith-auctions-for-woocommerce' ),
					'type'        => 'select',
					'description' => esc_html__( 'Choose the email format to send.', 'yith-auctions-for-woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}
}

return new YITH_WCACT_Stripe_Email_Couldnt_Process_Payment();
