<?php
/**
 * Email for admin when the auction product is rescheduled
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p><?php printf( esc_html__( 'Hi, we would like to inform you that the auction for:', 'yith-auctions-for-woocommerce' ) ); ?></p>
<p><?php esc_html_e( 'This auction was automatically rescheduled:', 'yith-auctions-for-woocommerce' ); ?></p>

<?php

$args = array(
	'product'       => $email->object['product'],
	'url'           => $email->object['url_product'],
	'product_name'  => $email->object['product_name'],
	'content_image' => '<p><a target="_blank" href="' . $email->object['url_product'] . '">' . esc_html__( 'View details>', 'yith-auctions-for-woocommerce' ) . '</a></p>',
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php esc_html_e( 'Good luck!', 'yith-auctions-for-woocommerce' ); ?></p>
	<p><?php echo wp_kses_post( get_bloginfo( 'name' ) ) . ' ' . esc_html__( 'Staff', 'yith-auctions-for-woocommerce' ); ?></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
