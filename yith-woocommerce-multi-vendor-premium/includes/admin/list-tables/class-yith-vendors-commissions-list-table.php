<?php
/**
 * YITH Vendors Commissions List Table Class.
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'YITH_Vendors_Commissions_List_Table' ) ) {
	/**
	 * @class      YITH_Vendors_Commissions_List_Table
	 * @since      Version 1.0.0
	 * @author     Your Inspiration Themes
	 * @package    Yithemes
	 */
	class YITH_Vendors_Commissions_List_Table extends WP_List_Table {

		/**
		 * Construct
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'commission',
					'plural'   => 'commissions',
					'ajax'     => false,
					'screen'   => 'yith-plugins_page_yith-wcmv-commissions-list',
				)
			);

			add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
		}

		/**
		 * Returns columns available in table
		 *
		 * @since 1.0.0
		 * @return array Array of columns of the table
		 */
		public function get_columns() {

			return apply_filters(
				'yith_wcmv_commissions_list_table_column',
				array(
					'commission_id' => __( 'ID', 'yith-woocommerce-product-vendors' ),
					'date'          => __( 'Date', 'yith-woocommerce-product-vendors' ),
					'date_edit'     => __( 'Last update', 'yith-woocommerce-product-vendors' ),
					'order_id'      => __( 'Order', 'yith-woocommerce-product-vendors' ),
					'line_item'     => __( 'Product', 'yith-woocommerce-product-vendors' ),
					'rate'          => __( 'Rate', 'yith-woocommerce-product-vendors' ),
					'amount'        => __( 'Total', 'yith-woocommerce-product-vendors' ),
					'refunded'      => __( 'Refunded', 'yith-woocommerce-product-vendors' ),
					'to_pay'        => __( 'Commission', 'yith-woocommerce-product-vendors' ),
					// translators: %s stand for the vendor taxonomy singular label.
					'vendor'        => sprintf( _x( '%s info', '[Admin] %s stand for the vendor taxonomy singular label', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) ),
					'status'        => __( 'Status', 'yith-woocommerce-product-vendors' ),
					'actions'       => '',
				)
			);
		}

		/**
		 * Gets a list of CSS classes for the WP_List_Table table tag.
		 *
		 * @since 3.1.0
		 * @return string[] Array of CSS classes for the table tag.
		 */
		protected function get_table_classes() {
			$classes = parent::get_table_classes();

			return array_merge( $classes, array( 'yith-plugin-fw__classic-table' ) );
		}

		/**
		 * Adjust which columns are displayed by default.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array  $hidden Current hidden columns.
		 * @param object $screen Current screen.
		 * @return array
		 */
		public function default_hidden_columns( $hidden, $screen ) {
			$hidden = array_merge(
				$hidden,
				array(
					'amount',
					'refunded',
					'date_edit',
				)
			);

			return $hidden;
		}

		/**
		 * Prepare items for table
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function prepare_items() {

			$items = array();

			// Sets pagination arguments.
			$per_page     = apply_filters( 'yith_wcmv_commissions_list_table_per_page', 20 );
			$current_page = absint( $this->get_pagenum() );
			$order        = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Commissions args.
			$args = array(
				'status'  => $this->get_current_view(),
				'paged'   => $current_page,
				'number'  => $per_page,
				'orderby' => $orderby ? $orderby : 'ID',
				'order'   => in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? $order : 'DESC',
			);

			$args = apply_filters( 'yith_wcmv_commissions_list_table_args', $args );

			$commission_ids = YITH_Vendors_Commissions_Factory::query( $args );
			$total_items    = YITH_Vendors_Commissions_Factory::count( $args );

			// Sets columns headers.
			$columns               = $this->get_columns();
			$hidden                = get_hidden_columns( $this->screen->id );
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			foreach ( $commission_ids as $commission_id ) {
				$items[] = yith_wcmv_get_commission( absint( $commission_id ) );
			}

			// Retrieve data for table. Use array filter to remove empty commission (this should not happen anyway).
			$this->items = array_filter( $items );

			// Sets pagination args.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}

		/**
		 * Display the search box.
		 *
		 * @since  4.0.0
		 * @access public
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 * @return mixed
		 */
		public function add_search_box( $text, $input_id ) {
			return false;
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @since 4.0.0
		 * @return array The array of columns that can be sorted by the user.
		 */
		public function get_sortable_columns() {
			return array(
				'commission_id' => array( 'ID', false ),
				'order_id'      => array( 'order_id', false ),
				'amount'        => array( 'amount', false ),
				'date_edit'     => array( 'last_edit', false ),
				'vendor'        => array( 'vendor_id', false ),
			);
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @since 4.0.0
		 * @return array Array of available actions.
		 */
		public function get_bulk_actions() {
			return array();
		}

		/**
		 * Print the columns information
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendors_Commission $commission  The commission object.
		 * @param string                  $column_name Current column name.
		 * @return string
		 */
		protected function column_default( $commission, $column_name ) {

			$return = '';

			switch ( $column_name ) {

				case 'commission_id':
					$return = '<a href="javascript:void(0)" class="commission-details" data-commission_id="' . $commission->get_id() . '">' . sprintf( '#%1$d', $commission->get_id() ) . '</a>';
					break;

				case 'order_id':
					$order     = $commission->get_order();
					$order_uri = $commission->get_formatted_order_uri();
					$user      = yith_wcmv_get_formatted_order_user_html( $order );
					// translators: %1$s is the order number, %2$s is the customer.
					$return = sprintf( _x( '%1$s by %2$s', '[Admin]Order number by user', 'yith-woocommerce-product-vendors' ), $order_uri, $user );

					$billing_email = $order ? $order->get_billing_email() : '';
					if ( $billing_email ) {
						$return .= '<small class="meta email"><a href="' . esc_url( 'mailto:' . $billing_email ) . '">' . esc_html( $billing_email ) . '</a></small>';
					}

					break;

				case 'line_item':
					$return = '<small class="meta">-</small>';

					if ( 'shipping' === $commission->get_type() ) {
						$shipping_fee = _x( 'Shipping fee', '[Admin]: Commission type', 'yith-woocommerce-product-vendors' );
						$return       = "<strong>{$shipping_fee}</strong>";
					} else {
						$item = $commission->get_item();

						if ( $item ) {
							/** @var WC_Product $product */
							$product     = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
							$product_id  = is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : false;
							$product_url = $product_id ? apply_filters( 'yith_wcmv_commissions_list_table_product_url', admin_url( 'post.php?post=' . $product_id . '&action=edit' ), $product, $commission ) : '';

							$return = $product instanceof WC_Product ? $product->get_image( 'thumbnail' ) : '';
							$return .= ! empty( $product_url ) ? "<a target='_blank' href='{$product_url}'><strong>{$item->get_name()}</strong></a>" : "<strong>{$item->get_name()}</strong>";
						}
					}

					break;

				case 'rate':
					$return = $commission->get_rate( 'display' );
					break;

				case 'vendor':
					$vendor = $commission->get_vendor();

					if ( ! empty( $vendor ) ) {
						if ( ! $vendor->is_valid() ) {
							$return = '<em>' . esc_html__( 'Vendor deleted.', 'yith-woocommerce-product-vendors' ) . '</em>';
						} else {

							$return = yith_wcmv_get_formatted_user_html( $commission->get_user() );
							$return && $return .= '<br>';

							$vendor_url = apply_filters( 'yith_wcmv_commissions_list_table_vendor_url', $vendor->get_url( 'admin' ), $vendor, $commission );
							$return     .= __( 'Store', 'yith-woocommerce-product-vendors' ) . ': ';
							$return     .= ! empty( $vendor_url ) ? ' <a href="' . esc_url( $vendor_url ) . '" target="_blank">' . esc_html( $vendor->get_name() ) . '</a>' : esc_html( $vendor->get_name() );
						}
					}

					break;

				case 'amount':
					$return = $commission->get_amount( 'display' );
					break;

				case 'status':
					$return = sprintf( '<span class="commission-status %s">%s</span>', strtolower( $commission->get_status() ), $commission->get_status( 'display' ) );

					break;

				case 'actions':
					yith_plugin_fw_get_component(
						array(
							'type'  => 'action-button',
							'class' => 'commission-details',
							'title' => __( 'View commission', 'yith-woocommerce-product-vendors' ),
							'data'  => array(
								'commission_id' => $commission->get_id(),
							),
							'icon'  => 'eye',
							'url'   => '#',
						)
					);

					do_action( 'yith_wcmv_commissions_user_actions', $commission );
					break;

				case 'date':
				case 'date_edit':
					$date      = $commission->get_date();
					$last_edit = $commission->get_last_edit();
					if ( 'date_edit' === $column_name && ( ! empty( $last_edit ) && strpos( $last_edit, '0000-00-00' ) === false ) ) {
						$date = $last_edit;
					}

					$return = yith_wcmv_get_formatted_date_html( $date );
					break;

				case 'to_pay':
					$return = $commission->get_amount_to_pay( 'display' );
					break;

				case 'refunded':
					$return = $commission->get_amount_refunded( 'display' );
					break;

				default:
					$vendor = $commission->get_vendor();
					do_action( "yith_wcmv_commissions_list_table_col_{$column_name}", $commission, $vendor, $column_name );
					break;
			}

			return apply_filters( "yith_wcmv_commissions_list_table_{$column_name}_column", $return, $commission );
		}

		/**
		 * Prints column cb
		 *
		 * @since 1.0.0
		 * @param YITH_Vendors_Commission $commission Item to use to print CB record.
		 * @return string
		 */
		public function column_cb( $commission ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['plural'], // Let's simply repurpose the table's plural label.
				$commission->get_id() // The value of the checkbox should be the record's id.
			);
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string The view name
		 */
		public function get_current_view() {
			return 'all';
		}

		/**
		 * Whether the table has items to display or not
		 *
		 * @since 3.1.0
		 *
		 * @return bool
		 */
		public function has_items() {
			// Private items empty for custom views.
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				$args = array( 'vendor_id' => $vendor->get_id() );
			} else {
				$args = array();
			}

			return ! empty( $this->items ) || YITH_Vendors_Commissions_Factory::count( $args );
		}
	}
}

