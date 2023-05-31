<?php
/**
 * Watchlist template in My Account
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\MyAccount
 */

?>
<div class="yith-wcact-my-watchlist-list-index-container-header">
	<h3><?php esc_html_e( 'My watchlist', 'yith-auctions-for-woocommerce' ); ?></h3>
</div>

<?php

if ( count( $auctions_by_user ) > 0 ) {
	?>
	<div class="ywcact-return-to-watchlist">
		<a href="<?php echo esc_url( $default_url ); ?>" class="ywcact-view-all-auction-list ywcact-my-account-link">
			<?php echo esc_html__( '< Back', 'yith-auctions-for-woocommerce' ); ?>
		</a>
	</div>

	<input class="ywcact-my-acount-auction-template" data-type="my-watchlist" type="hidden">

	<table class="shop_table shop_table_responsive my_account_orders yith_wcact_my_auctions_my_watchlist">
		<thead>
		<tr>
			<th class="product-remove"></th>
			<th class="toptable order-number"><span class="nobr"><?php echo esc_html__( 'Image', 'yith-auctions-for-woocommerce' ); ?></span></th>
			<th class="toptable order-status"><span class="nobr"><?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?></span></th>
			<th class="toptable order-date"><span class="nobr"><?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
			<th class="toptable order-total"><span class="nobr"><?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
			<th class="toptable order-actions"><span class="nobr"><?php echo esc_html_x( 'Ends in:', 'This is followed by a countdown (x days, x hours, x secs)', 'yith-auctions-for-woocommerce' ); ?></span></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $auctions_by_user as $auction ) {
			$product       = $auction['product'];
			$product_name  = $auction['product_name'];
			$product_url   = $auction['product_url'];
			$image         = $auction['image'];
			$color         = $auction['color'];
			$image         = $auction['image'];
			$last_bid_user = $auction['last_bid_user'];
			$auction_date  = $auction['auction_date'];

			?>
			<tr class="yith-wcact-auction-my-watchlist-endpoint" data-product="<?php echo esc_attr( $product->get_id() ); ?>">
				<td class="product-remove">
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'remove_from_watchlist' => $product->get_id(),
								'user_id'               => $user_id,
							)
						)
					);

					/**
					 * APPLY_FILTERS: yith_wcact_remove_product_watchlist_message_title
					 *
					 * Filter the title of the icon to remove the product from the watchlist
					 *
					 * @param string $title Title
					 *
					 * @return string
					 */
					?>
					" class="remove remove_from_watchlist" title="<?php echo esc_html( apply_filters( 'yith_wcact_remove_product_watchlist_message_title', __( 'Remove this product', 'yith-auctions-for-woocommerce' ) ) ); ?>">&times;</a>
				</td>
				<td class="yith-wcact-auction-image" data-title="<?php echo esc_html__( 'Image', 'yith-auctions-for-woocommerce' ); ?>"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				<td class="product-url" data-title="<?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?>"><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a></td>
				<td class="yith-wcact-my-bid yith-wcact-my-auctions <?php echo esc_attr( $color ); ?>" data-title="<?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?>">
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
				<td class="yith-wcact-current-bid yith-wcact-my-auctions"
						data-title="<?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?>"><?php echo 'yes' !== $product->get_auction_sealed() ? apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_price() ), $product->get_price(), $currency ) : esc_html__( 'Sealed', 'yith-auctions-for-woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				<td class="yith-wcact-end-on" data-title="<?php echo esc_html__( 'End on', 'yith-auctions-for-woocommerce' ); ?>">

					<div class="yith-wcact-timeleft-widget-watchlist">
						<?php
						if ( ! $product->is_closed() ) {
							$args = array(
								'product'          => $product,
								'auction_finish'   => $product->get_end_date(),
								'date'             => strtotime( 'now' ),
								'last_minute'      => 0,
								'total'            => $auction_date - strtotime( 'now' ),
								'yith_wcact_class' => 'yith-wcact-timeleft-default',
								'yith_wcact_block' => '',

							);
							wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
						} else {

							echo esc_html__( 'Finished', 'yith-auctions-for-woocommerce' );
						}
						?>
					</div>
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
	<p><?php echo esc_html__( 'Watch an auction to see it here!', 'yith-auctions-for-woocommerce' ); ?> </p>
	<?php
}
?>
