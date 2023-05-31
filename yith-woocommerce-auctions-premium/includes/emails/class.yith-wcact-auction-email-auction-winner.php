<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Email_Auction_Winner Class.
 *
 * @package YITH\Auctions\Includes\Emails
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Auction winner email
 *
 * @class   YITH_WCACT_Email_Auction_Winner
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
if ( ! class_exists( 'YITH_WCACT_Email_Auction_Winner' ) ) {
	/**
	 * Class YITH_WCACT_Email_Auction_Winner
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Email_Auction_Winner extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			$this->id = 'yith_wcact_email_auction_winner';

			$this->title = esc_html__( 'Auctions - Winner', 'yith-auctions-for-woocommerce' );

			$this->customer_email = true;

			$this->description = esc_html__( 'Email sent to the user who won the ended auction.', 'yith-auctions-for-woocommerce' );

			$this->heading = esc_html__( 'You won the auction', 'yith-auctions-for-woocommerce' );
			$this->subject = esc_html__( '[{site_title}] - You won the auction', 'yith-auctions-for-woocommerce' );

			$this->template_html = 'emails/auction-winner.php';
			$this->template_html = 'emails/auction-winner.php';

			add_action( 'yith_wcact_auction_winner', array( $this, 'trigger' ), 20, 3 );

			add_action( 'yith_wcact_after_content_winner_email', array( $this, 'pay_url_button' ), 10 );

			parent::__construct();
		}

		/**
		 * Trigger
		 *
		 * @param WC_Product $product Auction product.
		 * @param WP_User    $user User.
		 * @param mixed      $max_bidder Is $max_bidder.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since 1.0
		 */
		public function trigger( $product, $user, $max_bidder = false ) {
			if ( ! $this->is_enabled() ) {
				return;
			}

			$winner_email = $product->get_send_winner_email();

			if ( $winner_email || ! $product->is_closed() ) {
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

				/**
				 * APPLY_FILTERS: yith_wcact_winner_email_pay_now_url
				 *
				 * Filter the URL of the button to pay the auction in the winner email.
				 *
				 * @param string $url URL
				 *
				 * @return string
				 */
				$url = apply_filters( 'yith_wcact_pay_now_url', add_query_arg( array( 'yith-wcact-pay-won-auction' => $product->get_id() ), home_url() ) );
			}

			$this->object = array(
				'user_email'           => $user->data->user_email,
				'user_name'            => $user->user_login,
				'product_id'           => $product->get_id(),
				'product_name'         => $product->get_title(),
				'product'              => $product,
				'url_product'          => $url_product,
				'user'                 => $user,
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

			if ( $mail_is_send ) {
				$product->set_send_winner_email( true );

				yit_save_prop( $product, 'yith_wcact_winner_email_is_send', 1 );
				yit_save_prop( $product, 'yith_wcact_winner_email_send_custoner', $user );

				if ( $max_bidder ) {
					yit_save_prop( $product, '_yith_wcact_winner_email_max_bidder', $max_bidder );
				}

				yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );

				ywcact_logs( 'The email was send for the product ' . $product->get_id() );
			} else {
				yit_save_prop( $product, 'yith_wcact_winner_email_is_not_send', 1 );
				yit_save_prop( $product, '_yith_wcact_reason_why_is_not_send', $mail_is_send );

				ywcact_logs( 'Error: the email was not send for the product ' . $product->get_id() );
				ywcact_logs( 'Reason: ' . $mail_is_send );
			}

			$product->save();
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

		/**
		 * Pay url button
		 *
		 * @param object $email Object email.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function pay_url_button( $email ) {
			/**
			 * APPLY_FILTERS: yith_wcact_show_pay_url_button_winner_email
			 *
			 * Filter whether to show the section to pay the auction in the winner email.
			 *
			 * @param bool     $show_pay_now_button Whether to show the button to pay or not
			 * @param WC_Email $email               Email object
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_show_pay_url_button_winner_email', true, $email ) ) {
				$base      = get_option( 'woocommerce_email_base_color' );
				$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );

				?>
				<p>
					<?php
					/**
					 * APPLY_FILTERS: yith_wcact_pay_item_label
					 *
					 * Filter the label to pay the auction in the winner email.
					 *
					 * @param string $label Label
					 *
					 * @return string
					 */
					echo esc_html( apply_filters( 'yith_wcact_pay_item_label', __( 'Pay for the item now to avoid losing it!', 'yith-auctions-for-woocommerce' ) ) );
					?>
				</p>

				<?php
				/**
				 * APPLY_FILTERS: yith_wcact_show_pay_now_button_email
				 *
				 * Filter whether to show the button to pay the auction in the winner email.
				 *
				 * @param bool $show_pay_now_button Whether to show the button to pay or not
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcact_show_pay_now_button_email', true ) ) :
					?>
					<div style="text-align: center; margin-top: 60px !important; margin-bottom: 10px !important;">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcact_winner_email_pay_now_url
						 *
						 * Filter the URL of the button to pay the auction in the winner email.
						 *
						 * @param string   $url   URL
						 * @param WC_Email $email Email object
						 *
						 * @return string
						 */
						?>
						<a style="padding:10px 50px !important;font-size: 12px !important; background: <?php echo esc_attr( $base ); ?> !important; color: <?php echo esc_attr( $base_text ); ?> !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo esc_attr( apply_filters( 'yith_wcact_winner_email_pay_now_url', $email->object['url_redirect'], $email ) ); ?>"><?php echo esc_html( $email->object['pay_now_button_label'] ); ?></a>
					</div>
					<?php
				endif;
			}
		}
	}
}

return new YITH_WCACT_Email_Auction_Winner();
