<?php // phpcs:ignore WordPress.NamingConventions
/**
 * WC_Product_Auction_Premium Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *  Class YITH Product Auction Premium
 *
 * @class   YITH_AUCTIONS
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'WC_Product_Auction_Premium' ) ) {
	/**
	 * Class WC_Product_Auction_Premium
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class WC_Product_Auction_Premium extends WC_Product_Auction {

		/**
		 * Meta keys defaults props.
		 *
		 * @var array
		 */
		protected $auction_data_defaults = array(
			'start_price'                          => '',
			'bid_increment'                        => '',
			'bid_increment_advanced'               => array(),
			'minimum_increment_amount'             => '',
			'buy_now'                              => '',
			'reserve_price'                        => '',
			'check_time_for_overtime_option'       => '',
			'overtime_option'                      => '',
			'automatic_reschedule'                 => '',
			'automatic_reschedule_auction_unit'    => 'days',
			'upbid_checkbox'                       => 'no',
			'overtime_checkbox'                    => 'no',
			'start_date'                           => '',
			'end_date'                             => '',
			'is_in_overtime'                       => false,
			'is_closed_by_buy_now'                 => false,
			'auction_paid_order'                   => false,
			'send_winner_email'                    => false,
			'send_admin_winner_email'              => false,
			'item_condition'                       => '',
			'auction_type'                         => 'normal',
			'auction_sealed'                       => 'no',
			'buy_now_onoff'                        => '',
			'bid_type_onoff'                       => '',
			'bid_type_set_radio'                   => '',
			'bid_type_radio'                       => 'simple',
			'fee_onoff'                            => '',
			'fee_ask_onoff'                        => '',
			'fee_amount'                           => '',
			'reschedule_onoff'                     => '',
			'reschedule_closed_without_bids_onoff' => '',
			'reschedule_reserve_no_reached_onoff'  => '',
			'overtime_onoff'                       => '',
			'overtime_set_onoff'                   => '',
			'commission_fee_onoff'                 => 'no',
			'commission_apply_fee_onoff'           => 'no',
			'commission_fee'                       => array(
				'value' => '',
				'unit'  => '',
			),
			'commission_fee_label'                 => '',
			'order_id'                             => 0,
			'payment_gateway'                      => 'none',

		);

		/**
		 * Status.
		 *
		 * @var boolean
		 */
		protected $status = false;

		/**
		 * Constructor gets the post object and sets the ID for the loaded product.
		 *
		 * @param int|WC_Product|object $product Product ID, post object, or product object.
		 **/
		public function __construct( $product = 0 ) {
			$this->data = array_merge( $this->data, $this->auction_data_defaults );

			parent::__construct( $product );
		}

		/**
		 * Get internal type.
		 *
		 * @since  3.0.0
		 * @return string
		 */
		public function get_type() {
			return 'auction';
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		|
		| Methods for getting data from the product object.
		*/

		/**
		 * Get Auction start price
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_start_price( $context = 'view' ) {
			return $this->get_prop( 'start_price', $context );
		}

		/**
		 * Get Bid increment
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_bid_increment( $context = 'view' ) {
			return $this->get_prop( 'bid_increment', $context );
		}

		/**
		 * Get Bid increment advanced
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  2.0.0
		 */
		public function get_bid_increment_advanced( $context = 'view' ) {
			return $this->get_prop( 'bid_increment_advanced', $context );
		}

		/**
		 * Get Minimum increment amount
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_minimum_increment_amount( $context = 'view' ) {
			return $this->get_prop( 'minimum_increment_amount', $context );
		}

		/**
		 * Get Reserve price
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_reserve_price( $context = 'view' ) {
			return $this->get_prop( 'reserve_price', $context );
		}

		/**
		 * Get Buy now price
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_buy_now( $context = 'view' ) {
			return $this->get_prop( 'buy_now', $context );
		}
		/**
		 * Get Check time for overtime option
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_check_time_for_overtime_option( $context = 'view' ) {
			return $this->get_prop( 'check_time_for_overtime_option', $context );
		}
		/**
		 * Get Overtime option
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_overtime_option( $context = 'view' ) {
			return $this->get_prop( 'overtime_option', $context );
		}
		/**
		 * Get Automatic reschedule
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_automatic_reschedule( $context = 'view' ) {
			return $this->get_prop( 'automatic_reschedule', $context );
		}

		/**
		 * Get Automatic reschedule auction unit
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_automatic_reschedule_auction_unit( $context = 'view' ) {
			return $this->get_prop( 'automatic_reschedule_auction_unit', $context );
		}

		/**
		 * Get Upbid option on frontend
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_upbid_checkbox( $context = 'view' ) {
			return $this->get_prop( 'upbid_checkbox', $context );
		}

		/**
		 * Get Overtime option on frontend
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_overtime_checkbox( $context = 'view' ) {
			return $this->get_prop( 'overtime_checkbox', $context );
		}

		/**
		 * Get Start Date from Auction
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_start_date( $context = 'view' ) {
			return $this->get_prop( 'start_date', $context );
		}

		/**
		 * Get End Date
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_end_date( $context = 'view' ) {
			return $this->get_prop( 'end_date', $context );
		}

		/**
		 * Get Is in overtime
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 * @since  1.3.4
		 */
		public function get_is_in_overtime( $context = 'view' ) {
			return $this->get_prop( 'is_in_overtime', $context );
		}

		/**
		 * Get Is closed by buy now
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  1.3.4
		 */
		public function get_is_closed_by_buy_now( $context = 'view' ) {
			return $this->get_prop( 'is_closed_by_buy_now', $context );
		}

		/**
		 * Get Auction Paid order
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  1.3.4
		 */
		public function get_auction_paid_order( $context = 'view' ) {
			return $this->get_prop( 'auction_paid_order', $context );
		}

		// ---------------------- Get email properties ----------------------------------------
		/**
		 * Get Send winner email
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  1.3.4
		 */
		public function get_send_winner_email( $context = 'view' ) {
			return $this->get_prop( 'send_winner_email', $context );
		}

		/**
		 * Get Send admin winner email
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  1.3.4
		 */
		public function get_send_admin_winner_email( $context = 'view' ) {
			return $this->get_prop( 'send_admin_winner_email', $context );
		}

		/**
		 * Get current bid auction product
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function get_current_bid() {
			$start_price   = $this->get_start_price();
			$current_bid   = $start_price;
			$bid_increment = $this->calculate_bid_up_increment();
			$bids          = YITH_Auctions()->bids;

			if ( 'reverse' !== $this->get_auction_type() ) {
				$current_bid = $this->get_current_bid_normal_auction( $current_bid, $start_price, $bid_increment, $bids );
			} else {
				$current_bid = $this->get_current_bid_reverse_auction( $current_bid, $start_price, $bid_increment, $bids );
			}

			/**
			 * APPLY_FILTERS: yith_wcact_get_current_bid
			 *
			 * Filter the current bid for the auction.
			 *
			 * @param string                     $current_bid Current bid
			 * @param WC_Product_Auction_Premium $product     Auction product
			 *
			 * @return string
			 */
			$the_current_bid = apply_filters( 'yith_wcact_get_current_bid', $current_bid, $this );
			yit_set_prop( $this, 'current_bid', $the_current_bid );

			return $the_current_bid;
		}

		/**
		 * Get current bid normal auction
		 *
		 * @param  float           $current_bid   .
		 * @param  float           $start_price   .
		 * @param  float           $bid_increment .
		 * @param  YITH_WCACT_Bids $bids          .
		 * @return float
		 * @since  2.0.0
		 */
		public function get_current_bid_normal_auction( $current_bid, $start_price, $bid_increment, $bids ) {
			$reserve_price = $this->get_reserve_price();
			$buy_now       = $this->get_is_closed_by_buy_now();

			if ( ! $buy_now ) {
				if ( $bid_increment > 0 ) {
					/*-------WITH BID INCREMENT---------*/
					$last_two_bids = $bids->get_last_two_bids( $this->get_id() );

					if ( count( $last_two_bids ) === 2 ) {
						// I have two or more bids.
						$first_bid  = $last_two_bids[0] && isset( $last_two_bids[0]->bid ) ? $last_two_bids[0]->bid : 0;
						$second_bid = $last_two_bids[1] && isset( $last_two_bids[1]->bid ) ? $last_two_bids[1]->bid : 0;

						if ( $first_bid === $second_bid ) {
							$current_bid = max( $start_price, $first_bid );
						} else {
							$is_auto_bid = ( $first_bid - $second_bid ) > $bid_increment;

							if ( $first_bid >= $reserve_price && $second_bid < $reserve_price ) {
								$current_bid = $reserve_price;
							} elseif ( $is_auto_bid ) {
								/**
								 * APPLY_FILTERS: yith_wcact_current_bid_is_auto_bid
								 *
								 * Filter the bid value for the current automatic bid.
								 *
								 * @param string                     $current_bid   Current bid
								 * @param WC_Product_Auction_Premium $product       Auction product
								 * @param string                     $first_bid     First bid
								 * @param string                     $second_bid    Second bid
								 * @param string                     $reserve_price Reserve price
								 *
								 * @return string
								 */
								$current_bid = apply_filters( 'yith_wcact_current_bid_is_auto_bid', max( $start_price, $second_bid + $bid_increment ), $this, $first_bid, $second_bid, $reserve_price );
							} else {
								$current_bid = max( $start_price, $first_bid );
							}
						}
					} elseif ( count( $last_two_bids ) === 1 ) {
						// I have only one bid.
						$the_bid = $last_two_bids[0];

						if ( $the_bid && isset( $the_bid->bid ) && $the_bid->bid >= $start_price ) {
							if ( $the_bid->bid >= $reserve_price && isset( $reserve_price ) && $reserve_price > 0 ) {
								$current_bid = $reserve_price;
							} elseif ( 0 === $start_price ) {
								$current_bid = $the_bid->bid;
							} else {
								/**
								 * APPLY_FILTERS: yith_wcact_current_bid_first_bid
								 *
								 * Filter the start price for the auction.
								 *
								 * @param string                     $start_price   Start price
								 * @param WC_Product_Auction_Premium $product       Auction product
								 * @param object                     $the_bid       Bid object
								 * @param string                     $bid_increment Bid increment
								 * @param string                     $reserve_price Reserve price
								 *
								 * @return string
								 */
								$current_bid = apply_filters( 'yith_wcact_current_bid_first_bid', $start_price, $this, $the_bid, $bid_increment, $reserve_price );
							}
						}
					}
				} else {
					/*-------WITHOUT BID INCREMENT---------*/
					$max_bid = $bids->get_max_bid( $this->get_id() );

					if ( $max_bid && isset( $max_bid->bid ) && $max_bid->bid >= $start_price ) {
						$current_bid = $max_bid->bid;
					}
				}
			} else {
				$current_bid = $this->get_buy_now();
			}

			return $current_bid;
		}

		/**
		 * Get current bid reverse auction
		 *
		 * @param  float           $current_bid   .
		 * @param  float           $start_price   .
		 * @param  float           $bid_increment .
		 * @param  YITH_WCACT_Bids $bids          .
		 * @return float
		 * @since  2.0.0
		 */
		public function get_current_bid_reverse_auction( $current_bid, $start_price, $bid_increment, $bids ) {
			if ( $bid_increment > 0 ) {
				/*-------WITH BID INCREMENT---------*/
				$last_two_bids = $bids->get_last_two_bids( $this->get_id(), 'reverse' );

				if ( count( $last_two_bids ) === 2 ) {
					// I have two or more bids.
					$first_bid  = $last_two_bids[0] && isset( $last_two_bids[0]->bid ) ? $last_two_bids[0]->bid : 0;
					$second_bid = $last_two_bids[1] && isset( $last_two_bids[1]->bid ) ? $last_two_bids[1]->bid : 0;

					if ( $first_bid === $second_bid ) {
						$current_bid = min( $start_price, $first_bid );
					} else {
						$is_auto_bid = ( $first_bid - $second_bid ) < $bid_increment;

						if ( $is_auto_bid ) {
							$current_bid = apply_filters( 'yith_wcact_current_bid_is_auto_bid', min( $start_price, $second_bid - $bid_increment ), $this, $first_bid, $second_bid );
						} else {
							$current_bid = min( $start_price, $first_bid );
						}
					}
				} elseif ( count( $last_two_bids ) === 1 ) {
					// I have only one bid.
					$the_bid = $last_two_bids[0];

					if ( $the_bid && isset( $the_bid->bid ) && $the_bid->bid <= $start_price ) {
						if ( 0 === $start_price ) {
							$current_bid = $the_bid->bid;
						} else {
							apply_filters( 'yith_wcact_current_bid_first_bid', $start_price, $this, $the_bid, $bid_increment );
						}
					}
				}
			} else {
				/*-------WITHOUT BID INCREMENT---------*/
				$min_bid = $bids->get_min_bid( $this->get_id() );

				if ( $min_bid && isset( $min_bid->bid ) && $min_bid->bid <= $start_price ) {
					$current_bid = $min_bid->bid;
				}
			}

			return $current_bid;
		}


		/**
		 * Get current status of auction
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_auction_status() {
			$instance = YITH_Auctions()->bids;
			$max_bid  = $instance->get_max_bid( $this->get_id() );

			if ( $max_bid ) {
				$max_bid = $max_bid->bid;
			} else {
				$max_bid = 0;
			}

			if ( $this->is_start() && ! $this->is_closed() ) {
				if ( $this->has_reserve_price() && $max_bid < $this->get_reserve_price() && ! $this->get_is_closed_by_buy_now() ) {
					return 'started-reached-reserve';
				} elseif ( $this->get_is_closed_by_buy_now() ) {
					return 'finnish-buy-now';
				} else {
					return 'started';
				}
			} elseif ( $this->is_closed() ) {
				if ( $this->has_reserve_price() && $max_bid < $this->get_reserve_price() ) {
					return 'finished-reached-reserve';
				} else {
					return 'finished';
				}
			} else {
				return 'non-started';
			}
		}

		/**
		 * Get item condition
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_item_condition( $context = 'view' ) {
			return $this->get_prop( 'item_condition', $context );
		}

		/**
		 * Get auction type
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_auction_type( $context = 'view' ) {
			return $this->get_prop( 'auction_type', $context );
		}

		/**
		 * Get auction type
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_auction_sealed( $context = 'view' ) {
			return $this->get_prop( 'auction_sealed', $context );
		}

		/**
		 * Get Buy now onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_buy_now_onoff( $context = 'view' ) {
			return $this->get_prop( 'buy_now_onoff', $context );
		}

		/**
		 * Get bid type onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_bid_type_onoff( $context = 'view' ) {
			return $this->get_prop( 'bid_type_onoff', $context );
		}

		// bid_type_set_radio.
		/**
		 * Get bid type set radio
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_bid_type_set_radio( $context = 'view' ) {
			return $this->get_prop( 'bid_type_set_radio', $context );
		}
		/**
		 * Get bid type radio
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string
		 * @since  2.0.0
		 */
		public function get_bid_type_radio( $context = 'view' ) {
			return $this->get_prop( 'bid_type_radio', $context );
		}

		/**
		 * Get fee onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_fee_onoff( $context = 'view' ) {
			return $this->get_prop( 'fee_onoff', $context );
		}

		/**
		 * Get fee ask onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_fee_ask_onoff( $context = 'view' ) {
			return $this->get_prop( 'fee_ask_onoff', $context );
		}

		/**
		 * Get fee amount
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float
		 * @since  2.0.0
		 */
		public function get_fee_amount( $context = 'view' ) {
			return $this->get_prop( 'fee_amount', $context );
		}

		/**
		 * Get reschedule onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_reschedule_onoff( $context = 'view' ) {
			return $this->get_prop( 'reschedule_onoff', $context );
		}

		/**
		 * Get reschedule_closed_without_bids_onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_reschedule_closed_without_bids_onoff( $context = 'view' ) {
			return $this->get_prop( 'reschedule_closed_without_bids_onoff', $context );
		}

		/**
		 * Get reschedule_reserve_no_reached_onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_reschedule_reserve_no_reached_onoff( $context = 'view' ) {
			return $this->get_prop( 'reschedule_reserve_no_reached_onoff', $context );
		}

		/**
		 * Get overtime onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_overtime_onoff( $context = 'view' ) {
			return $this->get_prop( 'overtime_onoff', $context );
		}

		/**
		 * Get overtime set onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  2.0.0
		 */
		public function get_overtime_set_onoff( $context = 'view' ) {
			return $this->get_prop( 'overtime_set_onoff', $context );
		}

		/**
		 * Get comission fee onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  3.0.0
		 */
		public function get_commission_fee_onoff( $context = 'view' ) {
			return $this->get_prop( 'commission_fee_onoff', $context );
		}

		/**
		 * Get commission apply fee onoff
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return boolean
		 * @since  3.0.0
		 */
		public function get_commission_apply_fee_onoff( $context = 'view' ) {
			return $this->get_prop( 'commission_apply_fee_onoff', $context );
		}

		/**
		 * Get commission fee
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float
		 * @since  3.0.0
		 */
		public function get_commission_fee( $context = 'view' ) {
			return $this->get_prop( 'commission_fee', $context );
		}
		/**
		 * Get commission fee label
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string
		 * @since  3.0.0
		 */
		public function get_commission_fee_label( $context = 'view' ) {
			return $this->get_prop( 'commission_fee_label', $context );
		}

		/**
		 * Get order id
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return int
		 * @since  3.0.0
		 */
		public function get_order_id( $context = 'view' ) {
			return $this->get_prop( 'order_id', $context );
		}

		/**
		 * Get payment gateway
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return string
		 * @since  3.0.0
		 */
		public function get_payment_gateway( $context = 'view' ) {
			return $this->get_prop( 'payment_gateway', $context );
		}

		/**
		 * Product has a reserve price
		 */
		public function has_reserve_price() {
			$reserve_price = $this->get_reserve_price();

			if ( isset( $reserve_price ) && $reserve_price ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 *  Return if the auction product is visible
		 */
		public function is_visible() {
			if ( ( is_shop() || is_archive() ) && 'no' === get_option( 'yith_wcact_show_auctions_shop_page', 'no' ) ) {
				/**
				 * APPLY_FILTERS: yith_wcact_is_product_visible
				 *
				 * Filter whether the auction product is visible.
				 *
				 * @param bool                       $is_visible Whether the auction product is visible or not
				 * @param WC_Product_Auction_Premium $product    Auction product
				 *
				 * @return bool
				 */
				return apply_filters( 'yith_wcact_is_product_visible', false, $this );
			}

			if ( ( 'yes' === get_option( 'yith_wcact_hide_auctions_out_of_stock', 'no' ) && $this->get_is_closed_by_buy_now() ) && is_shop() ) {
				return apply_filters( 'yith_wcact_is_product_visible', false, $this );
			}

			/**
			 * APPLY_FILTERS: yith_wcact_specific_hide_auction_closed
			 *
			 * Filter whether to hide the closed auction.
			 *
			 * @param bool $hide_closed_auction Whether to hide closed auction or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_specific_hide_auction_closed', ( 'yes' === get_option( 'yith_wcact_hide_auctions_closed', 'no' ) ) && $this->is_closed() && is_shop() ) ) {
				return apply_filters( 'yith_wcact_is_product_visible', false, $this );
			}

			if ( ( 'yes' === get_option( 'yith_wcact_hide_auctions_not_started', 'no' ) && ! $this->is_start() ) && is_shop() ) {
				return apply_filters( 'yith_wcact_is_product_visible', false, $this );
			}

			return apply_filters( 'yith_wcact_is_product_visible', parent::is_visible(), $this );
		}

		/**
		 *  Return global or local check to add overtime
		 */
		public function check_for_overtime() {
			$overtime_on_off    = ( 'yes' === yith_wcact_field_onoff_value( 'overtime_onoff', 'check_time_for_overtime_option', $this ) );
			$overtime_set_onoff = ( 'yes' === yith_wcact_field_onoff_value( 'overtime_set_onoff', 'check_time_for_overtime_option', $this ) );

			if ( $overtime_on_off ) {
				if ( $overtime_set_onoff ) {
					$check_for_overtime = $this->get_check_time_for_overtime_option();

					if ( isset( $check_for_overtime ) && $check_for_overtime ) {
						return $check_for_overtime;
					}
				}
			} else {
				return 'yes' === get_option( 'yith_wcact_settings_set_overtime', 'no' ) ? get_option( 'yith_wcact_settings_overtime_option', 0 ) : 0;
			}

			return 0;
		}

		/**
		 *  Return global or local overtime
		 */
		public function get_overtime() {
			$overtime_on_off    = ( 'yes' === yith_wcact_field_onoff_value( 'overtime_onoff', 'check_time_for_overtime_option', $this ) );
			$overtime_set_onoff = ( 'yes' === yith_wcact_field_onoff_value( 'overtime_set_onoff', 'check_time_for_overtime_option', $this ) );

			if ( $overtime_on_off ) {
				if ( $overtime_set_onoff ) {
					$overtime = $this->get_overtime_option();

					if ( isset( $overtime ) && $overtime ) {
						return $overtime;
					}
				}
			} else {
				return 'yes' === get_option( 'yith_wcact_settings_set_overtime', 'no' ) ? get_option( 'yith_wcact_settings_overtime', 0 ) : 0;
			}

			return 0;
		}

		/**
		 *  Return true if is in overtime
		 */
		public function is_in_overtime() {
			$is_in_overtime = $this->get_is_in_overtime();

			if ( isset( $is_in_overtime ) && $is_in_overtime ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 *  Return automatic reschedule time
		 */
		public function get_automatic_reschedule_time() {
			$time_quantity_on_off = yith_wcact_field_onoff_value( 'reschedule_onoff', 'automatic_reschedule', $this );
			$time                 = array();
			$reschedule_bids      = false;

			if ( 'yes' === $time_quantity_on_off ) {
				$reschedule_reserve_no_reached_on_off = $this->get_reschedule_reserve_no_reached_onoff();
				$reschedule_reserve_without_bids_     = ( 'yes' === yith_wcact_field_onoff_value( 'reschedule_closed_without_bids_onoff', 'automatic_reschedule', $this ) );

				if ( 'yes' === $reschedule_reserve_no_reached_on_off || 'yes' === $reschedule_reserve_without_bids_ ) {
					$reschedule_bids = true;
					$time_quantity   = $this->get_automatic_reschedule();

					if ( isset( $time_quantity ) && $time_quantity >= 0 ) {
						$time = array(
							'time_quantity' => $time_quantity,
							'time_unit'     => $this->get_automatic_reschedule_auction_unit(),
						);
					}
				}
			} else {
				if ( 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_without_bids', 'no' ) || 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_reserve_price_reached', 'no' ) ) {
					$reschedule_bids = true;
					$time            = array(
						'time_quantity' => get_option( 'yith_wcact_settings_automatic_reschedule_auctions_number', 0 ),
						'time_unit'     => get_option( 'yith_wcact_settings_automatic_reschedule_auctions_unit', 'minutes' ),
					);
				}
			}

			if ( $reschedule_bids ) {
				$bids = YITH_Auctions()->bids;
				$bids->reshedule_auction( $this->get_id() );
			}

			return $time;
		}

		/**
		 *  Return list of watchlist user
		 */
		public function get_followers_list() {
			$instance       = YITH_Auctions()->bids;
			$followers_list = $instance->get_users_count_product_on_follower_list( $this->get_id() );

			if ( isset( $followers_list ) && $followers_list ) {
				return $followers_list;
			} else {
				return false;
			}
		}

		/**
		 *  Insert email in watchlist
		 *
		 * @param string $user_email User email.
		 * @param string $hash Hash string.
		 */
		public function add_user_in_followers_list( $user_email, $hash = '' ) {
			$user     = get_user_by( 'email', $user_email );
			$user_id  = ( $user ) ? $user->ID : null;
			$instance = YITH_Auctions()->bids;
			$instance->insert_follower( $this->get_id(), $user_email, $user_id, $hash );
		}

		/**
		 *  Check if user is in follower list
		 *
		 * @param string $user_email User email.
		 * @return boolean
		 */
		public function is_in_followers_list( $user_email ) {
			$instance = YITH_Auctions()->bids;

			$in_follower_list = $instance->is_a_follower( $this->get_id(), $user_email );

			return $in_follower_list;
		}

		/**
		 * Calculate bidup
		 *
		 * Calculate bidup based on product configuration on general configuration.
		 *
		 * @return float
		 * @since  2.0.0
		 */
		public function calculate_bid_up_increment() {
			$bid_increment = 0;

			$auction_type = $this->get_auction_type();

			$bid_type_on_off    = ( 'yes' === yith_wcact_field_onoff_value( 'bid_type_onoff', 'bid_increment', $this ) );
			$bid_type_set_radio = ( 'automatic' === yith_wcact_field_radio_value( 'bid_type_set_radio', 'bid_increment', $this, 'manual', 'automatic' ) );

			if ( $bid_type_on_off ) {
				if ( $bid_type_set_radio ) {
					if ( 'simple' === yith_wcact_field_radio_value( 'bid_type_radio', 'bid_increment', $this, 'simple' ) ) {
						$bid_increment = $this->get_bid_increment();
					} else {
						$rules = $this->get_bid_increment_advanced();

						if ( $rules && is_array( $rules ) ) {
							/**
							 * APPLY_FILTERS: yith_wcact_current_bid_bidup_increment
							 *
							 * Filter the bid increment for the current bid.
							 *
							 * @param string                     $bid_increment Bid increment
							 * @param WC_Product_Auction_Premium $product       Auction product
							 *
							 * @return string
							 */
							$current_bid = apply_filters( 'yith_wcact_current_bid_bidup_increment', get_post_meta( $this->get_id(), 'current_bid', true ), $this );

							if ( ! isset( $current_bid ) || ! $current_bid ) {
								$current_bid = $this->get_start_price();
							}

							$start = array_shift( $rules ); // First value.
							$end   = array_pop( $rules ); // Last value.

							// Check last rule.
							if ( 'reverse' === $auction_type ? ( $current_bid < ( ! empty( $end['start'] ) ? $end['start'] : 0 ) ) : ( $current_bid > ( ! empty( $end['start'] ) ? $end['start'] : 0 ) ) ) {
								$bid_increment = ! empty( $end['value'] ) ? $end['value'] : 0;
							} elseif ( 'reverse' === $auction_type ? ( $current_bid >= ( ! empty( $start['end'] ) ? $start['end'] : 0 ) ) : ( $current_bid <= ( ! empty( $start['end'] ) ? $start['end'] : 0 ) ) ) { // Check first rule.
								$bid_increment = ! empty( $start['value'] ) ? $start['value'] : 0;
							} else {
								foreach ( $rules as $rule ) {
									if ( ( isset( $rule['from'] ) && ! empty( $rule['from'] ) ) && ( isset( $rule['to'] ) && ! empty( $rule['to'] ) ) ) {
										$value = ! empty( $rule['value'] ) ? $rule['value'] : 0;

										if ( 'reverse' === $auction_type ? ( $rule['from'] >= $current_bid ) && ( $current_bid >= $rule['to'] ) : ( ( $rule['from'] <= $current_bid ) && ( $current_bid <= $rule['to'] ) ) ) {
											$bid_increment = $value;
											break;
										}
									}
								}
							}
						}
					}
				}
			} elseif ( 'automatic' === get_option( 'yith_wcact_settings_bid_type', 'manual' ) ) { // Use general rule.
				$rules = maybe_unserialize( get_option( 'yith_wcact_settings_automatic_bid_increment', 0 ) );

				if ( $rules && is_array( $rules ) ) { // array of rules.
					$current_bid = apply_filters( 'yith_wcact_current_bid_bidup_increment', get_post_meta( $this->get_id(), 'current_bid', true ), $this );

					if ( ! isset( $current_bid ) || ! $current_bid ) {
						$current_bid = $this->get_start_price();
					}

					$start = array_shift( $rules ); // First value.
					$end   = array_pop( $rules ); // Last value.

					// Check last rule.
					if ( 'reverse' === $auction_type ? ( $current_bid < ( ! empty( $end['start'] ) ? $end['start'] : 0 ) ) : ( $current_bid > ( ! empty( $end['start'] ) ? $end['start'] : 0 ) ) ) {
						$bid_increment = ! empty( $end['value'] ) ? $end['value'] : 0;
					} elseif ( 'reverse' === $auction_type ? ( $current_bid >= ( ! empty( $start['end'] ) ? $start['end'] : 0 ) ) : ( $current_bid <= ( ! empty( $start['end'] ) ? $start['end'] : 0 ) ) ) { // Check first rule.
						$bid_increment = ! empty( $start['value'] ) ? $start['value'] : 0;
					} else {
						foreach ( $rules as $rule ) {
							if ( ( isset( $rule['from'] ) && ! empty( $rule['from'] ) ) && ( isset( $rule['to'] ) && ! empty( $rule['to'] ) ) ) {
								$value = ! empty( $rule['value'] ) ? $rule['value'] : 0;

								if ( 'reverse' === $auction_type ? ( $current_bid < $rule['from'] && $current_bid >= $rule['to'] ) : ( $current_bid >= $rule['from'] && $current_bid <= $rule['to'] ) ) {
									$bid_increment = $value;
									break;
								}
							}
						}
					}
				} else {
					$bid_increment = $rules; // Simple number.
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcact_calculate_bid_up_increment
			 *
			 * Filter the bid increment.
			 *
			 * @param string                     $bid_increment Bid increment
			 * @param WC_Product_Auction_Premium $product       Auction product
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcact_calculate_bid_up_increment', $bid_increment, $this );
		}

		/**
		 * Update auction visibility terms.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $force Force update. Used during create.
		 */
		public function update_auction_status( $force ) {
			$auction_status = $this->get_auction_status();

			switch ( $auction_status ) {
				case 'non-started':
					$auction_status_term = 'scheduled';
					break;

				case 'started-reached-reserve':
				case 'started':
					$auction_status_term = 'started';
					break;

				case 'finished':
				case 'finnish-buy-now':
				case 'finished-reached-reserve':
					$auction_status_term = 'finished';
					break;

				default:
					$auction_status_term = 'started';
					break;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_update_auction_status_value
			 *
			 * Filter the term for the auction status.
			 *
			 * @param string                     $auction_status_term Auction status
			 * @param WC_Product_Auction_Premium $product             Auction product
			 *
			 * @return string
			 */
			$auction_status_term = apply_filters( 'yith_wcact_update_auction_status_value', $auction_status_term, $this );

			wp_set_post_terms( $this->get_id(), $auction_status_term, 'yith_wcact_auction_status', false );
		}

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		|
		| Functions for setting product data. These should not update anything in the
		| database itself and should only change what is stored in the class
		| object.
		*/

		/**
		 * Set Buy now price.
		 *
		 * @param string $buy_now Product buy now price.
		 * @since 1.3.4
		 */
		public function set_buy_now( $buy_now ) {
			$this->set_prop( 'buy_now', $buy_now );
		}

		/**
		 * Set start price.
		 *
		 * @param string $start_price Product start price.
		 * @since 1.3.4
		 */
		public function set_start_price( $start_price ) {
			$this->set_prop( 'start_price', $start_price );
		}

		/**
		 * Set Bid increment.
		 *
		 * @param string $bid_increment Product bid increment.
		 * @since 1.3.4
		 */
		public function set_bid_increment( $bid_increment ) {
			$this->set_prop( 'bid_increment', $bid_increment );
		}

		/**
		 * Set Bid increment advanced.
		 *
		 * @param array $bid_increment_advanced Advanced increment.
		 * @since 1.3.4
		 */
		public function set_bid_increment_advanced( $bid_increment_advanced ) {
			$this->set_prop( 'bid_increment_advanced', $bid_increment_advanced );
		}

		/**
		 * Set Min increment amount.
		 *
		 * @param string $minimum_increment_amount Minimum increment amount.
		 * @since 1.3.4
		 */
		public function set_minimum_increment_amount( $minimum_increment_amount ) {
			$this->set_prop( 'minimum_increment_amount', $minimum_increment_amount );
		}

		/**
		 * Set reserve price.
		 *
		 * @param string $reserve_price Reserve price.
		 * @since 1.3.4
		 */
		public function set_reserve_price( $reserve_price ) {
			$this->set_prop( 'reserve_price', $reserve_price );
		}

		/**
		 * Set check time for overtime option.
		 *
		 * @param string $check_time_for_overtime_option Check time for overtime option.
		 * @since 1.3.4
		 */
		public function set_check_time_for_overtime_option( $check_time_for_overtime_option ) {
			$this->set_prop( 'check_time_for_overtime_option', $check_time_for_overtime_option );
		}

		/**
		 * Set overtime option.
		 *
		 * @param string $overtime_option Overtime option.
		 * @since 1.3.4
		 */
		public function set_overtime_option( $overtime_option ) {
			$this->set_prop( 'overtime_option', $overtime_option );
		}

		/**
		 * Set automatic reschedule.
		 *
		 * @param string $automatic_reschedule Automatic reschedule.
		 * @since 1.3.4
		 */
		public function set_automatic_reschedule( $automatic_reschedule ) {
			$this->set_prop( 'automatic_reschedule', $automatic_reschedule );
		}

		/**
		 * Set automatic reschedule auction unit.
		 *
		 * @param string $automatic_reschedule_auction_unit Reschedule auction unit.
		 * @since 1.3.4
		 */
		public function set_automatic_reschedule_auction_unit( $automatic_reschedule_auction_unit ) {
			$this->set_prop( 'automatic_reschedule_auction_unit', $automatic_reschedule_auction_unit );
		}

		/**
		 * Set upbid checkbox.
		 *
		 * @param string $upbid_checkbox upbid checkbox.
		 * @since 1.3.4
		 */
		public function set_upbid_checkbox( $upbid_checkbox ) {
			$this->set_prop( 'upbid_checkbox', $upbid_checkbox );
		}

		/**
		 * Set Overtime checkbox
		 *
		 * @param string $overtime_checkbox Overtime checkbox.
		 * @since 1.3.4
		 */
		public function set_overtime_checkbox( $overtime_checkbox ) {
			$this->set_prop( 'overtime_checkbox', $overtime_checkbox );
		}

		/**
		 * Set Start auction date.
		 *
		 * @param string $start_date Start date.
		 * @since 1.3.4
		 */
		public function set_start_date( $start_date ) {
			$this->set_prop( 'start_date', wc_format_decimal( wc_clean( $start_date ) ) );
		}

		/**
		 * Set Finish auction date.
		 *
		 * @param string $end_date Finish date.
		 * @since 1.3.4
		 */
		public function set_end_date( $end_date ) {
			$this->set_prop( 'end_date', wc_format_decimal( wc_clean( $end_date ) ) );
		}

		/**
		 * Set is in overtime
		 *
		 * @param bool $is_in_overtime Is in overtime auction.
		 * @since 1.3.4
		 */
		public function set_is_in_overtime( $is_in_overtime ) {
			$this->set_prop( 'is_in_overtime', wc_format_decimal( wc_clean( $is_in_overtime ) ) );
		}

		/**
		 * Set is closed by buy now
		 *
		 * @param bool $is_closed_by_buy_now Is closed by buy now.
		 * @since 1.3.4
		 */
		public function set_is_closed_by_buy_now( $is_closed_by_buy_now ) {
			$this->set_prop( 'is_closed_by_buy_now', $is_closed_by_buy_now );
		}

		/**
		 * Set auction paid order
		 *
		 * @param bool $auction_paid_order Auction paid order boolean.
		 * @since 1.3.4
		 */
		public function set_auction_paid_order( $auction_paid_order ) {
			$this->set_prop( 'auction_paid_order', $auction_paid_order );
		}

		// ----------------------- Set email properties -------------------------------------------
		/**
		 * Set send winner email
		 *
		 * @param bool $send_winner_email Send winner email bool.
		 * @since 1.3.4
		 */
		public function set_send_winner_email( $send_winner_email ) {
			$this->set_prop( 'send_winner_email', wc_string_to_bool( $send_winner_email ) );
		}

		/**
		 * Set send admin winner email
		 *
		 * @param bool $send_admin_winner_email Send admin winner email bool.
		 * @since 1.3.4
		 */
		public function set_send_admin_winner_email( $send_admin_winner_email ) {
			$this->set_prop( 'send_admin_winner_email', wc_string_to_bool( $send_admin_winner_email ) );
		}

		/**
		 * Set item condition
		 *
		 * @param string $item_condition Item condition.
		 * @since 2.0.0
		 */
		public function set_item_condition( $item_condition ) {
			$this->set_prop( 'item_condition', $item_condition );
		}

		/**
		 * Set auction type
		 *
		 * @param string $auction_type Auction type.
		 * @since 2.0.0
		 */
		public function set_auction_type( $auction_type ) {
			$this->set_prop( 'auction_type', $auction_type );
		}

		/**
		 * Set auction sealed
		 *
		 * @param string $auction_sealed Auction sealed.
		 * @since 2.0.0
		 */
		public function set_auction_sealed( $auction_sealed ) {
			$this->set_prop( 'auction_sealed', $auction_sealed );
		}

		/**
		 * Set Buy now onoff
		 *
		 * @param string $buy_now_onoff Buy now onoff.
		 * @since 2.0.0
		 */
		public function set_buy_now_onoff( $buy_now_onoff ) {
			$this->set_prop( 'buy_now_onoff', $buy_now_onoff );
		}

		/**
		 * Set bid type onoff
		 *
		 * @param string $bid_type_onoff Bid type onoff.
		 * @since 2.0.0
		 */
		public function set_bid_type_onoff( $bid_type_onoff ) {
			$this->set_prop( 'bid_type_onoff', $bid_type_onoff );
		}

		/**
		 * Set bid type set radio
		 *
		 * @param string $bid_type_set_radio Bid type radio.
		 * @since 2.0.0
		 */
		public function set_bid_type_set_radio( $bid_type_set_radio ) {
			$this->set_prop( 'bid_type_set_radio', $bid_type_set_radio );
		}

		/**
		 * Set bid type onoff
		 *
		 * @param string $bid_type_radio Bid type radio.
		 * @since 2.0.0
		 */
		public function set_bid_type_radio( $bid_type_radio ) {
			$this->set_prop( 'bid_type_radio', $bid_type_radio );
		}

		/**
		 * Set fee onoff
		 *
		 * @param string $fee_onoff Fee onoff option.
		 * @since 2.0.0
		 */
		public function set_fee_onoff( $fee_onoff ) {
			$this->set_prop( 'fee_onoff', $fee_onoff );
		}

		/**
		 * Set fee ask onoff
		 *
		 * @param string $fee_ask_onoff Fee ask onoff option.
		 * @since 2.0.0
		 */
		public function set_fee_ask_onoff( $fee_ask_onoff ) {
			$this->set_prop( 'fee_ask_onoff', $fee_ask_onoff );
		}

		/**
		 * Set fee amount
		 *
		 * @param string $fee_amount Fee amount.
		 * @since 2.0.0
		 */
		public function set_fee_amount( $fee_amount ) {
			$this->set_prop( 'fee_amount', $fee_amount );
		}

		/**
		 * Set reschedule onoff
		 *
		 * @param string $reschedule_onoff Reschedule onoff option.
		 * @since 2.0.0
		 */
		public function set_reschedule_onoff( $reschedule_onoff ) {
			$this->set_prop( 'reschedule_onoff', $reschedule_onoff );
		}

		/**
		 * Set reschedule_closed_without_bids_onoff
		 *
		 * @param string $reschedule_closed_without_bids_onoff Reschedule closed without bids onoff.
		 * @since 2.0.0
		 */
		public function set_reschedule_closed_without_bids_onoff( $reschedule_closed_without_bids_onoff ) {
			$this->set_prop( 'reschedule_closed_without_bids_onoff', $reschedule_closed_without_bids_onoff );
		}

		/**
		 * Set reschedule_reserve_no_reached_onoff
		 *
		 * @param string $reschedule_reserve_no_reached_onoff Reschedule no reached reserve price onoff.
		 * @since 2.0.0
		 */
		public function set_reschedule_reserve_no_reached_onoff( $reschedule_reserve_no_reached_onoff ) {
			$this->set_prop( 'reschedule_reserve_no_reached_onoff', $reschedule_reserve_no_reached_onoff );
		}

		/**
		 * Set Overtime onoff
		 *
		 * @param string $overtime_onoff Overtime OnOff.
		 * @since 2.0.0
		 */
		public function set_overtime_onoff( $overtime_onoff ) {
			$this->set_prop( 'overtime_onoff', $overtime_onoff );
		}

		/**
		 * Set Overtime set onoff
		 *
		 * @param string $overtime_set_onoff OnOff.
		 * @since 2.0.0
		 */
		public function set_overtime_set_onoff( $overtime_set_onoff ) {
			$this->set_prop( 'overtime_set_onoff', $overtime_set_onoff );
		}

		/**
		 * Set commission fee onoff
		 *
		 * @param string $commission_fee_onoff OnOff.
		 * @since 3.0.0
		 */
		public function set_commission_fee_onoff( $commission_fee_onoff ) {
			$this->set_prop( 'commission_fee_onoff', $commission_fee_onoff );
		}

		/**
		 * Set commission apply fee onoff
		 *
		 * @param string $commission_apply_fee_onoff OnOff.
		 * @since 3.0.0
		 */
		public function set_commission_apply_fee_onoff( $commission_apply_fee_onoff ) {
			$this->set_prop( 'commission_apply_fee_onoff', $commission_apply_fee_onoff );
		}

		/**
		 * Set commission label
		 *
		 * @param string $commission_label Commission label string.
		 * @since 3.0.0
		 */
		public function set_commission_fee_label( $commission_label ) {
			$this->set_prop( 'commission_fee_label', $commission_label );
		}

		/**
		 * Set commission fee
		 *
		 * @param string $commission_fee Commission label string.
		 * @since 3.0.0
		 */
		public function set_commission_fee( $commission_fee ) {
			$this->set_prop( 'commission_fee', $commission_fee );
		}

		/**
		 * Set order id
		 *
		 * @param int $order_id Order id where bought auction product.
		 * @since 3.0.0
		 */
		public function set_order_id( $order_id ) {
			$this->set_prop( 'order_id', $order_id );
		}

		/**
		 * Set order id
		 *
		 * @param string $payment_gateway Payment gateway id.
		 * @since 3.0.0
		 */
		public function set_payment_gateway( $payment_gateway ) {
			$this->set_prop( 'payment_gateway', $payment_gateway );
		}

		// -----------------------------------------------------------------------------------------
	}
}
