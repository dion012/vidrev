<?php
/**
 * Max bidder template
 *
 * @package YITH\Auctions\Templates\Frontend
 */

/**
$showoverbid = $product->get_overtime_checkbox();
$over = "";
if ( 'yes' == $showoverbid ) {
	$over = ( $overtime = $product->get_overtime()) ?  sprintf(esc_html_x( 'Overtime: %s min','Overtime: 3 min', 'yith-auctions-for-woocommerce' ), $overtime) : esc_html__('Overtime: No overtime','yith-auctions-for-woocommerce')  ;
}
 */
$bid = $product->calculate_bid_up_increment();
?>
<div class="yith-wcact-max-bidder" id="yith-wcact-max-bidder">
		<div class="yith-wcact-overbidmode yith-wcact-bidupmode">
			<?php
			if ( $bid && $bid > 0 ) {
				$showbidup = ( 'yes' === $product->get_upbid_checkbox() || 'yes' === get_option( 'yith_wcact_settings_show_bid_increment_in_the_page', 'no' ) ) ? 'yes' : 'no';

				if ( $showbidup ) {
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
					$bidup = esc_html__( 'Bid up: ', 'yith-auctions-for-woocommerce' ) . apply_filters( 'yith_wcact_auction_product_price', wc_price( $bid ), $bid, $currency );

					?>
					<span id="yith-wcact-showbidup"><?php echo wp_kses_post( $bidup ); ?></span>
					<?php
				}
			}

			/**
			 * DO_ACTION: yith_wcact_after_bid_up_section
			 *
			 * Allow to render some content after the bid up section in the product page.
			 *
			 * @param string                     $bid     Bid
			 * @param WC_Product_Auction_Premium $product Auction product
			 */
			do_action( 'yith_wcact_after_bid_up_section', $bid, $product );
			?>
		</div>
	<?php
	// //////////////////////////////////
	$instance             = YITH_Auctions()->bids;
	$auction_product_type = $product->get_auction_type();

	$max_bid = $auction_product_type && 'reverse' === $auction_product_type ? $instance->get_min_bid( $product->get_id() ) : $instance->get_max_bid( $product->get_id() );
	$userid  = get_current_user_id();

	if ( $max_bid && (int) $userid === (int) $max_bid->user_id && 'no' === $product->get_auction_sealed() ) {
		$show_tooltip = '';

		$show_winner_modal = ( 'yes' === get_option( 'yith_wcact_settings_show_higher_bidder_modal', 'no' ) ) ? true : false;
		$message           = implode(
			'. ',
			array(
				esc_html__( 'Refresh the page regularly to see if you are still the highest bidder', 'yith-auctions-for-woocommerce' ),
				esc_html__( 'If your bid is higher or equivalent to the reserve price, your bid will match the reserve price with the remaining saved and used automatically to outbid a competitors bid.', 'yith-auctions-for-woocommerce' ),
			)
		);
		if ( $show_winner_modal ) {
			$show_tooltip = '<span class="yith-auction-help-tip" ></span>';

			?>
			<div class="yith-wcact-show-winner-modal" style="display: none">
				<div class="yith-wcact-modal-title">
					<h4><?php esc_html_e( 'Congratulations, you are the highest bidder.', 'yith-auctions-for-woocommerce' ); ?></h4>
				</div>
				<div class="yith-wcact-modal-content">
					<p><?php esc_html_e( 'Refresh the page regularly to see if you are still the highest bidder.', 'yith-auctions-for-woocommerce' ); ?> </p>
					<p>
						<?php esc_html_e( 'If your bid is higher or equivalent to the reserve price, your bid will match the reserve price with the remaining saved and used automatically to outbid a competitors bid.', 'yith-auctions-for-woocommerce' ); ?>
					</p>
				</div>
			</div>
			<?php
		} else {
			$show_tooltip = '<span title="' . $message . '" class="yith-auction-help-tip yith-auction-help-tip-tooltip "></span>';
		}
		?>

		<div id="winner_maximun_bid" class="ywcact-winner-max-bid <?php echo $show_winner_modal ? 'yith-wcact-popup-button' : ''; ?>" data-ywcact-content-id=".yith-wcact-show-winner-modal">
			<div>
				<span id="max_winner" class="ywcact-winner-max-bid-message">
					<?php
					/**
					 * APPLY_FILTERS: yith_wcact_current_max_bid_message
					 *
					 * Filter the message displayed to notify the user that is the highest bidder.
					 *
					 * @param string $message Message
					 *
					 * @return string
					 */
					echo esc_html( apply_filters( 'yith_wcact_current_max_bid_message', __( 'You are currently the highest bidder for this auction!', 'yith-auctions-for-woocommerce' ) ) );
					?>
				</span>

				<span>
				<?php
					/**
					 * APPLY_FILTERS: yith_wcact_current_max_bid
					 *
					 * Filter the message displayed to show the maximum bid from the user.
					 *
					 * @param string $message Message
					 *
					 * @return string
					 */
					// translators: %s id the max bid from the user.
					echo wp_kses_post( sprintf( apply_filters( 'yith_wcact_current_max_bid', _x( 'Your maximum bid: %s', 'My maximum bid: $ 50.00', 'yith-auctions-for-woocommerce' ), $show_tooltip ), apply_filters( 'yith_wcact_auction_product_price', wc_price( $max_bid->bid ), $max_bid->bid, $currency ) ) );
					echo wp_kses_post( $show_tooltip );
				?>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * DO_ACTION: yith_wcact_after_max_bidder_section
	 *
	 * Allow to render some content after the max bidder section in the product page.
	 *
	 * @param WC_Product_Auction_Premium $product  Auction product
	 * @param object                     $max_bid  Max bid
	 * @param int                        $userid   User ID
	 * @param int                        $bid      Bid
	 * @param YITH_WCACT_Bids            $instance YITH_WCACT_Bids object
	 */
	do_action( 'yith_wcact_after_max_bidder_section', $product, $max_bid, $userid, $bid, $instance );
	?>
</div>
