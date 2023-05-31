<?php
/**
 * YITH_WCACT_Email_Successfully_Follow Class.
 *
 * @package YITH\Auctions\Includes\Emails\Followers
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class Successfully follow email.
 *
 * @class   YITH_WCACT_Email_Successfully_Follow
 * @package Yithemes
 * @since   3.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Successfully_Follow' ) ) {
	/**
	 * Class YITH_WCACT_Email_Successfully_Follow
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Successfully_Follow extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0.0
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'yith_wcact_email_successfully_follow';

			// this is the title in WooCommerce Email settings.
			$this->title = esc_html__( 'Auctions - Follower successfully added', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			// this is the description in WooCommerce email settings.
			$this->description = esc_html__( 'Email sent when a user is successfully added to the followers list.', 'yith-auctions-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = esc_html__( 'You started to follow an auction', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - You started to follow an auction', 'yith-auctions-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html = 'emails/followers/successfully-follow.php';

			// Trigger on successfully follow email.
			add_action( 'yith_wcact_successfully_follow', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();
		}

		/**
		 * Trigger the email.
		 *
		 * @param string $email Email.
		 * @param int    $product_id Product id.
		 * @param string $hash Product Hash.
		 * @since 3.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function trigger( $email, $product_id, $hash ) {
			/*
			 * Edit Lorenzo: first of all, populate $the $this->object var with the parameter received here so
			 *              they will be available inside the template
			 */

			// Check is email enable or not.
			if ( ! $this->is_enabled() ) {
				return;
			}

			$product = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() ) {
				$url_product = get_permalink( $product->get_id() );

				$user     = get_user_by( 'email', $email );
				$username = ( $user ) ? $user->first_name : '';

				$this->object = array(
					'user_email'   => $email,
					'user_name'    => $username,
					'product_name' => $product->get_title(),
					'product'      => $product,
					'url_product'  => $url_product,
					'hash'         => $hash,
				);
				$this->send(
					$email,
					$this->get_subject(),
					$this->get_content(),
					$this->get_headers(),
					$this->get_attachments()
				);
			} else {
				return;
			}
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
			$enabled = true;

			return apply_filters( 'woocommerce_email_enabled_' . $this->id, $enabled, $this->object, $this );
		}
	}
}

return new YITH_WCACT_Email_Successfully_Follow();
