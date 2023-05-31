<?php
/**
 * Watchlist widget products
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\WooCommerce\Widgets
 * @version 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ywcact-watchlist-container-list" data-watchlist-product-counter="<?php echo esc_attr( count( $watchlist_products ) ); ?>">
	<?php if ( ! empty( $watchlist_products ) ) : ?>
		<ul class="watchlist_list">
			<?php
			foreach ( $watchlist_products as $watchlist_product ) :
				$product = wc_get_product( $watchlist_product->auction_id );

				if ( ! $product || 'auction' !== $product->get_type() ) {
					continue;
				}

				?>
				<li>
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
					?>
								" class="remove_from_watchlist" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"data-user-id="<?php echo esc_attr( $user_id ); ?>">&times;</a>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="image-thumb">
						<?php echo $product->get_image(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<div class="mini-watchlist-item-info ywcact-product-watchlist">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
						<small class="mini-watchlist-item-current-bid"> <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </small>
						<div class="mini-watchlist-item-end-date">
							<small><?php echo esc_html( $product->is_start() ? __( 'End time:', 'yith-auctions-for-woocommerce' ) : __( 'Start time:', 'yith-auctions-for-woocommerce' ) ); ?></small>
							<?php
							$auction_date = $product->is_start() ? $product->get_end_date() : $product->get_start_date();

							$args = array(
								'product'          => $product,
								'auction_finish'   => $product->get_end_date(),
								'date'             => strtotime( 'now' ),
								'last_minute'      => isset( $time_change_color ) ? $auction_end - $time_change_color : 0,
								'total'            => $auction_date - strtotime( 'now' ),
								'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default',
								'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',

							);
							?>
							<div class="yith-wcact-timeleft-widget-watchlist">
								<?php
								wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
								?>
							</div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="empty-watchlist">
			<?php
			if ( is_user_logged_in() ) {
				/**
				 * APPLY_FILTERS: yith_wcact_widget_items_empty_watchlist_list
				 *
				 * Filter the message shown when there are not any items in the watchlist.
				 *
				 * @param string $message Message
				 *
				 * @return string
				 */
				echo esc_html( apply_filters( 'yith_wcact_widget_items_empty_watchlist_list', __( 'Please, add your first item to the watchlist', 'yith-auctions-for-woocommerce' ) ) );
			} else {
				// translators: %s is the link to login.
				echo wp_kses_post( sprintf( __( 'Please, %s to use the watchlist feature', 'yith-auctions-for-woocommerce' ), '<a href=' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '>' . esc_html__( 'login', 'yith-auctions-for-woocommerce' ) . '</a>' ) );
			}
			?>
		</p>
	<?php endif; ?>
</div>
