<?php
/**
 * Auction timeleft template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

?>

<div class="timer yith-wcact-timer-auction <?php echo esc_attr( $yith_wcact_class ); ?>" id="timer_auction" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-current-time="<?php echo esc_attr( time() ); ?>" data-remaining-time="<?php echo esc_attr( $total ); ?>" data-finish-time="<?php echo esc_attr( $auction_finish ); ?>" data-finish="<?php echo esc_attr( $auction_finish ); ?>" data-date="<?php echo esc_attr( $date ); ?>" data-last-minute="<?php echo esc_attr( $last_minute ); ?>">
	<div class="yith-wcact-timeleft <?php echo esc_attr( $yith_wcact_block ); ?> yith-wcact-timeleft-days">
		<span id="days" class="yith-wcact-number days_product_<?php echo esc_html( $product->get_id() ); ?>"></span><span class="yith-wcact-number-label"><?php esc_html_e( 'Days', 'yith-auctions-for-woocommerce' ); ?> </span>
	</div>
	<div class="yith-wcact-timeleft <?php echo esc_attr( $yith_wcact_block ); ?> yith-wcact-timeleft-hours">
		<span id="hours" class="yith-wcact-number  hours_product_<?php echo esc_attr( $product->get_id() ); ?>"></span><span class="yith-wcact-number-label"><?php esc_html_e( 'Hours', 'yith-auctions-for-woocommerce' ); ?> <span>
	</div>
	<div class="yith-wcact-timeleft <?php echo esc_attr( $yith_wcact_block ); ?> yith-wcact-timeleft-minutes">
	<span id="minutes" class="yith-wcact-number  minutes_product_<?php echo esc_attr( $product->get_id() ); ?>"></span><span class="yith-wcact-number-label"><?php esc_html_e( 'Minutes', 'yith-auctions-for-woocommerce' ); ?> <span>
	</div>
	<div class="yith-wcact-timeleft <?php echo esc_attr( $yith_wcact_block ); ?> yith-wcact-timeleft-seconds">
	<span id="seconds" class="yith-wcact-number seconds_product_<?php echo esc_attr( $product->get_id() ); ?>"></span><span class="yith-wcact-number-label"><?php esc_html_e( 'Seconds', 'yith-auctions-for-woocommerce' ); ?><span>
	</div>
</div>
