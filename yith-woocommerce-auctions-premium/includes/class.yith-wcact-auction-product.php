<?php // phpcs:ignore WordPress.NamingConventions
/**
 * WC_Product_Auction Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class YITH Auctions
 *
 * @class   YITH_AUCTIONS
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'WC_Product_Auction' ) ) {
	/**
	 * Class WC_Product_Auction
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class WC_Product_Auction extends YITH_WCACT_Legacy_Auction_Product {

		/**
		 * Constructor gets the post object and sets the ID for the loaded product.
		 *
		 * @param int|WC_Product|object $product Product ID, post object, or product object.
		 */
		public function __construct( $product = 0 ) {
			$this->supports[] = 'ajax_add_to_cart_on_my_account';

			/** $this->set_manage_stock( 'yes' ); */
			/** $this->set_stock_quantity( 1 ); */

			parent::__construct( $product );
		}

		/**
		 * Get Auction price
		 *
		 * @param  string $context What the value is for. Valid values are view and edit.
		 * @return float|boolean
		 */
		public function get_price( $context = 'view' ) {
			$price = parent::get_price( 'edit' );

			global $wpml_post_translations;

			$id = $this->get_id();

			if ( $wpml_post_translations ) {
				$parent_id      = $wpml_post_translations->get_original_element( $id );
				$parent_product = wc_get_product( $parent_id );

				if ( $parent_product ) {
					/**
					 * APPLY_FILTERS: yith_wcact_get_price_for_customers
					 *
					 * Filter the current product price.
					 *
					 * @param string     $price Price
					 * @param WC_Product $product Product object
					 *
					 * @return $price
					 */
					return apply_filters( 'yith_wcact_get_price_for_customers', apply_filters( 'woocommerce_product_get_price', $price ? $price : $parent_product->get_current_bid(), $this ), $this );
				} else {
					return apply_filters( 'yith_wcact_get_price_for_customers', apply_filters( 'woocommerce_product_get_price', $this->get_current_bid(), $this ), $this );
				}
			} else {
				return apply_filters( 'yith_wcact_get_price_for_customers', apply_filters( 'woocommerce_product_get_price', $price ? $price : $this->get_current_bid(), $this ), $this );
			}
		}

		/**
		 *  Check if the auction is start.
		 */
		public function is_start() {
			$start_time = $this->get_start_date();

			if ( isset( $start_time ) && $start_time ) {
				$date_for = $start_time;
				$date_now = strtotime( 'now' );

				if ( $date_for <= $date_now ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		/**
		 *  Check if the auction is close.
		 */
		public function is_closed() {
			$end_time = $this->get_end_date();

			if ( isset( $end_time ) && $end_time ) {
				$date_to  = $end_time;
				$date_now = strtotime( 'now' );

				if ( $date_to <= $date_now ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		/**
		 *  Return auction status.
		 */
		public function get_auction_status() {
			if ( $this->is_start() && ! $this->is_closed() ) {
				return 'started';
			} elseif ( $this->is_closed() ) {
				return 'finished';
			} else {
				return 'non-started';
			}
		}
	}
}
