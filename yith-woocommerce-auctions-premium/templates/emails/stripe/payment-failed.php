<?php
/**
 * Email for user when second stripe attempt fails
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails\Stripe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$payment_link = '<a href="' . esc_url( yith_wcact_get_payment_method_url() ) . '">' . esc_html__( 'update your credit card information', 'yith-auctions-for-woocommerce' ) . '</a>';

/* translators: %s: Product price */
$content_image_message = esc_html__( 'price: %s', 'yith-auctions-for-woocommerce' );

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi!  %s,', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>
<p><?php echo esc_html__( 'We tried a second attempt, but we are not able to process your payment for this item:', 'yith-auctions-for-woocommerce' ); ?></p>

<?php

$args = array(
	'product'       => $email->object['product'],
	'url'           => $email->object['url_product'],
	'product_name'  => $email->object['product_name'],
	'content_image' => '<p>' . sprintf( $content_image_message, wc_price( $email->object['product']->get_price() ) ) . '</p>',
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

if ( 'requires_validation' === $email->object['gateway_response']['action'] ) { // Card needs validation.
	$base                 = get_option( 'woocommerce_email_base_color' );
	$base_text            = wc_light_or_dark( $base, '#202020', '#ffffff' );
	$order                = $email->object['order']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$checkout_payment_url = $order->get_checkout_payment_url();

	?>
	<p><?php esc_html_e( 'We don\'t want you to lose the item you have won, so we ask you to manually pay for the order as soon as possible.', 'yith-auctions-for-woocommerce' ); ?></p>
	<div style="text-align: center; margin-top: 60px !important; margin-bottom: 10px !important;">
		<?php
		/**
		 * APPLY_FILTERS: yith_wcact_checkout_payment_url
		 *
		 * Filter the checkout URL to pay the auction.
		 *
		 * @param string   $url   Checkout URL
		 * @param WC_Email $email Email object
		 *
		 * @return string
		 */
		?>
		<a style="padding:10px 50px !important;font-size: 12px !important; background: <?php echo esc_attr( $base ); ?> !important; color: <?php echo esc_attr( $base_text ); ?> !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo esc_attr( apply_filters( 'yith_wcact_checkout_payment_url', $checkout_payment_url, $email ) ); ?>"><?php esc_html_e( 'Pay order', 'yith-auctions-for-woocommerce' ); ?></a>
	</div>
	<?php
} else {
	?>
	<p>
		<?php
		// translators: %s is the URL to the payment section in My Account.
		echo wp_kses_post( sprintf( __( 'We don\'t want you to lose the item you have won, so we ask you to %s. We will try to charge your credit card again in the next 24hrs before deleting your winnings.', 'yith-auctions-for-woocommerce' ), $payment_link ) );
		?>
	</p>
<?php } ?>

<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php echo esc_html__( 'Regards,', 'yith-auctions-for-woocommerce' ); ?></p>
	<p>
		<?php
		// translators: %s is the blog name.
		printf( esc_html__( '%s Staff ', 'yith-auctions-for-woocommerce' ), esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ) );
		?>
	</p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
