<?php
/**
 * The template for displaying product widget entries
 *
 * @package YITH\Auctions\Templates\WooCommerce\Widgets
 **/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$datetime       = $product->get_end_date();
$to_auction     = $datetime ? $datetime : null;
$auction_finish = $to_auction;
$date           = strtotime( 'now' );
$auction_start  = $product->get_start_date();

$total = $auction_finish - $date;

$current_bid = ( 'no' === $product->get_auction_sealed() ) ? wc_price( $product->get_price() ) : esc_html__( 'Sealed', 'yith-auctions-for-woocommerce' );

$instance     = YITH_Auctions()->bids;
$auction_bids = $instance->get_bids_auction( $product->get_id() );

$time_left_message = ( $date < $auction_start ) ? __( 'Time left to start auction:', 'yith-auctions-for-woocommerce' ) : __( 'Time left to end auction:', 'yith-auctions-for-woocommerce' );

?>

<li class="ywcact-widget-product">
	<div class="ywcact-widget-product-section">
		<div class="ywcact-widget-product-section-image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="image-thumb">
				<?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
		<div class="ywcact-widget-product-section-info">
			<span class="ywcact-widget-product-section-info-title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_title() ); ?></a></span>
			<span class="ywcact-widget-product-section-info-current-bid"> <?php echo __( 'Current bid: ', 'yith-auctions-for-woocommerce' ) . $current_bid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </span>
			<div class="ywcact-widget-product-section-info-timeleft-section">
				<small> <?php echo esc_html( $time_left_message ); ?> </small>
				<?php
				$auction_date = $product->is_start() ? $product->get_end_date() : $product->get_start_date();
				$args         = array(
					'product'          => $product,
					'auction_finish'   => $auction_date,
					'date'             => $date,
					'last_minute'      => isset( $time_change_color ) ? $product->get_end_date() - $time_change_color : 0,
					'total'            => $auction_date - $date,
					'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-compact',
					'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',

				);
				?>
				<div class="ywcact-other-auction-info-timeleft">
					<?php
					wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
					?>
				</div>
			</div>
		</div>
	</div>
</li>
