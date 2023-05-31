<?php
/**
 *  Functions for auction product specific things.
 *
 * @author  YITH
 * @package YITH Auctions for WooCommerce
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! function_exists( 'ywcact_get_fee_amount' ) ) {
	/**
	 * Get fee amount of auction.
	 *
	 * @param  WC_Product $product Product.
	 * @return float
	 * @since  2.0.0
	 */
	function ywcact_get_fee_amount( $product ) {
		$fee_amount = false;

		if ( $product && 'auction' === $product->get_type() ) {
			if ( 'yes' === $product->get_fee_onoff() ) {
				if ( 'yes' === $product->get_fee_ask_onoff() && (float) $product->get_fee_amount() > 0 ) {
					$fee_amount = $product->get_fee_amount();
				}
			} else {
				if ( 'yes' === get_option( 'yith_wcact_settings_ask_fee_before_to_bid', 'no' ) && get_option( 'yith_wcact_settings_fee_amount', 0 ) > 0 ) {
					$fee_amount = get_option( 'yith_wcact_settings_fee_amount', 0 );
				}
			}
		}

		/**
		 * APPLY_FILTERS: ywcact_get_fee_amount_value
		 *
		 * Filter the fee amount for the auction.
		 *
		 * @param string     $fee_amount Fee amount
		 * @param WC_Product $product    Product object
		 *
		 * @return string
		 */
		return apply_filters( 'ywcact_get_fee_amount_value', $fee_amount, $product );
	}
}

if ( ! function_exists( 'ywcact_reschedule_auction_product' ) ) {
	/**
	 * Reschedule auction product.
	 *
	 * @param  WC_Product $product Product.
	 * @return bool
	 * @since  2.0.0
	 */
	function ywcact_reschedule_auction_product( $product ) {
		$status = false;

		$product = $product && is_int( $product ) ? wc_get_product( $product ) : $product;

		if ( $product && 'auction' === $product->get_type() ) {
			$product->set_stock_status( 'instock' );

			$bids = YITH_Auctions()->bids;
			$bids->reshedule_auction( $product->get_id() );

			$product->set_is_closed_by_buy_now( false );
			$product->set_is_in_overtime( false );
			$product->set_auction_paid_order( false );

			$product->set_send_winner_email( false );
			$product->set_send_admin_winner_email( false );

			// Delete followers 3.0.
			$bids->delete_followers( $product->get_id() );
			// Cancel order if exists and set meta key as 0.
			$order_id = $product->get_order_id();

			if ( $order_id && $order_id > 0 ) {
				$product->set_order_id( 0 );
				$order = wc_get_order( $order_id );

				if ( $order && $order instanceof WC_Order ) {
					$order->update_status( 'cancelled', __( 'Order cancelled - Auction rescheduled.', 'yith-auctions-for-woocommerce' ) );
					$order->save();
				}

				/**
				 * DO_ACTION: yith_wcact_after_reschedule_product_order
				 *
				 * Allow to fire some action when rescheduling the auction product.
				 *
				 * @param WC_Product $product  Product object
				 * @param int        $order_id Order ID
				 */
				do_action( 'yith_wcact_after_reschedule_product_order', $product, $order_id );
			}

			yit_delete_prop( $product, 'yith_wcact_send_admin_not_reached_reserve_price', false );
			yit_delete_prop( $product, 'yith_wcact_send_admin_without_any_bids', false );

			// delete winner email user prop (since v2.0.1).
			yit_delete_prop( $product, 'yith_wcact_winner_email_is_send', false );
			yit_delete_prop( $product, 'yith_wcact_winner_email_send_custoner', false );
			yit_delete_prop( $product, '_yith_wcact_winner_email_max_bidder', false );
			yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );
			yit_delete_prop( $product, 'current_bid', false );

			$product->save();

			$status = true;

			ywcact_logs( 'The Product ' . $product->get_id() . ' was rescheduled' );
		}

		return $status;
	}
}

