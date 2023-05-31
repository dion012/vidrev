<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Successfully_Bid Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Successfully bid email.
 *
 * @class   YITH_WCACT_Email_Successfully_Bid
 * @package Yithemes
 * @since   Version 1.1.11
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Successfully_Bid' ) ) {
	/**
	 * Class YITH_WCACT_Email_Successfully_Bid
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Successfully_Bid extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.1.11
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_successfully_bid';

			$this->title = esc_html__( 'Auctions - Bid successfully made', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			$this->description = esc_html__( 'Email sent when a customer made a bid successfully.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'You placed a bid on ', 'yith-auctions-for-woocommerce' ) . get_bloginfo( 'name' );
			$this->subject = esc_html__( '[{site_title}] - You placed a bid on ', 'yith-auctions-for-woocommerce' ) . get_bloginfo( 'name' );

			$this->template_html = 'emails/successfully-bid.php';
			$this->template_html = 'emails/successfully-bid.php';

			add_action( 'yith_wcact_successfully_bid', array( $this, 'trigger' ), 10, 3 );

			parent::__construct();
		}

		/**
		 *  Fire email notification.
		 *
		 *  @param int        $user_id User id.
		 *  @param WC_Product $product Auction product.
		 *  @param array      $args arguments array.
		 *  @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 *  @since 2.0
		 */
		public function trigger( $user_id, $product, $args ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$user            = get_user_by( 'id', $user_id );
			$url_product     = get_permalink( $product->get_id() );
			$args['user_id'] = $user_id;
			$this->object    = array(
				'user_email'   => $user->data->user_email,
				'user_name'    => $user->data->user_login,
				'user'         => $user,
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
				'args'         => $args,
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

return new YITH_WCACT_Email_Successfully_Bid();
