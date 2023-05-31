<?php
/**
 * Admin auction status template
 *
 * @package YITH\Auctions\Templates\Admin
 */

$product = wc_get_product( $post_id );

$datetime   = $product->get_end_date();
$to_auction = $datetime ? absint( $datetime ) : '';
$to_auction = $to_auction ? get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $to_auction ) ) : '';
$instance   = YITH_Auctions()->bids;
$max_bidder = $instance->get_max_bid( $product->get_id() );

if ( $max_bidder ) {
	$user     = get_user_by( 'id', $max_bidder->user_id );
	$username = ( $user ) ? $user->data->user_nicename : 'anonymous';
}

?>

<div class="yith-wcact-admin-auction-status">
	<div>
		<?php esc_html_e( 'Status:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo wp_kses_post( $product->get_auction_status() ); ?></span>
	</div>
	<div>
		<?php esc_html_e( 'End time:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo wp_kses_post( $to_auction ); ?></span>
	</div>
	<?php if ( ! $product->is_closed() ) { ?>
		<?php if ( $max_bidder ) { ?>
			<div>
				<?php esc_html_e( 'Max bidder:', 'yith-auctions-for-woocommerce' ); ?> <span><a href="user-edit.php?user_id=<?php echo absint( $max_bidder->user_id ); ?>"><?php echo wp_kses_post( $username ); ?></a></span>
			</div>
			<?php
		} else {
			esc_html_e( 'Max bidder:' )
			?>
			<span id=""> <?php esc_html_e( 'There is no bid for this item', 'yith-auctions-for-woocommerce' ); ?> </span>
			<?php
		}
	} else {
		$winner_email           = $product->get_send_winner_email();
		$check_email_is_send    = yit_get_prop( $product, 'yith_wcact_winner_email_is_send', true );
		$user_email_information = yit_get_prop( $product, 'yith_wcact_winner_email_send_custoner', true );

		if ( $winner_email ) {
			/**
			 * APPLY_FILTERS: yith_wcact_check_email_is_send
			 *
			 * Filter whether to show if the winner email has been sent.
			 *
			 * @param bool       $check_email_is_send Whether to show if the winner email has been sent or not
			 * @param WC_Product $product             Product object
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_check_email_is_send', $check_email_is_send, $product ) ) {
				esc_html_e( 'Email is send to:', 'yith-auctions-for-woocommerce' );

				?>
				<span><a href="user-edit.php?user_id=<?php echo absint( $user_email_information->data->ID ); ?>"><?php echo wp_kses_post( $user_email_information->user_login ); ?></a>( <?php echo wp_kses_post( $user_email_information->data->user_email ); ?> )</span>

				<?php
					$max_bidder = yit_get_prop( $product, '_yith_wcact_winner_email_max_bidder', true );

				if ( $max_bidder ) {
					?>
					<div>
						<span><?php esc_html_e( 'Winner\'s bid:', 'yith-auctions-for-woocommerce' ); ?></span>
						<span><?php echo wp_kses_post( wc_price( $max_bidder->bid ) ); ?></span>
					</div>
					<?php
				}

				echo '<p class="form-field"><input type="button" class="button" id="yith-wcact-send-winner-email" value="' . esc_html__( 'Send Winner Email', 'yith-auctions-for-woocommerce' ) . '"></p>';
			} elseif ( yit_get_prop( $product, 'yith_wcact_winner_email_is_not_send', true ) ) {
				$why_is_not_send = yit_get_prop( $product, 'yith_wcact_winner_email_is_not_send', true );
				?>
				<?php esc_html_e( 'Email is send to:', 'yith-auctions-for-woocommerce' ); ?>
				<span><?php esc_html_e( 'Error send the email', 'yith-auctions-for-woocommerce' ); ?></span>
				<p class="form-field"><?php echo wp_kses_post( $why_is_not_send ); ?> </p>
				<?php echo '<p class="form-field"><input type="button" class="button" id="yith-wcact-send-winner-email" value="' . esc_html__( 'Send Winner Email', 'yith-auctions-for-woocommerce' ) . '"></p>'; ?>
				<?php
			}
		} else {
			if ( $max_bidder ) {
				echo esc_html( __( 'An error occurred while sending Winner email', 'yith-auctions-for-woocommerce' ) );
				echo '<p class="form-field"><input type="button" class="button" id="yith-wcact-send-winner-email" value="' . esc_html__( 'Send Winner Email', 'yith-auctions-for-woocommerce' ) . '"></p>';
			} else {
				echo esc_html( __( 'Auction ended without bids', 'yith-auctions-for-woocommerce' ) );
			}
		} //Todo create an else in order to allow resend winner email if it's fail
		?>
	<?php } ?>
</div>
