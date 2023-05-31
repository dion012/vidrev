<?php
/**
 * Add to watchlist template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

?>

<div class="ywcact-add-to-watchlist ywcact-add-to-watchlist-<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $container_classes ); ?>">
	<?php if ( ! $ajax_loading ) : ?>
		<!-- ADD TO WATCHLIST -->

		<?php
		if ( 'button' === $template_part ) {
			?>
			<div class="yith-wcact-add-to-watchlist-button">
				<img class="yith-wcact-add-to-watchlist-icon" src="<?php echo esc_url( $icon ); ?>">
				<span class="yith-wcact-add-to-watchlist-button-message">
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'add_to_watchlist' => $product_id,
								'user_id'          => $user_id,
							),
							$base_url
						)
					);

					/**
					 * APPLY_FILTERS: yith_wcact_add_to_watchlist_title
					 *
					 * Filter the "Add to watchlist" title.
					 *
					 * @param string $title Title
					 *
					 * @return string
					 */
					?>
								" rel="nofollow" class="add_to_watchlist <?php echo esc_attr( $link_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-user-id="<?php echo esc_attr( $user_id ); ?>" data-title="<?php echo esc_attr( apply_filters( 'yith_wcact_add_to_watchlist_title', $add_watchlist_text ) ); ?>">
						<span><?php echo wp_kses_post( $add_watchlist_text ); ?></span>
					</a>
					<!-- COUNT TEXT -->
					<?php
					if ( $show_count ) {
						if ( $users_has_product > 0 ) {
							?>
						<span class="add-to-watchlist-number-of-users">
							<?php
							/* translators: %s number of products in watchlist 10 Users watching */
							echo esc_html( sprintf( __( '%s Users watching', 'yith-auctions-for-woocommerce' ), $users_has_product ) );
							?>
						</span>
							<?php
						} else {
							?>
							<span><?php echo esc_html__( 'Be the first to watch', 'yith-auctions-for-woocommerce' ); ?></span>
							<?php
						}
					}

					?>
				</span>
			</div>
			<?php
		} elseif ( 'browse' === $template_part ) {
			?>
			<div class="yith-wcact-add-to-watchlist-browse">
				<img class="yith-wcact-add-to-watchlist-icon" src="<?php echo esc_url( $icon ); ?>">
				<span class="yith-wcact-add-to-watchlist-browse-message">
					<a href="<?php echo esc_url( $watchlist_url ); ?>" rel="nofollow" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-title="<?php echo esc_attr( apply_filters( 'yith_wcact_add_to_watchlist_title', $add_watchlist_text ) ); ?>">
						<span><?php echo wp_kses_post( $already_in_watchlist_text ); ?></span>
					</a>
					<!-- COUNT TEXT -->
					<?php
					if ( $show_count && $users_has_product > 0 ) {
						?>
						<span class="add-to-watchlist-number-of-users">
							<?php
							/* translators: %s number of products in watchlist 10 Users watching */
							echo esc_html( sprintf( __( '%s Users watching', 'yith-auctions-for-woocommerce' ), $users_has_product ) );
							?>
						</span>
						<?php
					}
					?>
				</span>
			</div>
			<?php
		}
		?>
	<?php endif; ?>
</div>
