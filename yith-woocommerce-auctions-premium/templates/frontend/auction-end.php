<?php
/**
 * Auction end template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

/**
$auction_finish = ( $datetime = $product->get_end_date() ) ? $datetime : NULL;
$date = strtotime('now');
$total = $auction_finish - $date;
$product_id = $product->get_id();
$yith_wcact_class = isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default';*/

?>

<div id="auction_end" class="ywcact-auction-end-date">
	<label for="_yith_auction_end" class="ywcact-auction-end"><?php esc_html_e( 'Auction ends: ', 'yith-auctions-for-woocommerce' ); ?></label>
	<?php
	$date_format           = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
	$time_format           = get_option( 'yith_wcact_general_time_format', 'H:i:s' );
	$auction_end_formatted = gmdate( $date_format . ' ' . $time_format, $auction_finish );
	$time_zone             = get_option( 'yith_wcact_general_time_zone', '' );

	?>
	<div class="ywcact-date-end">
		<label id="dateend" class="yith_auction_datetime_shop" data-finnish-shop="<?php echo esc_attr( $auction_finish ); ?>" data-yith-product="<?php echo esc_attr( $product_id ); ?>"><?php echo wp_kses_post( $auction_end_formatted ); ?></label>
		<label><?php echo wp_kses_post( $time_zone ); ?></label>
	</div>
</div>
