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

<p>
	<?php
	// translators: %s is the bidder username.
	echo wp_kses_post( sprintf( __( 'Hi  %s,', 'yith-auctions-for-woocommerce' ), $email->object['user_name'] ) );
	?>
</p>
<p><?php esc_html_e( 'Congratulations, you\'re the winner of the auction:', 'yith-auctions-for-woocommerce' ); ?></p>

<?php

$args = array(
	'product'      => $email->object['product'],
	'url'          => $email->object['url_product'],
	'product_name' => $email->object['product_name'],
);

wc_get_template( 'product-email.php', $args, '', YITH_WCACT_PATH . 'templates/emails/product-emails/' );

/**
 * DO_ACTION: yith_wcact_after_content_winner_email
 *
 * Allow to render some content after the email content in the email sent to the auction winner.
 *
 * @param WC_Email $email Email object
 */
do_action( 'yith_wcact_after_content_winner_email', $email );

?>

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
