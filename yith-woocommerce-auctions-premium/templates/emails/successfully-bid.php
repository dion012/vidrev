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

$args_email = $email->object['args'];

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
	// translators: %1$s is the bid amount. %2$s is the name of the auction. %3$s is the site name.
	echo wp_kses_post( sprintf( __( 'You just placed a bid of %1$s for the auction %2$s on %3$s', 'yith-auctions-for-woocommerce' ), wc_price( $args_email['bid'] ), '<strong>' . $email->object['product_name'] . '</strong>', get_bloginfo( 'name' ) ) );
	?>
</p>
<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

if ( $args_email['bid'] >= $email->object['product']->get_price() ) {
	?>
	<div>
		<p><?php echo esc_html__( 'Congratulations, you are the highest bidder!', 'yith-auctions-for-woocommerce' ); ?></p>
	</div>
	<?php
}

?>

<div>
	<a href="<?php echo esc_url( $email->object['url_product'] ); ?>"><?php esc_html_e( 'Check the auction here >', 'yith-auctions-for-woocommerce' ); ?></a>
</div>

<div style="padding-top: 10px; padding-bottom: 10px;">
	<p><?php echo esc_html__( 'Regards,', 'yith-auctions-for-woocommerce' ); ?></p>
	<p><?php echo wp_kses_post( get_bloginfo( 'name' ) . ' ' . esc_html__( 'Staff', 'yith-auctions-for-woocommerce' ) ); ?></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
