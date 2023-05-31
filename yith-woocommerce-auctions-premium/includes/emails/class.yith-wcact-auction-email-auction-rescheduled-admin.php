<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Auction_Rescheduled_Admin Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Admin reschedule email.
 *
 * @class   YITH_WCACT_Email_Auction_Rescheduled_Admin
 * @package Yithemes
 * @since   Version 2.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Auction_Rescheduled_Admin' ) ) {
	/**
	 * Class YITH_WCACT_Email_Auction_Rescheduled_Admin
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Auction_Rescheduled_Admin extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'yith_wcact_email_auction_rescheduled_admin';

			// this is the title in WooCommerce Email settings.
			$this->title = esc_html__( 'Auctions - Rescheduled auction (admin)', 'yith-auctions-for-woocommerce' );

			// this is the description in WooCommerce email settings.
			$this->description = esc_html__( 'Email sent to the admin when an auction product will be automatically rescheduled.', 'yith-auctions-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = esc_html__( 'Auction Rescheduled', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - Auction Rescheduled', 'yith-auctions-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html = 'emails/auction-rescheduled-admin.php';

			// Trigger on new paid orders.
			add_action( 'yith_wcact_auction_email_rescheduled', array( $this, 'trigger' ) );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();

			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}

		/**
		 * Trigger
		 *
		 * @param WC_Product $product Auction product.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since 2.0
		 */
		public function trigger( $product ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$url_product  = get_permalink( $product->get_id() );
			$this->object = array(
				'product_id'   => $product->get_id(),
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
			);
			$mail_is_send = $this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
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
		 * Initialise Settings Form Fields - these are generic email options most will use.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'recipient'  => array(
					'title'       => esc_html__( 'Recipient(s)', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s: recipients */
					'description' => sprintf( esc_html__( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'yith-auctions-for-woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'    => array(
					'title'       => esc_html__( 'Subject', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s: subject */
					'description' => sprintf( esc_html__( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-auctions-for-woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => esc_html__( 'Email Heading', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s: heading */
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

		/**
		 * Checks if this email is enabled.
		 *
		 * @return bool
		 */
		public function is_enabled() {
			$enabled = false;

			if ( 'yes' === get_option( 'yith_wcact_settings_auction_is_reschedule', 'no' ) ) {
				$enabled = true;
			}

			return apply_filters( 'woocommerce_email_enabled_' . $this->id, $enabled, $this->object, $this );
		}
	}
}

return new YITH_WCACT_Email_Auction_Rescheduled_Admin();