if ( ! function_exists( 'ywcact_generate_content_winner' ) ) {
	/**
	 * Generate content for winner message.
	 *
	 * @param WC_Product $product Product.
	 * @param WP_User    $user User.
	 * @return bool
	 * @since  2.0.0
	 */
	function ywcact_generate_content_winner( $product, $user ) {
		$content_winner = '';

		if ( $product ) {
			$congratulations_message_settings = get_option( 'yith_wcact_auction_winner_custom_message', '' );

			$congratulations_message_settings = ! empty( $congratulations_message_settings ) ? $congratulations_message_settings : implode(
				'',
				array(
					'<p>' . esc_html__( 'You won this auction with the final price of %price%', 'yith-auctions-for-woocommerce' ) . '</p>',
					'<p>' . esc_html__( 'Be sure to pay for the product within 3 days to avoid losing it!', 'yith-auctions-for-woocommerce' ) . '</p>',
				)
			);

			$search  = array(
				'%price%',
				'%username%',
			);
			$replace = array(
				wc_price( $product->get_price() ),
				$user->user_login,
			);

			/**
			 * APPLY_FILTERS: yith_wcact_auction_winner_content_message
			 *
			 * Filter the message displayed to the auction winner.
			 *
			 * @param string     $fee_amount                       Fee amount
			 * @param array      $search                           Array with placeholders to replace
			 * @param array      $replace                          Array with data to replace placeholders
			 * @param string     $congratulations_message_settings Congratulations message
			 * @param WC_Product $product                          Product object
			 * @param WP_User    $user                             User object
			 *
			 * @return string
			 */
			$content_winner = apply_filters( 'yith_wcact_auction_winner_content_message', str_replace( $search, $replace, $congratulations_message_settings ), $search, $replace, $congratulations_message_settings, $product, $user );
		}

		return $content_winner;
	}
}

if ( ! function_exists( 'yith_wcact_get_auction_status_term_ids' ) ) {
	/**
	 * Get full list of product visibility term ids.
	 *
	 * @since  3.0.0
	 * @return int[]
	 */
	function yith_wcact_get_auction_status_term_ids() {
		if ( ! taxonomy_exists( 'yith_wcact_auction_status' ) ) {
			wc_doing_it_wrong( __FUNCTION__, 'yith_wcact_get_auction_status_term_ids should not be called before taxonomies are registered (woocommerce_after_register_post_type action).', '3.1' );

			return array();
		}

		return array_map(
			'absint',
			wp_parse_args(
				wp_list_pluck(
					get_terms(
						array(
							'taxonomy'   => 'yith_wcact_auction_status',
							'hide_empty' => false,
						)
					),
					'term_taxonomy_id',
					'name'
				),
				array(
					'started'     => 0,
					'finished'    => 0,
					'non-started' => 0,
				)
			)
		);
	}
}

