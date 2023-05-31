<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Cron Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WCACT_Cron' ) ) {
	/**
	 * YITH_WCACT_Cron_emails
	 *
	 * @since 1.0.0
	 */
	class YITH_WCACT_Cron {

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function __construct() {
			/* == Auction is about to end == */
			add_action( 'yith_wcact_register_cron_email', array( $this, 'cron_emails' ) ); // Create cronjob when auction is about to end.
			add_action( 'yith_wcact_send_emails', array( $this, 'yith_wcact_send_emails_bidders' ), 10, 1 );// Send email when cronjob is executed.

			/* == Auction finished == */
			add_action( 'yith_wcact_register_cron_email_auction', array( $this, 'cron_emails_auctions' ) );
			add_action( 'yith_wcact_send_emails_auction', array( $this, 'yith_wcact_send_emails' ), 10, 1 ); // Send email to customer or admin on auction finish.

			/* == Auction Overtime == */
			add_action( 'yith_wcact_send_emails_auction_overtime', array( $this, 'yith_wcact_send_emails' ), 10, 1 ); // Send email to customer or admin  on auction finish.

			/* == Winner email notification daily == */
			add_action( 'yith_wcact_cron_winner_email_notification', array( $this, 'cron_resend_winner_email' ) );

			/* == Reschedule not paid options == */
			add_action( 'yith_wcact_register_reschedule_not_paid_cron', array( $this, 'register_reschedule_not_paid_cron' ), 10, 2 );
			add_action( 'yith_wcact_reschedule_not_paid_event', array( $this, 'reschedule_auction_first_step' ), 10, 3 );
			add_action( 'yith_wcact_reschedule_not_paid_event_second_time', array( $this, 'reschedule_auction_second_step' ), 10, 3 );
			add_action( 'yith_wcact_reschedule_not_paid_event_third_time', array( $this, 'reschedule_auction_third_step' ) );

			/* == Auction started == */
			add_action( 'yith_wcact_register_cron_email_auction', array( $this, 'create_single_cron_on_started_for_scheduled_auctions' ), 20 );
			add_action( 'yith_wcact_auction_status_migration', array( $this, 'create_single_cron_on_started_for_scheduled_auctions' ), 20 );
			add_action( 'yith_wcact_auction_started_cron', array( $this, 'change_auction_status_on_started' ), 10, 2 );
		}

		/**
		 * Create single event
		 * Create single event for send emails to bidders and followers when the auction is about to end
		 *
		 * @param int $product_id Product id.
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function cron_emails( $product_id ) {
			if ( 'yes' === get_option( 'yith_wcact_settings_cron_auction_send_emails', 'no' ) || 'yes' === get_option( 'yith_wcact_notify_followers_auction_about_to_end', 'no' ) ) {
				$product          = wc_get_product( $product_id );
				$time_end_auction = $product->get_end_date( 'edit' );
				$number           = get_option( 'yith_wcact_settings_cron_auction_number_days' );
				$unit             = get_option( 'yith_wcact_settings_cron_auction_type_numbers' );

				/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
				$time_send_email = strtotime( ( sprintf( '-%d %s', $number, $unit ) ), (int) $time_end_auction );
				wp_schedule_single_event( $time_send_email, 'yith_wcact_send_emails', array( $product_id ) );
			}
		}

		/**
		 * Auction about to end cron
		 * Fire cronjob event to send emails to bidders or followers when the auction is about to end
		 *
		 * @param int $product_id Product id.
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_send_emails_bidders( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() && ! $product->is_closed() && ! $product->get_is_closed_by_buy_now() ) {
				// Auction about to end bidders.
				if ( 'yes' === get_option( 'yith_wcact_settings_cron_auction_send_emails', 'no' ) ) {
					$query = YITH_Auctions()->bids;
					$users = $query->get_users( $product_id );

					foreach ( $users as $id => $user_id ) {
						WC()->mailer();

						/**
						 * DO_ACTION: yith_wcact_end_auction
						 *
						 * Allow to fire some action when the auction is about to end.
						 *
						 * @param int $user_id    User ID
						 * @param int $product_id Product ID
						 */
						do_action( 'yith_wcact_end_auction', (int) $user_id->user_id, $product_id );
					}
				}

				// Auction about to end followers.
				if ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_allow_subscribe', 'no' ) && 'yes' === get_option( 'yith_wcact_notify_followers_auction_about_to_end', 'no' ) ) {
					$product = wc_get_product( $product_id );
					$users   = $product->get_followers_list();

					if ( $users ) {
						foreach ( $users as $key => $user ) {
							WC()->mailer();

							/**
							 * DO_ACTION: yith_wcact_end_auction
							 *
							 * Allow to fire some action when the auction is about to end.
							 *
							 * @param int $user_email User email
							 * @param int $product_id Product ID
							 */
							do_action( 'yith_wcact_end_auction', $user->email, $product_id );
						}
					}
				}
			}
		}

		/**
		 * Create single event when auction ends
		 * Create single event for send emails to user when the auction is about to end
		 *
		 * @param int $product_id Product id.
		 * @since  1.0.9
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function cron_emails_auctions( $product_id ) {
			$product = wc_get_product( $product_id );
			$time    = $product->get_end_date( 'edit' );

			wp_schedule_single_event( $time, 'yith_wcact_send_emails_auction', array( $product_id ) );
		}

		/**
		 * Sends email
		 * Send emails when end auction and admin check this option = true
		 *
		 * @param int $product_id Product id.
		 * @since  1.0.9
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_send_emails( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() && $product->is_closed() ) {
				$instance = YITH_Auctions()->bids;
				$max_bid  = $instance->get_max_bid( $product_id );

				if ( ! $product->get_is_closed_by_buy_now() && ( 'publish' === $product->get_status() || 'private' === $product->get_status() ) ) {
					if ( $product->has_reserve_price() && $product->get_price() < $product->get_reserve_price() && $max_bid ) { // Admin email.
						WC()->mailer();

						if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {
							$vendor = yith_get_vendor( $product, 'product' );

							if ( $vendor->is_valid() && ! user_can( $vendor->id, 'manage_options' ) ) {
								/**
								 * DO_ACTION: yith_wcact_vendor_not_reached_reserve_price
								 *
								 * Allow to fire some action when the auction from the vendor has not reached the reserve price.
								 *
								 * @param WC_Product  $product Product object
								 * @param YITH_Vendor $vendor  Vendor object
								 */
								do_action( 'yith_wcact_vendor_not_reached_reserve_price', $product, $vendor );
							} else {
								/**
								 * DO_ACTION: yith_wcact_not_reached_reserve_price
								 *
								 * Allow to fire some action when the auction has not reached the reserve price.
								 *
								 * @param WC_Product $product Product object
								 */
								do_action( 'yith_wcact_not_reached_reserve_price', $product );
							}
						} else {
							do_action( 'yith_wcact_not_reached_reserve_price', $product );
						}

						if ( $max_bid ) {
							$user = get_user_by( 'id', $max_bid->user_id );

							WC()->mailer();

							// Send email to max bidder but not reached reserve price.
							/**
							 * DO_ACTION: yith_wcact_not_reached_reserve_price_max_bidder
							 *
							 * Allow to fire some action when the auction has not reached the reserve price.
							 *
							 * @param WC_Product $product Product object
							 * @param WP_User    $user    User object
							 */
							do_action( 'yith_wcact_not_reached_reserve_price_max_bidder', $product, $user );
						}

						// reschedule auction no supera reserve price.

						$time = $product->get_automatic_reschedule_time();

						if ( ! empty( $time ) ) {
							if ( $time['time_quantity'] > 0 ) {
								$end_auction = $product->get_end_date( 'edit' );

								/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
								$new_end_auction = strtotime( ( sprintf( '+%d %s', $time['time_quantity'], $time['time_unit'] ) ), $end_auction );

								$product->set_end_date( $new_end_auction );
								$product->save();
								$product->update_auction_status( true );
								$this->cron_emails( $product_id );
								$this->cron_emails_auctions( $product_id );

								// Send email to admin that product was reschedule automatically.

								/**
								 * DO_ACTION: yith_wcact_auction_email_rescheduled
								 *
								 * Allow to fire some action when the auction has been rescheduled.
								 *
								 * @param WC_Product $product Product object
								 */
								do_action( 'yith_wcact_auction_email_rescheduled', $product );

								/**
								 * DO_ACTION: yith_wcact_after_automatic_reschedule_time
								 *
								 * Allow to fire some action when the auction has been rescheduled.
								 *
								 * @param WC_Product $product Product object
								 */
								do_action( 'yith_wcact_after_automatic_reschedule_time', $product );
							}
						}
					} else {
						if ( $max_bid ) { // Then we send the email to the winner with the button for paying the order.
							$user = get_user_by( 'id', $max_bid->user_id );

							WC()->mailer();

							// Send email to winner customer.
							/**
							 * DO_ACTION: yith_wcact_auction_winner
							 *
							 * Allow to fire some action when the auction has ended and has a winner.
							 *
							 * @param WC_Product $product Product object
							 * @param WP_User    $user    User object
							 * @param object     $max_bid Max bid object
							 */
							do_action( 'yith_wcact_auction_winner', $product, $user, $max_bid );

							/**
							 * DO_ACTION: yith_wcact_email_winner_admin
							 *
							 * Allow to fire some action when the auction has ended and has a winner.
							 *
							 * @param WC_Product $product Product object
							 * @param WP_User    $user    User object
							 */
							do_action( 'yith_wcact_email_winner_admin', $product, $user );

							if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {
								$vendor = yith_get_vendor( $product, 'product' );

								if ( $vendor->is_valid() && ! user_can( $vendor->id, 'manage_options' ) ) {
									/**
									 * DO_ACTION: yith_wcact_email_winner_vendor
									 *
									 * Allow to fire some action when the auction from the vendor has ended and has a winner.
									 *
									 * @param WC_Product  $product Product object
									 * @param YITH_Vendor $vendor  Vendor object
									 * @param WP_User     $user    User object
									 */
									do_action( 'yith_wcact_email_winner_vendor', $product, $vendor, $user );
								}
							}

							/**
							 * DO_ACTION: yith_wcact_register_reschedule_not_paid_cron
							 *
							 * Allow to register the cron event to reschedule non-paid auctions.
							 *
							 * @param WC_Product $product Product object
							 * @param WP_User    $user    User object
							 */
							do_action( 'yith_wcact_register_reschedule_not_paid_cron', $product, $user );

							// Send email to users who did not win the auction after it is finished.
							if ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_no_winner_email', 'no' ) ) {
								$users = $instance->get_users( $product_id );

								if ( $users ) {
									foreach ( $users as $bidder ) {
										if ( $bidder->user_id !== $max_bid->user_id ) {
											$user = get_user_by( 'id', $bidder->user_id );

											WC()->mailer();

											/**
											 * DO_ACTION: yith_wcact_auction_no_winner
											 *
											 * Allow to send the email to users who didn't win the auction when it's finished.
											 *
											 * @param WC_Product $product Product object
											 * @param WP_User    $user    User object
											 */
											do_action( 'yith_wcact_auction_no_winner', $product, $user );
										}
									}
								}
							}
						} else { // The auction is finished without any bids.
							WC()->mailer();

							if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {
								$vendor = yith_get_vendor( $product, 'product' );

								if ( $vendor->is_valid() && ! user_can( $vendor->id, 'manage_options' ) ) {
									/**
									 * DO_ACTION: yith_wcact_vendor_finished_without_any_bids
									 *
									 * Allow to fire some action when the auction from the vendor has ended without any bid.
									 *
									 * @param WC_Product  $product Product object
									 * @param YITH_Vendor $vendor  Vendor object
									 */
									do_action( 'yith_wcact_vendor_finished_without_any_bids', $product, $vendor );
								} else {
									// Reschedule time.
									$time = $product->get_automatic_reschedule_time();

									if ( ! empty( $time ) ) {
										if ( $time['time_quantity'] > 0 ) {
											$end_auction = $product->get_end_date( 'edit' );
											/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
											$new_end_auction = strtotime( ( sprintf( '+%d %s', $time['time_quantity'], $time['time_unit'] ) ), $end_auction );
											$product->set_end_date( $new_end_auction );
											$product->save();
											$product->update_auction_status( true );
											$this->cron_emails( $product_id );
											$this->cron_emails_auctions( $product_id );
										}

										// Send email to admin that product was reschedule automatically.
										do_action( 'yith_wcact_auction_email_rescheduled', $product );
									} else {
										/**
										 * DO_ACTION: yith_wcact_finished_without_any_bids
										 *
										 * Allow to fire some action when the auction has ended without any bid.
										 *
										 * @param WC_Product  $product Product object
										 */
										do_action( 'yith_wcact_finished_without_any_bids', $product );
									}
								}
							} else {
								// Reschedule time.
								$time = $product->get_automatic_reschedule_time();

								if ( ! empty( $time ) ) {
									if ( $time['time_quantity'] > 0 ) {
										$end_auction     = $product->get_end_date( 'edit' );
										$new_end_auction = strtotime( ( sprintf( '+%d %s', $time['time_quantity'], $time['time_unit'] ) ), $end_auction );
										$product->set_end_date( $new_end_auction );
										$product->save();
										$product->update_auction_status( true );
										$this->cron_emails( $product_id );
										$this->cron_emails_auctions( $product_id );

										// Send email to admin that product was reschedule automatically.
										do_action( 'yith_wcact_auction_email_rescheduled', $product );
									}
								} else {
									do_action( 'yith_wcact_finished_without_any_bids', $product );
								}
							}
						}
					}
				}

				// Update auction status taxonomy.
				$product->update_auction_status( true );
			}
		}

		/**
		 * Sends winner email daily
		 *
		 * Send fail winner email
		 *
		 * @since  1.2.2
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function cron_resend_winner_email() {
			$args = array(
				'post_type'   => 'product',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					array(
						'key'     => 'yith_wcact_winner_email_is_not_send',
						'value'   => '1',
						'compare' => '=',
					),
					array(
						'key'     => '_yith_auction_to',
						'value'   => strtotime( 'now' ),
						'compare' => '<=',
					),
				),
			);

			// Get all Auction ids.
			$auction_ids = get_posts( $args );

			if ( $auction_ids ) {
				foreach ( $auction_ids as $auction_id ) {
					$product    = wc_get_product( $auction_id );
					$instance   = YITH_Auctions()->bids;
					$max_bidder = $instance->get_max_bid( $product->get_id() );

					if ( $max_bidder ) {
						$user = get_user_by( 'id', $max_bidder->user_id );

						$product->set_send_winner_email( false );
						yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );

						$product->save();

						WC()->mailer();

						do_action( 'yith_wcact_auction_winner', $product, $user, $max_bidder );
					}
				}
			}
		}

		/**
		 * Create cronjob for reschedule not paid auctions
		 *
		 * @param  WC_Product $product Product.
		 * @param  WP_User    $user User.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function register_reschedule_not_paid_cron( $product, $user ) {
			$product = ( $product instanceof WC_Product ) ? $product : wc_get_product( $product );

			/**
			 * APPLY_FILTERS: yith_wcact_reschedule_not_paid_cron
			 *
			 * Filter whether to create the cron event to reschedule non-paid auctions.
			 *
			 * @param bool       $create_event Whether to create cron event or not
			 * @param WC_Product $product      Product object
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_reschedule_not_paid_cron', true, $product ) && 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_not_paid', 'no' ) && ! $product->get_auction_paid_order() && $product->is_closed() ) {
				$settings_reschedule_auction_not_paid = get_option( 'ywcact_settings_reschedule_auction_not_paid', array() );

				if ( $settings_reschedule_auction_not_paid && ! empty( $settings_reschedule_auction_not_paid ) && ! empty( $settings_reschedule_auction_not_paid['pay_max_number'] ) ) {
					$number = $settings_reschedule_auction_not_paid['pay_max_number'];
					$unit   = $settings_reschedule_auction_not_paid['pay_max_unit'];

					/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
					$create_event = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
					wp_schedule_single_event( $create_event, 'yith_wcact_reschedule_not_paid_event', array( $product->get_id(), $user, $settings_reschedule_auction_not_paid ) );
				}
			}
		}

		/**
		 * First step reschedule not paid auction
		 *
		 * @param  WC_Product/int $product Product or product_id.
		 * @param  WP_User        $user User.
		 * @param  mixed          $options Options array.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function reschedule_auction_first_step( $product, $user, $options ) {
			$product = ( $product instanceof WC_Product ) ? $product : wc_get_product( $product );

			if ( 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_not_paid', 'no' ) && $product && 'auction' === $product->get_type() && ! $product->get_auction_paid_order() && $product->is_closed() && ! empty( $options['pay_max_select_reminder'] ) ) {
				switch ( $options['pay_max_select_reminder'] ) {
					case 'reschedule':
						$number = get_option( 'ywcact_settings_reschedule_not_paid_number', '' );

						if ( $number ) {
							$unit = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );

							/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
							$new_end_auction = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
							ywcact_reschedule_auction_product( $product ); // Clean auction bids.
							$product->set_end_date( $new_end_auction );
							$product->save();
							$product->update_auction_status( true );
							$this->cron_emails( $product->get_id() );
							$this->cron_emails_auctions( $product->get_id() );

							WC()->mailer();

							// Send email to admin that product was reschedule automatically.
							do_action( 'yith_wcact_auction_email_rescheduled', $product );
						}

						break;

					case 'send_reminder':
						WC()->mailer();

						// Send reminder to winner customer.
						/**
						 * DO_ACTION: yith_wcact_auction_winner_email_reminder
						 *
						 * Allow to send the the reminder email to the auction winner.
						 *
						 * @param WC_Product $product Product object
						 * @param WP_User    $user    User object
						 */
						do_action( 'yith_wcact_auction_winner_email_reminder', $product, $user );

						// Generate cronjob.
						if ( ! $product->get_auction_paid_order() && ! empty( $options['after_number'] ) ) {
							$number = $options['after_number'];
							$unit   = $options['after_unit'];

							/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
							$create_event = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
							wp_schedule_single_event( $create_event, 'yith_wcact_reschedule_not_paid_event_second_time', array( $product->get_id(), $user, $options ) );
						}

						break;
				}
			}
		}

		/**
		 * Second step reschedule not paid auction
		 *
		 * @param  WC_Product/int $product Product or product_id.
		 * @param  WP_User        $user User.
		 * @param  mixed          $options Options array.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function reschedule_auction_second_step( $product, $user, $options ) {
			$product = ( $product instanceof WC_Product ) ? $product : wc_get_product( $product );

			if ( 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_not_paid', 'no' ) && $product && 'auction' === $product->get_type() && $product->is_closed() && ! $product->get_auction_paid_order() && ! empty( $options['after_select_reminder'] ) && $product->get_id() > 0 ) {
				switch ( $options['after_select_reminder'] ) {
					case 'do_nothing':
						break;

					case 'reschedule':
						$number = get_option( 'ywcact_settings_reschedule_not_paid_number', '' );

						if ( $number ) {
							$unit = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );

							/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
							$new_end_auction = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
							ywcact_reschedule_auction_product( $product ); // Clean auction bids.
							$product->set_end_date( $new_end_auction );
							$product->save();
							$product->update_auction_status( true );
							$this->cron_emails( $product->get_id() );
							$this->cron_emails_auctions( $product->get_id() );

							WC()->mailer();
							// Send email to admin that product was reschedule automatically.
							do_action( 'yith_wcact_auction_email_rescheduled', $product );
						}

						break;

					case 'send_winner_email_second_bidder':
						$instance = YITH_Auctions()->bids;
						$instance->remove_customer_bids( $user->ID, $product->get_id() );
						$max_bidder = $instance->get_max_bid( $product->get_id() );

						if ( ! $product->has_reserve_price() && $max_bidder || $product->has_reserve_price() && $product->get_price() > $product->get_reserve_price() && $max_bidder ) {
							WC()->mailer();

							$new_winner_user = get_user_by( 'id', $max_bidder->user_id );

							if ( $new_winner_user ) {
								$product->set_send_winner_email( false );

								yit_delete_prop( $product, 'yith_wcact_winner_email_is_send', 1, false );
								yit_delete_prop( $product, 'yith_wcact_winner_email_send_custoner', false );
								yit_delete_prop( $product, '_yith_wcact_winner_email_max_bidder', false );
								yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );

								// Cancel order if exists and set meta key as 0.
								$order_id = $product->get_order_id();

								if ( $order_id && $order_id > 0 ) {
									$product->set_order_id( 0 );
									$order = wc_get_order( $order_id );

									if ( $order && $order instanceof WC_Order ) {
										$order->update_status( 'cancelled', __( 'Order cancelled - Auctions not paid on time.', 'yith-auctions-for-woocommerce' ) );
										$order->save();
									}
								}

								do_action( 'yith_wcact_auction_winner', $product, $new_winner_user, $max_bidder );
							}
						}

						// Generate cronjob.
						if ( ! $product->get_auction_paid_order() && ! empty( $options['after_second_winner_number'] ) ) {
							$number = $options['after_second_winner_number'];
							$unit   = $options['after_second_winner_unit'];

							/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
							$create_event = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
							wp_schedule_single_event( $create_event, 'yith_wcact_reschedule_not_paid_event_third_time', array( $product->get_id() ) );
						}

						break;
				}
			}
		}

		/**
		 * Third step reschedule not paid auction
		 *
		 * @param  WC_Product/int $product Product or product_id.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function reschedule_auction_third_step( $product ) {
			$product = ( $product instanceof WC_Product ) ? $product : wc_get_product( $product );

			if ( 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_not_paid', 'no' ) && $product && 'auction' === $product->get_type() && $product->is_closed() && ! $product->get_auction_paid_order() && $product->get_id() > 0 ) {
				$number = get_option( 'ywcact_settings_reschedule_not_paid_number', '' );

				if ( $number ) {
					$unit = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );

					/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
					$new_end_auction = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );

					ywcact_reschedule_auction_product( $product ); // Clean auction bids.
					$product->set_end_date( $new_end_auction );
					$product->save();
					$product->update_auction_status( true );
					$this->cron_emails( $product->get_id() );
					$this->cron_emails_auctions( $product->get_id() );

					WC()->mailer();

					// Send email to admin that product was reschedule automatically.
					do_action( 'yith_wcact_auction_email_rescheduled', $product );
				}
			}
		}

		/**
		 * Create single cron job on started scheduled auctions
		 *
		 * Create a single cronjob when the auction starts in order to change auction status.
		 *
		 * @param  int $product_id Product id.
		 * @since  3.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function create_single_cron_on_started_for_scheduled_auctions( $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() && ! $product->is_start() ) {
				$start_date = $product->get_start_date( 'edit' );

				wp_schedule_single_event( $start_date, 'yith_wcact_auction_started_cron', array( $product_id, false ) );
			}
		}

		/**
		 * Change auction status when starts auction
		 *
		 * Create a single cronjob when the auction starts in order to change auction status.
		 *
		 * @param  int             $product_id Product id.
		 * @param  WC_Product/bool $product Auction Product or false.
		 * @since  3.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function change_auction_status_on_started( $product_id, $product ) {
			$product = ( $product ) ? $product : wc_get_product( $product_id );

			if ( $product && 'auction' === $product->get_type() ) {
				$product->update_auction_status( true );
			}
		}
	}
}

return new YITH_WCACT_Cron();
