<?php
/**
 * Email for admin when without any bids
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;// Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p><?php printf( esc_html__( 'Hi!, we would like to inform you that the auction:', 'yith-auctions-for-woocommerce' ) ); ?></p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<p><?php printf( esc_html__( 'The auction has ended and has been won by: ', 'yith-auctions-for-woocommerce' ) ); ?><?php echo wp_kses_post( $email->object['user_name'] ); ?></p>

<?php

do_action( 'woocommerce_email_footer', $email );
