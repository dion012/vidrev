<?php
/**
 * Email for user when end auction
 *
 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi  %s!', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>
<p>
	<?php
	// translators: %1$s is the URL of the auction product. %2$s is the auction product name.
	echo wp_kses_post( sprintf( __( 'The auction for the item <a href="%1$s">%2$s</a> is about to end:', 'yith-auctions-for-woocommerce' ), $email->object['url_product'], $email->object['product_name'], ) );
	?>
</p>

<?php

$args = array(
	'product'            => $email->object['product'],
	'url'                => $email->object['url_product'],
	'product_name'       => $email->object['product_name'],
	'show_auction_end'   => true,
	'auction_end_number' => $email->object['number'],
	'auction_end_time'   => $email->object['time'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

?>

<p><?php esc_html_e( 'You can still', 'yith-auctions-for-woocommerce' ); ?> <a href="<?php echo esc_url( $email->object['url_product'] ); ?>"><?php esc_html_e( 'place a new bid', 'yith-auctions-for-woocommerce' ); ?></a></p>

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
