<?php
/**
 * Auction follower list
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\Shortcodes
 */

?>

<div class="ywcact-follower-auction-list-main">
	<?php
	if ( ! empty( $auction_list ) ) {
		?>
			<div class="ywcact-section">
				<p><?php echo esc_html__( 'We are sorry to find you are no long interested in our auctions.', 'yith-auctions-for-woocommerce' ); ?></p>
				<p>
					<?php
					// translators: %s is the email address.
					echo sprintf( esc_html__( 'Unsubscribe your email address %s from the auctions you are following:' ), '<span class="ywcact-unsubscribe-email">' . esc_html( $email ) . '</span>' );
					?>
				</p>
			</div>
			<div class="ywcact-follower-auction-list-section">
			<input type="hidden" class="ywcact-unsubscribe-user-email" value="<?php echo esc_attr( $email ); ?>" >
			<?php
			foreach ( $auction_list as $key => $auction_id ) :
				$auction = wc_get_product( $auction_id );

				if ( $auction && 'auction' === $auction->get_type() ) {
					$checkbox_id = sanitize_key( 'yith-wcact-auction-follower-' . $key );

					/**
					 * APPLY_FILTERS: yith_wcact_unsubscribe_auction_title
					 *
					 * Filter the title to unsubscribe from the auction.
					 *
					 * @param string $title Auction title
					 * @param WC_Product_Auction_Premium $auction Auction product
					 */
					$title = apply_filters( 'yith_wcact_unsubscribe_auction_title', $auction->get_title(), $auction ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$name  = 'yith_wcact_auction_follower_products';
					?>
					<div class="ywcact-checkbox-array__row">
						<input type="checkbox" id="<?php echo esc_attr( $checkbox_id ); ?>" name="<?php echo esc_attr( $name ); ?>[]" class="ywcact-unsubscribe-auction-checkbox" value="<?php echo esc_attr( $auction_id ); ?>"/>
						<label for="<?php echo esc_attr( $checkbox_id ); ?>"><?php echo wp_kses_post( $title ); ?></label>
					</div>
					<?php
				}
			endforeach;
			?>
			</div>

			<div class="ywcact-section">
				<p> <?php echo esc_html__( 'And don’t worry, you will not receive any emails in future.', 'yith-auctions-for-woocommerce' ); ?> </p>
			</div>
			<div class="ywcact-unsubscribe-auction-button-section">
				<input type="button" class="button ywcact-unsubscribe-auction-button" value=<?php echo esc_attr( $button_label ); ?>>
			</div>
		<?php
	}
	?>
</div>
