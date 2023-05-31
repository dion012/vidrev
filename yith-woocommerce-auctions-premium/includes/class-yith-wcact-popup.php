<?php
/**
 * YITH_WCACT_Popup Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCACT_Popup' ) ) {
	/**
	 * YITH Auctions for WooCommerce
	 *
	 * @since 2.0.0
	 */
	class YITH_WCACT_Popup {

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			// add main popup.
			add_action( 'wp_footer', array( $this, 'add_popup' ), 10 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_popup_script' ) );

			add_filter( 'yith_wcact_auction_button_bid_class', array( $this, 'add_class_popup_on_bid_button' ), 10, 3 );

			add_action( 'yith_wcact_after_add_button_bid', array( $this, 'add_confirmation_message' ), 30 );

			add_action( 'yith_wcact_before_form_bid', array( $this, 'show_fee_message' ), 10, 2 );

			// Integrations with EASY LOGIN REGISTER AND POPUP.
			if ( 'yes' === get_option( 'yith_wcact_enable_login_popup', 'no' ) && ( defined( 'YITH_WELRP' ) && YITH_WELRP ) ) {
				add_filter( 'yith_welrp_script_data', array( $this, 'add_selectors' ), 10, 2 );
				add_filter( 'yith_welrp_init_popup', '__return_true' );
				add_filter( 'yith_welrp_email_section_template_args', array( $this, 'disable_guest_checkout_on_auction_login' ) );
			}
		}

		/**
		 * Output the popup
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return void
		 */
		public function add_popup() {
			global $product;

			if ( $product && $product instanceof WC_Product && 'auction' === $product->get_type() ) {
				/**
				 * APPLY_FILTERS: yith_ywcact_popup_template_args
				 *
				 * Filter the arguments sent to the popup template.
				 *
				 * @param array $args Array of arguments
				 *
				 * @return array
				 */
				wc_get_template(
					'ywcact-popup.php',
					apply_filters( 'yith_ywcact_popup_template_args', array() ),
					'',
					YITH_WCACT_TEMPLATE_PATH . 'frontend/'
				);
			}
		}

		/**
		 * Add Style and script for popup
		 *
		 * @since  2.0
		 * @author Carlos Rodríguez
		 */
		public function enqueue_popup_script() {
			if ( is_product() ) {
				wp_register_script(
					'ywcact_popup_handler_js',
					YITH_WCACT_ASSETS_URL . 'js/' . yit_load_js_file( 'popup-handler.js' ),
					array( 'jquery', 'wp-util', 'jquery-blockui' ),
					YITH_WCACT_VERSION,
					true
				);

				wp_enqueue_script( 'ywcact_popup_handler_js' );

				// add script data.
				wp_localize_script(
					'ywcact_popup_handler_js',
					'ywcact_popup_data',
					/**
					 * APPLY_FILTERS: ywcact_popup_handler_js_script_data
					 *
					 * Filter the data sent to the popup script.
					 *
					 * @param array $data Data
					 *
					 * @return array
					 */
					apply_filters(
						'ywcact_popup_handler_js_script_data',
						array(
							'popupWidth'   => '100%',
							'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
							'loader'       => YITH_WCACT_ASSETS_URL . '/images/loading.gif',
							'mainSelector' => '',
						)
					)
				);

				wp_register_style( 'ywcact_popup_style_css', YITH_WCACT_ASSETS_URL . 'css/popup-style.css', array(), YITH_WCACT_VERSION, 'all' );

				wp_enqueue_style( 'ywcact_popup_style_css' );
			}
		}

		/**
		 * Check if popup is enabled for bid and add the class
		 *
		 * @param  string     $classes Classes added to the html div.
		 * @param  WC_Product $product Product.
		 * @param  WP_User    $user User.
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @return string
		 */
		public function add_class_popup_on_bid_button( $classes, $product, $user ) {
			// TODO check this functions.

			$logged_in = ( $user && $user->ID > 0 ) ? true : false;

			if ( $logged_in ) {
				$fee_amount = ywcact_get_fee_amount( $product );
				$instance   = YITH_Auctions()->bids;

				/**
				 * APPLY_FILTERS: yith_wcact_show_fee_message
				 *
				 * Filter whether to show the fee message.
				 *
				 * @param bool       $show_fee_message Whether to show the fee message or not
				 * @param WC_Product $product          Product object
				 *
				 * @return bool
				 */
				if ( $fee_amount && $fee_amount > 0 && apply_filters( 'yith_wcact_show_fee_message', ! $instance->get_user_fee_payment( $user->ID, $product->get_id() ), $product ) ) {
					$classes = str_replace( 'auction_bid', 'ywcact-auction-fee-confirm', $classes );
				} elseif ( 'yes' === get_option( 'yith_wcact_settings_ask_confirmation_before_to_bid', 'no' ) ) {
					$classes = str_replace( 'auction_bid', 'ywcact-auction-confirm', $classes );
				}

				if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) && 'yes' === get_option( 'yith_wcact_verify_payment_method', 'no' ) ) {
					$tokens = WC_Payment_Tokens::get_customer_tokens( $user->ID );

					if ( empty( $tokens ) ) {
						$classes = str_replace( 'auction_bid', 'ywcact-verify-payment-method', $classes );
						$classes = str_replace( 'ywcact-auction-confirm', 'ywcact-verify-payment-method', $classes );
					}
				}
			} else {
				if ( 'yes' === get_option( 'yith_wcact_enable_login_popup', 'no' ) && ( defined( 'YITH_WELRP' ) && YITH_WELRP ) ) {
					$classes = str_replace( 'auction_bid', 'ywcact-auction-login-popup', $classes );
				}
			}

			return $classes;
		}

		/**
		 * Add confirmation section to display in a modal
		 *
		 * @since  2.0.0
		 * @param  WC_Product_Auction_Premium $product Product.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function add_confirmation_message( $product ) {
			if ( is_user_logged_in() && 'yes' === get_option( 'yith_wcact_settings_ask_confirmation_before_to_bid', 'no' ) ) {
				$args = array(
					'product' => $product,
				);

				wc_get_template( 'bid-confirmation-popup.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
			}
		}

		/**
		 * Add confirmation section to display in a modal
		 *
		 * @since  2.0.0
		 * @param  WC_Product $product Product.
		 * @param  WP_User    $user User.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function show_fee_message( $product, $user ) {
			$fee_amount = ywcact_get_fee_amount( $product );

			if ( $fee_amount && $fee_amount > 0 ) {
				$instance     = YITH_Auctions()->bids;
				$user_pay_fee = $user->ID > 0 ? $instance->get_user_fee_payment( $user->ID, $product->get_id() ) : false;

				if ( apply_filters( 'yith_wcact_show_fee_message', ! $user_pay_fee, $product ) ) {
					$args = array(
						'product'    => $product,
						'fee_amount' => $fee_amount,
						'user'       => $user->ID > 0,
					);

					wc_get_template( 'fee-amount-message.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/' );
				}
			}
		}

		/**
		 * Add selectors to localized script
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez
		 * @param  array $selectors Array of selectors.
		 * @return array
		 */
		public function add_selectors( $selectors ) {
			$selectors['additionalSelector'] .= ! empty( $selectors['additionalSelector'] ) ? ',.ywcact-auction-login-popup' : '.ywcact-auction-login-popup';

			return $selectors;
		}

		/**
		 * Disable guest checkout on easy login and register plugin for auction products
		 *
		 * @since  2.0.1
		 * @author Carlos Rodríguez
		 * @param  array $settings Array of easy login settings.
		 * @return array
		 */
		public function disable_guest_checkout_on_auction_login( $settings ) {
			if ( $settings['continue_as_guest'] ) {
				global $product;

				if ( is_product() && $product && 'auction' === $product->get_type() ) {
					$settings['continue_as_guest'] = false;
				}
			}

			return $settings;
		}
	}
}
