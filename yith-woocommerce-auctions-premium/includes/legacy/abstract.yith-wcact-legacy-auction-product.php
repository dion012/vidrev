<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Legacy_Auction_Product Class.
 *
 * @package YITH\Auctions\Includes\Migration
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class YITH_WCACT_Legacy_Auction_Product
 *
 * @class   YITH_WCACT_Legacy_Auction_Product
 * @package Yithemes
 * @since   Version 1.3.4
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_WCACT_Legacy_Auction_Product' ) ) {
	/**
	 * Class YITH_WCACT_Legacy_Auction_Product
	 * the Auction Product
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	abstract class YITH_WCACT_Legacy_Auction_Product extends WC_Product {

		/**
		 * Get minimum manual bid increment
		 *
		 * @since 1.3.4
		 */
		public function get_minimum_manual_bid_increment() {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::get_minimum_manual_bid_increment()', 'WC_Product_Auction_Premium::get_minimum_increment_amount', '1.3.4' );
			$this->get_minimum_increment_amount();
		}

		/**
		 * Get buy now price
		 *
		 * @since 1.3.4
		 */
		public function get_buy_now_price() {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::get_buy_now_price()', 'WC_Product_Auction_Premium::get_buy_now()', '1.3.4' );
			$this->get_buy_now();
		}

		/**
		 *  Check if the auction is close for user click in buttom buy_now and place order.
		 */
		public function is_closed_for_buy_now() {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::is_closed_for_buy_now()', 'WC_Product_Auction_Premium::get_is_closed_by_buy_now()', '1.3.4' );
			$this->get_is_closed_by_buy_now();
		}

		/**
		 *  Check if the auction is paid
		 */
		public function is_paid() {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::is_paid()', 'WC_Product_Auction_Premium::get_auction_paid_order()', '1.3.4' );
			$this->get_auction_paid_order();
		}

		/**
		 *  Check if User is in follower list
		 *
		 * @param string $user_email User email.
		 */
		public function is_in_watchlist( $user_email ) {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::is_in_watchlist($user_email)', 'WC_Product_Auction_Premium::is_in_followers_list($user_email)', '2.0.0' );
			$this->is_in_followers_list( $user_email );
		}

		/**
		 *  Add user un the follower list
		 *
		 * @param string $user_email User email.
		 */
		public function set_watchlist( $user_email ) {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::set_watchlist($user_email)', 'WC_Product_Auction_Premium::add_user_in_followers_list($user_email)', '2.0.0' );
			$this->add_user_in_followers_list( $user_email );
		}

		/**
		 * Get watchlist
		 */
		public function get_watchlist() {
			$this->yith_wcact_deprecated_function( 'WC_Product_Auction_Premium::get_watchlist()', 'WC_Product_Auction_Premium::get_followers_list()', '2.0.0' );
			$this->get_followers_list();
		}

		/**
		 * Show deprecated notice
		 *
		 * @param string $function Function.
		 * @param string $replacement Replacement.
		 * @param string $version Version.
		 * @since 1.3.4
		 */
		public function yith_wcact_deprecated_function( $function, $replacement, $version ) {
			if ( is_ajax() ) {
				do_action( 'deprecated_function_run', $function, $replacement, $version );

				$log_string  = "The {$function} function is deprecated since version {$version}.";
				$log_string .= $replacement ? " Replace with {$replacement}." : '';
				error_log( $log_string ); // phpcs:ignore
			} else {
				_deprecated_function( $function, $version, $replacement ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}
