<?php
/**
 * List bids template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

if ( $product instanceof WC_Product && 'auction' === $product->get_type() ) {
	?>
		<div class="yith-wcact-table-bids">
			<input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php echo esc_attr( $product->get_id() ); ?>">
			<?php
			/**
			 * APPLY_FILTERS: yith_wcact_show_list_bids
			 *
			 * Filter whether to show the bids list in the product page.
			 *
			 * @param bool $show_bids_list Whether to show bids list or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_show_list_bids', true ) ) {
				/**
				 * APPLY_FILTERS: yith_wcact_is_sealed_on_list_bids
				 *
				 * Filter the status of the auction, to check if it is sealed or not.
				 *
				 * @param string                     $product_sealed Is the auction sealed or not
				 * @param WC_Product_Auction_Premium $product        Auction product
				 *
				 * @return string
				 */
				$product_sealed   = apply_filters( 'yith_wcact_is_sealed_on_list_bids', $product->get_auction_sealed(), $product );
				$instance         = YITH_Auctions()->bids;
				$comparison_value = 'reverse' === $product->get_auction_type() ? '>' : '<'; // Display values for reverse or normal auctions.

				/**
				 * APPLY_FILTERS: yith_wcact_auction_bid
				 *
				 * Filter the bid amount.
				 *
				 * @param string $bid      Bid amount
				 * @param string $currency Currency
				 *
				 * @return string
				 */
				$auction_price = apply_filters( 'yith_wcact_auction_bid', $product->get_price(), $currency );

				if ( 'yes' === $product_sealed ) {
					/**
					 * APPLY_FILTERS: yith_wcact_auction_product_id
					 *
					 * Filter the auction product ID.
					 *
					 * @param int $product_id Auction product ID
					 *
					 * @return int
					 */
					$auction_list = is_user_logged_in() ? $instance->get_bids_auction( apply_filters( 'yith_wcact_auction_product_id', $product->get_id() ), get_current_user_id() ) : array();

					?>
					<div class="ywcact-list-bids-secret-auction">
						<p class="ywcact-list-bids-secret-auction-message">
							<?php
							/**
							 * APPLY_FILTERS: yith_wcact_list_bids_secret_auction_message
							 *
							 * Filter the message shown in the bids list when the auction is sealed.
							 *
							 * @param string $message Message
							 *
							 * @return string
							 */
							echo esc_html( apply_filters( 'yith_wcact_list_bids_secret_auction_message', __( 'You can see only your bids because this is a sealed auction', 'yith-auctions-for-woocommerce' ) ) );
							?>
						</p>
					</div>
					<?php
				} else {
					$auction_list = $instance->get_bids_auction( apply_filters( 'yith_wcact_auction_product_id', $product->get_id() ) );
				}

				if ( count( $auction_list ) === 0 ) {
					if ( 'no' === $product_sealed ) {
						if ( $product->is_closed() ) {
							?>
							<p id="single-product-no-bid"> <?php esc_html_e( 'There are no bids.', 'yith-auctions-for-woocommerce' ); ?></p>
							<?php
						} else {
							?>
							<p id="single-product-no-bid"> <?php ( ! $product->is_start() ) ? esc_html_e( 'This auction has not been started yet.', 'yith-auctions-for-woocommerce' ) : esc_html_e( 'There are no bids yet. Be the first!', 'yith-auctions-for-woocommerce' ); ?></p>
							<?php
						}
					}
				} else {
					?>
					<table id="datatable" class="ywcact-list-bids-table">
						<tr>
							<td class="toptable"><?php echo esc_html__( 'Bidder', 'yith-auctions-for-woocommerce' ); ?></td>
							<td class="toptable"><?php echo esc_html__( 'Bid amount', 'yith-auctions-for-woocommerce' ); ?></td>
							<td class="toptable">
								<?php
								/**
								 * APPLY_FILTERS: yith_wcact_datetime_table
								 *
								 * Filter the column heading to show the bid time.
								 *
								 * @param string $heading Heading
								 *
								 * @return string
								 */
								echo esc_html( apply_filters( 'yith_wcact_datetime_table', __( 'Bid time', 'yith-auctions-for-woocommerce' ) ) );
								?>
							</td>
						</tr>
						<?php
						$option = get_option( 'yith_wcact_settings_tab_auction_show_name' );

						foreach ( $auction_list as $object => $id ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							$user = get_user_by( 'id', $id->user_id );

							/**
							 * APPLY_FILTERS: yith_wcact_display_user_anonymous_name
							 *
							 * Filter the text shown for the bidder when it's not possible to retrieve the username.
							 *
							 * @param string  $text Text
							 * @param WP_User $user User object
							 *
							 * @return string
							 */
							$username = ( $user ) ? $user->data->user_nicename : apply_filters( 'yith_wcact_display_user_anonymous_name', 'anonymous', $user );

							/**
							 * APPLY_FILTERS: yith_wcact_display_username
							 *
							 * Filter the bidder username.
							 *
							 * @param string  $username Bidder username
							 * @param WP_User $user     User object
							 *
							 * @return string
							 */
							$username = apply_filters( 'yith_wcact_display_username', $username, $user );

							/**
							 * APPLY_FILTERS: yith_wcact_tab_auction_show_name
							 *
							 * Filter whether to show the bidder username.
							 *
							 * @param bool $show_bidder_name Whether to show the bidder username or not
							 * @param int  $user_id          User ID
							 *
							 * @return bool
							 */
							if ( 'no' === $option || apply_filters( 'yith_wcact_tab_auction_show_name', false, $id->user_id ) ) {
								$len      = strlen( $username );
								$start    = 1;
								$end      = 1;
								$username = substr( $username, 0, $start ) . str_repeat( '*', $len - ( $start + $end ) ) . substr( $username, $len - $end, $end );
							}

							if ( 0 === $object && ( 'yes' !== $product_sealed ) ) {
								$bid = $product->get_price();

								?>
								<tr>
									<td><?php echo wp_kses_post( $username ); ?></td>
									<td><?php echo wp_kses_post( wc_price( $bid, array( 'currency' => $currency ) ) ); ?></td>
									<td class="yith_auction_datetime"><?php echo wp_kses_post( $id->date ); ?></td>
								</tr>
								<?php
							} elseif ( yith_wcact_auction_compare_bids( $id->bid, $comparison_value, $auction_price ) ) {
								$bid = $id->bid;
								?>
								<tr>
									<td><?php echo wp_kses_post( $username ); ?></td>
									<td>
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
										echo wp_kses_post( apply_filters( 'yith_wcact_auction_product_price', wc_price( $bid ), $bid, $currency ) );
										?>
									</td>
									<td class="yith_auction_datetime"><?php echo wp_kses_post( $id->date ); ?></td>
								</tr>
								<?php
							}
						}
						if ( $product->is_start() && $auction_list && ( 'yes' !== $product_sealed ) ) {
							$date_format             = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
							$time_format             = get_option( 'yith_wcact_general_time_format', 'H:i:s' );
							$auction_start_formatted = gmdate( $date_format . ' ' . $time_format, $product->get_start_date() );

							?>
							<tr>
								<td><?php esc_html_e( 'Start auction', 'yith-auctions-for-woocommerce' ); ?></td>
								<td><?php echo wp_kses_post( apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_start_price(), $currency ), $product->get_start_price(), $currency ) ); ?></td>
								<td class="yith_auction_datetime_shop" data-finnish-shop="<?php echo wp_kses_post( $product->get_start_date() ); ?>"><?php echo wp_kses_post( $auction_start_formatted ); ?></td>
							</tr>
							<?php
						}
						?>

					</table>
					<?php
					if ( count( $auction_list ) === 0 ) {
						?>
						<p id="single-product-no-bid"><?php esc_html_e( 'There is no bid for this item', 'yith-auctions-for-woocommerce' ); ?></p>
						<?php
					}
				}
			}
			?>
		</div>
<?php } ?>
