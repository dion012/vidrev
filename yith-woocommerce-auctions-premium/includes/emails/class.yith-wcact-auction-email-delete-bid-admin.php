<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Delete_Bid_Admin Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Email when a bid is deleted admin.
 *
 * @class   YITH_WCACT_Email_Delete_Bid_Admin
 * @package Yithemes
 * @since   Version 1.2.7
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Delete_Bid_Admin' ) ) {
	/**
	 * Class YITH_WCACT_Email_Delete_Bid_Admin
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Delete_Bid_Admin extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_delete_bid_admin';

			$this->title = esc_html__( 'Auctions - Deleted bid (admin)', 'yith-auctions-for-woocommerce' );

			$this->description = esc_html__( 'Email sent to the admin when a bid is deleted.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'Auction bid removed', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - Auction bid removed', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/auction-delete-bid-admin.php';

			add_action( 'yith_wcact_auction_delete_customer_bid_admin', array( $this, 'trigger' ), 10, 3 );

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
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

			$url_product = add_query_arg(
				array(
					'post'   => $product->get_id(),
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			);

			$this->object = array(
				'user_id'      => $user->ID,
				'user_name'    => $user->user_login,
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
				'args'         => $args,
			);

			$this->send(
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
				'enabled'    => array(
					'title'   => esc_html__( 'Enable/Disable', 'yith-auctions-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'yith-auctions-for-woocommerce' ),
					'default' => 'no',
				),
				'recipient'  => array(
					'title'       => esc_html__( 'Recipient(s)', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s: recipient */
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

return new YITH_WCACT_Email_Delete_Bid_Admin();
