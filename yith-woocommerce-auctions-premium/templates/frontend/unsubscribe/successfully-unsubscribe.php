<?php
/**
 * Successfully unsubscribe content template
 *
 * @package YITH\Auctions\Templates\Frontend\Unsubscribe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


?>
<div class="yith-wcact-successfully-unsubscribe-content">
	<div class="entry-content">
		<div class="ywcact-main-message">
			<span id="checkmark"></span>
			<span><?php echo esc_html__( 'You are now successfully unsubscribed', 'yith-auctions-for-woocommerce' ); ?></span>
		</div>
		<div class="ywcact-main-action">
			<a href="<?php echo esc_url( $button_link ); ?>" class="button"><?php echo esc_html( $button_text ); ?></a>
		</div>
	</div>
<div>
