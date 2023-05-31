<?php
/**
 * YITH_Vendors_Orders_Premium class
 *
 * @since 4.0.0
 * @author YITH
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

if ( ! class_exists( 'YITH_Vendors_Orders_Premium' ) ) {
	/**
	 * Premium extension of YITH_Vendors_Orders class.
	 *
	 * @class      YITH_Vendors_Orders_Premium
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Orders_Premium extends YITH_Vendors_Orders {

		/**
		 * Class construct.
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 */
		public function __construct() {
			parent::__construct();
			$this->register_premium_actions();
		}

		/**
		 * Register class actions
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_premium_actions() {

			add_filter( 'yith_wcmv_force_to_trigger_new_order_email_action', '__return_true' );

			if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' ) ) {
				add_action( 'woocommerce_order_refunded', array( $this, 'child_order_refunded' ), 10, 2 );
				add_action( 'before_delete_post', array( $this, 'before_delete_child_refund' ), 5, 1 );
			}

			add_filter( 'woocommerce_order_actions', array( $this, 'add_custom_order_actions' ) );
			add_action( 'woocommerce_order_action_new_order_to_vendor', array( $this, 'woocommerce_order_action' ), 10, 1 );
			add_action( 'woocommerce_order_action_cancelled_order_to_vendor', array( $this, 'woocommerce_order_action' ), 10, 1 );
			// Add vendor information to parent shipping method.
			add_action( 'woocommerce_checkout_create_order_shipping_item', array( $this, 'add_vendor_information_to_parent_shipping_item' ), 10, 4 );
		}

		/**
		 * Handle order admin actions
		 *
		 * @since 4.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param WC_Order $order The order object.
		 * @return void
		 */
		public function woocommerce_order_action( $order ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $_POST['wc_order_action'] ) || ! ( $order instanceof WC_Order ) ) {
				return;
			}

			// Validate action.
			$email_action = sanitize_text_field( wp_unslash( $_POST['wc_order_action'] ) );

			// Switch back to the site locale.
			wc_switch_to_site_locale();
			// Ensure gateways are loaded in case they need to insert data into the emails.
			WC()->payment_gateways();
			WC()->shipping();

			// Load mailer.
			$mailer = WC()->mailer();
			$mails  = $mailer->get_emails();

			if ( ! empty( $mails ) ) {
				foreach ( $mails as $mail ) {
					if ( $mail->id === $email_action ) {
						$mail->trigger( $order->get_id(), $order );
						// translators: %s: email title.
						$order->add_order_note( sprintf( __( '%s email notification manually sent.', 'woocommerce' ), $mail->title ), false, true );
						break;
					}
				}
			}

			// Restore user locale.
			wc_restore_locale();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle a refund via the edit order screen.
		 * Called after wp_ajax_woocommerce_refund_line_items action
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param integer $order_id The ID of the refunded order.
		 * @param integer $parent_refund_id The refund ID.
		 * @return void
		 */
		public function order_refunded( $order_id, $parent_refund_id ) {
			remove_action( 'woocommerce_order_refunded', array( $this, 'child_order_refunded' ), 10 );
			parent::order_refunded( $order_id, $parent_refund_id );
			add_action( 'woocommerce_order_refunded', array( $this, 'child_order_refunded' ), 10, 2 );
		}

		/**
		 * Handle a refund via the edit order screen.
		 * Called after wp_ajax_woocommerce_refund_line_items action
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param integer $order_id The ID of the refunded order.
		 * @param integer $child_refund_id The refund ID.
		 * @return void
		 */
		public function child_order_refunded( $order_id, $child_refund_id ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$child_order = wc_get_order( $order_id );
			// Make sure there is a parent order.
			if ( empty( $child_order ) || ! $child_order->get_parent_id() ) {
				return;
			}

			remove_action( 'woocommerce_order_refunded', array( $this, 'order_refunded' ), 10 );
			remove_action( 'woocommerce_order_refunded', array( $this, 'child_order_refunded' ), 10 );

			$parent_order_id    = $child_order->get_parent_id();
			$refund_reason      = isset( $_POST['refund_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason'] ) ) : '';
			$line_items         = array();
			$child_refund       = wc_get_order( $child_refund_id );
			$refund_child_items = $child_refund->get_items( array( 'line_item', 'shipping' ) );
			$total_refund       = 0;

			foreach ( $refund_child_items as $refund_child_item_id => $refund_child_item ) {

				$item_id        = $refund_child_item->get_meta( '_refunded_item_id', true );
				$parent_item_id = self::get_parent_item_id( $child_order, $item_id );

				if ( $parent_item_id && ! isset( $line_items[ $parent_item_id ] ) ) {

					$child_refund_taxes = $refund_child_item->get_taxes();
					$refund_taxes       = array();
					$total_tax          = 0;

					foreach ( $child_refund_taxes as $key => $tax ) {
						foreach ( $tax as $tax_id => $value ) {
							$refund_taxes[ $tax_id ] = abs( $value );

							if ( 'total' === $key ) {
								$total_tax += $refund_taxes[ $tax_id ];
							}
						}
					}

					$line_items[ $parent_item_id ] = array(
						'qty'          => abs( $refund_child_item->get_quantity() ),
						'refund_total' => abs( $refund_child_item->get_total() ),
						'refund_tax'   => $refund_taxes,
					);

					$total_refund += abs( $refund_child_item->get_total() ) + $total_tax;
				}
			}

			if ( count( $line_items ) ) {
				// Create the refund object.
				$refund = wc_create_refund(
					array(
						'amount'     => $total_refund,
						'reason'     => $refund_reason,
						'order_id'   => $parent_order_id,
						'line_items' => $line_items,
					)
				);

				if ( $refund instanceof WC_Order_Refund ) {
					$child_order = wc_get_order( $child_refund_id );
					if ( $child_order instanceof WC_Order_Refund ) {
						$child_order->add_meta_data( '_parent_refund_id', $refund->get_id(), true );
						$child_order->save_meta_data();
					}

					$refund->add_meta_data( '_child_refund_id', $child_refund_id );
					$refund->save_meta_data();
				}
			}

			add_action( 'woocommerce_order_refunded', array( $this, 'order_refunded' ), 10, 2 );
			add_action( 'woocommerce_order_refunded', array( $this, 'child_order_refunded' ), 10, 2 );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle a refund via the edit order screen.
		 * Need to delete parent refund from child order
		 * Called in wp_ajax_woocommerce_delete_refund action
		 *
		 * @use before_delete_post
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param integer $refund_id The refund ID.
		 */
		public function before_delete_child_refund( $refund_id ) {

			$child_refund = wc_get_order( $refund_id );

			if ( $child_refund && 'shop_order_refund' === $child_refund->get_type() ) {

				$order_id = $child_refund->get_parent_id();
				$order    = wc_get_order( $order_id );

				// If is a child order, we are deleting a child refund.
				if ( $order && self::CREATED_VIA === $order->get_created_via() ) {

					$parent_order_id  = $order->get_parent_id();
					$parent_refund_id = $child_refund->get_meta( '_parent_refund_id' );
					$parent_refund    = wc_get_order( $parent_refund_id );

					if ( $parent_order_id && $parent_refund ) {
						YITH_Vendors()->commissions->delete_commission_refund( $refund_id, $order_id, $parent_order_id );
						wc_delete_shop_order_transients( $parent_order_id );
						wp_delete_post( $parent_refund_id );
					}
				}
			}
		}

		/**
		 * Add Order actions for vendors
		 *
		 * @since  1.9.14
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param array $actions An array of order actions available.
		 * @return array
		 */
		public function add_custom_order_actions( $actions ) {
			$actions = array_merge(
				$actions,
				array(
					'new_order_to_vendor'       => __( 'New order (to vendor)', 'yith-woocommerce-product-vendors' ),
					'cancelled_order_to_vendor' => __( 'Canceled order (to vendor)', 'yith-woocommerce-product-vendors' ),
				)
			);

			return $actions;
		}

		/**
		 * Add vendor info to parent shipping item
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param WC_Order_Item_Shipping $item The shipping order item.
		 * @param string                 $package_key The item package key.
		 * @param array                  $package The package.
		 * @param WC_Order               $order The order object.
		 * @return void
		 */
		public function add_vendor_information_to_parent_shipping_item( $item, $package_key, $package, $order ) {
			if ( $order instanceof WC_Order && 'checkout' === $order->get_created_via() && ! empty( $package['yith-vendor'] ) && $package['yith-vendor'] instanceof YITH_Vendor ) {
				$checkout = WC()->checkout();
				if ( ! empty( $checkout ) ) {
					$package_id = $package['rates'][ $checkout->shipping_methods[ $package_key ] ]->get_id();
					$vendor     = $package['yith-vendor'];
					$item->add_meta_data( '_vendor_package_id', $package_id, true );
					$item->add_meta_data( 'vendor_id', $vendor->get_id(), true );
					$item->save();
				}
			}
		}
	}
}
