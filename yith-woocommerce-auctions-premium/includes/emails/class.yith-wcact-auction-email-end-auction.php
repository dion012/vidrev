<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_End_Auction Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Email when auction is about to end for bidders and followers.
 *
 * @class   YITH_WCACT_Email_End_Auction
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_End_Auction' ) ) {
	/**
	 * Class YITH_WCACT_Email_End_Auction
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_End_Auction extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'yith_wcact_email_end_auction';

			// this is the title in WooCommerce Email settings.
			$this->title = esc_html__( 'Auctions - Auction is about to end', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			// this is the description in WooCommerce email settings.
			$this->description = esc_html__( 'Email sent to followers and bidders when the auction is about to end.', 'yith-auctions-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = esc_html__( 'The auction is about to end', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - The auction is about to end', 'yith-auctions-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html = 'emails/end-auction.php';

			// Trigger on cronjob fired.
			add_action( 'yith_wcact_end_auction', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();
		}

		/**
		 * Trigger the email.
		 *
		 * @param int $user_id User id.
		 * @param int $product_id Product id.
		 * @since 1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function trigger( $user_id, $product_id ) {
			/*
			 * Edit Lorenzo: first of all, populate $the $this->object var with the parameter received here so
			 *              they will be available inside the template
			 */

			// Check is email enable or not.
			if ( ! $this->is_enabled() ) {
				return;
			}

			$user        = ( is_int( $user_id ) ) ? get_user_by( 'id', $user_id ) : $user_id;
			$product     = wc_get_product( $product_id );
			$url_product = get_permalink( $product_id );
			$number      = get_option( 'yith_wcact_settings_cron_auction_number_days' );
			$unit        = get_option( 'yith_wcact_settings_cron_auction_type_numbers' );

			if ( 'days' === $unit ) {
				$unit = esc_html_x( 'days', 'Admin option: days', 'yith-auctions-for-woocommerce' );
			} elseif ( 'hours' === $unit ) {
				$unit = esc_html_x( 'hours', 'Admin option: hours', 'yith-auctions-for-woocommerce' );
			} else {
				$unit = esc_html_x( 'minutes', 'Admin option: hours', 'yith-auctions-for-woocommerce' );
			}

			$this->object = array(
				'user_email'   => ( isset( $user->data->user_email ) ) ? $user->data->user_email : $user,
				'user_name'    => isset( $user->data->user_login ) ? $user->data->user_login : ( get_user_by( 'email', $user ) ? get_user_by( 'email', $user )->data->user_login : $user ),
				'product_name' => $product->get_title(),
				'number'       => $number,
				'time'         => $unit,
				'product'      => $product,
				'url_product'  => $url_product,
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
		 * Checks if this email is enabled and will be sent.
		 *
		 * @return bool
		 */
		public function is_enabled() {
			$enabled = false;

			if ( 'yes' === get_option( 'yith_wcact_settings_cron_auction_send_emails', 'no' ) || 'yes' === get_option( 'yith_wcact_notify_followers_auction_about_to_end', 'no' ) ) {
				$enabled = true;
			}

			return apply_filters( 'woocommerce_email_enabled_' . $this->id, $enabled, $this->object, $this );
		}
	}
}

return new YITH_WCACT_Email_End_Auction();
