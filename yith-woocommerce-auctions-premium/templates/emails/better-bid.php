<?php
/**
 * Email for user when another user just overbid your maximun bid
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_bid = $email->object['product']->get_current_bid();

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
	if ( $email->object['is_reverse_email'] ) {
		// translators: %1$s is the name of the auction product. %2$s is the current bid.
		echo wp_kses_post( sprintf( __( 'Another buyer has outbid (placed a lower bid) for the item %1$s and the price is now %2$s', 'yith-auctions-for-woocommerce' ), '<strong>' . $email->object['product_name'] . '</strong>', wc_price( $current_bid ) ) );
	} else {
		// translators: %1$s is the name of the auction product. %2$s is the current bid.
		echo wp_kses_post( sprintf( __( 'Another buyer has outbid (placed a higher maximum bid) for the item %1$s and the price is now %2$s', 'yith-auctions-for-woocommerce' ), '<strong>' . $email->object['product_name'] . '</strong>', wc_price( $current_bid ) ) );
	}
	?>
</p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
	/** 'max_bid'     => $email->object['max_bid'], */
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<div>
	<p><?php echo esc_html__( 'You can still outbid with a new offer!', 'yith-auctions-for-woocommerce' ); ?></p>
</div>
<div>
	<a href="<?php echo esc_url( $email->object['url_product'] ); ?>"><?php esc_html_e( 'Place a new bid >', 'yith-auctions-for-woocommerce' ); ?></a> </p>
</div>
<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php echo esc_html__( 'Regards,', 'yith-auctions-for-woocommerce' ); ?></p>
	<p><?php echo wp_kses_post( get_bloginfo( 'name' ) ) . ' ' . esc_html__( 'Staff', 'yith-auctions-for-woocommerce' ); ?></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
