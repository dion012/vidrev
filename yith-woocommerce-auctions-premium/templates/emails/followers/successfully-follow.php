<?php
/**
 * Successfully follow email
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails\Followers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi!  %s,', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>
<p>
	<?php
	// translators: %1$s is the URL of the auction product. %2$s is the auction product name.
	echo wp_kses_post( sprintf( __( 'You are now following the auction for the item "<a href="%1$s"><strong>%2$s</strong></a>".', 'yith-auctions-for-woocommerce' ), $email->object['url_product'], $email->object['product_name'] ) );
	?>
</p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<div>
	<p><?php echo esc_html__( 'We will keep you updated!', 'yith-auctions-for-woocommerce' ); ?></p>
</div>
<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php echo esc_html__( 'Regards,', 'yith-auctions-for-woocommerce' ); ?></p>
	<p><?php echo wp_kses_post( get_bloginfo( 'name' ) ) . ' ' . esc_html__( 'Staff', 'yith-auctions-for-woocommerce' ); ?></p>
</div>

<?php

/**
 * DO_ACTION: yith_wcact_email_footer
 *
 * Allow to render some content in the footer for the auction emails
 *
 * @param WC_Email $email Email object
 */
do_action( 'yith_wcact_email_footer', $email );
