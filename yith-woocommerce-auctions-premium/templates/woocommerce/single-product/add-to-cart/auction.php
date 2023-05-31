<?php
/**
 * Auction product add to cart
 *
 * @author      Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @version     1.0.0
 * @package YITH\Auctions\Templates\WooCommerce\Widgets\SingleProduct\AddToCart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

$product_wpml = $product; // Fix redirect url problem with WPML active.

/**
 * APPLY_FILTERS: yith_wcact_get_auction_product
 *
 * Filter the auction product.
 *
 * @param WC_Product_Auction_Premium $product Auction product
 *
 * @return WC_Product_Auction_Premium
 */
$product = apply_filters( 'yith_wcact_get_auction_product', $product );

if ( 'yes' === get_option( 'yith_wcact_show_product_stock', 'yes' ) ) {
	// Availability.
	$availability      = $product->get_availability();
	$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';

	echo wp_kses_post( apply_filters( 'woocommerce_get_stock_html', $availability_html, $product ) );
}
?>

<?php
/**
 * DO_ACTION: yith_wcact_before_add_to_cart_form
 *
 * Allows to render some content before the add to cart form in the auction product.
 *
 * @param WC_Product_Auction_Premium $product Auction product
 */
do_action( 'yith_wcact_before_add_to_cart_form', $product );
?>

