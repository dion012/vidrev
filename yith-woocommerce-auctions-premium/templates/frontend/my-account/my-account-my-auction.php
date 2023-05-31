<?php
/**
 * Auctions template in My Account
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\MyAccount
 */

?>

<div class="yith-wcact-my-auctions-list-index-container-header">
	<h3><?php esc_html_e( 'My auctions', 'yith-auctions-for-woocommerce' ); ?></h3>
</div>

<?php

if ( count( $auctions_by_user ) > 0 ) {
	?>
		<div class="ywcact-return-to-watchlist">
			<a  href="<?php echo esc_url( $default_url ); ?>" class="ywcact-view-all-auction-list ywcact-my-account-link">
				<?php echo esc_html__( '< Back', 'yith-auctions-for-woocommerce' ); ?>
			</a>
		</div>

		<input class="ywcact-my-acount-auction-template" data-type="my-auction" type="hidden">
		<table class="shop_table shop_table_responsive my_account_orders yith_wcact_my_auctions_table">
			<thead>
				<tr>
					<th class="toptable order-status"><span class="nobr"><?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?></span></th>
					<th class="toptable order-date"><span class="nobr"><?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
					<th class="toptable order-total"><span class="nobr"><?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
					<th class="toptable order-actions"><span class="nobr"><?php echo esc_html__( 'Status', 'yith-auctions-for-woocommerce' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $auctions_by_user as $auction ) {
				$product        = $auction['product'];
				$product_name   = $auction['product_name'];
				$product_url    = $auction['product_url'];
				$image          = $auction['image'];
				$color          = $auction['color'];
				$image          = $auction['image'];
				$last_bid_user  = $auction['last_bid_user'];
				$button         = $auction['button'];
				$label          = $auction['label'];
				$auction_status = $auction['status'];

				?>
					<tr class="yith-wcact-auction-endpoint" data-product="<?php echo esc_attr( $product->get_id() ); ?>" >
						<td class="my-auction-list-index-product" data-title="<?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?>">
							<span class="yith-wcact-my-account-image" style="vertical-align: middle;"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a></td>
						<td class="my-auction-list-index-your-bid yith-wcact-my-bid-endpoint yith-wcact-my-auctions order-date <?php echo esc_attr( $color ); ?>" data-title="<?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?>">
							<?php
							/**
							 * APPLY_FILTERS: yith_wcact_auction_product_price
							 *
							 * Filter the auction product price.
							 *
							 * @param string $bid_price Bid price
							 * @param string $bid       Bid
							 * @param string $currency  Currency
							 *
							 * @return string
							 */
							echo wp_kses_post( apply_filters( 'yith_wcact_auction_product_price', wc_price( $last_bid_user ), $last_bid_user, $currency ) );
							?>
						</td>
						<td class="my-auction-list-index-status yith-wcact-current-bid-endpoint yith-wcact-my-auctions order-total" data-title="<?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?>"><?php echo 'yes' !== $product->get_auction_sealed() ? apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_price() ), $product->get_price(), $currency ) : esc_html__( 'Sealed', 'yith-auctions-for-woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
						<td class="yith-wcact-auctions-status yith-wcact-my-auctions order-status" data-title="<?php echo esc_html__( 'Status', 'yith-auctions-for-woocommerce' ); ?>">
							<span> <?php echo esc_html( $label ); ?> </span>
						<?php
						if ( $button ) {
							?>
							<a  href="<?php echo esc_url( $button['url'] ); ?>" class="<?php echo esc_attr( $button['button_class'] ); ?>" data-quantity="1" <?php echo $button['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="yith-wcact-auction-won-auction">
							<?php echo esc_html( $button['button_label'] ); ?>
							</a>
							<?php
							/**
							 * DO_ACTION: yith_wcact_auction_status_my_account_{$auction_status}
							 *
							 * Allow to render some content in the My Account page depending on the auction status.
							 *
							 * @param WC_Product $product Product
							 */
							do_action( "yith_wcact_auction_status_my_account_{$auction_status}", $product );
						}

						?>
						</td>
					</tr>
					<?php
			}
			?>
			</tbody>
		</table>
	<?php
} else {
	?>
	<p><?php echo esc_html__( 'Make your first bid to see your auctions here!', 'yith-auctions-for-woocommerce' ); ?> </p>
	<?php
}
?>
