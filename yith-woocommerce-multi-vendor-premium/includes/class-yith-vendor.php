<?php
/**
 * YITH Vendor Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendor' ) ) {

	/**
	 * The main class for the Vendor
	 *
	 * @class      YITH_Vendor
	 * @since      1.0.0
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendor extends YITH_Vendor_Legacy {

		/**
		 * Populate the instance with term data
		 *
		 * @param integer $vendor_id (Optional) The vendor ID. Default 0.
		 * @param null    $term      (Optional) The term. Default null.
		 * @return YITH_Vendor|false The current object, false otherwise.
		 */
		public function __construct( $vendor_id = 0, $term = null ) {
			$this->set_id( $vendor_id );
			if ( $this->get_id() ) {
				$this->read();
			}

			return $this;
		}

		/**
		 * Get the vendor name
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_name() {
			return $this->get_data( 'name' );
		}

		/**
		 * Get the vendor slug
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_slug() {
			return $this->get_data( 'slug' );
		}

		/**
		 * Get the vendor description
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_description() {
			return $this->get_data( 'description' );
		}

		/**
		 * Get the vendor term count
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_product_count() {
			return absint( $this->get_data( 'count' ) );
		}

		/**
		 * Get vendor owner
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $type (Optional) How return the data: id to get owner id, object to get the full WP_User object.
		 * @return integer|WP_User
		 */
		public function get_owner( $type = 'id' ) {
			global $yith_wcmv_cache;

			$owner = absint( $this->get_meta( 'owner' ) );
			if ( 'id' === $type ) {
				return $owner;
			}

			$owner_obj = $yith_wcmv_cache->get_vendor_cache( $this->get_id(), 'owner' );
			if ( false === $owner_obj ) {
				$owner_obj = get_user_by( 'id', $owner );
				$yith_wcmv_cache->set_vendor_cache( $this->get_id(), 'owner', $owner_obj );
			}

			return $owner_obj;
		}

		/**
		 * Get admins for vendor.
		 * This is an alias for get_owner but using a different filter useful for handle additional admins.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $type (Optional) How return the data: id to get an array of admin id, objects to get an array of WP_User objects.
		 * @return integer[]|WP_User[]
		 */
		public function get_admins( $type = 'id' ) {
			$admins = array( $this->get_owner( $type ) );
			return apply_filters( 'yith_wcmv_get_vendor_admins', $admins, $type, $this );
		}

		/**
		 * Get vendor commission type
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_commission_type() {
			$commission = $this->get_meta( 'commission' );
			// If meta commission_type doesn't exist but commission is not empty return 'custom'.
			if ( ! $this->meta_exists( 'commission_type' ) && ! empty( $commission ) ) {
				return 'custom';
			}
			return $this->get_meta( 'commission_type' );
		}

		/**
		 * Get the vendor commission
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param mixed $product_id The product ID, false otherwise.
		 * @return mixed
		 */
		public function get_commission( $product_id = false ) {

			if ( 'default' === $this->get_commission_type() ) {
				$commission = yith_wcmv_get_base_commission();
			} else {
				$commission = floatval( $this->get_meta( 'commission' ) );
			}

			if ( ! empty( $product_id ) ) {
				$product                 = wc_get_product( $product_id );
				$product_base_commission = $product ? $product->get_meta( '_product_commission' ) : 0;
				if ( ! empty( $product_base_commission ) ) {
					$commission = $product_base_commission;
				}
			}

			return apply_filters( 'yith_wcmv_get_vendor_commission', $commission / 100, $this->get_id(), $this, $product_id );
		}

		/**
		 * Alias for method get_commission
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param mixed $product_id The product ID, false otherwise.
		 * @return mixed
		 */
		public function get_commission_rate( $product_id = false ) {
			return $this->get_commission( $product_id );
		}

		/**
		 * Get vendor store header image ID
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @return   integer Store header image ID.
		 */
		public function get_header_image_id() {
			$image_id = $this->get_meta_data( 'header_image' );
			// Backward compatibility with url.
			if ( ! is_numeric( $image_id ) ) {
				$image_id = attachment_url_to_postid( $image_id );
			}

			return $image_id;
		}

		/**
		 * Get vendor avatar image ID
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @return   integer Vendor avatar ID.
		 */
		public function get_avatar_id() {
			$image_id = $this->get_meta_data( 'avatar' );
			// Backward compatibility with url.
			if ( ! is_numeric( $image_id ) ) {
				$image_id = attachment_url_to_postid( $image_id );
			}

			return $image_id;
		}

		/**
		 * Get vendor logo image ID. Alias for get_avatar_id
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @return   integer Vendor avatar ID.
		 */
		public function get_logo_id() {
			return $this->get_avatar_id();
		}

		/**
		 * Get vendor store header image
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $html (Optional) True to return the html, false to get only image url.
		 * @param boolean $use_placeholder (Optional) Whatever to use placeholder or nor if the no avatar image. By default, use store option.
		 * @return array|string Store header image url array or html.
		 */
		public function get_header_image( $html = true, $use_placeholder = false ) {
			$image = '';
			$size  = apply_filters( 'yith_wcmv_header_image_size', YITH_Vendors()->get_image_size( 'header' ) );

			$use_placeholder = empty( $use_placeholder ) ? get_option( 'yith_wpv_header_use_default_image', 'no-image' ) : 'no-image';
			$placeholder     = get_option( 'yith_wpv_header_default_image', 0 );
			$header_image_id = $this->get_header_image_id();
			switch ( $use_placeholder ) {
				case 'all':
					$image_id = $placeholder;
					break;
				case 'none':
					$image_id = $header_image_id;
					break;
				default:
					$image_id = $header_image_id ? $header_image_id : $placeholder;
					break;
			}

			if ( ! empty( $image_id ) ) {
				$class = apply_filters( 'yith_wcmv_header_img_class', array( 'class' => 'store-image' ) ); // deprecated.
				$image = $html ? wp_get_attachment_image( $image_id, $size, false, $class ) : wp_get_attachment_image_src( $image_id, $size );
			}

			return apply_filters( 'yith_wcmv_get_vendor_header_image', $image, $html, $this );
		}

		/**
		 * Get vendor store avatar image
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $html (Optional) True to return the html, false to get only image url.
		 * @param boolean $use_placeholder (Optional) Whatever to use placeholder or nor if the no avatar image. By default, use store option.
		 * @return array|string Store avatar image url array or html.
		 */
		public function get_avatar( $html = true, $use_placeholder = false ) {
			$image = '';
			$size  = apply_filters( 'yith_wcmv_avatar_image_size', YITH_Vendors()->get_image_size( 'avatar' ) );

			$use_placeholder = empty( $use_placeholder ) ? get_option( 'yith_wpv_avatar_use_default_image', 'no-image' ) : 'no-image';
			$placeholder     = get_option( 'yith_wpv_avatar_default_image', 0 );
			$avatar_id       = $this->get_avatar_id();
			switch ( $use_placeholder ) {
				case 'all':
					$image_id = $placeholder;
					break;
				case 'none':
					$image_id = $avatar_id;
					break;
				default:
					$image_id = $avatar_id ? $avatar_id : $placeholder;
					break;
			}

			if ( ! empty( $image_id ) ) {
				$image = $html ? wp_get_attachment_image( $image_id, $size ) : wp_get_attachment_image_src( $image_id, $size );
			}

			return apply_filters( 'yith_wcmv_get_vendor_avatar', $image, $html, $this );
		}

		/**
		 * Get vendor store logo image. Alias for get_avatar_image
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param boolean $html (Optional) True to return the html, false to get only image url.
		 * @return array|string Store avatar image url array or html.
		 */
		public function get_logo( $html = true ) {
			return $this->get_avatar();
		}

		/**
		 * Get enable selling
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @return   string Store header image url.
		 */
		public function get_enable_selling() {
			return $this->get_meta( 'enable_selling' );
		}

		/**
		 * Get posts of this vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string       $post_type the post type to get.
		 * @param array|string $fields    The fields to retrieve.
		 * @param string       $group_by  If group by the query.
		 * @return array
		 */
		protected function get_posts( $post_type, $fields = '*', $group_by = '' ) {
			global $wpdb;

			// TODO add cache

			if ( empty( $post_type ) ) {
				return array();
			}

			if ( 'all' === $fields ) {
				$fields = '*';
			}

			if ( is_array( $fields ) ) {
				$fields = implode( ',', $fields );
			}

			if ( 'shop_order' === $post_type ) {
				$join  = "INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID";
				$where = $wpdb->prepare( 'WHERE p.post_type = %s AND p.post_parent <> 0 AND pm.meta_key = %s AND pm.meta_value = %d', 'shop_order', 'vendor_id', $this->id );
			} else {
				$join  = "INNER JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.ID INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
				$where = $wpdb->prepare( 'WHERE tt.taxonomy = %s AND tt.term_id = %d AND p.post_type = %s', YITH_Vendors_Taxonomy::TAXONOMY_NAME, $this->id, $post_type );
			}

			// Set group by if any.
			$group_by = $group_by ? "GROUP BY {$group_by}" : '';
			$result   = $wpdb->get_results( "SELECT {$fields} FROM {$wpdb->posts} AS p $join $where $group_by" ); // phpcs:ignore

			return ! empty( $result ) ? $result : array();
		}

		/**
		 * Get products of this vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $extra (Optional) More arguments to append.
		 * @return array
		 */
		public function get_products( $extra = array() ) {
			global $wpdb, $yith_wcmv_cache;
			// Create a unique key for query.
			$query_key = md5( maybe_serialize( $extra ) );
			$cache_key = "products_$query_key";
			$products  = $yith_wcmv_cache->get_vendor_cache( $this->get_id(), $cache_key );

			if ( false === $products ) {
				$args = wp_parse_args(
					$extra,
					array(
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'post_type'      => 'product',
						'tax_query'      => array( // phpcs:ignore
							array(
								'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
								'field'    => 'id',
								'terms'    => $this->get_id(),
							),
						),
					)
				);

				// If post type includes product_variation we need to do a direct query since taxonomy is not associated with variation.
				if ( is_array( $args['post_type'] ) && empty( $args['include'] ) && in_array( 'product_variation', $args['post_type'], true ) ) {
					$includes = $wpdb->get_col( // phpcs:ignore
						$wpdb->prepare(
							"SELECT p.ID FROM {$wpdb->posts} AS p INNER JOIN {$wpdb->term_relationships} AS tr ON ( tr.object_id = p.ID OR tr.object_id = p.post_parent ) AND p.post_type IN ( 'product', 'product_variation' ) AND tr.term_taxonomy_id = %d",
							$this->get_id()
						)
					);

					$args['include'] = array_unique( $includes );
					unset( $args['tax_query'] );
				}

				// Let's filter args.
				$args     = apply_filters( 'yith_wcmv_vendor_get_products_query_args', $args, $extra, $this );
				$products = get_posts( $args );
				// Store result on cache.
				$yith_wcmv_cache->set_vendor_cache( $this->get_id(), $cache_key, $products );
			}

			return $products;
		}

		/**
		 * Get query order ids of this vendor
		 *
		 * @param string               $type   The type of order to get.
		 * @param boolean|array|string $status (Optional) Filter order by statuses. Default is false.
		 * @return array The order ids
		 */
		public function get_orders( $type = 'all', $status = false ) {
			global $wpdb;

			// TODO add cache

			switch ( $type ) {
				case 'all':
					$query = $wpdb->prepare( "SELECT DISTINCT order_id FROM {$wpdb->commissions} WHERE vendor_id = %d", $this->get_id() );
					break;

				case 'quote':
				case 'suborder':
					$query = $wpdb->prepare( "SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID WHERE p.post_parent <> 0 AND p.post_type = %s AND pm.meta_key = %s AND pm.meta_value = %d", 'shop_order', 'vendor_id', $this->get_id() );

					if ( $status ) {
						if ( is_array( $status ) ) {
							$post_status_in = '';
							$count          = count( $status );
							$i              = 1;
							foreach ( $status as $stati ) {
								$post_status_in .= "'{$stati}'";
								if ( $i < $count ) {
									$post_status_in .= ',';
								}
								$i++;
							}
							$query .= ' AND p.post_status IN (' . $post_status_in . ')';
						} else {
							$query .= $wpdb->prepare( ' AND p.post_status = %s', $status );
						}
					}

					break;

				default:
					break;
			}

			$order_ids = ! empty( $query ) ? $wpdb->get_col( $query ) : array(); // phpcs:ignore

			return $order_ids;
		}

		/**
		 * The review average and the product with reviews count
		 *
		 * @since  1.0.0
		 * @author Andrea Grillo
		 * @author Francesco Licandro
		 * @return array The review average and the product with reviews count
		 */
		public function get_reviews_average_and_product() {
			global $wpdb;

			// TODO add cache

			$response = apply_filters( 'yith_wcmv_reviews_average_and_product', array(), $this );
			if ( ! empty( $response ) ) {
				return $response;
			}

			$product_ids = $this->get_products();
			$response    = array(
				'average_rating'        => 0,
				'reviews_product_count' => 0,
			);

			if ( ! empty( $product_ids ) ) {
				$product_ids                       = implode( ',', $product_ids );
				$product_review_count_query        = $wpdb->prepare( "SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} as pm WHERE meta_key=%s AND post_id IN ( ##vendor_product_ids## )", '_wc_review_count' );
				$product_review_count_query        = str_replace( '##vendor_product_ids##', $product_ids, $product_review_count_query );
				$response['reviews_product_count'] = $wpdb->get_var( $product_review_count_query ); // phpcs:ignore

				$average_query = $wpdb->prepare( "SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} as pm WHERE meta_key=%s AND post_id IN ( ##vendor_product_ids## )", '_wc_average_rating' );
				$average_query = str_replace( '##vendor_product_ids##', $product_ids, $average_query );
				$average       = $wpdb->get_var( $average_query ); // phpcs:ignore

				$count_reviewed_products_query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} as pm WHERE meta_key=%s AND post_id IN ( ##vendor_product_ids## ) AND meta_value !=0", '_wc_review_count' );
				$count_reviewed_products_query = str_replace( '##vendor_product_ids##', $product_ids, $count_reviewed_products_query );
				$count_reviewed_products       = $wpdb->get_var( $count_reviewed_products_query ); // phpcs:ignore

				if ( ! empty( $count_reviewed_products ) ) {
					$response['average_rating'] = number_format( $average / $count_reviewed_products, 2 );
				}
			}

			return $response;
		}

		/**
		 * Get the frontend vendor store url
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $context (Optional) The url context.
		 * @return string
		 */
		public function get_url( $context = 'frontend' ) {
			$url = '';
			if ( 'frontend' === $context ) {
				$term_link = get_term_link( $this->get_id(), YITH_Vendors_Taxonomy::TAXONOMY_NAME );
				if ( ! is_wp_error( $term_link ) ) {
					$url = $term_link;
				}
			} else {
				if ( 'admin' === $context && function_exists( 'yith_wcmv_get_admin_panel_url' ) ) {
					$url = yith_wcmv_get_admin_panel_url(
						array(
							'tab' => 'vendors',
							's'   => $this->get_slug(),
						)
					);
				}
			}

			return apply_filters( 'yith_wcmv_get_vendor_url', $url, $this, $context );
		}

		/**
		 * Get vendor formatted address
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return string
		 */
		public function get_formatted_address() {
			$address = '';
			if ( ! empty( WC()->countries ) ) {
				$address = WC()->countries->get_formatted_address(
					array(
						'address_1' => $this->get_meta( 'location' ),
						'city'      => $this->get_meta( 'city' ),
						'state'     => $this->get_meta( 'state' ),
						'postcode'  => $this->get_meta( 'zipcode' ),
						'country'   => $this->get_meta( 'country' ),
					),
					','
				);
			}

			return apply_filters( 'yith_wcmv_get_vendor_formatted_address', $address, $this );
		}

		/**
		 * Get vendor socials
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_socials() {
			$socials = $this->get_meta( 'socials' );
			if ( empty( $socials ) ) {
				$socials = array();
			} else {
				$socials = array_filter( $socials );
				foreach ( $socials as $social => &$uri ) {
					$uri = str_replace( array( 'http:', 'https:' ), '', $uri );
				}
			}

			return $socials;
		}

		/**
		 * Get the registration date
		 *
		 * @param string  $context (Optional) The context of the date (timestamp|display|edit).
		 * @param string  $format  (Optional) The date format.
		 * @param boolean $gmt     (Optional) True to get gmt date, false otherwise.
		 * @return string The registration date.
		 */
		public function get_registration_date( $context = '', $format = '', $gmt = false ) {
			$registration_date = $gmt ? $this->get_meta( 'registration_date_gmt' ) : $this->get_meta( 'registration_date' );
			if ( empty( $registration_date ) ) {
				return '';
			}

			if ( 'timestamp' === $context ) {
				$registration_date = strtotime( $registration_date );
			} elseif ( 'display' === $context ) {
				if ( empty( $format ) ) {
					$format = get_option( 'date_format' );
				}
				$registration_date = date_i18n( $format, strtotime( $registration_date ) );
			}

			return $registration_date;
		}

		/**
		 * Get number of sales for this vendor
		 *
		 * @since 4.0.0
		 * @author Francesco Licandro
		 * @return integer
		 */
		public function get_number_of_sales() {
			return count( $this->get_orders( 'suborder' ) );
		}

		/**
		 * Set the vendor name
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $value The name to set.
		 * @return void
		 */
		public function set_name( $value ) {
			$this->set_data( 'name', $value );
		}

		/**
		 * Set the vendor slug
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $value The name to set.
		 * @return void
		 */
		public function set_slug( $value ) {
			$this->set_data( 'slug', $value );
		}

		/**
		 * Set the vendor description
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $value The name to set.
		 * @return void
		 */
		public function set_description( $value ) {
			$this->set_data( 'description', wp_kses_post( $value ) );
		}

		/**
		 * Set vendor owner
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param integer $user_id The user ID to set as owner.
		 * @return boolean
		 */
		public function set_owner( $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				return $this->remove_owner();
			}

			$this->set_meta( 'owner', $user->ID );
			return true;
		}

		/**
		 * Remove vendor owner
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function remove_owner() {
			$this->set_meta( 'owner', null );
			return true;
		}

		/**
		 * Set vendor commission type
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $value The name to set.
		 * @return void
		 */
		public function set_commission_type( $value ) {
			if ( in_array( $value, array( 'default', 'custom' ), true ) ) {
				$this->set_meta( 'commission_type', $value );
			}
		}

		/**
		 * Set vendor commission
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $value The name to set.
		 * @return void
		 */
		public function set_commission( $value ) {
			// Validate value.
			$value = (float) $value;
			if ( $value < 0 ) {
				$value = 0;
			} elseif ( $value > 100 ) {
				$value = 100;
			}

			$this->set_meta_data( 'commission', wc_format_decimal( $value ) );
		}

		/**
		 * Get enable selling
		 *
		 * @since    4.0.0
		 * @author   Francesco Licandro
		 * @param string $value The name to set.
		 * @return   void
		 */
		public function set_enable_selling( $value ) {
			$value = ( true === $value || 'no' !== $value ) ? 'yes' : 'no';
			$this->set_meta_data( 'enable_selling', $value );
		}

		/**
		 * Check if the current object is a valid vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return bool
		 */
		public function is_valid() {
			return ! empty( $this->get_id() ) && ! empty( $this->get_data( 'name' ) );
		}

		/**
		 * Is selling enabled for vendor?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function is_selling_enabled() {
			return 'yes' === $this->get_meta( 'enable_selling' );
		}

		/**
		 * Is vendor in pending?
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function is_in_pending() {
			return 'yes' === $this->get_meta( 'pending' );
		}

		/**
		 * Check if the vendor has accepted the latest privacy policy
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function has_privacy_policy_accepted() {
			$privacy_date_timestamp     = strtotime( $this->get_meta( 'data_privacy_policy' ) );
			$now_privacy_date_timestamp = strtotime( YITH_Vendors()->get_last_modified_data_privacy_policy() );

			return $privacy_date_timestamp === $now_privacy_date_timestamp;
		}

		/**
		 * Check if the vendor has accepted the latest terms ad conditions
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function has_terms_and_conditions_accepted() {
			$terms_date_timestamp     = strtotime( $this->get_meta_data( 'data_terms_and_condition' ) );
			$now_terms_date_timestamp = strtotime( YITH_Vendors()->get_last_modified_data_terms_and_conditions() );

			return $terms_date_timestamp === $now_terms_date_timestamp;
		}

		/**
		 * Check if the user passed in parameter is admin
		 *
		 * @since 4.0.0
		 * @param boolean|integer $user_id The user to check.
		 * @return boolean
		 */
		public function is_user_admin( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			// If the user is shop manager or administrator, return true.
			if ( current_user_can( 'manage_woocommerce' ) ) {
				return true;
			}

			return in_array( $user_id, $this->get_admins(), true );
		}

		/**
		 * Check if the user has limited access to admin dashboard, valid only for Vendor Admin
		 *
		 * @since 4.0.0
		 * @param boolean|integer $user_id The user to check.
		 * @return boolean
		 */
		public function has_limited_access( $user_id = false ) {
			return ! current_user_can( 'manage_woocommerce' ) && $this->is_user_admin( $user_id );
		}

		/**
		 * Check if the current user is the vendor owner
		 *
		 * @since  4.0.0
		 * @param boolean|integer $user_id The user to check.
		 * @return boolean
		 */
		public function is_owner( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			return $user_id === $this->get_owner();
		}

		/**
		 * Count posts of this vendor
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string $post_type The post type to count.
		 * @return array
		 */
		public function count_posts( $post_type ) {
			return $this->get_posts( $post_type, 'post_status, COUNT( DISTINCT ID ) AS count', 'p.post_status' );
		}

		/**
		 * Check if vendor can add a new product
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function can_add_products() {
			if ( 'no' === get_option( 'yith_wpv_enable_product_amount', 'no' ) ) {
				return true;
			}

			$products_limit = apply_filters( 'yith_wcmv_vendors_products_limit', get_option( 'yith_wpv_vendors_product_limit', 25 ), $this );
			$products_count = count( $this->get_products( array( 'post_status' => 'any' ) ) );

			return $products_count < $products_limit;
		}

		/**
		 * Check if vendor can handle featured products
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function can_handle_featured_products() {
			// Check vendor meta, if empty get the main option.
			$featured = $this->get_meta( 'featured_products' );
			return empty( $featured ) ? 'yes' === $featured : 'yes' === get_option( 'yith_wpv_vendors_option_featured_management', 'no' );
		}
	}
}
