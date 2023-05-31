<?php
/**
 * Email for admin when the admin delete the bid for the customer
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<p>
	<?php
	// translators: %1$s is the bidder username. %2$s is the bid amount.
	echo wp_kses_post( sprintf( __( 'The bid %1$s made by %2$s was successfully removed for the following auction:', 'yith-auctions-for-woocommerce' ), wc_price( $email->object['args']['bid'] ), $email->object['user_name'] ) );
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
