<?php
/**
 * Base template in My Account
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\MyAccount
 */

?>
<div>
	<input class="ywcact-my-acount-auction-template" data-type="auction-index" type="hidden">
	<div class="yith-wcact-my-auctions-list-index-container">
		<div class="yith-wcact-my-auctions-list-index-container-header">
			<h3><?php esc_html_e( 'My auctions', 'yith-auctions-for-woocommerce' ); ?></h3>
		</div>
		<div class="yith-wcact-my-auctions-list-index-container-content">
			<?php
			if ( ! empty( $auctions_by_user ) ) {
				?>
				<table class="shop_table shop_table_responsive my_account_orders yith_wcact_my_auctions_auction_list_index">
					<thead>
						<tr>
							<th class="toptable my-auction-list-index-product"><span class="nobr"><?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?></span></th>
							<th class="toptable my-auction-list-index-your-bid"><span class="nobr"><?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
							<th class="toptable my-auction-list-index-current-bid"><span class="nobr"><?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
							<th class="toptable my-auction-list-index-status"><span class="nobr"><?php echo esc_html__( 'Status', 'yith-auctions-for-woocommerce' ); ?></span></th>

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
									/**
									 * APPLY_FILTERS: yith_wcact_show_pay_now_button
									 *
									 * Filter whether to show the "Pay now" button.
									 *
									 * @param bool $show_button Whether to show the "Pay now" button
									 *
									 * @return bool
									 */
									if ( apply_filters( 'yith_wcact_show_pay_now_button', true ) && $button ) {
										?>
										<a  href="<?php echo esc_url( $button['url'] ); ?>" class="<?php echo esc_attr( $button['button_class'] ); ?>" data-quantity="1" <?php echo $button['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											id="yith-wcact-auction-won-auction">
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
				<p><?php esc_html_e( 'Make your first bid to see your auctions here!', 'yith-auctions-for-woocommerce' ); ?></p>
			<?php } ?>
		</div>

		<?php if ( $total_auctions > $limit ) { ?>
			<div class="yith-wcact-my-auctions-list-index-container-footer">
				<a  href="<?php echo esc_url( $auctions_list_url ); ?>" class="ywcact-view-all-auction-list ywcact-my-account-link">
					<?php echo esc_html__( 'View All >', 'yith-auctions-for-woocommerce' ); ?>
				</a>
			</div>
		<?php } ?>
	</div>

<?php
	// Watchlist index section.
?>
	<div class="yith-wcact-my-watchlist-list-index-container">
		<div class="yith-wcact-my-watchlist-list-index-container-header">
			<h3><?php esc_html_e( 'My watchlist', 'yith-auctions-for-woocommerce' ); ?></h3>
		</div>
		<div class="yith-wcact-my-watchlist-list-index-container-content">
			<?php
			if ( ! empty( $watchlist_by_user ) ) {
				?>
			<table class="shop_table shop_table_responsive my_account_orders yith_wcact_my_auctions_watchlist_list_index">
				<thead>
					<tr>
						<th class="product-remove"> </th>
						<th class="toptable my-auction-watchlist-list-index-product"><span class="nobr"><?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?></span></th>
						<th class="toptable my-auction-watchlist-list-index-your-bid"><span class="nobr"><?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
						<th class="toptable my-auction-watchlist-list-index-current-bid"><span class="nobr"><?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?></span></th>
						<th class="toptable my-auction-watchlist-list-index-end-on"><span class="nobr"><?php echo esc_html__( 'End on:', 'yith-auctions-for-woocommerce' ); ?></span></th>

					</tr>
				</thead>
				<tbody>
				<?php

				foreach ( $watchlist_by_user as $auction ) {
					$product       = $auction['product'];
					$product_name  = $auction['product_name'];
					$product_url   = $auction['product_url'];
					$image         = $auction['image'];
					$color         = $auction['color'];
					$image         = $auction['image'];
					$last_bid_user = $auction['last_bid_user'];
					$auction_date  = $auction['auction_date'];

					?>
					<tr class="yith-wcact-auction-content-my-watchlist-list" data-product="<?php echo esc_attr( $product->get_id() ); ?>" >
						<td class="product-remove">
							<div>
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'remove_from_watchlist' => $product->get_id(),
											'user_id' => $user_id,
										)
									)
								);

								/**
								 * APPLY_FILTERS: yith_wcact_remove_product_watchlist_message_title
								 *
								 * Filter the title of the icon to remove the product from the watchlist.
								 *
								 * @param string $title Title
								 *
								 * @return string
								 */
								?>
								" class="remove remove_from_watchlist" title="<?php echo esc_attr( apply_filters( 'yith_wcact_remove_product_watchlist_message_title', __( 'Remove this product', 'yith-auctions-for-woocommerce' ) ) ); ?>">&times;</a>
							</div>
						</td>
						<td class="my-auction-watchlist-list-index-product" data-title="<?php echo esc_html__( 'Product', 'yith-auctions-for-woocommerce' ); ?>">
							<span class="yith-wcact-my-account-image" style="vertical-align: middle;"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a></td>
						<td class="yith-wcact-my-bid yith-wcact-my-auctions <?php echo esc_attr( $color ); ?>" data-title="<?php echo esc_html__( 'Your bid', 'yith-auctions-for-woocommerce' ); ?>"><?php echo apply_filters( 'yith_wcact_auction_product_price', wc_price( $last_bid_user ), $last_bid_user, $currency ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
						<td class="yith-wcact-current-bid yith-wcact-my-auctions" data-title="<?php echo esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ); ?>"><?php echo 'yes' !== $product->get_auction_sealed() ? apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_price() ), $product->get_price(), $currency ) : esc_html__( 'Sealed', 'yith-auctions-for-woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
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
				<p><?php esc_html_e( 'Watch an auction to see it here!', 'yith-auctions-for-woocommerce' ); ?></p>
			<?php } ?>
		</div>

		<?php if ( $total_watchlist > $limit ) { ?>
		<div class="yith-wcact-my-watchlist-list-index-container-footer">
			<a  href="<?php echo esc_url( $watchlist_list_url ); ?>" class="ywcact-view-all-my-watchlist ywcact-my-account-link">
				<?php echo esc_html__( 'View All >', 'yith-auctions-for-woocommerce' ); ?>
			</a>
		</div>
		<?php } ?>
	</div>
</div>
