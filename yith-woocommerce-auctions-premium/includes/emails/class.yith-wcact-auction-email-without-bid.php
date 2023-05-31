<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Without_Bid Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *  Email without bid for admin.
 *
 * @class   YITH_WCACT_Email_Without_Bid
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Without_Bid' ) ) {
	/**
	 * Class YITH_WCACT_Email_Without_Bid
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Without_Bid extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_without_bid';

			$this->title = esc_html__( 'Auctions - Without bids', 'yith-auctions-for-woocommerce' );

			$this->description = esc_html__( 'Email sent when the auction ended, and the product does not have bids.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'The item does not have any bids', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - The item does not have any bids', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/without-any-bids.php';

			add_action( 'yith_wcact_finished_without_any_bids', array( $this, 'trigger' ), 10, 1 );

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}

		/**
		 * Trigger the email.
		 *
		 * @param WC_Product $product Product.
		 * @since 1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function trigger( $product ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$without_any_bid = yit_get_prop( $product, 'yith_wcact_send_admin_without_any_bids', true );

			if ( $without_any_bid ) {
				return;
			}

			$url_product = add_query_arg(
				array(
					'post'   => $product->get_id(),
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			);

			$this->object = array(
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
			);

			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);

			yit_save_prop( $product, 'yith_wcact_send_admin_without_any_bids', 1 );
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
				'enabled'    => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-auctions-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-auctions-for-woocommerce' ),
					'default' => 'yes',
				),
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
	}
}

return new YITH_WCACT_Email_Without_Bid();
