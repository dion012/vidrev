<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Better_Bid Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Email highest bid.
 *
 * @class   YITH_WCACT_Email_Better_Bid
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Better_Bid' ) ) {
	/**
	 * Class YITH_WCACT_Email_Better_Bid
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Better_Bid extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_better_bid';

			$this->title = esc_html__( 'Auctions - Highest bid outbid', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			$this->description = esc_html__( 'Email sent to invite to make a new bid after being outbid.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'You have been outbid', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - You have been outbid', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/better-bid.php';
			$this->template_html = 'emails/better-bid.php';

			add_action( 'yith_wcact_better_bid', array( $this, 'trigger' ), 10, 5 );

			parent::__construct();
		}

		/**
		 *  Fire email notification.
		 *
		 *  @param int        $user_id User id.
		 *  @param WC_Product $product Auction product.
		 *  @param float      $bid max bid.
		 *  @param bool       $exists_auction_bid auction has bids.
		 *  @param  bool       $is_reverse_email is a reverse mail.
		 *  @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 *  @since 1.0
		 */
		public function trigger( $user_id, $product, $bid, $exists_auction_bid, $is_reverse_email = false ) {
			if ( ! $this->is_enabled() || 'yes' === get_post_meta( $product->get_id(), '_yith_wcact_auction_sealed', true ) ) {
				return;
			}

			$user        = get_user_by( 'id', $user_id );
			$url_product = get_permalink( $product->get_id() );

			$this->object = array(
				'user_email'       => $user->data->user_email,
				'user_name'        => $user->data->user_login,
				'product_name'     => $product->get_title(),
				'product'          => $product,
				'url_product'      => $url_product,
				'max_bid'          => $bid,
				'last_bid'         => $exists_auction_bid,
				'is_reverse_email' => $is_reverse_email,
			);

			$this->send(
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
					'default' => 'yes',
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

return new YITH_WCACT_Email_Better_Bid();
