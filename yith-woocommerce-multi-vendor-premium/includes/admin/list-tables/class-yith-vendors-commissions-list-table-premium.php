<?php
/**
 * YITH Vendors Commissions List Table Class Premium.
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Commissions_List_Table_Premium' ) ) {
	/**
	 * @class class.yith-commissions-list-table
	 * @since      Version 1.0.0
	 * @author     Your Inspiration Themes
	 * @package    Yithemes
	 */
	class YITH_Vendors_Commissions_List_Table_Premium extends YITH_Vendors_Commissions_List_Table {

		/**
		 * Construct
		 */
		public function __construct() {
			parent::__construct();

			// Months dropdown.
			add_filter( 'pre_months_dropdown_query', array( $this, 'get_months_dropdown' ), 10 );
			add_action( 'wc_order_statuses', array( $this, 'custom_order_status' ) );

			// Handle search|filter query vars.
			add_action( 'yith_wcmv_commissions_list_table_args', array( $this, 'add_filter_query_vars' ) );

			// Premium columns.
			add_filter( 'yith_wcmv_commissions_list_table_column', array( $this, 'add_premium_columns' ), 0, 1 );
			add_action( 'yith_wcmv_commissions_list_table_order_id_column', array( $this, 'add_order_status' ), 10, 2 );
			add_filter( 'yith_wcmv_commissions_list_table_actions_column', array( $this, 'add_user_actions' ), 10, 2 );
		}

		/**
		 * Displays a dropdown for filtering items in the list table by month.
		 *
		 * @since 3.1.0
		 * @param string $post_type The post type.
		 * @global WP_Locale $wp_locale WordPress date and time locale object.
		 * @global wpdb      $wpdb WordPress database abstraction object.
		 */
		protected function months_dropdown( $post_type ) {
			global $wpdb, $wp_locale;

			/**
			 * Filters whether to remove the 'Months' drop-down from the post list table.
			 *
			 * @since 4.2.0
			 * @param bool   $disable Whether to disable the drop-down. Default false.
			 * @param string $post_type The post type.
			 */
			if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
				return;
			}

			/**
			 * Filters to short-circuit performing the months dropdown query.
			 *
			 * @since 5.7.0
			 * @param object[]|false $months 'Months' drop-down results. Default false.
			 * @param string         $post_type The post type.
			 */
			$months = apply_filters( 'pre_months_dropdown_query', false, $post_type );

			if ( ! is_array( $months ) ) {
				$current_view = $this->get_current_view();
				$where        = 'WHERE 1=1 ';

				if ( 'all' !== $current_view ) {
					if ( is_array( $current_view ) ) {
						$where .= sprintf( 'AND status IN ( \'%s\' )', implode( "','", $current_view ) );
					} else {
						$where .= $wpdb->prepare( 'AND status = %s', $current_view );
					}
				}

				$months = $wpdb->get_results(
					$wpdb->prepare(
						"
                SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
                FROM $wpdb->posts
                WHERE post_type = %s
                AND ID IN (
                    SELECT DISTINCT order_id
                    FROM $wpdb->commissions $where
                    )
                ORDER BY post_date DESC
            ",
						'shop_order'
					)
				);
			}

			/**
			 * Filters the 'Months' drop-down results.
			 *
			 * @since 3.7.0
			 * @param object[] $months Array of the months drop-down query results.
			 * @param string   $post_type The post type.
			 */
			$months = apply_filters( 'months_dropdown_results', $months, $post_type );

			$month_count = count( $months );

			if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
				return;
			}

			$m = isset( $_GET['m'] ) ? absint( $_GET['m'] ) : 0;
			?>
			<label for="filter-by-date" class="screen-reader-text"></label>
			<select name="m" id="filter-by-date">
				<option<?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'yith-woocommerce-product-vendors'); ?></option>
				<?php
				foreach ( $months as $arc_row ) {
					if ( 0 == $arc_row->year ) {
						continue;
					}

					$month = zeroise( $arc_row->month, 2 );
					$year  = $arc_row->year;

					printf(
						"<option %s value='%s'>%s</option>\n",
						selected( $m, $year . $month, false ),
						esc_attr( $arc_row->year . $month ),
						sprintf( '%1$s %2$d', $wp_locale->get_month( $month ), $year )
					);
				}
				?>
			</select>
			<?php
		}

		/**
		 * Handle search|filter table items
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param array $args An array of query args.
		 * @return array
		 */
		public function add_filter_query_vars( $args ) {

			foreach ( array( 'm', 's', 'vendor_id', 'product_id' ) as $key ) {
				if ( ! empty( $_GET[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
				}
			}

			return $args;
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return array Array of available actions.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'export_commissions_csv' => __( 'Export commissions to CSV file', 'yith-woocommerce-product-vendors' ),
				'delete_commissions'     => __( 'Delete commissions', 'yith-woocommerce-product-vendors' ),
			);
			foreach ( YITH_Vendors()->commissions->get_status() as $action => $label ) {
				$actions[ 'commissions_status_change_to_' . $action ] = __( 'Change to', 'yith-woocommerce-product-vendors' ) . ' ' . strtolower( $label );
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_bulk_actions', $actions );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @access protected
		 */
		protected function get_views() {

			if ( ! $this->has_items() ) {
				return array();
			}

			$views        = array_merge( array( 'all' => __( 'All', 'yith-woocommerce-product-vendors' ) ), YITH_Vendors()->commissions->get_status() );
			$current_view = (array) $this->get_current_view();

			// Merge Unpaid with Processing.
			$views['unpaid'] .= '/' . $views['processing'];
			unset( $views['processing'] );

			foreach ( $views as $id => $view ) {
				$args = array( 'status' => 'unpaid' === $id ? array( $id, 'processing' ) : $id );
				// Let's filter views query args.
				$args  = apply_filters( 'yith_wcmv_commissions_list_table_views_args', $args );
				$count = YITH_Vendors_Commissions_Factory::count( $args );

				// Build the navigation item.
				$href         = esc_url( add_query_arg( 'commission_status', $id ) );
				$class        = in_array( $id, $current_view, true ) ? 'current' : '';
				$views[ $id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $count );
			}

			return $views;
		}

		/**
		 * Add premium columns in table
		 *
		 * @since 4.0.0
		 * @param array $columns Array of default columns.
		 * @return array Array of columns of the table.
		 */
		public function add_premium_columns( $columns ) {
			return array_merge( array( 'cb' => '<input type="checkbox" />' ), $columns );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed The view name
		 */
		public function get_current_view() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['commission_status'] ) ) {
				return get_option( 'yith_commissions_default_table_view', 'all' );
			}

			$status = sanitize_text_field( wp_unslash( $_GET['commission_status'] ) );

			return ( 'unpaid' === $status ) ? array( 'unpaid', 'processing' ) : $status;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since 3.1.0
		 * @access protected
		 * @param string $which
		 */
		protected function extra_tablenav( $which ) {

			if ( ! $this->has_items() ) {
				return;
			}

			if ( 'top' == $which ) {
				if ( ! empty( $_REQUEST['status'] ) ) {
					echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
				}

				$this->months_dropdown( 'commissions' );
				$this->product_dropdown();
				$this->vendor_dropdown();

				submit_button( _x( 'Filter', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );

				if ( isset( $_REQUEST['s'] ) ) {
					$reset_button = apply_filters( 'yith_wcmv_commissions_list_table_reset_filter_url', YITH_Vendors_Admin_Commissions::get_commissions_list_table_url() );
					echo '<a href="' . esc_url( $reset_button ) . '" class="button-primary reset-button">' . esc_html_x( 'Reset', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ) . '</a>';
				}

				submit_button(
					_x( 'Export CSV', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ),
					'button filter-button',
					'export_commissions_csv',
					false,
					array(
						'id'     => 'export-commissions-csv',
						'target' => '_blank',
					)
				);
			}
		}

		/**
		 * Add the product dropdown
		 *
		 * @since 1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function product_dropdown() {

			if ( ! apply_filters( 'yith_wcmv_commissions_list_table_show_product_filter', true ) ) {
				return;
			}

			$product_id      = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$product         = ! empty( $product_id ) ? wc_get_product( $product_id ) : false;
			$product_display = ! empty( $product ) ? $product->get_name() . '(#' . $product_id . ')' : '';

			$select2_args = array(
				'class'            => 'wc-product-search',
				'id'               => 'product_id',
				'name'             => 'product_id',
				'data-placeholder' => __( 'Search for a product&hellip;', 'yith-woocommerce-product-vendors' ),
				'data-action'      => 'woocommerce_json_search_products',
				'data-allow_clear' => true,
				'data-selected'    => array( $product_id => $product_display ),
				'data-multiple'    => false,
				'value'            => $product_id,
				'style'            => 'width: 180px;',
			);

			?>
			<div id="product_data_search" class="panel data_search_wrapper">
				<div class="options_group">
					<?php yit_add_select2_fields( $select2_args ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Add the vendor dropdown
		 *
		 * @since 1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function vendor_dropdown() {

			if ( ! apply_filters( 'yith_wcmv_commissions_list_table_show_vendor_filter', true ) ) {
				return;
			}

			$vendor_id      = ! empty( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$vendor         = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
			$vendor_display = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() . '(#' . $vendor->get_id() . ')' : '';
			$select2_args   = array(
				'class'            => 'wc-product-search',
				'id'               => 'vendor_id',
				'name'             => 'vendor_id',
				'data-placeholder' => __( 'Search for a vendor&hellip;', 'yith-woocommerce-product-vendors' ),
				'data-action'      => 'yith_json_search_vendors',
				'data-allow_clear' => true,
				'data-selected'    => array( $vendor_id => $vendor_display ),
				'data-multiple'    => false,
				'value'            => $vendor_id,
				'style'            => 'width: 180px;',
			);

			?>
			<div id="vendor_data_search" class="panel data_search_wrapper">
				<div class="options_group">
					<?php yit_add_select2_fields( $select2_args ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Display the search box.
		 *
		 * @since 3.1.0
		 * @access public
		 * @param string $text The search button text.
		 * @param string $input_id The search input id.
		 */
		public function add_search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
		}

		/**
		 * Premium user actions column content.
		 *
		 * @since 4.0.0
		 * @access public
		 * @param string                  $html Current html value.
		 * @param YITH_Vendors_Commission $commission Current commission.
		 * @return string
		 */
		public function add_user_actions( $html, $commission ) {

			if ( ! apply_filters( 'yith_wcmv_commissions_list_table_add_single_actions', true ) ) {
				return $html;
			}

			$commission_status  = $commission->get_status( 'edit' );
			$available_statuses = YITH_Vendors()->commissions->get_status( true );
			$actions            = array();

			foreach ( $available_statuses as $status => $label ) {
				if ( ! YITH_Vendors()->commissions->is_status_changing_permitted( $status, $commission_status ) ) {
					continue;
				}

				$actions[] = array(
					// translators: %s stand for the commission status name.
					'name'         => sprintf( _x( 'Change to %s', '[Admin]Commission table action label', 'yith-woocommerce-product-vendors' ), strtolower( $label ) ),
					'url'          => YITH_Vendors_Admin_Commissions::get_commission_action_url( $commission->get_id(), 'change_commissions_status', array( 'status' => $status ) ),
					'confirm_data' => array(
						'title'   => _x( 'Confirm status change', '[Admin]Commission modal action title', 'yith-woocommerce-product-vendors' ),
						'message' => sprintf(
						// translators: %1$s stand for the commission ID, %2$s stand for the current commission status, %3$s stand for the new commission status.
							_x( 'Are you sure you want to change commission #%1$s status from %2$s to %3$s?', '[Admin]Commission modal action message', 'yith-woocommerce-product-vendors' ),
							$commission->get_id(),
							$commission->get_status( 'display' ),
							$label
						),
					),
				);
			}

			$actions[] = array(
				'name'         => 'Delete',
				'url'          => YITH_Vendors_Admin_Commissions::get_commission_action_url( $commission->get_id(), 'delete_commissions' ),
				'confirm_data' => array(
					'title'               => _x( 'Confirm delete', '[Admin]Commission modal action title', 'yith-woocommerce-product-vendors' ),
					'message'             => sprintf(
						// translators: %s stand for the commission ID.
						_x( 'Are you sure you want to delete commission #%s?', '[Admin]Commission modal action message', 'yith-woocommerce-product-vendors' ),
						$commission->get_id()
					),
					'confirm-button'      => _x( 'Delete', '[Admin]Commission modal button label', 'yith-woocommerce-product-vendors' ),
					'confirm-button-type' => 'delete',
				),
			);

			// Let's filter actions.
			$actions = apply_filters( 'yith_wcmv_single_commission_row_actions', $actions, $commission );

			if ( ! empty( $actions ) ) {
				yith_plugin_fw_get_component(
					array(
						'type'   => 'action-button',
						'action' => 'show-more',
						'icon'   => 'more',
						'menu'   => $actions,
					)
				);
			}

			return $html;
		}

		/**
		 * Add WooCommerce Order Custom Status
		 *
		 * @since 1.0.0
		 * @return array Array of columns of the table
		 */
		public function custom_order_status( $status ) {
			$status['trash'] = _x( 'Trashed', 'Order status', 'yith-woocommerce-product-vendors' );

			return $status;
		}

		/**
		 * Get order status by order item
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param string                  $html Current html value.
		 * @param YITH_Vendors_Commission $commission Current commission.
		 * @return string
		 */
		public function add_order_status( $html, $commission ) {
			$order        = $commission->get_order();
			$order_status = ! empty( $order ) ? wc_get_order_status_name( $order->get_status() ) : '';
			$html        .= $order_status ? '<small style="display:block;">(' . __( 'Order status:', 'yith-woocommerce-product-vendors' ) . ' ' . $order_status . ')</small>' : '';

			return $html;
		}
	}
}

