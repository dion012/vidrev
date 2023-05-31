<?php
/**
 * Email for user that remind the user to pay for the product
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$pay_options_number = $email->object['reschedule_options']['after_number'];
$pay_options_unit   = $email->object['reschedule_options']['after_unit'];

$base      = get_option( 'woocommerce_email_base_color' );
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi %s,', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>
<p>
	<?php esc_html_e( 'please don\'t forget you won our auction and you have to pay the following item:', 'yith-auctions-for-woocommerce' ); ?>
</p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

$url = add_query_arg(
	array(
		'yith-wcact-pay-won-auction'  => $email->object['product_id'],
		'yith-wcact-pay-won-redirect' => 'checkout',
	),
	home_url()
);


if ( $pay_options_number ) {
	/* translators: %1$s: 2 %2$2: minutes */
	$message_start = sprintf( __( 'If you don\'t pay in %1$s %2$s,', 'yith-auctions-for-woocommerce' ), $pay_options_number, $pay_options_unit );

	$select_reminder = $email->object['reschedule_options']['after_select_reminder'];

	$message_end = $select_reminder && 'reschedule' === $select_reminder ? esc_html__( 'the auction will be rescheduled.', 'yith-auctions-for-woocommerce' ) : esc_html__( 'your bids will be removed and you\'ll lose this item.', 'yith-auctions-for-woocommerce' );

	?>
	<p><?php echo esc_html( $message_start . ' ' . $message_end ); ?></p>
	<?php

}

?>

<div style="text-align: center; margin-top: 60px !important; margin-bottom: 10px !important;">
	<?php
	/**
	 * APPLY_FILTERS: yith_wcact_winner_email_pay_now_url
	 *
	 * Filter the URL of the button to pay for the auction.
	 *
	 * @param string  $url Button URL
	 * @param WC_Email $email Email object
	 *
	 * @return string
	 */
	?>
	<a style="padding:10px 50px !important;font-size: 12px !important; background: <?php echo esc_attr( $base ); ?> !important; color: <?php echo esc_attr( $base_text ); ?> !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo esc_url( apply_filters( 'yith_wcact_winner_email_pay_now_url', $email->object['url_redirect'], $email ) ); ?>"><?php echo esc_html( $email->object['pay_now_button_label'] ); ?></a>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
