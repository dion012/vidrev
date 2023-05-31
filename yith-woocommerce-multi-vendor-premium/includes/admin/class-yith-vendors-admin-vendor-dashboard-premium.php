<?php
/**
 * YITH Vendors Admin Vendor Dashboard Premium
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Vendor_Dashboard_Premium' ) ) {
	/**
	 * Vendor admin dashboard premium class
	 *
	 * @class      YITH_Vendors_Admin_Vendor_Dashboard_Premium
	 * @since      4.0.0
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Admin_Vendor_Dashboard_Premium extends YITH_Vendors_Admin_Vendor_Dashboard {

		/**
		 * Construct
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 */
		public function __construct() {
			parent::__construct();

			add_filter( 'yith_wcmv_admin_vendor_menu_items', array( $this, 'add_allowed_menu_items' ), 1 );
		}

		/**
		 * Register limited access hooks
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_limited_access_hooks() {
			parent::register_limited_access_hooks();

			add_action( 'admin_menu', array( $this, 'add_dashboard_widgets' ) );
			add_action( 'add_meta_boxes_shop_order', array( $this, 'custom_fields_meta_box_visibility' ), 99, 1 );
			add_action( 'add_meta_boxes_product', array( $this, 'product_tags_meta_box_visibility' ), 99, 1 );
			// Hide protected meta from custom meta table. This is for backward compatibility since meta visibility is not protected.
			add_filter( 'auth_post_meta_vendor_id_for_shop_order', '__return_false' );
			add_filter( 'auth_post_meta_has_sub_order_for_shop_order', '__return_false' );

			// Filter media attachment.
			add_action( 'pre_get_posts', array( $this, 'filter_attachment_content' ), 20, 1 );

			// WordPress User Frontend.
			if ( function_exists( 'wpuf' ) ) {
				remove_action( 'admin_init', array( wpuf(), 'block_admin_access' ) );
			}

			// YIT Shortcode compatibility.
			add_action( 'admin_init', array( $this, 'remove_shortcodes_button' ), 5 );
			// Disable GeoDirectory "Prevent admin access" for vendor.
			remove_action( 'admin_init', 'geodir_allow_wpadmin' );
			// WP User Avatar Compatibility. Enabled vendor to manage WP User Avatar dashboard.
			add_filter( 'wpua_subscriber_offlimits', '__return_empty_array' );

			// Handle comments sections.
			add_action( 'admin_init', array( $this, 'allowed_comments' ) );
			add_filter( 'pre_get_comments', array( $this, 'filter_reviews_list' ), 10, 1 );
			add_filter( 'wp_count_comments', array( $this, 'count_comments' ), 5, 2 );
			add_action( 'load-comment.php', array( $this, 'disabled_manage_other_comments' ) );

			// Filter the preview order data for vendors.
			add_filter( 'woocommerce_admin_order_preview_get_order_details', array( $this, 'order_preview_get_order_details' ), 99, 2 );
			add_filter( 'manage_shop_order_posts_columns', array( $this, 'customize_order_columns' ), 15 );

			do_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks_premium', $this->vendor );
		}

		/**
		 * Add allowed menu items to vendor dashboard
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param array $allowed An array of allowed items.
		 * @return array
		 */
		public function add_allowed_menu_items( $allowed ) {
			$allowed = array_merge( $allowed, array( 'upload.php' ) );
			return $allowed;
		}

		/**
		 * Hide/Show custom fields meta box for shop orders
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param WP_Post $post The current post object.
		 */
		public function custom_fields_meta_box_visibility( $post ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_edit_custom_fields', 'yes' ) ) {
				remove_meta_box( 'postcustom', 'shop_order', 'normal' );
			}
		}

		/**
		 * Hide/Show product tags meta box visibility
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param WP_Post $post The current post object.
		 */
		public function product_tags_meta_box_visibility( $post ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_product_tags_management', 'yes' ) ) {
				remove_meta_box( 'tagsdiv-product_tag', 'product', 'side' );
			}
		}

		/**
		 * Filter any attachment query for current vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param WP_Query $query The WP_Query object.
		 * @return void
		 */
		public function filter_attachment_content( $query ) {
			if ( 'attachment' === $query->get( 'post_type' ) ) {
				$vendor_admin_ids = $this->vendor->get_admins();
				if ( ! empty( $vendor_admin_ids ) ) {
					$query->set( 'author__in', $vendor_admin_ids );
				}
			}
		}

		/**
		 * Add vendor widget dashboard
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function add_dashboard_widgets() {
			$review_management = 'yes' === get_option( 'yith_wpv_vendors_option_review_management', 'no' );

			$to_adds = array(
				array(
					'id'       => 'woocommerce_dashboard_recent_reviews',
					'name'     => __( 'Recent reviews', 'yith-woocommerce-product-vendors' ),
					'callback' => array( $this, 'vendor_recent_reviews_widget' ),
					'context'  => $review_management ? 'side' : 'normal',
				),
			);

			if ( $review_management ) {
				$to_adds[] = array(
					'id'       => 'vendor_recent_reviews',
					'name'     => __( 'Recent comments', 'yith-woocommerce-product-vendors' ),
					'callback' => array( $this, 'vendor_recent_comments_widget' ),
					'context'  => 'normal',
				);
			}

			foreach ( $to_adds as $widget ) {
				extract( $widget ); // phpcs:ignore
				add_meta_box( $id, $name, $callback, 'dashboard', $context, 'high' );
			}
		}

		/**
		 * Vendor Recent Comments Widgets
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 */
		public function vendor_recent_comments_widget() {

			$comments        = array();
			$vendor_products = $this->vendor->get_products();
			$total_items     = apply_filters( 'yith_wcmv_vendor_recent_comments_widget_items', 5 );
			$comments_query  = array(
				'number'   => $total_items * 5,
				'offset'   => 0,
				'post__in' => ! empty( $vendor_products ) ? $vendor_products : array( 0 ),
			);
			if ( ! current_user_can( 'edit_posts' ) ) {
				$comments_query['status'] = 'approve';
			}

			while ( count( $comments ) < $total_items && $possible = get_comments( $comments_query ) ) {
				if ( ! is_array( $possible ) ) {
					break;
				}
				foreach ( $possible as $comment ) {
					if ( ! current_user_can( 'read_post', $comment->comment_post_ID ) ) {
						continue;
					}
					$comments[] = $comment;
					if ( count( $comments ) === $total_items ) {
						break 2;
					}
				}
				$comments_query['offset'] += $comments_query['number'];
				$comments_query['number']  = $total_items * 10;
			}

			if ( ! empty( $comments ) ) {
				echo '<div id="latest-comments" class="activity-block">';

				echo '<ul id="the-comment-list" data-wp-lists="list:comment">';
				foreach ( $comments as $comment ) {
					_wp_dashboard_recent_comments_row( $comment );
				}
				echo '</ul>';

				wp_comment_reply( - 1, false, 'dashboard', false );
				wp_comment_trashnotice();

				echo '</div>';

			} else {
				echo '<p>' . esc_html__( 'There are no comments yet.', 'yith-woocommerce-product-vendors' ) . '</p>';
			}
		}

		/**
		 * Vendor Recent Reviews Widgets
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 */
		public function vendor_recent_reviews_widget() {
			global $wpdb;

			$comments = $wpdb->get_results(
				"
                SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
                FROM $wpdb->comments
                LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
                WHERE comment_approved = '1'
                AND comment_type = ''
                AND post_password = ''
                AND post_type = 'product'
                AND comment_post_ID IN ( '" . implode( "','", $this->vendor->get_products( array( 'fields' => 'ids' ) ) ) . "' )
                ORDER BY comment_date_gmt DESC
                LIMIT 8"
			);

			if ( $comments ) {
				echo '<ul>';
				foreach ( $comments as $comment ) {

					echo '<li>';

					echo get_avatar( $comment->comment_author, '32' );

					$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

					echo '<div class="star-rating" title="' . esc_attr( $rating ) . '"><span style="width:' . ( $rating * 20 ) . '%">' . $rating . ' ' . esc_html__( 'out of 5', 'yith-woocommerce-product-vendors' ) . '</span></div>';

					echo '<h4 class="meta"><a href="' . get_permalink( $comment->ID ) . '#comment-' . absint( $comment->comment_ID ) . '">' . esc_html__( apply_filters( 'woocommerce_admin_dashboard_recent_reviews', $comment->post_title, $comment ) ) . '</a> ' . esc_html__( 'reviewed by', 'yith-woocommerce-product-vendors' ) . ' ' . esc_html( $comment->comment_author ) . '</h4>';
					echo '<blockquote>' . wp_kses_data( $comment->comment_excerpt ) . ' [...]</blockquote></li>';

				}
				echo '</ul>';
			} else {
				echo '<p>' . esc_html__( 'There are no product reviews yet.', 'yith-woocommerce-product-vendors' ) . '</p>';
			}
		}

		/**
		 * Allowed comments for vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @return void
		 */
		public function allowed_comments() {
			global $pagenow;
			if ( ! current_user_can( 'moderate_comments' ) ) {
				if ( 'comment.php' === $pagenow || 'edit-comments.php' === $pagenow ) {
					wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to edit comments.', 'yith-woocommerce-product-vendors' ) . '</p>', 403 );
				}
			}
		}

		/**
		 * Filter comments by vendor product.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param object $query The WP_Comment_Query object.
		 * @return void
		 */
		public function filter_reviews_list( $query ) {

			$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( empty( $current_screen ) || 'edit-comments' !== $current_screen->id ) {
				return;
			}

			$vendor_products = $this->vendor->get_products();
			/**
			 * If vendor haven't products there isn't comment to show with array(0) the query will abort.
			 * Another way to do this is to use the_comments hook: add_filter( 'the_comments', '__return_empty_array' );
			 */
			$query->query_vars['post__in'] = ! empty( $vendor_products ) ? $vendor_products : array( 0 );
		}

		/**
		 * Filter product reviews
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param array   $stats The comment stats.
		 * @param integer $post_id The post ID.
		 * @return bool|mixed|object
		 */
		public function count_comments( $stats, $post_id ) {

			global $wpdb;

			// Remove WooCommerce filter if any.
			$filter_p = has_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ) );
			if ( false !== $filter_p ) {
				remove_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ), $filter_p );
			}

			if ( empty( $post_id ) ) {

				$count = wp_cache_get( 'comments-0', 'counts' );
				if ( false !== $count ) {
					return $count;
				}

				$products = $this->vendor->get_products();
				$count    = array();
				$total    = 0;
				$approved = array(
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				);

				if ( ! empty( $products ) ) {
					$sql   = $wpdb->prepare( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} WHERE comment_type != '%s' AND comment_post_ID IN ( '%s' ) GROUP BY comment_approved", 'order_note', implode( "','", $products ) );
					$count = $wpdb->get_results( $sql, ARRAY_A );

					foreach ( (array) $count as $row ) {
						// Don't count post-trashed toward totals.
						if ( 'post-trashed' !== $row['comment_approved'] && 'trash' !== $row['comment_approved'] ) {
							$total += $row['num_comments'];
						}
						if ( isset( $approved[ $row['comment_approved'] ] ) ) {
							$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
						}
					}
				}

				$stats['total_comments'] = $total;
				foreach ( $approved as $key ) {
					if ( empty( $stats[ $key ] ) ) {
						$stats[ $key ] = 0;
					}
				}

				$stats = (object) $stats;
				wp_cache_set( 'comments-0', $stats, 'counts' );
			}

			return $stats;
		}

		/**
		 * Disable to mange other vendor options
		 *
		 * @since 1.6
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function disabled_manage_other_comments() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( 'load-comment.php' === current_action() && isset( $_GET['c'] ) && ! empty( $_GET['action'] ) && 'editcomment' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				$comment = get_comment( absint( $_GET['c'] ) );
				if ( ! in_array( $comment->comment_post_ID, $this->vendor->get_products() ) ) {
					// translators: %1$s and %2$s are open and close html tag for an anchor.
					wp_die( sprintf( __( 'You do not have permission to edit this review. %1$sClick here to view and edit your product reviews%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit-comments.php' ) . '">', '</a>' ) );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * If an user is a vendor admin remove the woocommerce prevent admin access
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $prevent_access Current value: true to prevent admin access, false otherwise.
		 * @return boolean
		 */
		public function prevent_admin_access( $prevent_access ) {
			return parent::prevent_admin_access( $prevent_access ) || $this->vendor->is_in_pending();
		}

		/**
		 * Remove YIT Shortcodes button in YITH Themes
		 *
		 * @since    1.6
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @return  void
		 */
		public function remove_shortcodes_button() {
			if ( function_exists( 'YIT_Shortcodes' ) && 'no' === get_option( 'yith_wpv_yit_shortcodes', 'no' ) ) {
				remove_action( 'admin_init', array( YIT_Shortcodes(), 'add_shortcodes_button' ) );
			}
		}

		/**
		 * Filtered the order preview data
		 *
		 * @since 3.4.1
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @param array    $data The order preview data.
		 * @param WC_Order $order Current order object.
		 * @return array Filtered preview data
		 */
		public function order_preview_get_order_details( $data, $order ) {

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_customer', 'no' ) ) {
				$data['data']['billing']['phone'] = '';
				$data['data']['billing']['email'] = '';
			}

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_payment', 'no' ) ) {
				$data['payment_via'] = '';
			}

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ) ) {
				$data['formatted_shipping_address'] = '';
				$data['formatted_billing_address']  = '';
			}

			return $data;
		}

		/**
		 * Customize WC_Order list table columns
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @param array $columns The order table columns.
		 * @return array
		 */
		public function customize_order_columns( $columns ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ) ) {
				unset( $columns['billing_address'], $columns['shipping_address'] );
			}

			return $columns;
		}
	}
}
