<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Reserve price and overtime template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

$instance                 = YITH_Auctions()->bids;
$max_bid                  = $instance->get_max_bid( $product->get_id() );
$userid                   = get_current_user_id();
$minimun_increment_amount = (int) $product->get_minimum_increment_amount();


if ( $minimun_increment_amount && $max_bid && $userid !== $max_bid->user_id ) {
	if ( 'reverse' === $product->get_auction_type() ) {
		/**
		 * APPLY_FILTERS: yith_wcact_min_bid_manual
		 *
		 * Filter the minimum bid amount.
		 *
		 * @param int                        $manual_min_bid Manual min bid
		 * @param WC_Product_Auction_Premium $product        Auction product
		 *
		 * @return int
		 */
		$manual_bid_increment = apply_filters( 'yith_wcact_min_bid_manual', (int) $product->get_current_bid() - $minimun_increment_amount, $product );
		/* translators: %s: bid amount. Example Enter 5$ or less */
		$manual_bid_increment_text = sprintf( esc_html__( 'Enter "%s" or less.', 'yith-auctions-for-woocommerce' ), wc_price( $manual_bid_increment ) );

	} else {
		/**
		 * APPLY_FILTERS: yith_wcact_max_bid_manual
		 *
		 * Filter the maximum bid amount.
		 *
		 * @param int                        $manual_max_bid Manual max bid
		 * @param WC_Product_Auction_Premium $product        Auction product
		 *
		 * @return int
		 */
		$manual_bid_increment = apply_filters( 'yith_wcact_max_bid_manual', (int) $product->get_current_bid() + $minimun_increment_amount, $product );
		/* translators: %s: bid amount. Example Enter 5$ or more */
		$manual_bid_increment_text = sprintf( esc_html__( 'Enter "%s" or more.', 'yith-auctions-for-woocommerce' ), wc_price( $manual_bid_increment ) );
	}

	if ( $manual_bid_increment > 0 && 'no' === get_post_meta( $product->get_id(), '_yith_wcact_auction_sealed', true ) ) {
		echo '<div id="yith_wcact_manual_bid_increment" class="yith-wcact-manual-bid-increment yith_wcact_font_size">';
		echo '<p>';
		/**
		 * APPLY_FILTERS: yith_wcact_manual_bid_increment_text
		 *
		 * Filter the text for the manual bid increment.
		 *
		 * @param string                     $text    Text
		 * @param WC_Product_Auction_Premium $product Auction product
		 *
		 * @return string
		 */
		echo wp_kses_post( apply_filters( 'yith_wcact_manual_bid_increment_text', $manual_bid_increment_text, $product ) );
		echo '</p>';
		echo '</div>';
	}
}

echo '<div id="yith_wcact_reserve_and_overtime">';

if ( 'yes' === get_option( 'yith_wcact_show_reserve_price_reached', 'yes' ) && 'reverse' !== $product->get_auction_type() ) {

	echo '<div id="yith_reserve_price" class="yith_wcact_font_size">';

	if ( $product->has_reserve_price() ) {
		if ( $max_bid && $max_bid->bid >= $product->get_reserve_price() ) {
			/**
			 * APPLY_FILTERS: yith_wcact_product_exceeded_reserve_price_message
			 *
			 * Filter the message shown when the product has exceeded the reserve price.
			 *
			 * @param string $message Message
			 *
			 * @return string
			 */
			echo '<p class="yith_wcact_exceeded_reserve_price">' . esc_html( apply_filters( 'yith_wcact_product_exceeded_reserve_price_message', __( 'The product has exceeded the reserve price. ', 'yith-auctions-for-woocommerce' ) ) ) . '</p>';
		} else {
			/**
			 * APPLY_FILTERS: yith_wcact_product_has_reserve_price_message
			 *
			 * Filter the message shown when the product has a reserve price.
			 *
			 * @param string                     $message Message
			 * @param WC_Product_Auction_Premium $product Auction product
			 * @param int                        $userid  User ID
			 *
			 * @return string
			 */
			echo '<p class="yith_wcact_has_reserve_price">' . esc_html( apply_filters( 'yith_wcact_product_has_reserve_price_message', __( 'The product has a reserve price. ', 'yith-auctions-for-woocommerce' ), $product, $userid ) ) . '</p>';
		}
	} else {
		/**
		 * APPLY_FILTERS: yith_wcact_product_does_not_have_a_reserve_price_message
		 *
		 * Filter the message shown when the product doesn't have a reserve price.
		 *
		 * @param string $message Message
		 *
		 * @return string
		 */
		echo '<p class="yith_wcact_does_not_have_reserve_price">' . esc_html( apply_filters( 'yith_wcact_product_does_not_have_a_reserve_price_message', __( 'This product does not have a reserve price. ', 'yith-auctions-for-woocommerce' ) ) ) . '</p>';
	}
	echo '</div>';
}

if ( 'yes' === get_option( 'yith_wcact_show_in_overtime', 'yes' ) ) {
	echo '<div id="yith-wcact-overtime">';
	if ( $product->is_in_overtime() ) {
		?>
		<span id="yith-wcact-is-overtime"> <?php esc_html_e( 'Currently in overtime', 'yith-auctions-for-woocommerce' ); ?> </span>
		<?php

	}
	echo '</div>';
}
echo '</div>';
