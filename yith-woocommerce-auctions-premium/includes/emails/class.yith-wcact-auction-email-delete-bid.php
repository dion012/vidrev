<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Delete_Bid Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Delete bid email for users
 *
 * @class   YITH_WCACT_Email_Delete_Bid
 * @package Yithemes
 * @since   Version 1.2.7
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Delete_Bid' ) ) {
	/**
	 * Class YITH_WCACT_Email_Delete_Bid
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Delete_Bid extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_auction_delete_bid';

			$this->title = esc_html__( 'Auctions - Deleted bid', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			$this->description = esc_html__( 'Email sent to the user when the bid is deleted. ', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'Auction Delete Bid', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - Auction Delete Bid', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/auction-delete-bid.php';
			$this->template_html = 'emails/auction-delete-bid.php';

			add_action( 'yith_wcact_auction_delete_customer_bid', array( $this, 'trigger' ), 10, 3 );

			parent::__construct();
		}

		/**
		 * Trigger
		 *
		 * @param int   $product_id Auction product id.
		 * @param int   $user_id User id.
		 * @param array $args Email args.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since 1.0
		 */
		public function trigger( $product_id, $user_id, $args ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$product = wc_get_product( $product_id );
			$user    = get_user_by( 'id', $user_id );

			$url_product = get_permalink( $product_id );

			$this->object = array(
				'user_email'   => $user->data->user_email,
				'user_name'    => $user->user_login,
				'product_id'   => $product->get_id(),
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
				'args'         => $args,
			);

			$mail_is_send = $this->send(
				$this->object['user_email'],
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
				'enabled'    => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-auctions-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-auctions-for-woocommerce' ),
					'default' => 'no',
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

return new YITH_WCACT_Email_Delete_Bid();
