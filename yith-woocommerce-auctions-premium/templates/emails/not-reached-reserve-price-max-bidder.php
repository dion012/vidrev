<?php
/**
 * Email for user when the highest bid for the following auction doesn't exceed the minimum reserve price:
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$base      = get_option( 'woocommerce_email_base_color' );
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<h2><?php esc_html_e( 'Your bid doesn\'t exceed the minimum reserve price!', 'yith-auctions-for-woocommerce' ); ?></h2>
<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi %s, we would like to inform you that the highest bid for the following auction doesn\'t exceed the minimum reserve price:', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

do_action( 'woocommerce_email_footer', $email );
