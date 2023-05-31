<?php
/**
 * Successfully follow email
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails\Common
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
<p><?php esc_html_e( 'This auction is ended because an user purchased the item through the "Buy it now" option:', 'yith-auctions-for-woocommerce' ); ?></p>

<?php

$args = array(
	'product'       => $email->object['product'],
	'url'           => $email->object['url_product'],
	'product_name'  => $email->object['product_name'],
	'content_image' => '<p class="ywcat-image-price" style="display: block;"><span style="font-weight: 800 !important;">' . esc_html__( 'Sales price:', 'yith-auctions-for-woocommerce' ) . '</span> <span>' . wc_price( $email->object['sales_price'] ) . '</span></p>',
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php echo esc_html__( 'Regards,', 'yith-auctions-for-woocommerce' ); ?></p>
	<p><?php echo wp_kses_post( get_bloginfo( 'name' ) ) . ' ' . esc_html__( 'Staff', 'yith-auctions-for-woocommerce' ); ?></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