if ( ! function_exists( 'yith_wcact_get_commission_fee_display' ) ) {
	/**
	 * Get the auction commission fee for display.
	 *
	 * @param  WC_Product $product Auction product.
	 * @return float/bool $fee_amount
	 * @since  2.0.0
	 */
	function yith_wcact_get_commission_fee_display( $product ) {
		$fee_amount = false;

		if ( $product && 'auction' === $product->get_type() ) {
			if ( 'yes' === $product->get_commission_fee_onoff() ) {
				$commission_fee = $product->get_commission_fee();

				if ( 'yes' === $product->get_commission_apply_fee_onoff() && $commission_fee['value'] > 0 ) {
					$commission_value    = $commission_fee['value'];
					$commission_fee_unit = $commission_fee['unit'];

					$display_commission = ( 'percentage' === $commission_fee_unit ) ? $commission_value . '%' : wc_price( $commission_value );

					$commission_label = $product->get_commission_fee_label();

					$fee_amount = '+ ' . $display_commission . ' ' . $commission_label;
				}
			} else {
				$general_commission_fee = get_option(
					'yith_wcact_general_default_commission_fee',
					array(
						'value' => 0,
						'unit'  => 'fixed',
					)
				);

				if ( 'yes' === get_option( 'yith_wcact_general_charge_commission_fee_onoff', 'no' ) && $general_commission_fee['value'] > 0 ) {
					$general_commission_value    = $general_commission_fee['value'];
					$general_commission_fee_unit = $general_commission_fee['unit'];
					$display_commission          = ( 'percentage' === $general_commission_fee_unit ) ? $general_commission_value . '%' : wc_price( $general_commission_value );

					$label            = get_option( 'yith_wcact_general_single_commission_label', '' );
					$commission_label = ! $label ? yith_wcact_get_label( 'default_commission_fee' ) : $label;

					$fee_amount = '+ ' . $display_commission . ' ' . $commission_label;
				}
			}
		}

		/**
		 * APPLY_FILTERS: ywcact_get_commission_fee_display
		 *
		 * Filter the message shown to notify that a commissions will be charged for the auction.
		 *
		 * @param string     $fee_amount Fee amount
		 * @param WC_Product $product    Product object
		 *
		 * @return string
		 */
		return apply_filters( 'ywcact_get_commission_fee_display', $fee_amount, $product );
	}
}

if ( ! function_exists( 'yith_wcact_calculate_commission_fee' ) ) {
	/**
	 * Calculate Auction Commission Fee and label.
	 *
	 * @param  WC_Product $product Auction product.
	 * @param  float      $price Auction product price on Cart.
	 * @return array/bool
	 * @since  3.0.0
	 */
	function yith_wcact_calculate_commission_fee( $product, $price ) {
		$commision_fee = false;

		if ( 'yes' === $product->get_commission_fee_onoff() ) {
			$commission_fee = $product->get_commission_fee();

			if ( 'yes' === $product->get_commission_apply_fee_onoff() && $commission_fee['value'] > 0 ) {
				$commission_value       = $commission_fee['value'];
				$commission_fee_unit    = $commission_fee['unit'];
				$label                  = $product->get_commission_fee_label();
				$label                  = ! $label ? yith_wcact_get_label( 'default_commission_fee' ) : $label;
				$commision_fee['label'] = $label;

				if ( 'percentage' === $commission_fee_unit ) {
					$commision_fee['value'] = ( $price * $commission_value ) / 100;
				} else {
					$commision_fee['value'] = $commission_value;
				}
			}
		} else {
			$general_commission_fee = get_option(
				'yith_wcact_general_default_commission_fee',
				array(
					'value' => 0,
					'unit'  => 'fixed',
				)
			);

			if ( 'yes' === get_option( 'yith_wcact_general_charge_commission_fee_onoff', 'no' ) && $general_commission_fee['value'] > 0 ) {
				$general_commission_value    = $general_commission_fee['value'];
				$general_commission_fee_unit = $general_commission_fee['unit'];
				$label                       = get_option( 'yith_wcact_general_single_commission_label', '' );
				$commision_fee['label']      = ! $label ? yith_wcact_get_label( 'default_commission_fee' ) : $label;

				if ( 'percentage' === $general_commission_fee_unit ) {
					$commision_fee['value'] = ( $price * $general_commission_value ) / 100;
				} else {
					$commision_fee['value'] = $general_commission_value;
				}
			}
		}

		/**
		 * APPLY_FILTERS: ywcact_get_commission_fee_value
		 *
		 * Filter the array with the data for the commission fee.
		 *
		 * @param array      $commision_fee Commission fee data
		 * @param WC_Product $product       Product object
		 * @param double     $price         Price
		 *
		 * @return array
		 */
		return apply_filters( 'ywcact_get_commission_fee_value', $commision_fee, $product, $price );
	}
}
