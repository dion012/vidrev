<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_WPML_Compatibility Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_STRIPE_GATEWAY' ) ) {
	/**
	 * Stripe Integration Class
	 *
	 * @class   YITH_WCACT_Stripe_Compatibility
	 * @package Yithemes
	 * @since   Version 3.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WCACT_Stripe_Compatibility {

		/**
		 * Boolean for check plugin option
		 *
		 * @var   bool
		 * @since 3.0.0
		 */
		public $charge_automatically;

		/**
		 * Max failed attempt
		 *
		 * @var   bool
		 * @since 3.0.0
		 */
		public $max_payment_attempt;

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function __construct() {
			$this->charge_automatically = 'yes' === get_option( 'yith_wcact_stripe_charge_automatically_price', 'no' ) && 'yes' === get_option( 'yith_wcact_verify_payment_method', 'no' );

			/**
			 * APPLY_FILTERS: yith_wcact_max_payment_attempts
			 *
			 * Filter the number of maximum payments attempts to charge the auction automatically.
			 *
			 * @param int $max_payment_attempts Max payment attempts
			 *
			 * @return int
			 */
			$this->max_payment_attempt = apply_filters( 'yith_wcact_max_payment_attempts', 3 );

			add_filter( 'yith_wcact_general_options_auction_rescheduling', array( $this, 'non_paid_auction_option_for_stripe' ) );
			add_action( 'yit_panel_wc_after_update', array( $this, 'save_stripe_non_paid_auction_option' ), 20 );

			add_action( 'woocommerce_process_product_meta_auction', array( $this, 'auction_process_with_stripe' ), 30 );

			add_filter( 'yith_wcact_reschedule_not_paid_cron', array( $this, 'prevent_generate_default_reschedule_cronjobs' ), 10, 2 );

			add_filter( 'yith_wcact_automatically_create_order', array( $this, 'create_order' ), 10, 2 );

			add_action( 'yith_wcact_auction_winner', array( $this, 'process_payment' ), 30, 3 );

			// Action scheduler failed payments.
			add_action( 'yith_wcact_after_failed_attempts', array( $this, 'update_scheduled_action_by_failed_attempt' ), 10, 3 );
			add_action( 'yith_wcact_schedule_new_payment_attempt', array( $this, 'new_payment_attempt' ), 10, 2 );

			// Option after max payment attempt.
			add_action( 'yith_wcact_stripe_after_max_attempt', array( $this, 'non_paid_auction_options' ), 10, 3 );

			// Reschedule product.
			add_action( 'yith_wcact_after_reschedule_product_order', array( $this, 'cancel_payment_attempt' ), 10, 2 );

			// Emails.
			add_filter( 'yith_wcact_show_pay_url_button_winner_email', array( $this, 'show_card_notification' ), 10, 2 );
			add_filter( 'woocommerce_email_classes', array( $this, 'register_stripe_email_classes' ) );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_stripe_template' ), 10, 3 );
		}

		/**
		 * Non paid auction for stripe
		 *
		 * Manage non paid auction option when stripe is enabled.
		 *
		 * @param array $options array of options.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return array
		 */
		public function non_paid_auction_option_for_stripe( $options ) {
			if ( $this->charge_automatically ) {
				$options['general-auctions-rescheduling']['settings_auction_reschedule_how_to_not_paid'] = array(
					'id'              => 'yith_wcact_auction_reschedule_how_to_not_paid_stripe',
					'title'           => esc_html__( 'Unpaid auctions options', 'yith-auctions-for-woocommerce' ),
					'type'            => 'yith-field',
					'yith-type'       => 'custom',
					'yith-wcact-type' => '/integrations/stripe/rescheduled-not-paid-stripe',
					'action'          => 'yith_wcact_general_custom_fields',
					'deps'            => array(
						'id'    => 'yith_wcact_settings_reschedule_auctions_not_paid',
						'value' => 'yes',
						'type'  => 'hide',
					),
				);
			}

			return $options;
		}

		/**
		 * Save non paid option Stripe.
		 *
		 * Save non paid Stripe options when Stripe option is enabled.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function save_stripe_non_paid_auction_option() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['ywcact_settings_reschedule_auction_not_paid_stripe'] ) && ! empty( $_POST['ywcact_settings_reschedule_auction_not_paid_stripe'] ) ) {
				update_option( 'ywcact_settings_reschedule_auction_not_paid_stripe', array_map( 'sanitize_text_field', wp_unslash( $_POST['ywcact_settings_reschedule_auction_not_paid_stripe'] ) ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Register Stripe emails.
		 *
		 * @param int $product_id Product id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function auction_process_with_stripe( $product_id ) {
			if ( isset( $_POST['yith_wcact_auction_form'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yith_wcact_auction_form'] ) ), 'yith-wcact-auction-form' ) ) {
				$product = wc_get_product( $product_id );

				if ( $product && 'auction' === $product->get_type() && $this->charge_automatically ) {
					$product->set_payment_gateway( 'yith-stripe' );

					$product->save();
				}
			}
		}

		/**
		 * Register Stripe emails.
		 *
		 * @param bool       $status Boolean to allow or not default cronjobs.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return bool
		 */
		public function prevent_generate_default_reschedule_cronjobs( $status, $product ) {
			if ( $status && $this->charge_automatically && $product && 'auction' === $product->get_type() && 'yith-stripe' === $product->get_payment_gateway() ) {
				$status = false;
			}

			return $status;
		}

		/**
		 * Create order.
		 *
		 * @param bool       $status Boolean to allow or not default cronjobs.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return bool
		 */
		public function create_order( $status, $product ) {
			if ( ! $status && $this->charge_automatically && $product && 'auction' === $product->get_type() && 'yith-stripe' === $product->get_payment_gateway() ) {
				$status = true;
			}

			return $status;
		}

		/**
		 * Process payment with Stripe.
		 *
		 * @param WC_Product $product Auction product.
		 * @param WP_User    $user Winner user.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @throws Exception When unable to process the payment item.
		 * @since  3.0
		 */
		public function process_payment( $product, $user ) {
			if ( $product && 'auction' === $product->get_type() && 'yith-stripe' === $product->get_payment_gateway() && ! $product->get_auction_paid_order() ) {
				$order_id = $product->get_order_id();

				$gateway = YITH_WCStripe()->get_gateway();

				if ( $gateway && $order_id ) {
					$order = wc_get_order( $order_id );
					$order->set_payment_method( $gateway );
					$order->save();

					ywcact_logs( 'Start process to try to pay automatically with YITH WooCommerce Stripe Premium the product ' . $product->get_id() . ' in order ' . $order_id );
					$response = $this->pay_order( $order );

					if ( $response ) {
						$this->register_payment( $response, $order, $product );
					} else {
						ywcact_logs( 'Error trying to pay automatically with YITH WooCommerce Stripe Premium the product ' . $product->get_id() . ' in order ' . $order_id );
					}
				}
			}
		}

		/**
		 * Process payment with Stripe.
		 *
		 * @param array      $response Response payment stripe.
		 * @param WC_Order   $order Order.
		 * @param WC_Product $product Auction product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function register_payment( $response, $order, $product ) {
			if ( $response && isset( $response['status'] ) ) {
				switch ( $response['status'] ) {
					case 'failed':
						$this->register_failed_payment( $response, $order, $product );
						break;

					default:
						$this->register_success_payment( $response, $order );
						break;
				}
			}
		}

		/**
		 * Pay Order
		 *
		 * @param WC_Order $order Order.
		 * @param bool     $payment_attempt Payment attempt.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return array
		 */
		public function pay_order( $order, $payment_attempt = false ) {
			$gateway = YITH_WCStripe()->get_gateway();

			$code = array(
				'status'  => 'failed',
				'message' => __( 'Payment failed: The payment cannot be made', 'yith-auctions-for-woocommerce' ),
			);

			if ( ! $payment_attempt ) {
				$payment_attempt = $order->get_meta( 'yith_wcact_payment_attempts' );
				$payment_attempt = empty( $payment_attempt ) ? 0 : $payment_attempt;
			}

			$payment_attempt = intval( $payment_attempt ) + 1;

			if ( $gateway ) {
				/* translators: %s Payment attempt number */
				$order->add_order_note( sprintf( __( 'Payment attempt: %s', 'yith-auctions-for-woocommerce' ), $payment_attempt ) );
				ywcact_logs( 'Payment attempt: ' . $payment_attempt );
				$user_id = $order->get_user_id();

				if ( $user_id ) {
					$default_token = WC_Payment_Tokens::get_customer_default_token( $user_id );

					$gateway_id = $default_token->get_gateway_id();
					$source     = '';
					if ( $default_token && 'yith-stripe' === $gateway_id ) {
						$source      = $default_token->get_token();
						$token       = $default_token;
						$customer    = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );
						$customer_id = isset( $customer['id'] ) ? $customer['id'] : false;
					} else {
						$customer_id = false;
						$token       = false;

						ywcact_logs( 'No default token or the default token is not a YITH Stripe ID ' . $order->get_id() );
						$order->add_order_note( __( 'No default token or the default token is not a YITH Stripe ID', 'yith-auctions-for-woocommerce' ) );
					}
				}

				$gateway->init_stripe_sdk();

				try {
					$intent = $gateway->api->create_intent(
						array(
							'amount'               => YITH_WCStripe::get_amount( $order->get_total() ),
							'currency'             => $order->get_currency(),
							/**
							 * APPLY_FILTERS: yith_wcact_stripe_charge_description
							 *
							 * Filter the description sent to Stripe for the automatic charge.
							 *
							 * @param string $charge_description Charge description
							 * @param string $blog_name          Blog name
							 * @param int    $order_number       Order number
							 *
							 * @return string
							 */
							// translators: 1. Blog name. 2. Order number.
							'description'          => apply_filters( 'yith_wcact_stripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-auctions-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
							/**
							 * APPLY_FILTERS: yith_wcact_stripe_metadata
							 *
							 * Filter the array with the metadata sent to Stripe for the automatic charge.
							 *
							 * @param array  $metadata    Meta data
							 * @param string $charge_type Charge type
							 *
							 * @return array
							 */
							'metadata'             => apply_filters(
								'yith_wcact_stripe_metadata',
								array(
									'order_id'    => $order->get_id(),
									'order_email' => $order->get_billing_email(),
								),
								'charge'
							),
							'customer'             => $customer_id,
							'payment_method_types' => array( 'card' ),
							'payment_method'       => $source,
							'off_session'          => true,
							'confirm'              => true,
							'capture_method'       => 'automatic',
						)
					);
				} catch ( Stripe\Exception\ApiErrorException $e ) {
					$body = $e->getJsonBody();
					$err  = $body['error'];

					if (
						isset( $err['payment_intent'] ) &&
						isset( $err['payment_intent']['status'] ) &&
						in_array( $err['payment_intent']['status'], array( 'requires_action', 'requires_payment_method' ), true ) &&
						(
							! empty( $err['payment_intent']['next_action'] ) && isset( $err['payment_intent']['next_action']->type ) && 'use_stripe_sdk' === $err['payment_intent']['next_action']->type ||
							'authentication_required' === $err['code']
						)
					) {
						if ( isset( $token ) ) {
							$token->update_meta_data( 'confirmed', false );
							$token->save();
						}

						$code = array(
							'status'          => 'failed',
							'message'         => __( 'Payment failed: Please, validate your payment method before proceeding further', 'yith-auctions-for-woocommerce' ),
							'action'          => 'requires_validation',
							'payment_attempt' => $payment_attempt,
						);
					} else {
						$code = array(
							'status'          => 'failed',
							// translators: Payment failed message.
							'message'         => sprintf( __( 'Payment failed: %s', 'yith-auctions-for-woocommerce' ), $err['message'] ),
							'action'          => 'error_card',
							'payment_attempt' => $payment_attempt,
						);
					}
				} catch ( Exception $e ) {
					$code = array(
						'status'          => 'failed',
						'message'         => __( 'Payment failed: Sorry, There was an error while processing payment; please, try again', 'yith-auctions-for-woocommerce' ),
						'payment_attempt' => $payment_attempt,
					);
				}
			}

			if ( isset( $intent ) && $intent ) {
				// register intent for the order.
				$order->update_meta_data( 'intent_id', $intent->id );

				// retrieve charge to use for next steps.
				$charge = end( $intent->charges->data );

				// payment complete.
				$order->payment_complete( $charge->id );

				// update order meta.
				$order->update_meta_data( '_captured', $charge->captured ? 'yes' : 'no' );
				$order->update_meta_data( '_stripe_customer_id', $customer_id );
				$order->save();

				$code = array(
					'status'  => 'success',
					// translators: 1. Stripe charge id.
					'message' => sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-auctions-for-woocommerce' ), $charge->id ),
					'action'  => 'payment_success',
				);
			}

			return $code;
		}

		/**
		 * Register success payment
		 *
		 * @param array    $call array with payment params.
		 * @param WC_Order $order Order.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function register_success_payment( $call, $order ) {
			ywcact_logs( 'The payment success for the order ' . $order->get_id() );

			if ( isset( $call['message'] ) ) {
				ywcact_logs( 'Message: ' . $call['message'] );

				$order->add_order_note( $call['message'] );
			}

			/**
			 * DO_ACTION: yith_wcact_stripe_after_register_success_payment
			 *
			 * Allow to fire some action after the Stripe payment has been registered successfully.
			 *
			 * @param WC_Order $order Order object
			 */
			do_action( 'yith_wcact_stripe_after_register_success_payment', $order );
		}

		/**
		 * Register failed payment
		 *
		 * @param array      $call array with payment params.
		 * @param WC_Order   $order Order.
		 * @param WC_Product $product Auction Product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function register_failed_payment( $call, $order, $product ) {
			ywcact_logs( 'The payment fails for the order ' . $order->get_id() );

			if ( isset( $call['message'] ) ) {
				ywcact_logs( 'Message: ' . $call['message'] );

				$order->add_order_note( $call['message'] );
			}

			$failed_attempts = isset( $call['payment_attempt'] ) ? $call['payment_attempt'] : 0;

			// Emailhook.
			WC()->mailer();

			/**
			 * DO_ACTION: yith_wcact_failed_attempts_{$failed_attempts}
			 *
			 * Allow to send the plugin email for the failed payment attempts.
			 *
			 * @param WC_Order   $order   Order object
			 * @param WC_Product $product Product object
			 * @param array      $call    Response
			 */
			do_action( "yith_wcact_failed_attempts_{$failed_attempts}", $order, $product, $call );

			$order->update_meta_data( 'yith_wcact_payment_attempts', $failed_attempts );

			$order->save();

			/**
			 * DO_ACTION: yith_wcact_after_failed_attempts
			 *
			 * Allow to fire some action after the failed payment attempts.
			 *
			 * @param WC_Order   $order           Order object
			 * @param WC_Product $product         Product object
			 * @param int        $failed_attempts Failed attempts
			 */
			do_action( 'yith_wcact_after_failed_attempts', $order, $product, $failed_attempts );
		}

		/**
		 * Update scheduled action
		 *
		 * @param WC_Order   $order Order.
		 * @param WC_Product $product Product.
		 * @param int        $payment_attempt Payment attempt.
		 *
		 * @return void;
		 */
		public function update_scheduled_action_by_failed_attempt( $order, $product, $payment_attempt ) {
			/**
			 * APPLY_FILTERS: yith_wcact_payment_schedule_action_new_attempt
			 *
			 * Filter whether to schedule a new payment attempt.
			 *
			 * @param bool $schedule_new_attempt Whether to schedule a new payment attempt or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_payment_schedule_action_new_attempt', false ) || $payment_attempt < $this->max_payment_attempt ) {
				$args = array(
					'order_id'   => (int) $order->get_id(),
					'product_id' => (int) $product->get_id(),
				);

				/**
				 * APPLY_FILTERS: yith_wcact_scheduled_interval
				 *
				 * Filter the interval to schedule new payment attempts.
				 *
				 * @param int        $interval Time interval
				 * @param WC_Order   $order    Order object
				 * @param WC_Product $product  Product object
				 *
				 * @return int
				 */
				$time = apply_filters( 'yith_wcact_scheduled_interval', 24 * HOUR_IN_SECONDS, $order, $product );

				$new_date = time() + $time;

				$has_hook_scheduled = WC()->queue()->get_next( 'yith_wcact_schedule_new_payment_attempt', $args );

				if ( $has_hook_scheduled !== $new_date ) {
					WC()->queue()->cancel_all( 'yith_wcact_schedule_new_payment_attempt', $args );

					if ( $new_date > time() && ! $order->has_status( array( 'cancelled', 'completed', 'processing' ) ) ) {
						WC()->queue()->schedule_single( $new_date, 'yith_wcact_schedule_new_payment_attempt', $args );
					}
				}
			} else {
				/**
				 * DO_ACTION: yith_wcact_stripe_after_max_attempt
				 *
				 * Allow to fire some action after the maximum Stripe payment attempt.
				 *
				 * @param WC_Order   $order           Order object
				 * @param WC_Product $product         Product object
				 * @param int        $payment_attempt Payment attempt
				 */
				do_action( 'yith_wcact_stripe_after_max_attempt', $order, $product, $payment_attempt );
			}
		}

		/**
		 * Non paid auction options
		 *
		 * @param WC_Order   $order Order.
		 * @param WC_Product $product Product.
		 * @param int        $payment_attempt Payment attempt.
		 *
		 * @return void;
		 */
		public function non_paid_auction_options( $order, $product, $payment_attempt ) {
			ywcact_logs( 'The Payment for the order ' . $order->get_id() . ' wasn\'t paid' );

			if ( 'yes' === get_option( 'yith_wcact_settings_reschedule_auctions_not_paid', 'no' ) && ! $order->has_status( array( 'cancelled', 'completed', 'processing' ) ) && $product && 'auction' === $product->get_type() && ! $product->get_auction_paid_order() && $product->is_closed() ) {
				$not_paid_stripe = get_option( 'ywcact_settings_reschedule_auction_not_paid_stripe', array() );

				if ( ! empty( $not_paid_stripe ) ) {
					$transfer_second_winner = get_post_meta( $product->get_id(), '_yith_wcact_transfer_second_winner', true );

					if ( $transfer_second_winner ) { // The second max bidder is the winner.
						$second_step = $not_paid_stripe['second_step'];

						switch ( $second_step ) {
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
									/**
									 * DO_ACTION: yith_wcact_auction_email_rescheduled
									 *
									 * Allow to fire some action when the auction has been rescheduled.
									 *
									 * @param WC_Product $product Product object
									 */
									do_action( 'yith_wcact_auction_email_rescheduled', $product );

									ywcact_logs( 'The Product ' . $product->get_id() . ' was reschedule on second step' );
								}
								break;

							default:
								break;
						}
					} else {
						$fist_step = $not_paid_stripe['first_step'];

						switch ( $fist_step ) {
							case 'change_second_bidder':
								$instance = YITH_Auctions()->bids;
								$instance->remove_customer_bids( $order->get_user_id(), $product->get_id() );
								$max_bidder = $instance->get_max_bid( $product->get_id() );

								if ( ! $product->has_reserve_price() && $max_bidder || $product->has_reserve_price() && $product->get_price() > $product->get_reserve_price() && $max_bidder ) {
									update_post_meta( $product->get_id(), '_yith_wcact_transfer_second_winner', true );

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

										ywcact_logs( 'The Product ' . $product->get_id() . ' was changed to the second max bidder' );

										/**
										 * DO_ACTION: yith_wcact_auction_winner
										 *
										 * Allow to fire some action when the auction has ended and has a winner.
										 *
										 * @param WC_Product $product Product object
										 * @param WP_User    $user    User object
										 * @param object     $max_bid Max bid object
										 */
										do_action( 'yith_wcact_auction_winner', $product, $new_winner_user, $max_bidder );
									}
								} else {
									$number = get_option( 'ywcact_settings_reschedule_not_paid_number', '' );

									if ( $number ) {
										$unit = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );
										/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
										$new_end_auction = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
										ywcact_reschedule_auction_product( $product ); // Clean auction bids.
										$product->set_end_date( $new_end_auction );
										$product->update_auction_status( true );
										$product->save();

										/**
										 * DO_ACTION: yith_wcact_register_cron_email
										 *
										 * Allow to register the cron event to send the emails.
										 *
										 * @param int $product_id Product ID
										 */
										do_action( 'yith_wcact_register_cron_email', $product->get_id() );

										/**
										 * DO_ACTION: yith_wcact_register_cron_email_auction
										 *
										 * Allow to register the cron event to send the emails.
										 *
										 * @param int $product_id Product ID
										 */
										do_action( 'yith_wcact_register_cron_email_auction', $product->get_id() );

										WC()->mailer();
										// Send email to admin that product was reschedule automatically.
										do_action( 'yith_wcact_auction_email_rescheduled', $product );

										ywcact_logs( 'The Product ' . $product->get_id() . ' was reschedule on first step because no second winner' );
									}
								}
								break;

							default:
								$number = get_option( 'ywcact_settings_reschedule_not_paid_number', '' );

								if ( $number ) {
									$unit = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );
									/* translators: %d: number. %s: Unit time (hours, minutes, seconds) */
									$new_end_auction = strtotime( ( sprintf( '+%d %s', $number, $unit ) ), (int) time() );
									ywcact_reschedule_auction_product( $product ); // Clean auction bids.
									$product->set_end_date( $new_end_auction );
									$product->save();
									$product->update_auction_status( true );
									do_action( 'yith_wcact_register_cron_email', $product->get_id() );
									do_action( 'yith_wcact_register_cron_email_auction', $product->get_id() );

									WC()->mailer();
									// Send email to admin that product was reschedule automatically.
									do_action( 'yith_wcact_auction_email_rescheduled', $product );

									ywcact_logs( 'The Product ' . $product->get_id() . ' was reschedule on first step' );
								}
								break;
						}
					}
				}
			} else {
				$order_id = $product->get_order_id();

				if ( $order_id && $order_id > 0 ) {
					$product->set_order_id( 0 );
					$order = wc_get_order( $order_id );

					if ( $order && $order instanceof WC_Order ) {
						$order->update_status( 'cancelled', __( 'Order cancelled - Auctions not paid on time.', 'yith-auctions-for-woocommerce' ) );
						$order->save();
					}
				}
			}
		}

		/**
		 * New payment attempt
		 *
		 * @param int $order_id Order id.
		 * @param int $product_id Product id.
		 *
		 * @return void;
		 */
		public function new_payment_attempt( $order_id, $product_id ) {
			$order = wc_get_order( $order_id );

			if ( $order && ! $order->has_status( array( 'cancelled', 'completed', 'processing' ) ) ) {
				$payment_attempt = $order->get_meta( 'yith_wcact_payment_attempts' );
				$payment_attempt = empty( $payment_attempt ) ? 0 : $payment_attempt;

				$product = wc_get_product( $product_id );

				if ( $product && 'auction' === $product->get_type() && ! $product->get_auction_paid_order() && $product->is_closed() ) {
					$response = $this->pay_order( $order, $payment_attempt );
					$this->register_payment( $response, $order, $product );

				}
			}
		}

		/**
		 * Cancel payment attempt when auction is reschedule
		 *
		 * @param WC_Product $product Product.
		 * @param int        $order_id Order id.
		 *
		 * @return void;
		 */
		public function cancel_payment_attempt( $product, $order_id ) {
			$args = array(
				'order_id'   => (int) $order_id,
				'product_id' => (int) $product->get_id(),
			);

			WC()->queue()->cancel_all( 'yith_wcact_schedule_new_payment_attempt', $args );

			delete_post_meta( $product->get_id(), '_yith_wcact_transfer_second_winner', true );
		}

		/**
		 * Show card notification if order will be paid automatically
		 *
		 * @param bool   $val Boolean for change or not the message.
		 * @param object $email Email options.
		 *
		 * @return bool;
		 */
		public function show_card_notification( $val, $email ) {
			$product = $email->object['product'];

			if ( $product && 'yith-stripe' === $product->get_payment_gateway() && $this->charge_automatically ) {
				$user          = $email->object['user'];
				$default_token = WC_Payment_Tokens::get_customer_default_token( $user->ID );

				if ( $default_token ) {
					$card_label       = wc_get_credit_card_type_label( $default_token->get_card_type() );
					$last_four_digits = $default_token->get_last4();
					/* translators: %1$s Card label %2$s Last four digits */
					$message     = sprintf( __( 'Your credit card %1$s ending in %2$s will automatically be charged for the item\'s price.', 'yith-auctions-for-woocommerce' ), '<b>' . $card_label . '</b>', '<b>' . $last_four_digits . '</b>' );
					$message_end = __( 'Enjoy your winnings!', 'yith-auctions-for-woocommerce' );

					?>
					<p><?php echo wp_kses_post( $message ); ?></p>
					<p><?php echo esc_html( $message_end ); ?></p>

					<?php

					$val = false;
				}
			}

			return $val;
		}

		/**
		 * Register Stripe emails.
		 *
		 * @param array $email_classes Email classes.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return array
		 */
		public function register_stripe_email_classes( $email_classes ) {
			$email_classes['YITH_WCACT_Stripe_Email_Couldnt_Process_Payment'] = include YITH_WCACT_PATH . 'includes/compatibility/stripe/emails/class-yith-wcact-stripe-email-couldnt-process-payment.php';
			$email_classes['YITH_WCACT_Stripe_Email_Payment_Failed']          = include YITH_WCACT_PATH . 'includes/compatibility/stripe/emails/class-yith-wcact-stripe-email-payment-failed.php';
			$email_classes['YITH_WCACT_Stripe_Email_Item_Lost']               = include YITH_WCACT_PATH . 'includes/compatibility/stripe/emails/class-yith-wcact-stripe-email-item-lost.php';

			return $email_classes;
		}

		/**
		 * Locate Stripe email template.
		 *
		 * @param string $core_file Template type.
		 * @param string $template Template name.
		 * @param string $template_base Template base.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return string
		 */
		public function locate_stripe_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				'emails/stripe/couldnt-process-payment.php',
				'emails/stripe/payment-failed.php',
				'emails/stripe/item-lost.php',
			);

			if ( in_array( $template, $custom_template, true ) ) {
				$core_file = YITH_WCACT_TEMPLATE_PATH . $template;
			}

			return $core_file;
		}
	}
}

return new YITH_WCACT_Stripe_Compatibility();
