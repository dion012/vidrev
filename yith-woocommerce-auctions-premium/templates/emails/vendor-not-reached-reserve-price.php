<?php
/**
 * Email for admin when not reached reserve price
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p><?php printf( esc_html__( 'Hi, we would like to inform you that you have the highest bid for:', 'yith-auctions-for-woocommerce' ) ); ?></p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<p><?php printf( esc_html__( "Doesn't exceed the minimum reserve price.", 'yith-auctions-for-woocommerce' ) ); ?></p>
<div>
	<p><?php esc_html_e( 'If you want to take another action with the item, click this', 'yith-auctions-for-woocommerce' ); ?><a href="<?php echo esc_url( $email->object['url_product'] ); ?>"><?php esc_html_e( 'link', 'yith-auctions-for-woocommerce' ); ?></a></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
