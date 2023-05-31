<?php
/**
 * Bid confirmation popup template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

$price_format = sprintf( get_woocommerce_price_format(), '<span class="ywcact-bid-popup-symbol">' . get_woocommerce_currency_symbol() . '</span>', '<span class="ywcact-bid-popup-value"></span>' );

?>

<span class="yith-wcact-confirmation-bid yith-wcact-popup-button" data-ywcact-content-id=".yith-wcact-ask-confirmation-modal"></span>
<div class="yith-wcact-ask-confirmation-modal" style="display: none">
	<div class="yith-wcact-modal-title">
		<h3>
			<?php
			// translators: %s is the amount you are going to bid.
			echo wp_kses_post( sprintf( __( 'You are bidding %s for this auction.', 'yith-auctions-for-woocommerce' ), $price_format ) );
			?>
		</h3>
	</div>
	<div class="yith-wcact-modal-content">
		<p>
			<?php
			/**
			 * APPLY_FILTERS: yith_wcact_confirmation_popup_message_before
			 *
			 * Filter the message shown in the popup when bidding.
			 *
			 * @param string $message Message
			 *
			 * @return string
			 */
			echo esc_html( apply_filters( 'yith_wcact_confirmation_popup_message_before', __( 'it\'s great!', 'yith-auctions-for-woocommerce' ) ) );
			?>
		</p>
		<p>
			<?php
			/**
			 * APPLY_FILTERS: yith_wcact_confirmation_popup_message_after
			 *
			 * Filter the message shown in the popup when bidding.
			 *
			 * @param string $message Message
			 *
			 * @return string
			 */
			echo esc_html( apply_filters( 'yith_wcact_confirmation_popup_message_after', __( 'Remember, a bid is considered a binding contract. That means that if you bid on this item, you are committing to buy it if you win.', 'yith-auctions-for-woocommerce' ) ) );
			?>
		</p>
	</div>
	<div class="yith-wcact-modal-buttons">
		<button type="button" class="button alt ywcact-modal-button ywcact-modal-button-confirm-bid" ><?php esc_html_e( 'Yes, I want to bid', 'yith-auctions-for-woocommerce' ); ?></button>
	</div>
</div>
