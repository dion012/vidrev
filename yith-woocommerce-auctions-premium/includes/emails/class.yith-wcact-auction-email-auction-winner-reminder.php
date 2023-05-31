<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Auction_Reminder Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Email Winner remainder.
 *
 * @class   YITH_WCACT_Email_Auction_Reminder
 * @package Yithemes
 * @since   Version 2.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Auction_Reminder' ) ) {
	/**
	 * Class YITH_WCACT_Email_Auction_Reminder
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Auction_Winner_Reminder extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'yith_wcact_email_auction_winner_reminder';

			// this is the title in WooCommerce Email settings.
			$this->title          = esc_html__( 'Auctions - Winner reminder', 'yith-auctions-for-woocommerce' );
			$this->customer_email = true;

			// this is the description in WooCommerce email settings.
			$this->description = esc_html__( 'This is the reminder email to the user who won the auction, in order to pay for the item', 'yith-auctions-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = esc_html__( 'Don\'t forget to pay this item!', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - Don\'t forget to pay this item!', 'yith-auctions-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html = 'emails/auction-winner-reminder.php';

			// Trigger on new paid orders.
			add_action( 'yith_wcact_auction_winner_email_reminder', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();
		}

		/**
		 * Trigger
		 *  Fire email notification
		 *
		 *  @param WC_Product $product Auction product.
		 *  @param WP_User    $user User win the auction.
		 *  @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 *  @since 2.0
		 */
		public function trigger( $product, $user ) {
			/*
			* Edit Lorenzo: first of all, populate $the $this->object var with the parameter received here so
			*              they will be available inside the template
			*/

			// Check is email enable or not.
			if ( ! $this->is_enabled() ) {
				return;
			}

			$url_product = get_permalink( $product->get_id() );

			$order_id = $product->get_order_id();

			if ( $order_id && $order_id > 0 ) {
				$order                = wc_get_order( $order_id );
				$pay_now_button_label = __( 'Pay order', 'yith-auctions-for-woocommerce' );
				$url                  = $order->get_checkout_payment_url();
			} else {
				$label_pay_now        = get_option( 'yith_wcact_auction_winner_label_pay_now', false );
				$pay_now_button_label = $label_pay_now ? $label_pay_now : __( 'Pay now', 'yith-auctions-for-woocommerce' );
				$url                  = add_query_arg( array( 'yith-wcact-pay-won-auction' => $product->get_id() ), home_url() );
			}

			$this->object = array(
				'user_email'           => $user->data->user_email,
				'user_name'            => $user->user_login,
				'product_id'           => $product->get_id(),
				'product_name'         => $product->get_title(),
				'product'              => $product,
				'url_product'          => $url_product,
				'user'                 => $user,
				'reschedule_options'   => get_option( 'ywcact_settings_reschedule_auction_not_paid', array() ),
				'pay_now_button_label' => $pay_now_button_label,
				'url_redirect'         => $url,
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

return new YITH_WCACT_Email_Auction_Winner_Reminder();
