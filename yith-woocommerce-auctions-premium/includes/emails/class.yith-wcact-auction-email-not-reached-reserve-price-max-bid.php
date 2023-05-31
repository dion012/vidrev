<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Max bid not reached reserve price
 *
 * @class   YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder
 * @package Yithemes
 * @since   Version 2.0.2
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder' ) ) {
	/**
	 * Class YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_not_reached_reserve_price_max_bidder';

			$this->title = esc_html__( 'Auctions - Reserve price not exceeded (to best bidder)', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			$this->description = esc_html__( 'Email sent to the user when the auction has ended and the best bid doesn\'t exceed the minimum reserve price.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'Your max bid doesn\'t exceed the minimum reserve price', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - Your max bid doesn\'t exceed the minimum reserve price', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/not-reached-reserve-price-max-bidder.php';

			add_action( 'yith_wcact_not_reached_reserve_price_max_bidder', array( $this, 'trigger' ), 10, 2 );

			parent::__construct();
		}

		/**
		 * Trigger the email.
		 *
		 * @param WC_Product $product Product.
		 * @param WP_User    $user User.
		 * @since 1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function trigger( $product, $user ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$url_product = get_permalink( $product->get_id() );

			$this->object = array(
				'user_email'   => $user->data->user_email,
				'user_name'    => $user->user_login,
				'product_id'   => $product->get_id(),
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
				'user'         => $user,
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

return new YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder();
