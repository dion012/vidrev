<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Product_List_Table Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Auction_Product_List_Table' ) ) {

	/**
	 * YITH_WCACT_Fee_Product
	 *
	 * @since 2.0.0
	 */
	class YITH_Auction_Product_List_Table extends WP_List_Table {

		/**
		 * Bids object
		 *
		 * @var YITH_WCACT_Bids
		 */
		public $bids;

		/**
		 * Construct
		 *
		 * @param array $args Args.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  2.0
		 */
		public function __construct( $args = array() ) {
			parent::__construct(
				array(
					'singular' => esc_html__( 'Auction List', 'yith-auctions-for-woocommerce' ),
					'plural'   => esc_html__( 'Auctions Tables', 'yith-auctions-for-woocommerce' ),
					'ajax'     => false,
				)
			);

			$this->bids = YITH_Auctions()->bids;
		}

		/**
		 * Return the columns for the table
		 *
		 * @return array
		 * @since  2.1.0
		 * @author YITH
		 */
		public function get_columns() {
			$columns = array(
				'auction'       => esc_html__( 'Auction', 'yith-auctions-for-woocommerce' ),
				'started_on'    => esc_html__( 'Started on', 'yith-auctions-for-woocommerce' ),
				'start_price'   => esc_html__( 'Start price', 'yith-auctions-for-woocommerce' ),
				'current_bid'   => esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ),
				'bids'          => esc_html__( 'Bids', 'yith-auctions-for-woocommerce' ),
				'bidders'       => esc_html__( 'Bidders', 'yith-auctions-for-woocommerce' ),
				'followers'     => esc_html__( 'Followers', 'yith-auctions-for-woocommerce' ),
				'watchers'      => esc_html__( 'Watchers', 'yith-auctions-for-woocommerce' ),
				'reserve_price' => esc_html__( 'Reserve price', 'yith-auctions-for-woocommerce' ),
				'end_on'        => esc_html__( 'End on:', 'yith-auctions-for-woocommerce' ),
				'status'        => esc_html__( 'Status:', 'yith-auctions-for-woocommerce' ),
			);

			/**
			 * APPLY_FILTERS: yith_wcact_auction_list_columns
			 *
			 * Filter the columns of the auctions table.
			 *
			 * @param array $columns Columns
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_auction_list_columns', $columns );
		}

		/**
		 * Column Default
		 *
		 * @param object|int $product Product.
		 * @param string     $column_name Column name.
		 */
		public function column_default( $product, $column_name ) {
			$output = '';

			switch ( $column_name ) {
				case 'started_on':
					$format_date = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
					$format_time = get_option( 'yith_wcact_general_time_format', 'h:i:s' );

					$format = $format_date . ' ' . $format_time;

					$data = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', (int) $product->get_start_date() ), $format );

					$explode = explode( ' ', $data );
					$implode = implode( '<br>', $explode );
					$output  = $implode;
					break;

				case 'start_price':
					$output = wc_price( $product->get_start_price() );
					break;

				case 'current_bid':
					$output = wc_price( $product->get_current_bid() );
					break;

				case 'bids':
					$bids = $this->bids->get_bids_auction( $product->get_id() );

					if ( $bids && is_array( $bids ) && ! empty( $bids ) ) {
						$bid_count  = count( $bids );
						$data_attr  = array(
							'product_id' => $product->get_id(),
							'bids'       => $bid_count,
						);
						$icon_array = array(
							'icon'       => 'eye',
							'title'      => esc_html__( 'Show details', 'yith-auctions-for-woocommerce' ),
							'type'       => 'action-button',
							'class'      => 'yith-wcact-auction-bidders-button',
							'icon_class' => 'yith-icon yith-icon-eye yith-wcact-icon yith-wcact-icon-bidders-button',
							'data'       => $data_attr,
						);

						$icon = yith_plugin_fw_get_component( $icon_array, false );

						$bids_value = '<span>' . $bid_count . '</span>' . $icon;
					} else {
						$bids_value = 0;
					}

					$output = $bids_value;
					break;

				case 'bidders':
					$users  = $this->bids->get_bidders( $product->get_id() );
					$output = ! empty( $users ) ? $users : 0;
					break;

				case 'followers':
					$followers = $this->bids->get_users_count_product_on_follower_list( $product->get_id() );

					if ( $followers && is_array( $followers ) && ! empty( $followers ) ) {
						$output = count( $followers );
					} else {
						$output = 0;
					}
					break;

				case 'watchers':
					$users_watchlist = $this->bids->get_users_count_product_on_watchlist( $product->get_id() );
					$output          = ! empty( $users_watchlist ) ? $users_watchlist : 0;
					break;

				case 'reserve_price':
					$reserve_price = $product->get_reserve_price();

					if ( $reserve_price > 0 ) {
						$price = $product->get_price();

						if ( $price > $reserve_price ) {
							$output = "<span class='yith-wcact-reserve-price-passed'>" . wc_price( $reserve_price ) . ' (&#10003)</span>';
						} else {
							$output = wc_price( $reserve_price );
						}
					} else {
						$output = esc_html( '-' );
					}
					break;

				case 'end_on':
					$end_date = $product->get_end_date();
					$time_now = time();

					if ( $end_date > $time_now ) {
						$format_date = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
						$format_time = get_option( 'yith_wcact_general_time_format', 'h:i:s' );
						$format      = $format_date . ' ' . $format_time;

						$data = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $product->get_end_date() ), $format );

						$explode = explode( ' ', $data );
						$implode = implode( '<br>', $explode );
						$output  = $implode;
					} else {
						$output = esc_html__( 'Ended', 'yith-auctions-for-woocommerce' );
					}
					break;

				case 'status':
					$product_status = $product->get_status();

					if ( 'draft' === $product_status ) {
						$output = esc_html__( 'Draft', 'yith-auctions-for-woocommerce' );
					} else {
						$type   = $product->get_auction_status();
						$output = yith_wcact_auction_get_status_icon( $type );
					}
					break;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_auction_list_output_column
			 *
			 * Filter the content of the default column in the auctions table.
			 *
			 * @param string     $output      Column output
			 * @param string     $column_name Column name
			 * @param WC_Product $product     Product object
			 */
			echo wp_kses_post( apply_filters( 'yith_wcact_auction_list_output_column', $output, $column_name, $product ) );
		}

		/**
		 * Column Auction
		 *
		 * @param object|int $product Product.
		 */
		public function column_auction( $product ) {
			$edit_link = get_edit_post_link( $product->get_id() );
			$title     = $product->get_title();

			$product_status = $product->get_status();

			$output  = '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';
			$output .= ( 'draft' === $product_status ? ' - ' . ucfirst( $product_status ) : '' );
			$output .= '</strong>';

			return $output;
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * @uses WP_List_Table::set_pagination_args()
		 *
		 * @since 1.0.0
		 */
		public function prepare_items() {
			if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
				wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore
				exit;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_auction_list_per_page
			 *
			 * Filter the amount of items per page in the auctions table.
			 *
			 * @param int $per_page Number of items per page
			 *
			 * @return int
			 */
			$per_page              = apply_filters( 'yith_wcact_auction_list_per_page', 15 );
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->_column_headers = array( $columns, $hidden );

			$auction_status = array( 'non-started', 'started', 'finished' );

			$current_page = $this->get_pagenum();

			$query_args = array();

			$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['auction_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $auction_type ) ) {
				$query_args['ywcact_auction_type'] = $auction_type;
			}

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['status'] ) && 'all' !== $_GET['status'] ) {
				if ( in_array( $_GET['status'], $auction_status, true ) ) {
					$query_args['ywcact_auction_type'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
					$query_args['status']              = 'all';
				} else {
					$query_args['status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			/**
			 * APPLY_FILTERS: yith_wcact_auction_list_query_args
			 *
			 * Filter the array with the arguments to get the auction products.
			 *
			 * @param array $args Array of arguments
			 *
			 * @return array
			 */
			$items = wc_get_products(
				apply_filters(
					'yith_wcact_auction_list_query_args',
					array_merge(
						array(
							'type'   => 'auction',
							'limit'  => $per_page,
							'offset' => ( ( $current_page - 1 ) * $per_page ),
							's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						),
						$query_args
					)
				)
			);

			$total_items = count(
				wc_get_products(
					array_merge(
						array(
							'type'  => 'auction',
							'limit' => -1,
							's'     => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						),
						$query_args
					)
				)
			);

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);

			$this->items = $items;
		}

		/**
		 * Generates the tbody element for the list table.
		 *
		 * @since 3.1.0
		 */
		public function display_rows_or_placeholder() {
			if ( $this->has_items() ) {
				$this->display_rows();
			} else {
				echo '<tr class="no-items"><td class="colspanchange" colspan="' . esc_attr( $this->get_column_count() ) . '">';
				$this->no_items();
				echo '</td></tr>';
			}
		}

		/**
		 * Get views for the table
		 *
		 * @return array
		 * @since  1.0.0
		 * @author YITHEMES
		 */
		protected function get_views() {
			$views = array(
				'all'   => __( 'All', 'yith-auctions-for-woocommerce' ),
				/**
				'scheduled' => __( 'Scheduled', 'yith-auctions-for-woocommerce' ),
				'started'    => __( 'Started', 'yith-auctions-for-woocommerce' ),
				'ended'    => __( 'Ended', 'yith-auctions-for-woocommerce' ),
				*/
				'draft' => __( 'Draft', 'yith-auctions-for-woocommerce' ),
				'trash' => __( 'Trash', 'yith-auctions-for-woocommerce' ),
			);

			$current_view = $this->get_current_view();

			foreach ( $views as $view_id => $view ) {
				$query_args = array(
					'posts_per_page'  => - 1,
					'post_type'       => 'product',
					'post_status'     => array( 'publish', 'draft' ),
					'suppress_filter' => false,
					'tax_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => 'auction',
						),
					),
				);

				$status = 'status';
				$id     = $view_id;

				if ( 'all' !== $view_id ) {
					$query_args['post_status'] = $view_id;
				}

				$href        = esc_url( add_query_arg( $status, $id ) );
				$total_items = count( get_posts( $query_args ) );

				if ( $total_items > 0 ) {
					$class             = $view_id === $current_view ? 'current' : '';
					$views[ $view_id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $total_items );
				} else {
					unset( $views[ $view_id ] );
				}
			}

			return $views;
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @param string $which Which.
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' !== $which ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_auction_list_filter_status
			 *
			 * Filter the array with the filter statuses for the auction products.
			 *
			 * @param array $statuses Filter statuses
			 *
			 * @return array
			 */
			$filter_options = apply_filters(
				'yith_wcact_auction_list_filter_status',
				array(
					'all'         => esc_html__( 'All status', 'yith-auctions-for-woocommerce' ),
					'non-started' => esc_html__( 'Scheduled', 'yith-auctions-for-woocommerce' ),
					'started'     => esc_html__( 'Started', 'yith-auctions-for-woocommerce' ),
					'finished'    => esc_html__( 'Ended', 'yith-auctions-for-woocommerce' ),
				)
			);

			$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['auction_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			echo '<div class="alignleft actions">';

			yith_wcact_get_dropdown(
				array(
					'name'    => 'auction_type',
					'id'      => 'dropdown_auction_type',
					'class'   => '',
					'options' => $filter_options,
					'value'   => $auction_type,
					'echo'    => true,
				)
			);

			submit_button( esc_html__( 'Filter', 'yith-auctions-for-woocommerce' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			echo '</div>';

			submit_button( __( 'Export CSV', 'yith-auctions-for-woocommerce' ), 'button', 'export_action', false );
			wp_nonce_field( 'yith-wcact-auction-product-list', 'yith_wcact_auction_product_list' );
		}

		/**
		 * Return current view
		 *
		 * @return string
		 * @since  1.0.0
		 * @author YITHEMES
		 */
		public function get_current_view() {
			return empty( $_GET['status'] ) ? 'all' : sanitize_text_field( wp_unslash( $_GET['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Generates and displays row action links.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Post $auction        Post being acted upon.
		 * @param string  $column_name Current column name.
		 * @param string  $primary     Primary column name.
		 * @return string Row actions output for posts, or an empty string
		 *                if the current column is not the primary column.
		 */
		protected function handle_row_actions( $auction, $column_name, $primary ) {
			if ( $primary !== $column_name ) {
				return '';
			}

			$post = get_post( $auction->get_id() );

			$post_type_object = get_post_type_object( $post->post_type );
			$can_edit_post    = current_user_can( 'edit_post', $post->ID );
			$actions          = array();
			$title            = _draft_or_post_title();

			if ( $can_edit_post && 'trash' !== $post->post_status ) {
				$actions['edit'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					get_edit_post_link( $post->ID ),
					/* translators: %s: Post title. */
					esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'yith-auctions-for-woocommerce' ), $title ) ),
					__( 'Edit', 'yith-auctions-for-woocommerce' )
				);

				// if ( 'wp_block' !== $post->post_type ) {
				// $actions['inline hide-if-no-js'] = sprintf(
				// '<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
						/*
						 translators: %s: Post title. */
				// esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline' ), $title ) ),
				// __( 'Quick&nbsp;Edit' )
				// );
				// }
			}

			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( 'trash' === $post->post_status ) {
					$actions['untrash'] = sprintf(
						'<a href="%s" aria-label="%s">%s</a>',
						wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
						/* translators: %s: Post title. */
						esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'yith-auctions-for-woocommerce' ), $title ) ),
						__( 'Restore', 'yith-auctions-for-woocommerce' )
					);
				} elseif ( EMPTY_TRASH_DAYS ) {
					$actions['trash'] = sprintf(
						'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
						get_delete_post_link( $post->ID ),
						/* translators: %s: Post title. */
						esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'yith-auctions-for-woocommerce' ), $title ) ),
						_x( 'Trash', 'verb', 'yith-auctions-for-woocommerce' )
					);
				}

				if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
					$actions['delete'] = sprintf(
						'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
						get_delete_post_link( $post->ID, '', true ),
						/* translators: %s: Post title. */
						esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'yith-auctions-for-woocommerce' ), $title ) ),
						__( 'Delete Permanently', 'yith-auctions-for-woocommerce' )
					);
				}
			}

			if ( is_post_type_viewable( $post_type_object ) ) {
				if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ), true ) ) {
					if ( $can_edit_post ) {
						$preview_link    = get_preview_post_link( $post );
						$actions['view'] = sprintf(
							'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
							esc_url( $preview_link ),
							/* translators: %s: Post title. */
							esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'yith-auctions-for-woocommerce' ), $title ) ),
							__( 'Preview', 'yith-auctions-for-woocommerce' )
						);
					}
				} elseif ( 'trash' !== $post->post_status ) {
					$actions['view'] = sprintf(
						'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
						get_permalink( $post->ID ),
						/* translators: %s: Post title. */
						esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'yith-auctions-for-woocommerce' ), $title ) ),
						__( 'View', 'yith-auctions-for-woocommerce' )
					);
				}
			}

			return $this->row_actions( $actions );
		}
	}
}