<?php if ( $product->is_in_stock() ) { ?>
	<?php
	do_action( 'woocommerce_before_add_to_cart_form' );

	/**
	 * APPLY_FILTERS: yith_wcact_before_add_to_cart
	 *
	 * Filter whether to render the auction product content before the add to cart.
	 *
	 * @param bool                       $render_content Whether to render content or not
	 * @param WC_Product_Auction_Premium $product        Auction product
	 *
	 * @return bool
	 */
	if ( apply_filters( 'yith_wcact_before_add_to_cart', true, $product ) ) {
		// Auction started and it's not closed.
		if ( $product->is_start() && ! $product->is_closed() ) {
			$datetime       = $product->get_end_date();
			$auction_finish = $datetime ? $datetime : null;
			$date           = strtotime( 'now' );

			$user = wp_get_current_user();

			if ( $user instanceof WP_User && $user->ID > 0 ) {
				$is_banned   = get_user_meta( $user->ID, '_yith_wcact_user_ban', true );
				$ban_message = get_user_meta( $user->ID, '_yith_wcact_ban_message', true );

				$stripe_checked = false;

				if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) && 'yes' === get_option( 'yith_wcact_verify_payment_method', 'no' ) ) {
					$tokens = WC_Payment_Tokens::get_customer_tokens( $user->ID );
					if ( empty( $tokens ) ) {
						$stripe_checked = true;
					}
				}
			}

			/**
			 * DO_ACTION: yith_wcact_before_form_auction_product
			 *
			 * Allows to render some content before the auction form in the product page.
			 *
			 * @param WC_Product_Auction_Premium $product Auction product
			 */
			do_action( 'yith_wcact_before_form_auction_product', $product );

			?>
				<form class="cart" method="post" enctype='multipart/form-data'>
					<div class="yith-wcact-main-auction-product">

						<?php
						$bid_increment = 1;
						$total         = $auction_finish - time();

						/**
						 * DO_ACTION: yith_wcact_in_to_form_add_to_cart
						 *
						 * Allows to render some content inside the add to cart form in the auction product.
						 *
						 * @param WC_Product_Auction_Premium $product Auction product
						 */
						do_action( 'yith_wcact_in_to_form_add_to_cart', $product );

						?>
						<div id="time" class="timetito" data-finish-time="<?php echo esc_attr( $auction_finish ); ?>" data-current-time="<?php echo esc_attr( time() ); ?>" data-remaining-time=" <?php echo esc_attr( $total ); ?>" data-bid-increment="<?php echo esc_attr( $bid_increment ); ?>" data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>" data-product="<?php echo esc_attr( $product_wpml->get_id() ); ?>"data-current="<?php echo esc_attr( $product->get_price() ); ?>"data-finish="<?php echo esc_attr( $auction_finish ); ?>">
							<div class="yith-wcact-time-left-main">
								<div id="yith-wcact-auction-timeleft">
									<?php
									/**
									 * DO_ACTION: yith_wcact_auction_before_set_bid
									 *
									 * Allows to render some content before the bid field in the auction product.
									 *
									 * @param WC_Product_Auction_Premium $product Auction product
									 */
									do_action( 'yith_wcact_auction_before_set_bid', $product );
									?>
								</div>
							</div>

							<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

							<?php if ( ! isset( $is_banned ) || ! $is_banned ) { ?>
								<?php
									/**
									 * DO_ACTION: yith_wcact_before_form_bid
									 *
									 * Allows to render some content before the form to place a bid.
									 *
									 * @param WC_Product_Auction_Premium $product Auction product
									 * @param WP_User                    $user    User object
									 */
									do_action( 'yith_wcact_before_form_bid', $product, $user );
								?>

								<?php
								$buttons              = get_option( 'yith_wcact_settings_tab_auction_show_button', 'theme' );
								$input_class_quantity = ( 'theme' === $buttons ) ? array( 'input-text', 'qty', 'text', 'ywcact-wcact-bid-type-' . $buttons, 'ywcact-bid-input' ) : array( 'input-text', 'qty', 'text', 'yith-wcact-bid-quantity', 'ywcact-wcact-bid-type-' . $buttons, 'ywcact-bid-input' );

								if ( isset( $stripe_checked ) && $stripe_checked ) {
									$payment_method_url = yith_wcact_get_payment_method_url( 'add-payment-method' );
									$buttons            = 'verify-payment';
								}

								/**
								 * APPLY_FILTERS: yith_wcact_show_form_bid
								 *
								 * Filter whether to render the form to bid in the auction product page.
								 *
								 * @param bool                       $show_bid_form Whether to show the form to bid or not
								 * @param WC_Product_Auction_Premium $product       Auction product
								 *
								 * @return bool
								 */
								if ( apply_filters( 'yith_wcact_show_form_bid', true, $product ) ) {
									?>
									<div name="form_bid" id="yith-wcact-form-bid" class="ywcact-bid-form ywcact-wcact-bid-form-<?php echo esc_attr( $buttons ); ?>">
										<div class="ywcact-your-bid-header">
											<?php
											if ( ! $product->calculate_bid_up_increment() ) {
												?>
												<p><?php esc_html_e( 'Your bid:', 'yith-auctions-for-woocommerce' ); ?></p>
												<?php
											} else {
												$bidup      = esc_html__( 'Your automatic bid:', 'yith-auctions-for-woocommerce' );
												$show_modal = 'yes' === get_option( 'yith_wcact_settings_show_automatic_bidding_modal_info', 'no' ) ? true : false;
												?>
												<p>
													<span id="yith-wcact-showbidup"><?php echo wp_kses_post( $bidup ); ?></span>
														<?php
														if ( $show_modal ) {
															?>
																<span title="" class="yith-auction-help-tip yith-wcact-popup-button" data-ywcact-content-id=".yith-wcact-show-bidup-modal"> </span>
																<div class="yith-wcact-show-bidup-modal" style="display: none">
																	<div class="yith-wcact-modal-title">
																		<h4><?php esc_html_e( 'How automatic bidding works?', 'yith-auctions-for-woocommerce' ); ?></h4>
																	</div>
																	<div class="yith-wcact-modal-content">
																		<p>
																		<?php esc_html_e( "You can enter the maximum you're willing to pay for this item. Our system will bid for you, with the smallest amount possible every time, making sure you are always one step ahead of the other bidders", 'yith-auctions-for-woocommerce' ); ?>
																		</p>
																		<p>
																		<?php esc_html_e( 'Once you have reached your maximum limit, we will notify you and no longer bid on your name unless you decide to set up a new automatic bid.', 'yith-auctions-for-woocommerce' ); ?>
																		</p>
																	</div>
																</div>
																<span class="yith-wcact-show-bidup-modal" style=" display: none;"><?php echo esc_html__( 'Total used from pool of money for automatic bid up.', 'yith-auctions-for-woocommerce' ); ?> </span>
															<?php
														}
											}
											?>
												</p>
										</div>
									<?php

									if ( 'custom' === $buttons ) {
										?>
										<div class="yith-wcact-bid-section" >
										<input type="button" class="bid button_bid_subtr" value="-">
										<?php
									}
									?>
									<span class="ywcact-currency-symbol ywcact-currency-value"><?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?></span>
									<?php
									$current_bid     = $product->get_current_bid();
									$min_incr_amount = (int) ( $product->get_minimum_increment_amount() ) ? $product->get_minimum_increment_amount() : 1;
									$actual_amount   = '';
									$auction_sealed  = 'no' === $product->get_auction_sealed();
									$min_value       = ( $auction_sealed ) ? $current_bid + $min_incr_amount : 0;
									$max_value       = '';
									$instance        = YITH_Auctions()->bids;
									$bids            = $instance->get_bids_auction( $product->get_id() );

									if ( 'yes' === get_option( 'yith_wcact_show_next_available_amount', 'no' ) && $auction_sealed ) {
										if ( 'reverse' === $product->get_auction_type() ) {
											$actual_amount = $current_bid - $min_incr_amount;
											$min_value     = 0;
											$max_value     = $current_bid - $min_incr_amount;
										} else {
											if ( $product->get_price() === $product->get_start_price() && empty( $bids ) ) {
												$min_value     = $current_bid;
												$actual_amount = $current_bid;
											} else {
												$min_value     = $min_incr_amount + $current_bid;
												$actual_amount = $min_incr_amount + $current_bid;
											}

											$max_value = '';
										}
									}

									woocommerce_quantity_input(
										array(
											'input_id'    => '_actual_bid',
											/**
											 * APPLY_FILTERS: yith_wcact_quantity_input_classes
											 *
											 * Filter the array with the CSS clases for the quantity input in auction products.
											 *
											 * @param array                      $input_class_quantity CSS classes
											 * @param WC_Product_Auction_Premium $product              Auction product
											 *
											 * @return array
											 */
											'classes'     => apply_filters( 'yith_wcact_quantity_input_classes', $input_class_quantity, $product ),
											'input_name'  => 'ywcact_bid_quantity',
											'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $min_value, $product ),
											'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $max_value, $product ),
											/**
											 * APPLY_FILTERS: yith_wcact_quantity_input_step
											 *
											 * Filter the step in the quantity field in the auction products.
											 *
											 * @param int                        $min_incr_amount CSS classes
											 * @param WC_Product_Auction_Premium $product         Auction product
											 *
											 * @return int
											 */
											'step'        => apply_filters( 'yith_wcact_quantity_input_step', $min_incr_amount, $product ),
											/**
											 * APPLY_FILTERS: yith_wcact_actual_bid_value
											 *
											 * Filter the actual bid value.
											 *
											 * @param int                        $actual_amount Actual bid value
											 * @param WC_Product_Auction_Premium $product       Auction product
											 *
											 * @return int
											 */
											'input_value' => apply_filters( 'yith_wcact_actual_bid_value', $actual_amount, $product ),
										)
									);

									if ( 'custom' === $buttons ) {
										?>
										<input type="button" class="bid button_bid_add" value="+">
										</div>
										<?php
									}

									/**
									 * DO_ACTION: yith_wcact_after_form_bid
									 *
									 * Allows to render some content after the form to place a bid.
									 *
									 * @param WC_Product_Auction_Premium $product Auction product
									 */
									do_action( 'yith_wcact_after_form_bid', $product );

									/**
									 * DO_ACTION: yith_wcact_before_add_button_bid
									 *
									 * Allows to render some content before the button a bid.
									 *
									 * @param WC_Product_Auction_Premium $product Auction product
									 */
									do_action( 'yith_wcact_before_add_button_bid', $product );

									/**
									 * APPLY_FILTERS: yith_wcact_auction_button_bid_class
									 *
									 * Filter the classes of the button to place a bid.
									 *
									 * @param string                     $classes Button classes
									 * @param WC_Product_Auction_Premium $product Auction product
									 * @param WP_User                    $user    User object
									 *
									 * @return string
									 */
									$bid_class_button = apply_filters( 'yith_wcact_auction_button_bid_class', 'auction_bid button alt', $product, $user );

									/**
									 * APPLY_FILTERS: yith_wcact_bid_button_label
									 *
									 * Filter the label of the button to place a bid.
									 *
									 * @param string $label Button label
									 *
									 * @return string
									 */
									?>
									<button type="button" class="<?php echo esc_attr( $bid_class_button ); ?>"><?php echo esc_html( apply_filters( 'yith_wcact_bid_button_label', __( 'Bid', 'yith-auctions-for-woocommerce' ) ) ); ?></button>
								</div>
									<?php
									if ( isset( $payment_method_url ) ) {
										?>
										<div class="ywcact-add-yith-wcstripe-message">
											<span class="yith-wcact-valid-credit-card"> <?php echo esc_html__( 'You need to add a valid credit card in order to bid.', 'yith-auctions-for-woocommerce' ); ?>  </span>
											<?php // translators: %1$s is the URL to add a new credit card. %2$s is the link text. ?>
											<span><?php echo wp_kses_post( sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $payment_method_url, __( 'Add a credit card >', 'yith-auctions-for-woocommerce' ) ) ); ?></span>
										</div>
										<?php
									}

									/**
									 * DO_ACTION: yith_wcact_after_add_button_bid
									 *
									 * Allows to render some content after the button a bid.
									 *
									 * @param WC_Product_Auction_Premium $product_wpml Auction product
									 */
									do_action( 'yith_wcact_after_add_button_bid', $product_wpml );
								}
							} else {
								if ( $is_banned ) {
									?>
									<div class="yith-wcact-ban-message-section">
										<p class="yith-wcact-ban-message"> <?php echo wp_kses_post( $ban_message ); ?> </p>
									</div>
									<?php
								}
							}
							?>
						</div>

						<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
						</div>
					</form>

					<?php
					/**
					 * DO_ACTION: yith_wcact_after_add_to_cart_form
					 *
					 * Allows to render some content after the add to cart form.
					 *
					 * @param WC_Product_Auction_Premium $product Auction product
					 */
					do_action( 'yith_wcact_after_add_to_cart_form', $product );
		} elseif ( ! $product->is_closed() || ! $product->is_start() ) { // Auction scheduled ( no started ).
			$datetime    = $product->get_start_date();
			$for_auction = $datetime ? $datetime : null;
			$date        = strtotime( 'now' );

			$args = array(
				'product'          => $product,
				'product_id'       => $product->get_id(),
				'auction_finish'   => $for_auction,
				'date'             => $date,
				'total'            => $for_auction - $date,
				'last_minute'      => 0,
				'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default yith-wcact-timeleft-product-page',
				'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',
			);

			/**
			 * APPLY_FILTERS: yith_wcact_auction_not_available_message
			 *
			 * Filter the message shown when the auction has not started yet.
			 *
			 * @param string                     $label   Button label
			 * @param WC_Product_Auction_Premium $product Auction product
			 *
			 * @return string
			 */
			?>
			<h3><?php echo esc_html( apply_filters( 'yith_wcact_auction_not_available_message', __( 'This auction has not been started yet', 'yith-auctions-for-woocommerce' ), $product ) ); ?></h3>
			<div id="time">
				<div class="yith-wcact-time-left-main">
					<div id="yith-wcact-auction-timeleft">
						<?php
						/**
						 * DO_ACTION: yith_wcact_auction_before_set_bid
						 *
						 * Allows to render some content before the bid field in the auction product.
						 *
						 * @param WC_Product_Auction_Premium $product        Auction product
						 * @param string                     $auction_status Auction status
						 */
						do_action( 'yith_wcact_auction_before_set_bid', $product, 'not_started' );
						?>
					</div>
				</div>
			</div>
			<?php
			/**
			 * DO_ACTION: yith_wcact_after_no_start_auction
			 *
			 * Allows to render some content for auctions that have not started yet.
			 *
			 * @param WC_Product_Auction_Premium $product_wpml Auction product
			 */
			do_action( 'yith_wcact_after_no_start_auction', $product_wpml );
		} else {
			// Auction finished.
			/**
			 * DO_ACTION: yith_wcact_auction_end
			 *
			 * Allows to render some content after the auction has ended.
			 *
			 * @param WC_Product_Auction_Premium $product Auction product
			 */
			do_action( 'yith_wcact_auction_end', $product );
		}
	}
	do_action( 'woocommerce_after_add_to_cart_form' );
} else {
	do_action( 'yith_wcact_auction_end', $product );
}
?>
