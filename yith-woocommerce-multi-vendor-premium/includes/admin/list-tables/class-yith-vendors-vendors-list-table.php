<?php
/**
 * YITH Vendors List Table Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Vendors_List_Table' ) ) {

	class YITH_Vendors_Vendors_List_Table extends WP_List_Table {

		/**
		 * Constructor.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $args An associative array of arguments.
		 * @see    WP_List_Table::__construct() for more information on default arguments.
		 */
		public function __construct( $args = array() ) {
			parent::__construct(
				array(
					'plural'   => 'vendors',
					'singular' => 'vendor',
					'ajax'     => false,
					'screen'   => 'yith-plugins_page_yith-wcmv-vendors-list',
				)
			);

			add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
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
			$hidden = array_merge( $hidden, array( 'id' ) );
			return $hidden;
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
		 * Prepares the list of items for displaying.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 * @uses   WP_List_Table::set_pagination_args()
		 */
		public function prepare_items() {
			// phpcs:disable WordPress.Security.NonceVerification

			// Let's build the tax query args.
			$page = $this->get_pagenum();
			$args = array(
				'taxonomy'   => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
				'page'       => $page,
				'offset'     => ( $page - 1 ) * 20,
				'number'     => 20,
				'hide_empty' => 0,
				'search'     => '',
				'fields'     => 'ids',
			);

			$search = ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			if ( $search ) {
				$args['search'] = $search;
			}

			// Handle search by ID.
			if ( is_numeric( $args['search'] ) ) {
				$term        = get_term( absint( $args['search'] ) );
				$this->items = $term instanceof WP_Term ? array( $term->term_id ) : array();
				$this->set_pagination_args(
					array(
						'total_items' => count( $this->items ),
						'per_page'    => 20,
					)
				);

			} else {
				if ( ! empty( $_GET['orderby'] ) ) {
					$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
					if ( in_array( $orderby, array( 'term_id', 'name', 'slug' ), true ) ) {
						$args['orderby'] = $orderby;
					} else {
						$args['meta_key'] = $orderby; // phpcs:ignore
						$args['orderby']  = 'meta_value';
					}
				}

				if ( ! empty( $_GET['order'] ) ) {
					$args['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
				}

				$this->items = get_terms( $args );
				$this->set_pagination_args(
					array(
						'total_items' => wp_count_terms(
							array(
								'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
								'search'   => $search,
							)
						),
						'per_page'    => 20,
					)
				);
			}

			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Message to be displayed when there are no items
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function no_items() {
			echo '';
		}

		/**
		 * Retrieves the list of bulk actions available for this table.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_bulk_actions() {
			return array();
		}

		/**
		 * Display the search box.
		 *
		 * @since  3.1.0
		 * @access public
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 */
		public function add_search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
		}

		/**
		 * Gets a list of columns.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_columns() {
			return apply_filters(
				'yith_wcmv_vendors_list_table_columns',
				array(
					'id'              => _x( 'ID', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'name'            => _x( 'Name', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'owner'           => _x( 'Owner', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'registered'      => _x( 'Registered on', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'policies'        => _x( 'Policies & VAT', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'commission_rate' => _x( 'Commission', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'enable'          => _x( 'Enable sales', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'actions'         => '',
				)
			);
		}

		/**
		 * Gets a list of sortable columns.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_sortable_columns() {
			return apply_filters(
				'yith_wcmv_vendors_list_table_sortable_columns',
				array(
					'id'              => 'term_id',
					'commission_rate' => 'commission',
					'name'            => 'name',
					'registered'      => 'registration_date',
				)
			);
		}

		/**
		 * Gets the name of the primary column.
		 *
		 * @since 4.3.0
		 * @return string The name of the primary column.
		 */
		protected function get_primary_column_name() {
			return 'name';
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 4.0.0
		 * @param object|array $vendor_id The current vendor ID.
		 */
		public function single_row( $vendor_id ) {
			$vendor = yith_wcmv_get_vendor( $vendor_id );
			$class  = $vendor->is_in_pending() ? 'pending' : '';

			echo '<tr class="' . esc_attr( $class ) . '">';
			$this->single_row_columns( $vendor_id );
			echo '</tr>';
		}

		/**
		 * Return name column content for given vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $vendor_id Vendor ID.
		 * @return void
		 */
		public function _column_name( $vendor_id ) { // phpcs:ignore
			$vendor = yith_wcmv_get_vendor( $vendor_id );
			?>
			<td class='name column-name column-primary'>
				<a href="javascript:void(0)" class="edit-vendor" data-vendor_id="<?php echo absint( $vendor->get_id() ); ?>">
					<?php echo esc_html( $vendor->get_name() ); ?>
				</a>
			</td>
			<?php
		}

		/**
		 * Return default column content
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $vendor_id   Vendor ID.
		 * @param string  $column_name Name of the column.
		 * @return string
		 */
		protected function column_default( $vendor_id, $column_name ) {

			$vendor = yith_wcmv_get_vendor( $vendor_id );
			$return = '';

			switch ( $column_name ) {

				case 'id':
					$return = '#' . $vendor->get_id();
					break;

				case 'owner':
					$owner = $vendor->get_owner( 'all' );
					if ( $owner instanceof WP_User ) {
						// Define row actions.
						$actions = apply_filters(
							'yith_wcmv_vendors_list_table_owner_row_actions',
							array(
								'edit' => sprintf(
									'<a href="%s" target="_blank">%s</a>',
									esc_url( add_query_arg( array( 'user_id' => $owner->ID ), admin_url( 'user-edit.php' ) ) ),
									esc_html_x( 'Edit', '[Admin]Vendor owner action label', 'yith-woocommerce-product-vendors' )
								),
							)
						);

						if ( class_exists( 'user_switching' ) ) {
							global $user_switching;
							if ( ! empty( $user_switching ) && $user_switching instanceof user_switching ) {
								$actions = $user_switching->filter_user_row_actions( $actions, $owner );
							}
						}

						$return = $owner->display_name . sprintf( '<br><a href="mailto:%1$1s">%1$1s</a>', $owner->user_email );
						if ( ! empty( $actions ) ) {
							$return .= '<div class="row-actions">' . implode( ' | ', $actions ) . '</div>';
						}
					} else {
						$return = '-';
					}
					break;

				case 'registered':
					$return = yith_wcmv_get_formatted_date_html( $vendor->get_registration_date() );
					break;

				case 'commission_rate':
					$return = ( $vendor->get_commission() * 100 ) . '%';
					break;

				case 'enable':
					$return = self::get_vendor_enable_switch_html( $vendor );
					break;

				case 'actions':
					$return = self::get_vendor_actions_html( $vendor );
					break;

				case 'policies':
					$polices = array(
						array(
							'label' => __( 'Policy', 'yith-woocommerce-product-vendors' ),
							'value' => $vendor->has_privacy_policy_accepted(),
						),
						array(
							'label' => __( 'Terms & Conditions', 'yith-woocommerce-product-vendors' ),
							'value' => $vendor->has_terms_and_conditions_accepted(),
						),
						array(
							'label' => __( 'VAT', 'yith-woocommerce-product-vendors' ),
							'value' => ! empty( $vendor->get_meta( 'vat' ) ),
						),
					);

					$return = '<ul class="vendor-polices-container">';
					foreach ( $polices as $policy ) {
						$return .= '<li><strong>' . esc_html( $policy['label'] ) . ':</strong>';
						$return .= '<i class="yith-icon ' . ( $policy['value'] ? 'yith-icon-check-circle' : 'yith-icon-warning-triangle' ) . '"></i></li>';
					}
					$return .= '</ul>';
					break;

				default:
					$vendor = yith_wcmv_get_vendor( 'current', 'user' );
					do_action( "yith_wcmv_vendors_list_table_col_{$column_name}", $vendor, $column_name );
					break;
			}

			/**
			 * Filters the displayed columns in the terms list table.
			 * The dynamic portion of the hook name, `$this->screen->taxonomy`,
			 * refers to the slug of the current taxonomy.
			 *
			 * @since 4.0.0
			 * @param string      $return Current column content.
			 * @param YITH_Vendor $vendor Current vendor.
			 */
			return apply_filters( "yith_wcmv_vendors_list_table_{$column_name}_column", $return, $vendor );
		}

		/**
		 * Get vendor enable switch html
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendor $vendor Current vendor.
		 * @return string
		 */
		public static function get_vendor_enable_switch_html( $vendor ) {
			ob_start();

			if ( $vendor->is_in_pending() ) :
				?>
				<span class="vendor-approve-container">
					<span class="approve-vendor approve-action" data-vendor_id="<?php echo esc_attr( $vendor->get_id() ); ?>" data-request="approve-vendor">
						<?php echo esc_html_x( 'Approve', '[Admin] Vendors lists: approve vendor label', 'yith-woocommerce-product-vendors' ); ?>
					</span>
					<span class="sep">|</span>
					<span class="approve-vendor reject-action" data-vendor_id="<?php echo esc_attr( $vendor->get_id() ); ?>" data-request="reject-vendor">
						<?php echo esc_html_x( 'Reject', '[Admin] Vendors lists: approve vendor label', 'yith-woocommerce-product-vendors' ); ?>
					</span>
				</span>
				<?php
			else :
				?>
				<span class="yith-plugin-ui vendor-enable-container">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'                => 'enabled_vendor_' . $vendor->get_id(),
						'name'              => 'enabled_vendor_' . $vendor->get_id(),
						'type'              => 'onoff',
						'default'           => 'no',
						'value'             => $vendor->is_selling_enabled() ? 'yes' : 'no',
						'custom_attributes' => array(
							'data-vendor_id' => $vendor->get_id(),
						),
					),
					true,
					false
				);
				?>
			</span>
				<?php
			endif;

			return ob_get_clean();
		}

		/**
		 * Get vendor actions html
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param YITH_Vendor $vendor Current vendor.
		 * @return string
		 */
		public static function get_vendor_actions_html( $vendor ) {
			ob_start();

			if ( ! $vendor->is_in_pending() ) {
				yith_plugin_fw_get_component(
					array(
						'type'  => 'action-button',
						'class' => 'view-vendor',
						'title' => __( 'View Vendor', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'eye',
						'url'   => $vendor->get_url(),
					)
				);
			}

			yith_plugin_fw_get_component(
				array(
					'type'   => 'action-button',
					'class'  => 'edit-vendor',
					'action' => 'edit',
					'title'  => __( 'Edit Vendor', 'yith-woocommerce-product-vendors' ),
					'data'   => array(
						'vendor_id' => $vendor->get_id(),
					),
					'icon'   => 'pencil',
					'url'    => '#',
				)
			);

			yith_plugin_fw_get_component(
				array(
					'type'   => 'action-button',
					'action' => 'delete',
					'title'  => __( 'Delete Vendor', 'yith-woocommerce-product-vendors' ),
					'class'  => 'delete-vendor',
					'data'   => array(
						'vendor_id' => $vendor->get_id(),
					),
					'icon'   => 'trash',
					'url'    => '#',
				)
			);

			return ob_get_clean();
		}
	}
}
