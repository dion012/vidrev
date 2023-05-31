<?php
/**
 * Email for user when the user is the winner of the auction
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<h2><?php esc_html_e( 'Sorry, you are not the winner.', 'yith-auctions-for-woocommerce' ); ?></h2>
<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Sorry %s, you are not the winner of the auction:', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
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
	<p><?php esc_html_e( 'Thank you for your participation', 'yith-auctions-for-woocommerce' ); ?></p>
</div>

<?php

do_action( 'woocommerce_email_footer', $email );
