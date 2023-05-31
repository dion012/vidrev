<?php
/**
 * Other auctions items template
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\Shortcodes
 */

?>

<li class="ywcact-other-auction-product-container" style="background-color: <?php echo esc_attr( $color ); ?>">
	<div class="ywcact-other-auction-product-section">
		<div class="ywcact-other-auction-product-image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="image-thumb">
				<?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
		<div class="ywcact-other-auction-product-info">
			<span class="ywcact-other-auction-info-title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_title() ); ?></a></span>
			<span class="ywcact-other-auction-info-current-bid"> <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </span>
			<div class="ywcact-other-auction-info-timeleft-section">
				<small> <?php echo esc_html__( 'Time left:', 'yith-auctions-for-woocommerce' ); ?> </small>
				<?php
				$auction_date = $product->is_start() ? $product->get_end_date() : $product->get_start_date();
				$args         = array(
					'product'          => $product,
					'auction_finish'   => $product->get_end_date(),
					'date'             => strtotime( 'now' ),
					'last_minute'      => isset( $time_change_color ) ? $auction_end - $time_change_color : 0,
					'total'            => $auction_date - strtotime( 'now' ),
					'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default',
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
