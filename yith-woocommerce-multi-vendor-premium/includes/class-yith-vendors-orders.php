<?php
/**
 * YITH_Vendors_Orders class
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Orders' ) ) {
	/**
	 * This class handle orders stuff!
	 *
	 * @class      YITH_Vendors_Orders
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Orders extends YITH_Vendors_Orders_Legacy {

		/**
		 * The _created_via meta
		 *
		 * @const string
		 */
		const CREATED_VIA = 'yith_wcmv_vendor_suborder';

		/**
		 * Instance of class YITH_Vendors_Orders_Sync
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Orders_Sync|null
		 */
		public $sync = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->sync = new YITH_Vendors_Orders_Sync();
			$this->register_actions();
		}

		/**
		 * Register class actions
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_actions() {
			// Add vendor capabilities.
			add_filter( 'yith_wcmv_vendor_additional_capabilities', array( $this, 'add_order_capabilities' ) );
			// Check suborders on order.
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'check_suborder' ), 20, 2 );
			// Prevent duplicate order if the user use external payment gateway.
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_awaiting_payment' ) );

			// Prevent Multiple Email Notifications for suborders.
			add_filter( 'woocommerce_email_recipient_new_order', array( $this, 'woocommerce_email_recipient_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_recipient_cancelled_order', array( $this, 'woocommerce_email_recipient_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_processing_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_new_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_completed_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_partially_refunded_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_refunded_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_on_hold_order', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );
			add_filter( 'woocommerce_email_enabled_customer_note', array( $this, 'woocommerce_email_enabled_new_order' ), 10, 2 );

			// Order Refund.
			add_action( 'woocommerce_order_refunded', array( $this, 'order_refunded' ), 10, 2 );
			add_action( 'woocommerce_refund_deleted', array( $this, 'refund_deleted' ), 10, 2 );
			// Trash Sync.
			add_action( 'trashed_post', array( $this, 'trash_suborder' ), 10, 1 );
			// YITH WooCommerce Stripe Support.
			add_filter( 'yith_stripe_skip_capture_charge', array( $this, 'skip_stripe_charge_for_suborders' ), 10, 2 );
			// Add shipping addresses to vendor email.
			add_filter( 'woocommerce_order_needs_shipping_address', array( $this, 'order_needs_shipping_address' ), 10, 3 );
			add_action( 'woocommerce_recorded_sales', array( $this, 'recorded_sales_hack' ) );
			// The revoke download permission and the grant download permission would be always synchronized.
			add_action( 'woocommerce_ajax_revoke_access_to_product_download', array( $this, 'revoke_access_to_product_download' ), 10, 3 );
			add_action( 'wp_ajax_woocommerce_grant_access_to_download', array( $this, 'grant_access_to_download' ), 5 );
			// Create order from admin area.
			// WooCommerce complete all process order meta with priority set to 50 or greater.
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'create_suborder_in_admin_area' ), 100, 2 );
		}

		/**
		 * Get order capabilities
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_capabilities() {
			return array(
				'edit_shop_orders'             => true,
				'edit_others_shop_orders'      => true,
				'read_shop_orders'             => true,
				'delete_shop_orders'           => true,
				'publish_shop_orders'          => true,
				'edit_published_shop_orders'   => true,
				'delete_published_shop_orders' => true,
			);
		}

		/**
		 * Add order capabilities for vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $capabilities An array of vendor additional capabilities.
		 * @return array
		 */
		public function add_order_capabilities( $capabilities ) {
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_management', 'no' ) ) {
				$capabilities['orders'] = $this->get_capabilities();
			}

			return $capabilities;
		}

		/**
		 * Get excluded item meta to be imported on suborder
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public static function get_excluded_item_meta() {
			return apply_filters(
				'yith_wcmv_order_item_meta_no_sync',
				array(
					'_child__commission_id',
					'_commission_included_tax',
					'_commission_included_coupon',
				)
			);
		}

		/**
		 * Get suborders from parent_order_id
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @param boolean|integer $parent_order_id The parent order ID. Default false.
		 * @return array
		 */
		public static function get_suborders( $parent_order_id = false ) {
			global $yith_wcmv_cache;

			$suborder_ids    = array();
			$parent_order_id = absint( $parent_order_id );

			if ( $parent_order_id ) {
				// Get first cached value.
				$suborder_ids = $yith_wcmv_cache->get( "suborders_{$parent_order_id}", 'orders' );
				// If cache is empty init it to empty array.
				if ( false === $suborder_ids ) {
					$parent_ids = array( absint( $parent_order_id ) );
					// Compatibility with YITH WooCommerce Sequential Order Number Premium.
					if ( defined( 'YWSON_PREMIUM' ) ) {

						$order_ids = get_posts(
							array(
								'post_type'      => 'shop_order',
								'posts_per_page' => -1,
								'post_status'    => array_keys( wc_get_order_statuses() ),
								'fields'         => 'ids',
								'meta_query'     => array( // phpcs:ignore
									'relation' => 'OR',
									array(
										'key'     => '_ywson_custom_number_order_complete',
										'value'   => $parent_order_id,
										'compare' => 'LIKE',
									),
									array(
										'key'     => 'ywson_custom_quote_number_order',
										'value'   => $parent_order_id,
										'compare' => 'LIKE',
									),
								),
							)
						);

						$parent_ids = array_unique( array_merge( $parent_ids, $order_ids ) );
					}

					$suborder_ids = get_posts(
						array(
							'post_type'       => 'shop_order',
							'posts_per_page'  => -1,
							'post_status'     => array_keys( wc_get_order_statuses() ),
							'post_parent__in' => $parent_ids,
							'fields'          => 'ids',
							'meta_query'      => array( // phpcs:ignore
								array(
									'key'     => '_created_via',
									'value'   => self::CREATED_VIA,
									'compare' => '=',
								),
							),
						)
					);

					// Set instance cache.
					$yith_wcmv_cache->set( "suborders_{$parent_order_id}", $suborder_ids, 'orders' );
				}
			}

			return apply_filters( 'yith_wcmv_get_suborders_ids', $suborder_ids, $parent_order_id );
		}

		/**
		 * Get parent item id from child item id
		 *
		 * @since    1.6
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param integer $parent_item_id The parent item ID.
		 * @return   integer|bool The parent item id if exist, false otherwise.
		 */
		public static function get_child_item_id( $parent_item_id ) {
			global $wpdb, $yith_wcmv_cache;

			$parent_item_id = absint( $parent_item_id );
			// Get first cached value.
			$child_item_id = $yith_wcmv_cache->get( "child_item_id_{$parent_item_id}", 'orders' );
			// If cache is empty init it to empty array.
			if ( false === $child_item_id ) {
				$child_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT order_item_id FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND meta_value=%d", '_parent_line_item_id', $parent_item_id ) ); // phpcs:ignore
				// Set cache.
				$yith_wcmv_cache->set( "child_item_id_{$parent_item_id}", $child_item_id, 'orders' );
			}

			return absint( $child_item_id );
		}

		/**
		 * Retrieve all items from an order, grouping all by vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $parent_order_id The parent order id.
		 * @param array   $args            Additional parameters.
		 * @return array
		 */
		public static function get_order_items_by_vendor( $parent_order_id, $args = array() ) {

			$defaults = array(
				'hide_no_vendor'        => false,
				'hide_without_shipping' => false,
			);

			// Parse incoming $args into an array and merge it with $defaults.
			$args = wp_parse_args( $args, $defaults );

			$parent_order      = wc_get_order( $parent_order_id );
			$items             = $parent_order->get_items();
			$product_by_vendor = array();

			// Check for vendor product.
			foreach ( $items as $item_id => $item ) {
				$vendor    = yith_wcmv_get_vendor( $item['product_id'], 'product' );
				$vendor_id = ( $vendor && $vendor->is_valid() ) ? $vendor->get_id() : 0;
				// Optionally skip product without vendor.
				if ( $args['hide_no_vendor'] && ! $vendor_id ) {
					continue;
				}

				// Optionally skip product without shipping.
				if ( $args['hide_without_shipping'] ) {
					$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
					$product    = wc_get_product( $product_id );
					if ( ! $product->needs_shipping() ) {
						continue;
					}
				}

				$product_by_vendor[ $vendor_id ][ $item_id ] = $item;
			}

			return $product_by_vendor;
		}

		/**
		 * Check order awaiting payment, delete commissions and suborders
		 *
		 * @param array $posted Array of posted data.
		 * @return void
		 */
		public function check_awaiting_payment( $posted ) {
			// Insert or update the post data.
			$order_id = absint( WC()->session->order_awaiting_payment );
			$order    = $order_id ? wc_get_order( $order_id ) : false;

			// Resume the unpaid order if it's pending.
			if ( $order && $order->has_status( array( 'pending', 'failed' ) ) ) {
				$suborder_ids = self::get_suborders( $order_id );

				foreach ( $suborder_ids as $suborder_id ) {
					// Delete associated commissions.
					$commission_ids = yith_wcmv_get_commissions( array( 'order_id' => $suborder_id ) );
					foreach ( $commission_ids as $commission_id ) {
						YITH_Vendors_Commissions_Factory::delete( $commission_id );
					}

					wc_delete_shop_order_transients( $suborder_id );
					wp_delete_post( $suborder_id, true );
				}
			}
		}

		/**
		 * Check for vendor sub-order
		 *
		 * @since   1.6
		 * @author  Andrea Grillo
		 * @author  Francesco Licandro
		 * @param integer|string $parent_order_id The parent order id.
		 * @param array          $posted          (Optional) Array of posted form data. Default empty array.
		 * @param boolean        $return          (Optional) If return or not the sub-orders id array. Default false.
		 * @return array|void
		 * @throws Exception Error on create suborders.
		 */
		public function check_suborder( $parent_order_id, $posted = array(), $return = false ) {
			// Make sure it is a parent order.
			$parent_order = wc_get_order( $parent_order_id );
			if ( $parent_order->get_parent_id() ) {
				return array();
			}

			$items              = $parent_order->get_items();
			$products_by_vendor = array();
			$suborder_ids       = array();

			// Check for vendor product.
			foreach ( $items as $item ) {
				$vendor = yith_wcmv_get_vendor( $item['product_id'], 'product' );
				if ( $vendor && $vendor->is_valid() ) {
					$products_by_vendor[ $vendor->get_id() ][] = $item;
				}
			}

			// If no vendor orders were found, return.
			if ( ! count( $products_by_vendor ) ) {
				return array();
			}

			// Add sub-order to parent.
			foreach ( $products_by_vendor as $vendor_id => $vendor_products ) {
				// Create sub-orders.
				$suborder_ids[] = $this->create_suborder( $parent_order, $vendor_id, $vendor_products, $posted );
			}

			$suborder_ids = array_filter( $suborder_ids ); // Remove empty.
			if ( ! empty( $suborder_ids ) ) {
				$parent_order->add_meta_data( 'has_sub_order', true, true );
				foreach ( $suborder_ids as $suborder_id ) {
					do_action( 'yith_wcmv_checkout_order_processed', $suborder_id );
				}
			}

			$parent_order->save_meta_data();
			if ( $return ) {
				return $suborder_ids;
			}
		}

		/**
		 * Create vendor sub-order
		 * Create an order. Error codes:
		 *        520 - Cannot insert order into the database.
		 *        521 - Cannot get order after creation.
		 *        522 - Cannot update order.
		 *        525 - Cannot create line item.
		 *        526 - Cannot create fee item.
		 *        527 - Cannot create shipping item.
		 *        528 - Cannot create tax item.
		 *        529 - Cannot create coupon item.
		 *
		 * @since  1.6
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param WC_Order $parent_order The parent order object.
		 * @param integer  $vendor_id    The vendor ID.
		 * @param array    $vendor_items An array of vendor order product items.
		 * @param array    $posted       Optional. Array of posted form data.
		 * @return int|boolean
		 * @throws Exception Error on create suborders.
		 */
		public function create_suborder( $parent_order, $vendor_id, $vendor_items, $posted = array() ) {
			global $yith_wcmv_cache;

			try {

				// Get the parent order ID.
				$parent_order_id = $parent_order instanceof WC_Order ? $parent_order->get_id() : 0;
				if ( ! $parent_order_id ) {
					throw new Exception( 'Error: is not possible to create a sub-order without a valid parent order.' );
				}

				$vendor = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
				if ( ! $vendor->is_valid() ) {
					throw new Exception( sprintf( 'Error: trying to create a sub-order for a invalid vendor #%s.', $vendor_id ) );
				}

				// New Order.
				$suborder = new WC_Order();
				// Start adding some order props.
				$customer_id = $parent_order->get_customer_id( 'edit' );
				$order_props = array(
					'parent_id'            => absint( $parent_order_id ),
					'payment_method'       => $parent_order->get_payment_method(),
					'payment_method_title' => $parent_order->get_payment_method_title(),
					'prices_include_tax'   => $parent_order->get_prices_include_tax(),
					'currency'             => $parent_order->get_currency(),
					'created_via'          => self::CREATED_VIA,
					'customer_id'          => is_numeric( $customer_id ) ? absint( $customer_id ) : 0,
					'customer_ip_address'  => $parent_order->get_customer_ip_address(),
					'customer_user_agent'  => $parent_order->get_customer_user_agent(),
					'version'              => YITH_WPV_VERSION,
				);

				foreach ( $order_props as $key => $value ) {
					$setter = "set_{$key}";
					$suborder->$setter( $value );
				}

				// Add custom order meta.
				$suborder->add_meta_data( 'vendor_id', $vendor_id, true ); // Add Vendor ID in order meta.
				foreach ( $parent_order->get_meta_data() as $meta ) {
					$suborder->add_meta_data( $meta->key, $meta->value );
				}

				// Set billing and shipping address.
				$address_types = apply_filters(
					'yith_wcmv_create_order_address_fields',
					array(
						'billing',
						'shipping',
					)
				);
				foreach ( $address_types as $type ) {
					$suborder->set_address( $parent_order->get_address( $type ), $type );
				}

				// Start processing order items.
				$this->create_suborder_line_items( $suborder, $vendor_items );
				// Process shipping methods.
				$this->create_suborder_shipping_items( $suborder, $parent_order->get_shipping_methods(), $vendor_id );
				// Handle coupons.
				$this->create_suborder_coupon_items( $suborder, $parent_order->get_coupons(), $vendor_id );

				// Calculate totals and save.
				$suborder->calculate_totals(); // It also save the order.
				$suborder_id = $suborder->get_id();

				// Let plugins add meta.
				do_action( 'yith_wcmv_checkout_update_order_meta', $suborder_id, $posted );

				// Re calculate totals and save to be sure action are correctly processed.
				$suborder->calculate_totals(); // It also save the order.

				// TODO find a better solution.
				$this->set_post_author( $suborder_id, $vendor->get_owner() );

				// Clear cache.
				$yith_wcmv_cache->delete( "suborders_{$parent_order_id}", 'orders' );
				do_action( 'yith_wcmv_suborder_created', $suborder_id, $parent_order_id, $vendor_id );

				return $suborder_id;

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return false;
			}
		}

		/**
		 * Set post author for new suborder.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $suborder_id The suborder ID.
		 * @param integer $owner_id    The user owner ID.
		 * @return void
		 */
		protected function set_post_author( $suborder_id, $owner_id ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author = %d WHERE ID = %d", $owner_id, $suborder_id ) ); // phpcs:ignore
		}

		/**
		 * Add line items to the suborder.
		 *
		 * @param WC_Order                $suborder  Suborder instance.
		 * @param WC_Order_Item_Product[] $items     An array of items to add.
		 * @param integer                 $vendor_id (Optional) The vendor ID, default 0.
		 * @throws Exception Error on creating suborder items.
		 */
		protected function create_suborder_line_items( &$suborder, $items, $vendor_id = 0 ) {

			foreach ( $items as $item ) {
				$suborder_item = new WC_Order_Item_Product();
				$suborder_item->set_props(
					array(
						'name'         => $item->get_name(),
						'tax_class'    => $item->get_tax_class(),
						'product_id'   => $item->get_product_id(),
						'variation_id' => $item->get_variation_id(),
						'quantity'     => $item->get_quantity(),
						'subtotal'     => $item->get_subtotal(),
						'total'        => $item->get_total(),
						'subtotal_tax' => $item->get_subtotal_tax(),
						'total_tax'    => $item->get_total_tax(),
						'taxes'        => $item->get_taxes(),
					)
				);

				$suborder_item->add_meta_data( '_parent_line_item_id', $item->get_id(), true );
				// TODO check order item reduced stock.
				$item->add_meta_data( '_reduced_stock', $item['quantity'], true );

				$item_meta_data = $item->get_meta_data();
				foreach ( $item_meta_data as $item_obj ) {
					$line_item_data = $item_obj->get_data();

					if ( ! in_array( $line_item_data['key'], self::get_excluded_item_meta(), true ) ) {
						$suborder_item->add_meta_data( $line_item_data['key'], $line_item_data['value'] );
					}
				}

				// Add item to order.
				if ( false === $suborder->add_item( $suborder_item ) ) {
					$suborder_id = $suborder->get_id();
					throw new Exception( "An error occurred adding order item to suborder #{$suborder_id}. Item details: " . print_r( $item, true ) ); // phpcs:ignore
				}
			}
		}

		/**
		 * Add line coupons to the suborder.
		 *
		 * @param WC_Order               $suborder  Suborder instance.
		 * @param WC_Order_Item_Coupon[] $coupons   An array of WC_Order_Item_Coupon to add.
		 * @param integer                $vendor_id Optional. The vendor ID, default 0.
		 * @throws Exception Error on creating suborder coupon items.
		 */
		protected function create_suborder_coupon_items( &$suborder, $coupons, $vendor_id = 0 ) {

			// If empty try to get it from suborder.
			$vendor_id = empty( $vendor_id ) ? absint( $suborder->get_meta( 'vendor_id', true ) ) : absint( $vendor_id );

			foreach ( $coupons as $coupon ) {

				if ( apply_filters( 'yith_wcmv_check_is_vendor_coupon_in_create_suborder', $coupon && absint( $coupon->get_meta( 'vendor_id' ) ) === $vendor_id, $coupon, $vendor_id ) ) {

					$suborder_item = new WC_Order_Item_Coupon();
					$suborder_item->set_props(
						array(
							'code'         => $coupon->get_code(),
							'discount'     => $coupon->get_discount(),
							'discount_tax' => $coupon->get_discount_tax(),
						)
					);

					$coupon_data = $coupon->get_data();
					unset( $coupon_data['used_by'] );
					$suborder_item->add_meta_data( 'coupon_data', $coupon_data );

					$suborder_item->add_meta_data( '_parent_line_item_id', $coupon->get_id(), true );

					if ( false === $suborder->add_item( $suborder_item ) ) {
						$suborder_id = $suborder->get_id();
						throw new Exception( "An error occurred adding coupon to suborder #{$suborder_id}. Coupon details: " . print_r( $suborder_item, true ) ); // phpcs:ignore
					}
				}
			}
		}

		/**
		 * Add line shipping to the suborder.
		 *
		 * @param WC_Order                 $suborder         Suborder instance.
		 * @param WC_Order_Item_Shipping[] $shipping_methods An array of shipping cost to add.
		 * @param integer                  $vendor_id        Optional. The vendor ID, default 0.
		 * @throws Exception Error on creating suborder shipping items.
		 */
		protected function create_suborder_shipping_items( &$suborder, $shipping_methods, $vendor_id = 0 ) {
			// If empty try to get it from suborder.
			$vendor_id = empty( $vendor_id ) ? absint( $suborder->get_meta( 'vendor_id', true ) ) : absint( $vendor_id );

			foreach ( $shipping_methods as $shipping_method ) {
				if ( absint( $shipping_method->get_meta( 'vendor_id' ) ) !== $vendor_id ) {
					continue;
				}

				$suborder_item = new WC_Order_Item_Shipping();
				$suborder_item->set_props(
					array(
						'method_title' => $shipping_method->get_method_title(),
						'method_id'    => $shipping_method->get_method_id(),
						'instance_id'  => $shipping_method->get_instance_id(),
						'total'        => wc_format_decimal( $shipping_method->get_total() ),
						'taxes'        => array(
							'total' => $shipping_method->get_taxes(),
						),
					)
				);

				foreach ( $shipping_method->get_meta_data() as $meta ) {
					$meta_data = $meta->get_data();
					$suborder_item->add_meta_data( $meta_data['key'], $meta_data['value'], true );
				}

				$suborder_item->add_meta_data( '_parent_line_item_id', $shipping_method->get_id(), true );

				if ( false === $suborder->add_item( $suborder_item ) ) {
					$suborder_id = $suborder->get_id();
					throw new Exception( "An error occurred adding coupon to suborder #{$suborder_id}. Shipping method details: " . print_r( $suborder_item, true ) ); // phpcs:ignore
				}
			}

			// Allows plugins to add order item meta to shipping.
			do_action( 'yith_wcmv_add_shipping_order_item', $suborder->get_id(), $this, $vendor_id );
		}

		/**
		 * Prevent duplicated email for customer
		 *
		 * @since  1.6
		 * @author Andrea Grillo
		 * @param boolean $enabled Is email enabled.
		 * @param object  $object  Current object.
		 * @return boolean
		 */
		public function woocommerce_email_enabled_new_order( $enabled, $object ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended

			$is_editpost_action = ! empty( $_REQUEST['action'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), array( 'editpost', 'edit' ), true );
			$request_post_id    = ! empty( $_REQUEST['post_ID'] ) ? absint( $_REQUEST['post_ID'] ) : 0;
			$order_id           = $object instanceof WC_Order ? $object->get_id() : 0;

			if ( $is_editpost_action && $request_post_id && ! wp_get_post_parent_id( $request_post_id ) && $request_post_id !== $order_id ) {
				return false;
			}

			$checksuborder = $order_id && wp_get_post_parent_id( $order_id );

			if ( ! empty( $_REQUEST['ywot_picked_up'] ) ) {
				$checksuborder = ! $checksuborder;
			}

			return $enabled && $checksuborder ? false : $enabled;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Check for email recipient
		 *
		 * @since  1.6
		 * @author Andrea Grillo
		 * @param string   $recipient Email recipient.
		 * @param WC_Order $order     Current object.
		 * @return boolean
		 */
		public function woocommerce_email_recipient_new_order( $recipient, $order ) {
			return ( ( get_option( 'recipient' ) === $recipient || get_option( 'admin_email' ) === $recipient ) && $order instanceof WC_Order && $order->get_parent_id() ) ? false : $recipient;
		}

		/**
		 * Handle a refund via the edit order screen.
		 * Called after wp_ajax_woocommerce_refund_line_items action
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param integer $order_id         The ID of the refunded order.
		 * @param integer $parent_refund_id The refund ID.
		 * @return void
		 */
		public function order_refunded( $order_id, $parent_refund_id ) {
			// phpcs:disable WordPress.Security.NonceVerification
			remove_action( 'woocommerce_order_refunded', array( $this, 'order_refunded' ), 10 );
			if ( ! wp_get_post_parent_id( $order_id ) ) {
				$create_refund           = true;
				$refund                  = false;
				$parent_line_item_refund = 0;
				$refund_amount           = isset( $_POST['refund_amount'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['refund_amount'] ) ), wc_get_price_decimals() ) : 0;
				$refund_reason           = isset( $_POST['refund_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason'] ) ) : '';
				$line_item_qtys          = isset( $_POST['line_item_qtys'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_qtys'] ) ), true ) : array();
				$line_item_totals        = isset( $_POST['line_item_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_totals'] ) ), true ) : array();
				$line_item_tax_totals    = isset( $_POST['line_item_tax_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_tax_totals'] ) ), true ) : array();
				$order                   = wc_get_order( $order_id );
				$parent_order_total      = wc_format_decimal( $order->get_total() );
				$suborder_ids            = self::get_suborders( $order_id );

				// Calculate line items total from parent order.
				foreach ( $line_item_totals as $item_id => $total ) {
					$parent_line_item_refund += (float) wc_format_decimal( $total );
				}

				foreach ( $suborder_ids as $suborder_id ) {
					$suborder               = wc_get_order( $suborder_id );
					$suborder_items_ids     = array_keys( $suborder->get_items( array( 'line_item', 'shipping' ) ) );
					$suborder_total         = wc_format_decimal( $suborder->get_total() );
					$max_refund             = wc_format_decimal( $suborder_total - $suborder->get_total_refunded() );
					$child_line_item_refund = 0;

					// Prepare line items which we are refunding.
					$line_items = array();
					$item_ids   = array_unique( array_merge( array_keys( $line_item_qtys ), array_keys( $line_item_totals ) ) );

					foreach ( $item_ids as $item_id ) {
						$child_item_id = self::get_child_item_id( $item_id );
						if ( $child_item_id && in_array( $child_item_id, $suborder_items_ids ) ) {
							$line_items[ $child_item_id ] = array(
								'qty'          => 0,
								'refund_total' => 0,
								'refund_tax'   => array(),
							);
						}
					}

					foreach ( $line_item_qtys as $item_id => $qty ) {
						$child_item_id = self::get_child_item_id( $item_id );
						if ( $child_item_id && in_array( $child_item_id, $suborder_items_ids ) ) {
							$line_items[ $child_item_id ]['qty'] = max( $qty, 0 );
						}
					}

					foreach ( $line_item_totals as $item_id => $total ) {
						$child_item_id = self::get_child_item_id( $item_id );
						if ( $child_item_id && in_array( $child_item_id, $suborder_items_ids ) ) {
							$child_line_item_refund                      += $total;
							$total                                        = wc_format_decimal( $total );
							$line_items[ $child_item_id ]['refund_total'] = $total;
						}
					}

					foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
						$child_item_id = self::get_child_item_id( $item_id );
						if ( $child_item_id && in_array( $child_item_id, $suborder_items_ids ) ) {
							$line_items[ $child_item_id ]['refund_tax'] = array_map( 'wc_format_decimal', $tax_totals );
						}
					}

					// Calculate refund amount percentage.
					$suborder_refund_amount = ( ( ( $refund_amount - $parent_line_item_refund ) * $suborder_total ) / $parent_order_total );
					$suborder_total_refund  = wc_format_decimal( $child_line_item_refund + $suborder_refund_amount );

					if ( ! $refund_amount || $max_refund < $suborder_total_refund || 0 > $suborder_total_refund ) {
						/**
						 * Invalid refund amount.
						 * Check if suborder total != 0 create a partial refund, exit otherwise
						 */
						$surplus               = wc_format_decimal( $suborder_total_refund - $max_refund );
						$suborder_total_refund = $suborder_total_refund - $surplus;
						$create_refund         = $suborder_total_refund > 0 ? true : false;
					}

					if ( $create_refund ) {

						// Create the refund object.
						$refund = wc_create_refund(
							array(
								'amount'     => $suborder_total_refund,
								'reason'     => $refund_reason,
								'order_id'   => $suborder->get_id(),
								'line_items' => $line_items,
							)
						);

						$refund->add_meta_data( '_parent_refund_id', $parent_refund_id, true );
						$refund->save_meta_data();
					}
				}
			}
			add_action( 'woocommerce_order_refunded', array( $this, 'order_refunded' ), 10, 2 );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle a refund via the edit order screen.
		 * Called after wp_ajax_woocommerce_delete_refund action
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param integer $refund_id       The ID of the refund deleted.
		 * @param integer $parent_order_id The order ID related to the refund deleted.
		 * @return void
		 */
		public static function refund_deleted( $refund_id, $parent_order_id ) {
			check_ajax_referer( 'order-item', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				die( -1 );
			}

			if ( ! wp_get_post_parent_id( $parent_order_id ) ) {
				global $wpdb;
				$child_refund_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s", '_parent_refund_id', $refund_id ) ); // phpcs:ignore

				foreach ( $child_refund_ids as $child_refund_id ) {
					if ( $child_refund_id && 'shop_order_refund' === get_post_type( $child_refund_id ) ) {
						$order_id = wp_get_post_parent_id( $child_refund_id );
						$deleted  = YITH_Vendors()->commissions->delete_commission_refund( $child_refund_id, $order_id, $parent_order_id );

						wc_delete_shop_order_transients( $order_id );
						wp_delete_post( $child_refund_id );
					}
				}
			}
		}

		/**
		 * Trashed parent order sync
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param integer $order_id The order ID trashed.
		 * @return void
		 */
		public function trash_suborder( $order_id ) {
			if ( ! wp_get_post_parent_id( $order_id ) ) {
				$suborder_ids = self::get_suborders( $order_id );
				if ( ! empty( $suborder_ids ) ) {
					foreach ( $suborder_ids as $suborder_id ) {
						wp_trash_post( $suborder_id );
					}
				}
			}
		}

		/**
		 * Skip Stripe capture charge for suborders.
		 *
		 * @param boolean $skip     Current filter value. True to skip, false otherwise.
		 * @param integer $order_id The order ID.
		 * @return false|mixed
		 */
		public function skip_stripe_charge_for_suborders( $skip, $order_id ) {
			if ( wp_get_post_parent_id( $order_id ) ) {
				$skip = false;
			}

			return $skip;
		}

		/**
		 * Delete download permissions via ajax function.
		 *
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param integer $download_id The download ID revoked.
		 * @param integer $product_id  The product ID.
		 * @param integer $order_id    The order ID.
		 * @return void
		 */
		public function revoke_access_to_product_download( $download_id, $product_id, $order_id ) {

			$parent_order_id  = 0;
			$current_order_id = 0;
			if ( wp_get_post_parent_id( $order_id ) ) {
				$order           = wc_get_order( $order_id );
				$parent_order_id = get_post_field( 'post_parent', $order_id );
			}

			global $wpdb;

			if ( $parent_order_id ) {
				$suborders     = self::get_suborders( $order_id );
				$vendor        = yith_wcmv_get_vendor( $product_id, 'product' );
				$vendor_orders = $vendor->get_orders();
				$suborder_id   = array_intersect( $vendor_orders, $suborders );

				if ( count( $suborder_id ) === 1 ) {
					$current_order_id = implode( '', $suborder_id );
				}
			} else {
				$current_order_id = $parent_order_id;
			}

			$query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE order_id = %d AND product_id = %d AND download_id = %s;", $current_order_id, $product_id, $download_id );
			$wpdb->query( $query ); // phpcs:ignore

			wp_die();
		}

		/**
		 * Grant download permissions via ajax function.
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 */
		public static function grant_access_to_download() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended

			check_ajax_referer( 'grant-access', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['loop'], $_POST['order_id'], $_POST['product_ids'] ) ) {
				wp_die( -1 );
			}

			global $wpdb;

			$wpdb->hide_errors();

			$order_id        = intval( $_POST['order_id'] );
			$product_ids     = array_filter( array_map( 'absint', (array) wp_unslash( $_POST['product_ids'] ) ) );
			$loop            = intval( $_POST['loop'] );
			$file_counter    = 0;
			$parent_order_id = 0;

			if ( wp_get_post_parent_id( $order_id ) ) {
				$order           = wc_get_order( $order_id );
				$parent_order_id = get_post_field( 'post_parent', $order_id );
			}

			$suborders = self::get_suborders( $order_id );

			if ( ! is_array( $product_ids ) ) {
				$product_ids = array( $product_ids );
			}

			foreach ( $product_ids as $product_id ) {

				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}

				$files = $product->get_downloads();

				if ( ! $parent_order_id ) {
					$vendor        = yith_wcmv_get_vendor( $product_id, 'product' );
					$vendor_orders = $vendor->get_orders();
					$suborder_id   = array_intersect( $vendor_orders, $suborders );

					if ( count( $suborder_id ) === 1 ) {
						$suborder_id = implode( '', $suborder_id );
						$order       = wc_get_order( $suborder_id );
					}
				} else {
					$order = wc_get_order( $parent_order_id );
				}

				$billing_email = ! empty( $order ) ? $order->get_meta( 'billing_email' ) : false;

				if ( ! $billing_email ) {
					return;
				}

				if ( ! empty( $files ) ) {
					foreach ( $files as $download_id => $file ) {
						$inserted_id = wc_downloadable_file_permission( $download_id, $product_id, $order );
						if ( $inserted_id ) {
							// Insert complete - get inserted data.
							$download = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE permission_id = %d", $inserted_id ) ); // phpcs:ignore

							$loop++;
							$file_counter++;

							if ( isset( $file['name'] ) ) {
								$file_count = $file['name'];
							} else {
								// translators: %d is a number representing the number of file.
								$file_count = sprintf( __( 'File %d', 'yith-woocommerce-product-vendors' ), $file_counter );
							}
							// include( WC()->plugin_path().'/includes/admin/meta-boxes/views/html-order-download-permission.php' );
						}
					}
				}
			}

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Checks if an order needs display the shipping address, based on shipping method.
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param boolean  $needs_address Current value.
		 * @param array    $hide          An array of shipping methods that do not need to display shipping address.
		 * @param WC_Order $order         The order instance.
		 * @return boolean
		 */
		public function order_needs_shipping_address( $needs_address, $hide, $order ) {
			// Exclude quote orders. TODO move this code to a compatibility class.
			$raq_order_meta = $order->get_meta( 'ywraq_raq' );
			$is_quote       = ! empty( $raq_order_meta );

			$post_parent = get_post_field( 'post_parent', $order->get_id() );

			if ( $post_parent && ! $is_quote ) {
				$parent_order = wc_get_order( $post_parent );

				if ( ! function_exists( 'wc_shipping_enabled' ) ? wc_shipping_enabled() : 'yes' === get_option( 'woocommerce_calc_shipping' ) ) {
					return false;
				}

				$hide          = apply_filters( 'woocommerce_order_hide_shipping_address', array( 'local_pickup' ), $this );
				$needs_address = false;

				foreach ( $parent_order->get_shipping_methods() as $shipping_method ) {
					if ( ! in_array( $shipping_method['method_id'], $hide, true ) ) {
						$needs_address = true;
						break;
					}
				}
			}

			return $needs_address;
		}

		/**
		 * Update total sales amount for each product within a paid order.
		 *
		 * @since   4.0.0
		 * @author  Andrea Grillo
		 * @author  Francesco Licandro
		 * @param integer $order_id The order ID.
		 * @return void
		 */
		public function recorded_sales_hack( $order_id ) {
			$order = wc_get_order( $order_id );
			// If is a sub-order.
			if ( $order && $order->get_parent_id() && count( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item ) {
					$product_id = $item->get_product_id();

					if ( $product_id ) {
						$data_store = WC_Data_Store::load( 'product' );
						$data_store->update_product_sales( $product_id, absint( $item->get_quantity() ), 'decrease' );
					}
				}

				$order->get_data_store()->set_recorded_sales( $order, true );
			}
		}

		/**
		 * Create Suborder via admin area
		 *
		 * @since    3.4.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param integer $post_id The created order ID.
		 * @param WP_Post $post    WP_post object.
		 * @return void
		 */
		public function create_suborder_in_admin_area( $post_id, $post ) {
			$parent_order = wc_get_order( $post_id );
			$has_suborder = $parent_order ? $parent_order->get_meta( 'has_sub_order' ) : false;

			if ( empty( $has_suborder ) && is_admin() && ! wp_doing_ajax() ) {
				$suborder_ids = $this->check_suborder( $post_id, array(), true );
				if ( ! empty( $suborder_ids ) ) {
					do_action( 'yith_wcmv_suborders_created_via_dashboard', $suborder_ids );
				}
			}
		}
	}
}
