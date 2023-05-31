<?php
/**
 * Email for user when third stripe attempt fails
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails\Stripe
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
<p>
	<?php
	// translators: %s is the name of the auction product.
	printf( esc_html__( 'We made several attemps but unfortunately we were unable to charge your credit card for the “%s” item you won.', 'yith-auctions-for-woocommerce' ), esc_html( $email->object['product_name'] ) );
	?>
</p>
<p><?php echo esc_html__( "We're sorry but for this reason you lost the item.", 'yith-auctions-for-woocommerce' ); ?></p>
<p><?php echo esc_html__( 'We wish you a good luck for your future auctions!', 'yith-auctions-for-woocommerce' ); ?></p>

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
