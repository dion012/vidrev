<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Vendor Email not reached reserve price.
 *
 * @class   YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price' ) ) {
	/**
	 * Class YITH_WCACT_Email_Not_Reached_Reserve_Price
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			// set ID, this simply needs to be a unique name.
			$this->id = 'yith_wcact_vendor_email_not_reached_reserve_price';

			// this is the title in WooCommerce Email settings.
			$this->title = esc_html__( 'Auctions - Reserve price not exceeded (to vendor)', 'yith-auctions-for-woocommerce' );

			// this is the description in WooCommerce email settings.
			$this->description = esc_html__( 'Email sent when the auction has ended and the highest bid doesn\'t exceed the minimum reserve price.', 'yith-auctions-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = esc_html__( 'Max bid doesn\'t exceed minimum reserve price', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( 'Max bid doesn\'t exceed minimum reserve price', 'yith-auctions-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html = 'emails/vendor-not-reached-reserve-price.php';

			// Trigger on new paid orders.
			add_action( 'yith_wcact_vendor_not_reached_reserve_price', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();
		}

		/**
		 * Trigger email
		 *
		 * @param WC_Product  $product Product.
		 * @param YITH_Vendor $vendor Vendor.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function trigger( $product, $vendor ) {
			/*
			 * Edit Lorenzo: first of all, populate $the $this->object var with the parameter received here so
			 *              they will be available inside the template
			 */

			// Check is email enable or not.
			if ( ! $this->is_enabled() ) {
				return;
			}

			$url_product = add_query_arg(
				array(
					'post'   => $product->get_id(),
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			);

			$owner = get_user_by( 'id', $vendor->get_owner() );
			$owner = $owner->user_email;

			$this->object = array(
				'product_name' => $product->post->post_title,
				'product'      => $product,
				'url_product'  => $url_product,
				'owner'        => $owner,
			);

			$this->send(
				$this->object['owner'],
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		/**
		 * Get email html content
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
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
		 * Get email plain content
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
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
		 * Initialise Settings Form Fields
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
					/* Translators: %s: Subject */
					'description' => sprintf( esc_html__( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yith-auctions-for-woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => esc_html__( 'Email Heading', 'yith-auctions-for-woocommerce' ),
					'type'        => 'text',
					/* Translators: %s: Heading */
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

return new YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price();
