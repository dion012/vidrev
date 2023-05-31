<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Auction_Ajax_Premium Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Auction_Ajax_Premium' ) ) {
	/**
	 * YITH_WCACT_Auction_Ajax_Premium
	 *
	 * @since 1.0.0
	 */
	class YITH_WCACT_Auction_Ajax_Premium extends YITH_WCACT_Auction_Ajax {

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function __construct() {
			add_action( 'wp_ajax_yith_wcact_reshedule_product', array( $this, 'yith_wcact_reshedule_product' ) );
			add_action( 'wp_ajax_yith_wcact_update_list_bids', array( $this, 'update_list_bids' ) );
			add_action( 'wp_ajax_nopriv_yith_wcact_update_list_bids', array( $this, 'update_list_bids' ) );
			add_action( 'wp_ajax_yith_wcact_delete_customer_bid', array( $this, 'delete_customer_bid' ) );
			add_action( 'wp_ajax_yith_wcact_resend_winner_email', array( $this, 'resend_winner_email' ) );

			// Pay Fee auction.
			add_action( 'wp_ajax_yith_wcact_pay_fee', array( $this, 'pay_fee' ) );

			/* == My watchlist == */
			add_action( 'wp_ajax_yith_wcact_update_my_account_auctions', array( $this, 'yith_wcact_update_auction_list' ) );
			add_action( 'wp_ajax_yith_wcact_update_my_watchlist_auctions', array( $this, 'yith_wcact_update_watchlist_list' ) );

			/* == Add to watchlist == */
			add_action( 'wp_ajax_yith_wcact_add_to_watchlist', array( $this, 'add_to_watchlist' ) );
			add_action( 'wp_ajax_nopriv_yith_wcact_add_to_watchlist', array( $this, 'redirect_to_my_account_watchlist_button' ) );

			add_action( 'wp_ajax_yith_wcact_remove_from_watchlist', array( $this, 'remove_from_watchlist' ) );
			add_action( 'wp_ajax_yith_wcact_get_watchlist_fragment_products', array( $this, 'get_fragment_product_list' ) );

			/* == Display popup table list bids on auction list panel == */
			add_action( 'wp_ajax_yith_wcact_load_bidders_table', array( $this, 'load_bidders_table' ) );

			/* == Unsubscribe auctions followers == */
			add_action( 'wp_ajax_yith_wcact_unsubscribe_auctions', array( $this, 'unsubscribe_auctions' ) );
			add_action( 'wp_ajax_nopriv_yith_wcact_unsubscribe_auctions', array( $this, 'unsubscribe_auctions' ) );

			parent::__construct();
		}

		/**
		 * Add a bid to the product
		 *
		 * @since  1.0.11
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_add_bid() {
			check_ajax_referer( 'add-bid', 'security' );

			$userid = get_current_user_id();

			$user_can_make_bid = apply_filters( 'yith_wcact_user_can_make_bid', true, $userid );

			if ( ! $user_can_make_bid ) {
				die();
			}

			if ( $userid && isset( $_POST['bid'] ) && isset( $_POST['product'] ) && apply_filters( 'yith_wcact_check_if_add_bid', true, $userid, sanitize_key( wp_unslash( $_POST['product'] ) ), sanitize_key( wp_unslash( $_POST['bid'] ) ) ) ) {
				$currency = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : get_woocommerce_currency();
				$bid      = apply_filters( 'yith_wcact_auction_bid', wp_unslash( $_POST['bid'] ), $currency ); // phpcs:ignore

				// Convert bid number with only 2 decimals.
				$bid = number_format( (float) $bid, 2, '.', '' );

				$product_id = apply_filters( 'yith_wcact_auction_product_id', sanitize_key( wp_unslash( $_POST['product'] ) ) );

				$date = date( 'Y-m-d H:i:s' ); // phpcs:ignore

				$product = wc_get_product( $product_id );

				if ( $product && 'auction' === $product->get_type() ) {
					$end_auction = $product->get_end_date();

					if ( strtotime( $date ) < $end_auction ) {
						if ( apply_filters( 'yith_wcact_is_valid_bid', true, $product, $userid ) && $bid >= 0 ) {
							if ( 'yes' === $product->get_auction_sealed() && apply_filters( 'yith_wcact_add_bid_on_sealed_auction_without_control', false, $product ) ) {
								$bids = YITH_Auctions()->bids;

								$bids->add_bid( $userid, $product_id, $bid, $date );

								update_post_meta( $product_id, 'current_bid', $bid ); // Save this data for use on automatic bid increment value.

								$args = compact( 'bid', 'date' );

								WC()->mailer();

								do_action( 'yith_wcact_successfully_bid', $userid, $product, $args );

								$message_successfully = apply_filters( 'yith_wcact_message_successfully', yith_wcact_auction_message( 0, $product ), $bid, $product, $userid );
								$notice_type          = apply_filters( 'yith_wcact_message_successfully_notice_type', 'success', $bid, $product, $userid );

								wc_add_notice( $message_successfully, $notice_type );
							} else {
								if ( 'reverse' !== $product->get_auction_type() ) {  // Add bid on normal auction.
									$this->add_bid_normal_auction( $bid, $product, $userid, $currency, $date, $end_auction );
								} else { // Add bid on reverse auction.
									$this->add_bid_reverse_auction( $bid, $product, $userid, $currency, $date, $end_auction );
								}
							}
						} else {
							if ( apply_filters( 'yith_wcact_show_message', true ) ) {
								$message = yith_wcact_auction_message( 2, $product );

								wc_add_notice( $message, 'error' );
							}
						}

						$return_url = isset( $_POST['return_url'] ) ? esc_url_raw( wp_unslash( $_POST['return_url'] ) ) : '';

						$user_bid = array(
							'user_id'    => $userid,
							'product_id' => $product_id,
							'bid'        => $bid,
							'date'       => $date,
							'url'        => isset( $return_url ) && ! empty( $return_url ) ? $return_url : get_permalink( $product_id ),
						);

						wp_send_json( $user_bid );
					}
				} else {
					$url = array(
						'url' => get_permalink( $product_id ),
					);

					wp_send_json( $url );
				}
			} else {
				$url = array(
					'url' => get_permalink( sanitize_key( wp_unslash( $_POST['product'] ) ) ),
				);

				wp_send_json( $url );
			}

			die();
		}

		/**
		 * Add bid normal auction
		 *
		 * @param float      $bid bid amount.
		 * @param WC_Product $product Auction product.
		 * @param int        $userid User id.
		 * @param string     $currency Currency code.
		 * @param string     $date  Date when the bid is added.
		 * @param int        $end_auction Auction ends.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return boolean
		 */
		public function add_bid_normal_auction( $bid, $product, $userid, $currency, $date, $end_auction ) {
			$outbid                = false;
			$bid_added             = false;
			$send_better_bid_email = false;

			$overtime = $product->get_overtime();

			$product_id = $product->get_id();

			if ( $overtime ) {
				$date_end = $end_auction;
				$date_now = time();

				$interval_seconds = $date_end - $date_now;
				$interval_minutes = apply_filters( 'yith_wcact_interval_minutes', ceil( $interval_seconds / MINUTE_IN_SECONDS ), $interval_seconds, $product );
			}

			$set_overtime = false;

			$bids = YITH_Auctions()->bids;

			$current_price = $product->get_price();
			$last_bid_user = $bids->get_last_bid_user( $userid, $product_id );

			$exist_auctions = $bids->get_max_bid( $product_id );

			if ( $exist_auctions ) { // Auction product has at least one bid.
				$minimun_increment_amount = $product->get_minimum_increment_amount();

				if ( $minimun_increment_amount ) {
					$max_bid_manual = apply_filters( 'yith_wcact_max_bid_manual', $minimun_increment_amount + $product->get_current_bid(), $product );

					if ( $bid >= $max_bid_manual && ! $last_bid_user ) { // Customer doesn't have bids on auction product.
						$bid_added = true;

						if ( $exist_auctions->bid < $bid && (int) $exist_auctions->user_id !== (int) $userid ) {
							$send_better_bid_email = true;
						} else {
							if ( (int) $exist_auctions->user_id !== (int) $userid ) {
								$outbid = true;
							}
						}

						$set_overtime = true;

					} elseif ( $bid > $last_bid_user && $bid >= $max_bid_manual ) {
						$bid_added = true;

						$set_overtime = true;

						if ( $exist_auctions->bid < $bid && (int) $exist_auctions->user_id !== (int) $userid ) {
							$send_better_bid_email = true;
						} else {
							if ( (int) $exist_auctions->user_id !== (int) $userid ) {
								$outbid = true;
							}
						}
					} else {
						if ( $last_bid_user > $max_bid_manual ) {
							$max_bid_manual = $last_bid_user + $minimun_increment_amount;
						}

						if ( apply_filters( 'yith_wcact_show_message', true ) ) {
							wc_add_notice(
								sprintf(
									yith_wcact_auction_message( 4, $product ),
									wc_price( $max_bid_manual )
								),
								'error'
							);
						}
					}
				} else {
					if ( $bid > $current_price && ! $last_bid_user ) {  // Customer doesn't have bids on auction product.
						$bid_added = true;

						if ( $exist_auctions->bid < $bid && (int) $exist_auctions->user_id !== (int) $userid ) {
							$send_better_bid_email = true;
						} else {
							if ( (int) $exist_auctions->user_id !== (int) $userid ) {
								$outbid = true;
							}
						}

						$set_overtime = true;
					} elseif ( $bid > $last_bid_user && $bid > $current_price ) {
						$bid_added = true;

						if ( $exist_auctions->bid < $bid && (int) $exist_auctions->user_id !== (int) $userid ) {
							$send_better_bid_email = true;
						} else {
							if ( (int) $exist_auctions->user_id !== (int) $userid ) {
								$outbid = true;
							}
						}

						$set_overtime = true;
					} else {
						if ( apply_filters( 'yith_wcact_show_message', true ) ) {
							wc_add_notice(
								sprintf(
									yith_wcact_auction_message( 4, $product ),
									apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_current_bid() ), $product->get_current_bid(), $currency )
								),
								'error'
							);
						}
					}
				}
			} else { // No bids on auction product.
				$min_incr_amount = (int) ( $product->get_minimum_increment_amount() ) ? $product->get_minimum_increment_amount() : 1;
				$max_bid_manual  = $min_incr_amount + $product->get_current_bid();

				if ( apply_filters( 'yith_wcact_check_bid_increment', false, $product ) && $product->get_minimum_increment_amount() && $bid < $max_bid_manual ) {
					if ( apply_filters( 'yith_wcact_show_message', true ) ) {
						wc_add_notice(
							sprintf(
								yith_wcact_auction_message( 4, $product ),
								apply_filters( 'yith_wcact_auction_bid_increment_price', wc_price( $max_bid_manual ), $product, $product->get_current_bid(), $currency )
							),
							'error'
						);
					}
				} elseif ( $bid >= $current_price ) {
					$bid_added = true;

					$set_overtime = true;
				} else {
					if ( apply_filters( 'yith_wcact_show_message', true ) ) {
							wc_add_notice(
								sprintf(
									yith_wcact_auction_message( 4, $product ),
									apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_start_price() ), $product->get_start_price(), $currency )
								),
								'error'
							);
					}
				}
			}

			if ( apply_filters( 'yith_wcact_register_bid', $bid_added, $userid, $product_id, $bid, $date, $send_better_bid_email, $outbid ) ) {
				$bids->add_bid( $userid, $product_id, $bid, $date );

				update_post_meta( $product_id, 'current_bid', $bid ); // Save this data for use on automatic bid increment value.

				$args = compact( 'bid', 'date' );

				WC()->mailer();
				do_action( 'yith_wcact_successfully_bid', $userid, $product, $args );

				if ( $send_better_bid_email ) {
					WC()->mailer();
					do_action( 'yith_wcact_better_bid', $exist_auctions->user_id, $product, $bid, $exist_auctions->bid );
				}

				if ( apply_filters( 'yith_wcact_show_message', true ) ) {
					if ( $outbid ) {
						$message_successfully = apply_filters( 'yith_wcact_message_successfully', yith_wcact_auction_message( 3, $product ), $bid, $product, $userid );
						$notice_type          = apply_filters( 'yith_wcact_message_successfully_notice_type', 'notice', $bid, $product, $userid );

						wc_add_notice( $message_successfully, $notice_type );
					} else {
						$message_successfully = apply_filters( 'yith_wcact_message_successfully', yith_wcact_auction_message( 0, $product ), $bid, $product, $userid );
						$notice_type          = apply_filters( 'yith_wcact_message_successfully_notice_type', 'success', $bid, $product, $userid );

						wc_add_notice( $message_successfully, $notice_type );
					}
				}

				update_post_meta( $product_id, 'yith_wcact_new_bid', true );
			}

			$actual_price = $product->get_current_bid();

			$product->set_price( $actual_price );

			if ( $set_overtime && $overtime ) {
				if ( $interval_minutes <= $product->check_for_overtime() ) {
					$new_date_finish = apply_filters( 'yith_wcact_new_date_finish', strtotime( '+' . $overtime . 'minute', $date_end ), $overtime, $date_end, $product );

					if ( $new_date_finish ) {
						// Remove cronjob for winner email.
						if ( wp_next_scheduled( 'yith_wcact_send_emails_auction', array( $product_id ) ) ) {
							wp_clear_scheduled_hook( 'yith_wcact_send_emails_auction', array( $product_id ) );
						}

						// Add new cronjob with the new end auction (end_auction + overtime).
						if ( wp_next_scheduled( 'yith_wcact_send_emails_auction_overtime', array( $product_id ) ) ) {
							wp_clear_scheduled_hook( 'yith_wcact_send_emails_auction_overtime', array( $product_id ) );
						}

						wp_schedule_single_event( $new_date_finish, 'yith_wcact_send_emails_auction_overtime', array( $product_id ) );

						$product->set_end_date( $new_date_finish );

						$product->set_is_in_overtime( true );
					}
				}
			}

			$product->save();

			return true;
		}

		/**
		 * Add bid reverse auction
		 *
		 * @param float      $bid bid amount.
		 * @param WC_Product $product Auction product.
		 * @param int        $userid User id.
		 * @param string     $currency Currency code.
		 * @param string     $date  Date when the bid is added.
		 * @param int        $end_auction Auction ends.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return boolean
		 */
		public function add_bid_reverse_auction( $bid, $product, $userid, $currency, $date, $end_auction ) {
			$bids                  = YITH_Auctions()->bids;
			$current_price         = $product->get_price();
			$add_bid_on_database   = false;
			$send_better_bid_email = false;
			$product_id            = $product->get_id();
			$exist_auctions        = $bids->get_min_bid( $product_id );
			$is_outbid             = false;

			if ( $exist_auctions ) {
				$last_bid_user = $bids->get_last_bid_user( $userid, $product_id );

				$minimum_decrement_amount = $product->get_minimum_increment_amount(); // minimum decrement amount.

				if ( $minimum_decrement_amount ) {
					$min_bid_val = $current_price - $minimum_decrement_amount;

					$min_bid_manual = apply_filters( 'yith_wcact_min_bid_manual', ( $min_bid_val > 0 ) ? $min_bid_val : 0, $product );

					if ( $bid <= $min_bid_manual && ! $last_bid_user ) { // Customer doesn't have bids on auction product.
						$add_bid_on_database   = true;
						$send_better_bid_email = true;
					} elseif ( $bid < $last_bid_user && $bid <= $min_bid_manual ) {
						$add_bid_on_database   = true;
						$send_better_bid_email = true;
					} else {
						if ( $last_bid_user < $min_bid_manual ) {
							$min_bid_manual = $last_bid_user - $minimum_decrement_amount;
						}

						if ( apply_filters( 'yith_wcact_show_message', true ) ) {
							wc_add_notice(
								sprintf(
									yith_wcact_auction_message( 1, $product ),
									wc_price( $min_bid_manual )
								),
								'error'
							);
						}
					}
				} else {
					if ( $bid < $current_price && ! $last_bid_user ) {  // Customer doesn't have bids on auction product.
						$add_bid_on_database   = true;
						$send_better_bid_email = true;
					} elseif ( $bid < $last_bid_user && $bid < $current_price ) {
						$add_bid_on_database   = true;
						$send_better_bid_email = true;
					} else {
						if ( apply_filters( 'yith_wcact_show_message', true ) ) {
							$product_current_bid = $product->get_current_bid();

							wc_add_notice(
								sprintf(
									yith_wcact_auction_message( 1, $product ),
									apply_filters( 'yith_wcact_auction_product_price', wc_price( $product_current_bid ), $product_current_bid, $currency )
								),
								'error'
							);
						}
					}
				}
			} else {
				if ( $bid <= $current_price ) {
					$add_bid_on_database = true;
				} else {
					if ( apply_filters( 'yith_wcact_show_message', true ) ) {
						wc_add_notice(
							sprintf(
								yith_wcact_auction_message( 1, $product ),
								apply_filters( 'yith_wcact_auction_product_price', wc_price( $product->get_start_price() ), $product->get_start_price(), $currency )
							),
							'error'
						);
					}
				}
			}

			if ( $add_bid_on_database ) {  // Flag in true, add the bid on database.
				$bids->add_bid( $userid, $product_id, $bid, $date );
				$args = compact( 'bid', 'date' );

				WC()->mailer();
				do_action( 'yith_wcact_successfully_bid', $userid, $product, $args );

				update_post_meta( $product_id, 'current_bid', $bid ); // Save this data for use on automatic bid increment value.

				if ( $send_better_bid_email ) {
					$is_outbid = $this->send_better_bid_email_reverse_auction( $product, $exist_auctions->bid, $bid, $exist_auctions->user_id, $userid );
				}

				if ( apply_filters( 'yith_wcact_show_message', true ) ) {
					if ( $is_outbid ) {
						$message_successfully = apply_filters( 'yith_wcact_message_successfully', yith_wcact_auction_message( 5, $product ), $bid, $product, $userid );
						$notice_type          = apply_filters( 'yith_wcact_message_successfully_notice_type', 'notice', $bid, $product, $userid );

						wc_add_notice( $message_successfully, $notice_type );
					} else {
						$message_successfully = apply_filters( 'yith_wcact_message_successfully', yith_wcact_auction_message( 0, $product ), $bid, $product, $userid );
						$notice_type          = apply_filters( 'yith_wcact_message_successfully_notice_type', 'success', $bid, $product, $userid );

						wc_add_notice( $message_successfully, $notice_type );
					}
				}

				update_post_meta( $product_id, 'yith_wcact_new_bid', true );

				$actual_price = $product->get_current_bid();

				$product->set_price( $actual_price );

				// Overtime section.

				$overtime = $product->get_overtime();

				if ( $overtime ) {
					$date_end = $end_auction;
					$date_now = time();

					$interval_seconds = $date_end - $date_now;
					$interval_minutes = apply_filters( 'yith_wcact_interval_minutes', ceil( $interval_seconds / MINUTE_IN_SECONDS ), $interval_seconds, $product );

					if ( $interval_minutes <= $product->check_for_overtime() ) {
						$new_date_finish = apply_filters( 'yith_wcact_new_date_finish', strtotime( '+' . $overtime . 'minute', $date_end ), $overtime, $date_end, $product );

						// Remove cronjob for winner email.
						if ( wp_next_scheduled( 'yith_wcact_send_emails_auction', array( $product_id ) ) ) {
							wp_clear_scheduled_hook( 'yith_wcact_send_emails_auction', array( $product_id ) );
						}

						// Add new cronjob with the new end auction (end_auction + overtime).
						if ( wp_next_scheduled( 'yith_wcact_send_emails_auction_overtime', array( $product_id ) ) ) {
							wp_clear_scheduled_hook( 'yith_wcact_send_emails_auction_overtime', array( $product_id ) );
						}

						wp_schedule_single_event( $new_date_finish, 'yith_wcact_send_emails_auction_overtime', array( $product_id ) );

						$product->set_end_date( $new_date_finish );

						$product->set_is_in_overtime( true );
					}
				}

				$product->save();

			}

			return true;
		}

		/**
		 * Send_better_bid_email_reverse_auction
		 *
		 * Check if it's necessary to send a better bid email
		 *
		 * @param  WC_Product $product Product.
		 * @param  float      $exists_auction_bid User bid.
		 * @param  float      $bid Product bid.
		 * @param  WP_User    $exists_auction_user_id User id.
		 * @param  WP_User    $user_id User id.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return boolean
		 */
		public function send_better_bid_email_reverse_auction( $product, $exists_auction_bid, $bid, $exists_auction_user_id, $user_id ) {
			$is_outbid = true;

			if ( $exists_auction_bid > $bid && $exists_auction_user_id !== $user_id ) {
				WC()->mailer();
				do_action( 'yith_wcact_better_bid', $exists_auction_user_id, $product, $bid, $exists_auction_bid, true );
				$is_outbid = false;
			}

			return $is_outbid;
		}

		/**
		 * Reshedule auction product
		 *
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_reshedule_product() {
			check_ajax_referer( 'reschedule-product', 'security' );

			if ( isset( $_POST['id'] ) ) {
				$id      = array_map( 'sanitize_title', (array) wp_unslash( $_POST['id'] ) );
				$product = wc_get_product( $id['ID'] );

				if ( $product && 'auction' === $product->get_type() ) {
					ywcact_reschedule_auction_product( $product );

					$array = array(
						'product_id' => $id,
						'url'        => get_edit_post_link( $id ),
					);

					wp_send_json( $array );
				}
			}

			die();
		}

		/**
		 * Update auction list
		 *
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_update_auction_list() {
			check_ajax_referer( 'update-template', 'security' );

			$instance         = YITH_Auctions()->bids;
			$user_id          = get_current_user_id();
			$auctions_by_user = $instance->get_auctions_by_user( $user_id );
			$currency         = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : get_woocommerce_currency();
			$auction          = array();

			foreach ( $auctions_by_user as $valor ) {
				$product = wc_get_product( $valor->auction_id );

				if ( ! $product || 'auction' !== $product->get_type() ) {
					continue;
				}

				$max_bid = $instance->get_max_bid( $valor->auction_id );

				if ( (int) $max_bid->user_id === (int) $user_id ) {
					$color = 'yith-wcact-max-bidder';
				} else {
					$color = 'yith-wcact-outbid-bidder';
				}

				$last_bid_user = $instance->get_last_bid_user( $user_id, $valor->auction_id );

				$auction[] = array(
					'product_id'   => $product->get_id(),
					'price'        => wc_price( $product->get_price(), array( 'currency' => $currency ) ),
					'product_name' => get_the_title( $valor->auction_id ),
					'product_url'  => get_the_permalink( $valor->auction_id ),
					'image'        => $product->get_image( 'thumbnail' ),
					'my_bid'       => apply_filters( 'yith_wcact_auction_product_price', wc_price( $last_bid_user ), $last_bid_user, $currency ),
					'status'       => $this->yith_wcact_get_status( $product, $valor, $user_id, $instance ),
					'color'        => $color,
				);
			}

			wp_send_json( $auction );
		}

		/**
		 * Get status of an auctions
		 *
		 * @param WC_Product $product Product auction.
		 * @param object     $valor   Object with auction information.
		 * @param int        $user_id User id.
		 * @param object     $instance Bids class instance.
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function yith_wcact_get_status( $product, $valor, $user_id, $instance ) {
			if ( $product->is_type( 'auction' ) && $product->is_closed() ) {
				$max_bid = $instance->get_max_bid( $valor->auction_id );

				if ( (int) $max_bid->user_id === (int) $user_id && ! $product->get_auction_paid_order() && ( ! $product->has_reserve_price() || ( $product->has_reserve_price() && $max_bid->bid >= $product->get_reserve_price() ) ) ) {
					/**
					 * APPLY_FILTERS: yith_wcact_get_checkout_url
					 *
					 * Filter the URL to redirect to pay the won auction.
					 *
					 * @param string $url        URL
					 * @param int    $product_id Product ID
					 *
					 * @return string
					 */
					$url    = add_query_arg( array( 'yith-wcact-pay-won-auction' => $product->get_id() ), apply_filters( 'yith_wcact_get_checkout_url', wc_get_checkout_url(), $product->get_id() ) );
					$status = $this->print_won_auctions( $url );
				} else {
					$status = $this->status_closed();
				}
			} else {
				$status = $this->status_started();
			}

			return $status;
		}

		/**
		 * Print won auctions
		 *
		 * @param string $url Url.
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function print_won_auctions( $url ) {
			$won     = apply_filters( 'yith_wcact_you_won_this_auction_label', esc_html__( 'You won this auction', 'yith-auctions-for-woocommerce' ) );
			$pay_now = apply_filters( 'yith_wcact_pay_now_label', esc_html__( 'Pay now', 'yith-auctions-for-woocommerce' ) );

			return '<span>' . $won . '</span><a href="' . $url . '" class="auction_add_to_cart_button ywcact-auction-buy-now-button button alt" id="yith-wcact-auction-won-auction">' . $pay_now . '</a>';
		}

		/**
		 * Status closed
		 *
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function status_closed() {
			$closed = esc_html__( 'Closed', 'yith-auctions-for-woocommerce' );

			return '<span>' . apply_filters( 'yith_wcact_auction_my_account_status_closed', $closed ) . '</span>';
		}

		/**
		 * Status started
		 *
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function status_started() {
			$started = esc_html__( 'Started', 'yith-auctions-for-woocommerce' );

			return '<span>' . apply_filters( 'yith_wcact_auction_my_account_status_open', $started ) . '</span>';
		}

		/**
		 * Update list bid tab
		 *
		 * @since  1.1.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function update_list_bids() {
			check_ajax_referer( 'update-list-bids', 'security' );

			if ( isset( $_POST['product'] ) ) {
				$product = wc_get_product( sanitize_text_field( wp_unslash( $_POST['product'] ) ) );

				if ( 'auction' === $product->get_type() ) {
					$currency       = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : get_woocommerce_currency();
					$templates      = array();
					$datetime       = $product->get_end_date();
					$auction_finish = ( $datetime ) ? $datetime : null;
					$date           = strtotime( 'now' );

					$countdown_style = get_option( 'yith_wcact_countdown_style', 'default' );

					$countdown_blocks = ( 'big-blocks' === $countdown_style ) ? 'yith-wcact-blocks' : '';

					$yith_wcact_class = 'yith-wcact-timeleft-' . $countdown_style . ' yith-wcact-timeleft-product-page';

					$time_end_auction  = $auction_finish;
					$color_number      = get_option( 'yith_wcact_customization_countdown_color_numbers', 24 );
					$color_unit        = get_option( 'yith_wcact_customization_countdown_color_unit', 'hours' );
					$time_change_color = strtotime( ( sprintf( '-%d %s', $color_number, $color_unit ) ), (int) $time_end_auction );

					if ( $date > $time_change_color ) {
						$yith_wcact_class .= ' yith-wcact-countdown-last-minute';
						$time_change_color = 0;
					}

					$args = apply_filters(
						'yith_wcact_add_auction_timeleft_args',
						array(
							'product'          => $product,
							'currency'         => $currency,
							'product_id'       => $product->get_id(),
							'auction_finish'   => $auction_finish,
							'date'             => $date,
							'last_minute'      => isset( $time_change_color ) ? $auction_finish - $time_change_color : 0,
							'total'            => $auction_finish - $date,
							'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default',
							'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',
						),
						$product
					);

					ob_start();
					wc_get_template( 'list-bids.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
					$templates['list_bids']   = ob_get_clean();
					$templates['current_bid'] = ( 'yes' === $product->get_auction_sealed() ) ? esc_html__( 'This is a sealed auction.', 'yith-auctions-for-woocommerce' ) : wc_price( $product->get_price(), array( 'currency' => $currency ) );
					ob_start();
					wc_get_template( 'max-bidder.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
					$templates['max_bid'] = ob_get_clean();
					ob_start();
					wc_get_template( 'reserve_price_and_overtime.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
					$templates['reserve_price_and_overtime'] = ob_get_clean();

					if ( $product->is_in_overtime() ) {
						ob_start();
						/**YITH_Auction_Frontend_Premium::get_instance()->add_auction_timeleft( $product );*/
						$templates['timeleft']     = true;
						$templates['new_end_date'] = $product->get_end_date();
					}

					wp_send_json( $templates );
				}
			}
		}

		/**
		 * Delete customer bid
		 *
		 * @since  1.1.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function delete_customer_bid() {
			check_ajax_referer( 'delete-bid', 'security' );

			$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
			$user_id    = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : '';
			$datetime   = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
			$bid        = isset( $_POST['bid'] ) ? sanitize_text_field( wp_unslash( $_POST['bid'] ) ) : '';

			$delete_id = isset( $_POST['delete_id'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_id'] ) ) : '';

			$instance = YITH_Auctions()->bids;

			$instance->delete_customer_bid( $delete_id );
			$product = wc_get_product( $product_id );
			yit_delete_prop( $product, 'current_bid' );

			$instance   = YITH_Auctions()->bids;
			$max_bidder = $instance->get_max_bid( $product->get_id() );

			if ( $max_bidder ) {
				$price = $product->get_price();

				yit_save_prop( $product, 'current_bid', $price );
			}

			$args = compact( 'bid', 'datetime' );
			WC()->mailer();
			do_action( 'yith_wcact_auction_delete_customer_bid', $product_id, $user_id, $args );
			do_action( 'yith_wcact_auction_delete_customer_bid_admin', $product_id, $user_id, $args );

			die();
		}

		/**
		 * Resend Winner Email
		 *
		 * @since  1.2.2
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function resend_winner_email() {
			if ( isset( $_POST['id'] ) ) { // phpcs:ignore
				$id      = $_POST['id']; // phpcs:ignore
				$product = wc_get_product( $id['ID'] );

				$instance   = YITH_Auctions()->bids;
				$max_bidder = $instance->get_max_bid( $product->get_id() );

				$user = get_user_by( 'id', $max_bidder->user_id );

				$product->set_send_winner_email( false );

				$product->save();

				WC()->mailer();

				/**
				 * DO_ACTION: yith_wcact_auction_winner
				 *
				 * Allow to fire some action when the auction has ended and has a winner.
				 *
				 * @param WC_Product $product Product object
				 * @param WP_User    $user    User object
				 * @param object     $max_bid Max bid object
				 */
				do_action( 'yith_wcact_auction_winner', $product, $user, $max_bidder );

				$args = array(
					'post_id' => $id['ID'],
					'product' => $product,
				);

				ob_start();

				wc_get_template( 'admin-auction-status.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'admin/' );
				$templates['resend_winner_email'] = ob_get_clean();

				wp_send_json( $templates );
			}

			die();
		}

		/**
		 * Pay fee auction
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function pay_fee() {
			check_ajax_referer( 'add-bid', 'security' );

			if ( isset( $_POST['fee_price'] ) && isset( $_POST['product_id'] ) ) {
				$fee_price      = sanitize_text_field( wp_unslash( $_POST['fee_price'] ) );
				$product_fee_id = get_option( 'yith_wcact_fee_auction_product_id', '' );
				$auction_id     = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );

				if ( $product_fee_id ) {
					$product_fee = wc_get_product( $product_fee_id );

					if ( $product_fee && $product_fee instanceof WC_Product ) {
						$fee_is_in_cart = false;

						if ( ! WC()->cart->is_empty() ) {
							foreach ( WC()->cart->get_cart() as $cart_item ) {
								if ( ! empty( $cart_item['product_id'] ) && ! empty( $cart_item['yith_wcact_auction_id'] ) ) {
									if ( (int) $cart_item['product_id'] === (int) $product_fee_id && (int) $auction_id === (int) $cart_item['yith_wcact_auction_id'] ) {
										$fee_is_in_cart = true;
										break;
									}
								}
							}
						}

						if ( ! $fee_is_in_cart ) {
							WC()->cart->add_to_cart(
								apply_filters( 'yith_wcact_product_add_to_cart', $product_fee_id ),
								1,
								0,
								0,
								array(
									'yith_wcact_if_fee_product' => true,
									'yith_wcact_fee_value' => $fee_price,
									'yith_wcact_auction_id' => $auction_id,
								)
							);
						}

						$args = array(
							'cart_url' => apply_filters( 'yith_wcact_pay_fee_url', wc_get_checkout_url(), $auction_id ),
						);

						wp_send_json( $args );
					}
				}
			}

			die();
		}

		/**
		 * Update watchlist list
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_update_watchlist_list() {
			check_ajax_referer( 'update-template', 'security' );

			$instance         = YITH_Auctions()->bids;
			$user_id          = get_current_user_id();
			$auctions_by_user = $instance->get_watchlist_product_by_user( $user_id );
			$currency         = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : get_woocommerce_currency();
			$auction          = array();

			foreach ( $auctions_by_user as $valor ) {
				$product = wc_get_product( $valor->auction_id );

				if ( ! $product ) {
					continue;
				}

				$product_name = get_the_title( $valor->auction_id );
				$product_url  = get_the_permalink( $valor->auction_id );

				$auction_product_type = $product->get_auction_type();

				$max_bid = $auction_product_type && 'reverse' === $auction_product_type ? $instance->get_min_bid( $valor->auction_id ) : $instance->get_max_bid( $valor->auction_id );

				if ( $max_bid && (int) $max_bid->user_id === (int) $user_id ) {
					$color = 'yith-wcact-max-bidder';
				} else {
					$color = 'yith-wcact-outbid-bidder';
				}

				$my_bid = $instance->get_last_bid_user( $user_id, $valor->auction_id );

				$my_bid = ! empty( $my_bid ) ? $my_bid : 0;

				$auction_date = $product->is_start() ? $product->get_end_date() : $product->get_start_date();

				ob_start();

				?>
					<div class="yith-wcact-timeleft-widget-watchlist">
				<?php
				if ( ! $product->is_closed() ) {
					$args = array(
						'product'          => $product,
						'auction_finish'   => $auction_date,
						'date'             => strtotime( 'now' ),
						'last_minute'      => isset( $time_change_color ) ? $auction_date - $time_change_color : 0,
						'total'            => $auction_date - current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
						'yith_wcact_class' => isset( $yith_wcact_class ) ? $yith_wcact_class : 'yith-wcact-timeleft-default',
						'yith_wcact_block' => isset( $countdown_blocks ) ? $countdown_blocks : '',

					);

					wc_get_template( 'auction-timeleft.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
				} else {
					echo esc_html__( 'Finished', 'yith-auctions-for-woocommerce' );
				}
				?>
					</div>
				<?php

				$timeleft = ob_get_clean();

				$auction[] = array(
					'url_remove'   => add_query_arg(
						array(
							'remove_from_watchlist' => $product->get_id(),
							'user_id'               => $user_id,
						),
						yith_wcact_get_watchlist_url()
					),
					'product_id'   => $product->get_id(),
					'price'        => wc_price( $product->get_price(), array( 'currency' => $currency ) ),
					'product_name' => $product_name,
					'product_url'  => $product_url,
					'image'        => $product->get_image( 'thumbnail' ),
					'my_bid'       => apply_filters( 'yith_wcact_auction_my_last_bid_watchlist', wc_price( $my_bid ), $my_bid, $currency ),
					'timeleft'     => $timeleft,
					'color'        => $color,
				);

			}

			wp_send_json( $auction );
		}

		/**
		 * Add to watchlist
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function add_to_watchlist() {
			check_ajax_referer( 'add-bid', 'security' );

			$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : false;
			$user_id    = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : false;

			$product = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() ) {
				$instance             = YITH_Auctions()->bids;
				$product_in_watchlist = $instance->is_product_in_watchlist( $product_id, $user_id );

				if ( ! $product_in_watchlist ) {
					$added = $instance->add_product_to_watchlist( $product_id, $user_id );

					if ( $added ) {
						$templates = array();

						$templates['template_watchlist_button'] = do_shortcode( '[yith_wcact_add_to_watchlist product_id=' . $product_id . ']' );

						if ( $templates ) {
							wp_send_json( $templates );
						}
					}
				}
			}

			die();
		}

		/**
		 * Remove from Watchlist
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function remove_from_watchlist() {
			check_ajax_referer( 'add-bid', 'security' );

			$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : false;
			$user_id    = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : false;
			$product    = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() ) {
				$instance             = YITH_Auctions()->bids;
				$product_in_watchlist = $instance->is_product_in_watchlist( $product_id, $user_id );

				if ( $product_in_watchlist ) {
					$removed = $instance->remove_product_to_watchlist( $product_id, $user_id );

					if ( $removed ) {
						$templates = array();

						$templates['template_watchlist_button'] = do_shortcode( '[yith_wcact_add_to_watchlist product_id=' . $product_id . ']' );

						if ( $templates ) {
							wp_send_json( $templates );
						}
					}
				}
			}
		}

		/**
		 * Fragment product list
		 *
		 * Get watchlist product list
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function get_fragment_product_list() {
			check_ajax_referer( 'add-bid', 'security' );

			$user_id = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : false;

			if ( $user_id ) {
				$auction_bids       = YITH_Auctions()->bids;
				$watchlist_products = $auction_bids->get_watchlist_product_by_user( $user_id );

				$args = array(
					'watchlist_products' => $watchlist_products,
					'user_id'            => $user_id,
				);

				ob_start();

				wc_get_template( 'widgets/ywcact-watchlist-products.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );

				$templates['fragmets_watchlist_product'] = ob_get_clean();

				if ( $templates ) {
					wp_send_json( $templates );
				}
			}

			die();
		}

		/**
		 * Add to watchlist no logged users
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function redirect_to_my_account_watchlist_button() {
			check_ajax_referer( 'add-bid', 'security' );

			if ( ! is_user_logged_in() && ( ! defined( 'YITH_WELRP' ) || ( defined( 'YITH_WELRP' ) && 'no' === get_option( 'yith_wcact_enable_login_popup', 'no' ) ) ) ) {
				/**
				 * APPLY_FILTERS: yith_wcact_redirect_url
				 *
				 * Filter the URL to redirect when a guest user tries to place a bid.
				 *
				 * @param string $redirect_url Redirect URL
				 *
				 * @return string
				 */
				$account = apply_filters( 'yith_wcact_redirect_url', wc_get_page_permalink( 'myaccount' ) );

				if ( isset( $_POST['product_id'] ) ) {
					$product_id = sanitize_key( wp_unslash( $_POST['product_id'] ) );

					/**
					 * APPLY_FILTERS: yith_wcact_get_product_permalink_redirect_to_my_account_watchlist
					 *
					 * Filter the product URL to redirect when a guest user tries to place a bid.
					 *
					 * @param string $redirect_url Redirect URL
					 * @param int    $product_id   Product ID
					 *
					 * @return string
					 */
					$get_product_permalink = apply_filters( 'yith_wcact_get_product_permalink_redirect_to_my_account_watchlist', rawurlencode( get_permalink( $product_id ) ), $product_id );
					$url_to_redirect       = add_query_arg(
						array(
							'redirect_after_login' => $get_product_permalink,
							'watchlist_notice'     => true,
						),
						$account
					);

					wc_add_notice( esc_html__( 'You need to be logged in, before you can add a product in your watchlist', 'yith-auctions-for-woocommerce' ), 'error' );

					$array = array(
						'product_id' => $product_id,
						'url'        => $url_to_redirect,
					);

				}

				wp_send_json( $array );
			}

			die();
		}

		/**
		 * Load popup bidders table
		 *
		 * @since  3.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return void
		 */
		public function load_bidders_table() {
			check_ajax_referer( 'display-bids', 'security' );

			$modal      = array(
				'title'   => '',
				'content' => '',
				'footer'  => '',
			);
			$product_id = isset( $_POST['product_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : false;

			if ( $product_id ) {
				$product      = wc_get_product( $product_id );
				$instance     = YITH_Auctions()->bids;
				$current_page = isset( $_POST['current_page'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['current_page'] ) ) : 1;
				$per_page     = apply_filters( 'yith_wcact_admin_auction_bid_limit', 10, $product );
				$offset       = ( ( $current_page - 1 ) * $per_page );

				$auction_list = $instance->get_bids_auction( $product_id, false, $offset, $per_page );
				$all_auctions = count( $instance->get_bids_auction( $product_id ) );

				$args = array(
					'post_id'       => $product_id,
					'auction_list'  => $auction_list,
					'product'       => $product,
					'pagination'    => true,
					'current_page'  => $current_page,
					'total_pages'   => ceil( $all_auctions / $per_page ),
					'bidders_count' => isset( $_POST['bidders_count'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['bidders_count'] ) ) : 0,
				);

				ob_start();

				wc_get_template( 'admin-list-bids.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'admin/' );

				$content = ob_get_clean();

				if ( $content ) {
					$bids          = isset( $_POST['bidders_count'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['bidders_count'] ) ) : 0;
					$product_title = $product->get_title();

					/* translators: %1s: int number %2s: Product title*/
					$modal['title']   = sprintf( esc_html_x( '%1$1s bids for %2$2s', '50 bids for Auction title', 'yith-auctions-for-woocommerce' ), $bids, $product_title );
					$modal['content'] = $content;
				}
			} else {
				$modal['content'] = esc_html__( 'An error occurred while loading the bids.', 'yith-auctions-for-woocommerce' );
			}

			$modal = apply_filters( 'yith_wcact_load_bidders_table_modal', $modal, $product_id );

			wp_send_json( $modal );
			die();
		}

		/**
		 * Unsubscribe auctions
		 *
		 * @since  3.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return void
		 */
		public function unsubscribe_auctions() {
			if ( !  isset( $_POST['security'] ) || ( isset( $_POST['security'] ) && ! wp_verify_nonce( $_POST['security'], 'ajax-unsubscribe-auctions' ) ) ) { // phpcs:ignore
				die( 'Busted!' );
			}

			$auctions = isset( $_POST['auctions'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['auctions'] ) ) : array();
			$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

			if ( $email && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$instance = YITH_Auctions()->bids;

				foreach ( $auctions as $auction ) {
					$instance->delete_follower( $auction, $email );
				}

				ob_start();

				$args = apply_filters(
					'yith_wcact_succesfully_unsubscribe_args',
					array(
						'button_link' => esc_url( get_home_url() ),
						'button_text' => esc_html__( 'Visit our site', 'yith-auctions-for-woocommerce' ),
					)
				);

				wc_get_template( 'successfully-unsubscribe.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/unsubscribe/' );

				$content = ob_get_clean();

				if ( ! $content ) {
					$content = esc_html__( 'An error occurred while process your request.', 'yith-auctions-for-woocommerce' );
				}
			} else {
				$content = esc_html__( 'An error occurred while process your request.', 'yith-auctions-for-woocommerce' );
			}

			wp_send_json( $content );

			die();
		}
	}
}
