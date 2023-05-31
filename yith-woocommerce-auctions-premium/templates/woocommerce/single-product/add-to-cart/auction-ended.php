<?php
/**
 * Auction ended template
 *
 * @var string $winner_message The winner message.
 * @var string $img  Image badge.
 * @var object $current_user Current user.
 * @var WC_Product $product Auction product.
 * @var int/bool $order_id Order id where auction was bought.
 * @var bool $stripe_checked Check if stripe and option is enabled to verify the card.
 * @var string $payment_method_url Url to redirect to saved cards.
 * @var bool $show_reason Show why the auction was closed.
 * @var object $max_bid Winner user.
 * @author      Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @version     3.0.0
 * @package YITH\Auctions\Templates\WooCommerce\Widgets\SingleProduct\AddToCart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( isset( $winner_message ) ) { // Template for winner.
	?>
	<div class="ywcact-congratulations-winner-auction-section">
		<div class="ywcact-congratulation-message-container">
			<div class="ywcact-congratulation-message-header">
				<span class="ywcact-congratulation-title">
					<img src="<?php echo esc_url_raw( $img ); ?>">
					<span class="ywcact-congratulation-title-message">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcact_congratulation_message_title
						 *
						 * Filter the message title shown the auction winner.
						 *
						 * @param string $title Title
						 *
						 * @return string
						 */
						/* translators: %s: Winner name*/
						echo wp_kses_post( apply_filters( 'yith_wcact_congratulation_message_title', sprintf( __( 'Congratulations %s!', 'yith-auctions-for-woocommerce' ), $current_user->data->display_name ), $product, $current_user->data->display_name ) );
						?>
					</span>
				</span>
			</div>
			<div class="ywcact-congratulation-message-content">
				<?php echo apply_filters( 'the_content', $winner_message ); // phpcs:ignore ?>
			</div>
		</div>
		<?php if ( ! $stripe_checked ) { ?>
			<form class="cart" method="get" enctype='multipart/form-data'>
				<input type="hidden" name="yith-wcact-pay-won-auction" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
				<?php

				/**
				 * APPLY_FILTERS: yith_wcact_show_buttons_auction_end
				 *
				 * Filter whether to show the buttons to pay the auction when it is ended.
				 *
				 * @param bool $show_buttons Whether to show buttons or not
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcact_show_buttons_auction_end', false ) || ( ! $product->get_auction_paid_order() && ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_show_button_pay_now', 'no' ) ) ) ) {
					if ( $order_id ) {
						$button_value = __( 'Pay order', 'yith-auctions-for-woocommerce' );
					} elseif ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_show_add_to_cart_in_auction_product', 'no' ) ) {
						$button_value = __( 'Add to cart', 'yith-auctions-for-woocommerce' );

					} else {
						$button_value = __( 'Pay now', 'yith-auctions-for-woocommerce' );
					}

					?>
						<button type="submit" class="auction_add_to_cart_button ywcact-auction-buy-now-button button alt" id="yith-wcact-auction-won-auction">
							<?php echo esc_html( $button_value ); ?>
						</button>
					<?php
				}
				?>
			</form>
			<?php
		} else {
			?>
			<div class="ywcact-add-yith-wcstripe-message">
				<p><?php echo sprintf( __( 'You need to add at least one credit card on <a href="%1$s" target="_blank">%2$2s</a> section in order to pay for this product.', 'yith-auctions-for-woocommerce' ), $payment_method_url, 'Payment method' ); // phpcs:ignore ?></p>
			</div>
			<?php
		}
		?>
	</div>
	<?php
} elseif ( $show_reason ) { // Template no logged users and no winners.
	if ( $product->get_is_closed_by_buy_now() ) {
		?>
		<div id="yith_auction_end_product_page">
			<h2>
				<?php
				/**
				 * APPLY_FILTERS: yith_wcact_closed_buy_now_message
				 *
				 * Filter the message shown when the auction is closed by 'Buy now'.
				 *
				 * @param string $message Message
				 *
				 * @return string
				 */
				echo esc_html( apply_filters( 'yith_wcact_closed_buy_now_message', __( 'This auction has ended to \'Buy Now\'', 'yith-auctions-for-woocommerce' ) ) );
				?>
			</h2>
		</div>
		<?php
	} else {
		?>
		<div id="yith_auction_end_product_page">
			<h2>
				<?php esc_html_e( 'This auction has ended', 'yith-auctions-for-woocommerce' ); ?>
			</h2>
		</div>
		<?php
	}

	if ( ! $no_reserve_price ) {
		/**
		 * DO_ACTION: yith_wcact_auction_auction_reserve_price
		 *
		 * Allows to render some content when the auction product has no reserve price.
		 *
		 * @param WC_Product_Auction_Premium $product Auction product
		 * @param int                        $max_bid Max bid
		 */
		do_action( 'yith_wcact_auction_auction_reserve_price', $product, $max_bid );
	}
}

/**
 * APPLY_FILTERS: yith_wcact_display_other_auctions
 *
 * Filter whether to show other auctions when the auction is closed.
 *
 * @param bool $show_other_auctions Message
 * @param WC_Product_Auction_Premium $product Auction product
 *
 * @return bool
 */
if ( 'yes' === apply_filters( 'yith_wcact_display_other_auctions', get_option( 'yith_wcact_ended_suggest_other_auction', 'yes' ), $product ) ) {
	echo do_shortcode( '[yith_wcact_other_auctions]' );
}

/**
 * DO_ACTION: yith_wcact_after_auction_end
 *
 * Allows to render some content when the auction has ended.
 *
 * @param WC_Product_Auction_Premium $product Auction product
 * @param int                        $max_bid Max bid
 */
do_action( 'yith_wcact_after_auction_end', $product, $max_bid );
