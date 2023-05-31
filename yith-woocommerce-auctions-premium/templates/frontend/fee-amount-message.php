<?php
/**
 * Fee amount message template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

?>

<div class="ywcact-fee-amount-message ">
	<div class="ywcact-fee-amount-container <?php echo ( $user ) ? 'yith-wcact-popup-button' : ''; ?>" data-ywcact-content-id=".yith-wcact-fee-modal">
		<span class="ywcact-fee-amount-title" style="font-weight: bold;"><?php echo esc_html__( 'This is a bidding fee auction.', 'yith-auctions-for-woocommerce' ); ?></span></br>
		<span class="ywcact-fee-amount-content">
			<?php
			/**
			 * APPLY_FILTERS: yith_wcact_fee_amount_message
			 *
			 * Filter the message shown in the product page to notify that the payment of a fee is mandatory in order to be able to bid.
			 *
			 * @param string $message Message
			 *
			 * @return string
			 */
			// translators: %s is the fee amount that needs to be paid to be able to bid.
			echo wp_kses_post( apply_filters( 'yith_wcact_fee_amount_message', sprintf( _x( 'All participants must pay a non-refundable fee of %s to place bids', 'All participants must pay a non-refundable fee of $5 to place bids', 'yith-auctions-for-woocommerce' ), wc_price( $fee_amount ) ), $fee_amount ) );
			?>
		</span>
	</div>
</div>

<?php
if ( $user ) {
	?>
	<div class="yith-wcact-fee-modal" style="display: none">
		<div class="yith-wcact-modal-title">
			<h3><?php echo esc_html__( 'Pay the fee and start now to bid!', 'yith-auctions-for-woocommerce' ); ?></h3>
		</div>
		<div class="yith-wcact-modal-content">
			<span><?php esc_html_e( 'This is a bidding fee auction.', 'yith-auctions-for-woocommerce' ); ?></span></br>
			<p>
			<?php
			/**
			 * APPLY_FILTERS: yith_wcact_fee_amount_message_modal
			 *
			 * Filter the message shown in the popup to notify that the payment of a fee is mandatory in order to be able to bid.
			 *
			 * @param string $message Message
			 *
			 * @return string
			 */
			// translators: %s is the fee amount that needs to be paid to be able to bid.
			echo wp_kses_post( apply_filters( 'yith_wcact_fee_amount_message_modal', sprintf( __( "To participate you must pay a non-refundable fee of %s, then you'll immediately be able to bid.", 'yith-auctions-for-woocommerce' ), '<span class="ywcact-price">' . wc_price( $fee_amount ) . '</span>' ), $fee_amount ) );
			?>
			</p>
		</div>
		<div class="yith-wcact-modal-buttons">
			<input type="hidden" name="yith-wcact-pay-fee-auction-value" value="<?php echo esc_attr( $fee_amount ); ?>"/>
			<button type="button" class="button alt ywcact-modal-button ywcact-modal-button-pay-fee"><?php esc_html_e( 'Pay now', 'yith-auctions-for-woocommerce' ); ?></button>
		</div>
	</div>
	<?php
}
?>
