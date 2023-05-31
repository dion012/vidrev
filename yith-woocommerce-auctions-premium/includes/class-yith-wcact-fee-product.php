<?php
/**
 * YITH_WCACT_Fee_Product Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WCACT_Fee_Product' ) ) {
	/**
	 * YITH_WCACT_Fee_Product
	 *
	 * @since 2.0.0
	 */
	class YITH_WCACT_Fee_Product {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Fee_Product
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Var Auction fee creation
		 *
		 * @var int The default product for create fee on auction product
		 */
		public $auction_product_fee_id = -1;

		/**
		 * Constructor
		 *
		 * @since  2.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'create_fee_auction_product' ) );

			add_filter( 'woocommerce_is_purchasable', array( $this, 'auction_is_purchasable' ), 10, 2 );

			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'update_order_item_data_for_auction_fee' ), 30, 3 );

			add_filter( 'woocommerce_cart_item_name', array( $this, 'fee_product_name' ), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'fee_product_name' ), 10, 3 );
			add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'fee_product_image' ), 10, 3 );
			add_filter( 'woocommerce_admin_order_item_thumbnail', array( $this, 'fee_product_image_admin_order' ), 10, 3 );

			add_action( 'woocommerce_order_status_completed', array( $this, 'register_customer_fee' ), 10, 1 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'register_customer_fee' ), 10, 1 );

			/**
			 * Hide the default product for auction fee on the admin products list
			 * */
			add_action( 'pre_get_posts', array( $this, 'hide_default_auction_fee_product' ) );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Fee_Product
		 * @since  2.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Create fee auction product
		 *
		 * @since 2.0.0
		 */
		public function create_fee_auction_product() {
			$this->auction_product_fee_id = get_option( 'yith_wcact_fee_auction_product_id', -1 );

			if ( -1 === $this->auction_product_fee_id || ! wc_get_product( $this->auction_product_fee_id ) ) {
				// Create auction fee product.
				$args = array(
					'post_title'     => esc_html__( 'Auction fee', 'yith-auctions-for-woocommerce' ),
					'post_name'      => 'yith_wcact_auction_fee_product',
					'post_content'   => esc_html__( 'This product has been automatically created by the plugin YITH WooCommerce Auction. You must not edit it, or the plugin might not work properly. The main functionality of this product is to be used for the feature "Ask fee payment before placing a bid"', 'yith-auctions-for-woocommerce' ),
					'post_status'    => 'private',
					'post_date'      => gmdate( 'Y-m-d H:i:s' ),
					'post_author'    => 0,
					'post_type'      => 'product',
					'comment_status' => 'closed',
				);

				$this->auction_product_fee_id = wp_insert_post( $args );

				update_option( 'yith_wcact_fee_auction_product_id', $this->auction_product_fee_id );
				wp_set_object_terms( $this->auction_product_fee_id, 'simple', 'product_type' );

				// set this default gift card product as virtual.
				$product = wc_get_product( $this->auction_product_fee_id );

				if ( $product ) {
					yit_save_prop( $product, '_virtual', 'yes' );
					yit_save_prop( $product, '_visibility', 'hidden' );
				}
			} else {
				$product = wc_get_product( $this->auction_product_fee_id );

				if ( $product && 'simple' !== $product->get_type() ) {
					wp_set_object_terms( $product->get_id(), 'simple', 'product_type' );
				}
			}
		}

		/**
		 * Check if it's fee product in order to make it purchasable
		 *
		 * @param bool       $purchasable is product purchasable.
		 * @param WC_Product $product Product.
		 * @since 2.0.0
		 */
		public function auction_is_purchasable( $purchasable, $product ) {
			if ( ( $product instanceof WC_Product_Simple ) && ( (int) $product->get_id() === (int) $this->auction_product_fee_id ) ) {
				return true;
			}

			return $purchasable;
		}

		/**
		 *  Add order item for auction fee product
		 *
		 * @param  WC_PRODUCT $item product item.
		 * @param  string     $cart_item_key cart item key.
		 * @param  mixed      $values values.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function update_order_item_data_for_auction_fee( $item, $cart_item_key, $values ) {
			if ( isset( $values['yith_wcact_if_fee_product'] ) && $values['yith_wcact_if_fee_product'] ) {
				$item->add_meta_data( '_ywcact_is_fee', true );
				$item->add_meta_data( '_ywcact_fee_amount', $values['yith_wcact_fee_value'] );
				$item->add_meta_data( '_ywcact_auction_id', $values['yith_wcact_auction_id'] );

				/**
				 * DO_ACTION: yith_wcact_order_item_data
				 *
				 * Allow to fire some action when saving meta data for the fee product.
				 *
				 * @param WC_Order_Item_Product $item          Order item
				 * @param string                $cart_item_key Cart item key
				 * @param array                 $values        Values
				 */
				do_action( 'yith_wcact_order_item_data', $item, $cart_item_key, $values );
			}
		}

		/**
		 *  Register customer fee on database
		 *
		 * @param  int $order_id order id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function register_customer_fee( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order ) {
				$order_items = $order->get_items();

				foreach ( $order_items as $order_item_id => $order_item ) {
					$is_fee_item = wc_get_order_item_meta( $order_item_id, '_ywcact_is_fee', true );

					if ( ! $is_fee_item ) {
						continue;
					}

					$fee_amount = wc_get_order_item_meta( $order_item_id, '_ywcact_fee_amount', true );
					$auction_id = wc_get_order_item_meta( $order_item_id, '_ywcact_auction_id', true );
					$user_id    = $order->get_user_id();
					$date       = gmdate( 'Y-m-d H:i:s' );
					$db         = YITH_Auctions()->bids;
					$db->register_fee( $user_id, $auction_id, $date, $order_id, $fee_amount );
				}
			}
		}

		/**
		 *  Get Fee product title
		 *
		 * @param string $product_title The product title HTML.
		 * @param array  $cart_item     The cart item array.
		 * @param bool   $cart_item_key The cart item key.
		 *
		 * @since  2.0.0
		 * @author Carlos Rodriguez <carlos.rodriguez@yithemes.com>
		 * @return string  The product title HTML
		 * @use    woocommerce_cart_item_name hook
		 */
		public function fee_product_name( $product_title, $cart_item, $cart_item_key = false ) {
			if ( is_array( $cart_item ) && ! empty( $cart_item['yith_wcact_if_fee_product'] ) && ! empty( $cart_item['yith_wcact_auction_id'] ) ) {
				$product_id = $cart_item['yith_wcact_auction_id'];

				/**
				 * APPLY_FILTERS: yith_wcact_cart_auction_fee_start_message
				 *
				 * Filter the start label for the fee product in the cart.
				 *
				 * @param string $start_message Start message
				 *
				 * @return
				 */
				$product_title = apply_filters( 'yith_wcact_cart_auction_fee_start_message', esc_html__( 'Auction fee - ', 'yith-auctions-for-woocommerce' ) ) . wc_get_product( $product_id )->get_name();
			}

			/**
				 * APPLY_FILTERS: yith_wcact_cart_product_title
				 *
				 * Filter the title for the fee product in the cart.
				 *
				 * @param string $product_title Product title
				 * @param array  $cart_item     Cart item
				 * @param string $cart_item_key Cart item key
				 *
				 * @return
				 */
			return apply_filters( 'yith_wcact_cart_product_title', $product_title, $cart_item, $cart_item_key );
		}

		/**
		 *    Fee product image on cart page.
		 *
		 * @param string $product_image The product image HTML.
		 * @param array  $cart_item     The cart item array.
		 * @param bool   $cart_item_key The cart item key.
		 */
		public function fee_product_image( $product_image, $cart_item, $cart_item_key = false ) {
			if ( ! empty( $cart_item['yith_wcact_if_fee_product'] ) && ! empty( $cart_item['yith_wcact_auction_id'] ) ) {
				$product_id = $cart_item['yith_wcact_auction_id'];
				$product    = wc_get_product( $product_id );

				if ( $product && 'auction' === $product->get_type() ) {
					$product_image = $product->get_image( 'thumbnail', array( 'title' => '' ), false );
				}
			}

			return $product_image;
		}

		/**
		 *    Fee product image on admin order page
		 *
		 * @param string $product_image The product image HTML.
		 * @param int    $item_id     The cart item array.
		 * @param bool   $item The cart item key.
		 */
		public function fee_product_image_admin_order( $product_image, $item_id, $item ) {
			$auction_id = wc_get_order_item_meta( $item_id, '_ywcact_auction_id', true );

			if ( $auction_id ) {
				$product = wc_get_product( $auction_id );

				if ( $product && 'auction' === $product->get_type() ) {
					$product_image = $product->get_image( 'thumbnail', array( 'title' => '' ), false );
				}
			}

			return $product_image;
		}

		/**
		 * Avoid to show the default auction fee product
		 *
		 * @param array $query The query.
		 *
		 * @author Carlos Rodríguez
		 * @since  2.0.0
		 */
		public function hide_default_auction_fee_product( $query ) {
			global $pagenow;

			/**
			 * APPLY_FILTERS: yith_wcact_pre_get_posts_hide_default_auction_fee_product
			 *
			 * Filter whether to hide the fee product from the products list in the backend.
			 *
			 * @param bool     $hide_fee_product Whether to hide the fee product or not
			 * @param WP_Query $query            Query
			 *
			 * @return bool
			 */
			if ( $query->is_admin && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] && apply_filters( 'yith_wcact_pre_get_posts_hide_default_auction_fee_product', true, $query ) ) { // phpcs:ignore
				$query->set( 'post__not_in', array( get_option( 'yith_wcact_fee_auction_product_id' ) ) );
			}
		}
	}
}

/**
 * Unique access to instance of YITH_WCAF_Premium class
 *
 * @return \YITH_WCACT_Fee_Product
 * @since  2.0.0
 */
function YITH_WCACT_Fee_Product() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WCACT_Fee_Product::get_instance();
}
