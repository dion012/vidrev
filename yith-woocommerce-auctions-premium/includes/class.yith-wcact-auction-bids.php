<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Bids Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Bids' ) ) {
	/**
	 * YITH_WCACT_Bids
	 *
	 * @since 1.0.0
	 */
	class YITH_WCACT_Bids {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Bids
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Auction table
		 *
		 * @var string
		 */
		public $table_name = '';

		/**
		 * Fee table
		 *
		 * @var string
		 */
		public $table_name_fee = '';

		/**
		 * Watchlist table
		 *
		 * @var string
		 */
		public $table_watchlist = '';

		/**
		 * Followers table
		 *
		 * @var string
		 */
		public $table_followers = '';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Bids
		 * @since  1.0.0
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			if ( is_null( $self::$instance ) ) {
				$self::$instance = new $self();
			}

			return $self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function __construct() {
			global $wpdb;

			$this->table_name      = $wpdb->prefix . YITH_WCACT_DB::$auction_table;
			$this->table_name_fee  = $wpdb->prefix . YITH_WCACT_DB::$fee_table;
			$this->table_watchlist = $wpdb->prefix . YITH_WCACT_DB::$watchlist_table;
			$this->table_followers = $wpdb->prefix . YITH_WCACT_DB::$follow_auctions;
		}

		/**
		 * Add bid in the db
		 *
		 * @param int   $user_id User id.
		 * @param int   $auction_id Auction id.
		 * @param float $bid Bid.
		 * @param int   $date Date when it's added.
		 */
		public function add_bid( $user_id, $auction_id, $bid, $date ) {
			global $wpdb;

			/**
			* DO_ACTION: yith_wcact_add_bid
			*
			* Adds an action when inserting the bid data into the database.
			*
			* @param int $auction_id The auction(product) ID.
			* @param float $bid      The bid amount to be inserted in the table.
			*/
			do_action( 'yith_wcact_add_bid', $auction_id, $bid );

			$insert_query = "INSERT INTO $this->table_name (`user_id`, `auction_id`, `bid`, `date`) VALUES ('" . $user_id . "', '" . $auction_id . "', '" . $bid . "' , '" . $date . "' )"; // phpcs:ignore

			$wpdb->query( $insert_query ); // phpcs:ignore
		}

		/**
		 * Get auction bids
		 *
		 * @param  int      $auction_id Auction id.
		 * @param bool/int $user_id User id.
		 * @param bool/int $offset Offset used for query.
		 * @param bool/int $per_page Auction Per page.
		 * @return array|null|object
		 */
		public function get_bids_auction( $auction_id, $user_id = false, $offset = false, $per_page = false ) {
			global $wpdb;

			$auction = wc_get_product( $auction_id );

			$order_by = ( $auction && 'auction' === $auction->get_type() && 'reverse' === $auction->get_auction_type() ) ? 'ASC' : 'DESC';

			$order_by = stripslashes( $order_by );

			$limit_query  = ( $per_page ) ? $wpdb->prepare( ' LIMIT %d ', $per_page ) : '';
			$offset_query = ( $offset ) ? $wpdb->prepare( ' OFFSET %d ', $offset ) : '';

			if ( isset( $user_id ) && $user_id > 0 ) {
				$query = $wpdb->prepare( "SELECT * FROM $this->table_name WHERE auction_id = %d AND user_id = %d  ORDER by CAST( bid AS decimal(50,5))" . $order_by . ', date ASC', $auction_id, $user_id ); // phpcs:ignore
			} else {
				$query = $wpdb->prepare( "SELECT * FROM $this->table_name WHERE auction_id = %d ORDER by CAST( bid AS decimal(50,5))" . $order_by . ', date ASC' . $limit_query . $offset_query, $auction_id ); // phpcs:ignore
			}

			$results = $wpdb->get_results( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Get bidders from a specific auction.
		 *
		 * @param int $auction_id Auction id.
		 *
		 * @return string
		 */
		public function get_bidders( $auction_id ) {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT count( distinct ( user_id ) ) FROM $this->table_name WHERE auction_id = %d", $auction_id ); // phpcs:ignore

			$results = $wpdb->get_var( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Get max bid from specific auction
		 *
		 * @param int $product_id Auction id.
		 *
		 * @return array|null|object
		 */
		public function get_max_bid( $product_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT user_id, auction_id, bid FROM $this->table_name WHERE auction_id = %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1", $product_id ); // phpcs:ignore
			$results = $wpdb->get_row( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Get min bid from specific auction
		 *
		 * @param int $product_id Auction id.
		 *
		 * @return array|null|object
		 */
		public function get_min_bid( $product_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT user_id, auction_id, bid FROM $this->table_name WHERE auction_id = %d ORDER by CAST(bid AS decimal(50,5)) ASC, date ASC LIMIT 1", $product_id ); // phpcs:ignore
			$results = $wpdb->get_row( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Get last two bids made on a specific auction product
		 *
		 * @param int    $product_id Product id.
		 * @param string $type Auction type.
		 *
		 * @return array|null|object
		 */
		public function get_last_two_bids( $product_id, $type = 'normal' ) {
			global $wpdb;

			$bids      = array();
			$first_bid = 'reverse' === $type ? $this->get_min_bid( $product_id ) : $this->get_max_bid( $product_id );

			if ( $first_bid ) {
				$bids[] = $first_bid;

				if ( isset( $first_bid->user_id ) ) {
					if ( 'reverse' === $type ) {
						$query = $wpdb->prepare( "SELECT user_id, auction_id, bid FROM $this->table_name WHERE auction_id = %d AND user_id <> %d ORDER by CAST(bid AS decimal(50,5)) ASC, date ASC LIMIT 1", $product_id, $first_bid->user_id ); // phpcs:ignore
					} else {
						$query = $wpdb->prepare( "SELECT user_id, auction_id, bid FROM $this->table_name WHERE auction_id = %d AND user_id <> %d ORDER by CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1", $product_id, $first_bid->user_id ); // phpcs:ignore
					}

					$second_bid = $wpdb->get_row( $query ); // phpcs:ignore

					if ( $second_bid ) {
						$bids[] = $second_bid;
					}
				}
			}

			return $bids;
		}

		/**
		 * Get the last bid from the giver user
		 *
		 * @param int $user_id User ID.
		 * @param int $auction_id Auction ID.
		 *
		 * @return null|object
		 */
		public function get_last_bid_user( $user_id, $auction_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT bid FROM $this->table_name WHERE user_id = %d AND auction_id = %d ORDER by date DESC LIMIT 1", $user_id, $auction_id ); // phpcs:ignore
			$results = $wpdb->get_var( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Get users who have made bids for an auction.
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return null|object
		 */
		public function get_users( $product_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT DISTINCT  user_id FROM $this->table_name WHERE auction_id = %d ", $product_id ); // phpcs:ignore
			$results = $wpdb->get_results( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Get auctions products where user place a bid.
		 *
		 * @param int         $user_id User id.
		 * @param bool/int    $limit Auction product limit.
		 * @param bool/string $status auctions status.
		 * @return null|object
		 */
		public function get_auctions_by_user( $user_id, $limit = false, $status = false ) {
			global $wpdb;

			$group_by = ' GROUP by auction_id ';
			$orderby  = ' ORDER BY date DESC ';
			$limit    = ( $limit ) ? $wpdb->prepare( ' LIMIT %d', $limit ) : '';
			$where    = $wpdb->prepare( " WHERE auction.user_id = %d AND pm2.meta_value = 'instock' AND posts.post_status = 'publish' AND term_taxonomy.taxonomy = 'product_type' AND terms.slug = 'auction' ", $user_id );
			$join     = " LEFT JOIN {$wpdb->postmeta} AS pm1 ON ( auction.auction_id = pm1.post_id ) LEFT JOIN {$wpdb->postmeta} AS pm2 ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_stock_status') LEFT JOIN {$wpdb->posts} AS posts ON ( auction.auction_id = posts.ID ) ";

			$inner_join_tax = " INNER JOIN {$wpdb->term_relationships} AS term_relationships ON ( auction.auction_id = term_relationships.object_id ) INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy ON ( term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id )
								INNER JOIN {$wpdb->terms} AS terms ON ( term_taxonomy.term_id = terms.term_id ) ";

			switch ( $status ) {
				case 'started':
					$where = $wpdb->prepare( " WHERE auction.user_id = %s AND pm1.meta_key = '_yith_auction_for' AND pm1.meta_value < %s AND pm2.meta_value = 'instock' AND posts.post_status = 'publish' AND term_taxonomy.taxonomy = 'product_type' AND terms.slug = 'auction'", $user_id, strtotime( 'now' ) );
					break;

				default:
					$where = $where;
			}

			$select = "SELECT auction_id FROM $this->table_name auction ";

			$query = $select . $join . $inner_join_tax . $where . $group_by . $orderby . $limit;

			$results = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

			/**
			 * APPLY_FILTERS: yith_wcact_get_auctions_by_user_results
			 *
			 * Filter the query results for the products where the user placed a bid.
			 *
			 * @param array  $results Query results
			 * @param int    $user_id User ID
			 * @param string $limit   Query limit
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_get_auctions_by_user_results', $results, $user_id, $limit );
		}

		/**
		 * Reschedule auction
		 *
		 * @param int $auction_id Auction ID.
		 *
		 * @return null|object
		 */
		public function reshedule_auction( $auction_id ) {
			/**
			 * APPLY_FILTERS: yith_wcact_not_delete_bids_for_reschedule_auctions
			 *
			 * Filter whether to delete the bids when the auction is rescheduled.
			 *
			 * @param bool $delete_bids Whether to delete the bids or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_not_delete_bids_for_reschedule_auctions', false ) ) {
				return;
			}

			global $wpdb;

			$query   = $wpdb->prepare( "DELETE FROM $this->table_name WHERE auction_id=%d", $auction_id ); // phpcs:ignore
			$results = $wpdb->get_results( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Delete customer bid
		 *
		 * @param int $delete_id ID index for remove.
		 *
		 * @return null|object
		 */
		public function delete_customer_bid( $delete_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "DELETE FROM $this->table_name WHERE id=%d", $delete_id ); // phpcs:ignore
			$results = $wpdb->get_results( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Delete all customer bids for a specific auction product
		 *
		 * @param int $user_id User ID.
		 * @param int $auction_id Auction ID.
		 *
		 * @return null|object
		 */
		public function remove_customer_bids( $user_id, $auction_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "DELETE FROM $this->table_name WHERE auction_id=%d AND user_id=%d", $auction_id, $user_id ); // phpcs:ignore
			$results = $wpdb->get_results( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 * Get auctions by user
		 *
		 * @param $product_id
		 *
		 * @return null|object
		 */
		/*public function get_auctions_by_user_export( $user_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT auction_id, bid FROM $this->table_name WHERE user_id = %d  ORDER by auction_id DESC", $user_id );
			$results = $wpdb->get_results( $query );

			return $results;
		}*/

		// ==== Fee database call === //

		/**
		 *  Get if user paid the fee for auction product.
		 *
		 * @param  int $user_id User ID.
		 * @param  int $auction_id Auction ID.
		 * @since  2.0.0
		 * @return null|object
		 */
		public function get_user_fee_payment( $user_id, $auction_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT auction_id FROM $this->table_name_fee WHERE user_id = %d  AND auction_id = %d", $user_id, $auction_id ); // phpcs:ignore
			$results = $wpdb->get_var( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Register fee on database
		 *
		 * @param  int      $user_id User ID.
		 * @param  int      $auction_id Auction ID.
		 * @param  DateTime $date Date.
		 * @param  int      $order_id Order ID.
		 * @param  float    $fee_amount Fee amount.
		 * @since  2.0.0
		 */
		public function register_fee( $user_id, $auction_id, $date, $order_id, $fee_amount ) {
			global $wpdb;

			$insert_query = "INSERT INTO $this->table_name_fee (`user_id`, `auction_id`, `date`, `order_id`, `fee_amount`) VALUES ('" . $user_id . "', '" . $auction_id . "', '" . $date . "' , '" . $order_id . "', '" . $fee_amount . "' )";
			$wpdb->query( $insert_query ); // phpcs:ignore
		}

		/**
		 *  Get auction products from watchlist.
		 *
		 * @param int      $user_id User id.
		 * @param bool/int $limit Auction product limit.
		 * @since  2.0.0
		 * @return null|object
		 */
		public function get_watchlist_product_by_user( $user_id, $limit = false ) {
			global $wpdb;

			$group_by = ' GROUP by auction_id ';
			$orderby  = ' ORDER BY dateadded DESC ';
			$limit    = ( $limit ) ? $wpdb->prepare( ' LIMIT %d', $limit ) : '';
			$where    = $wpdb->prepare( " WHERE watchlist.user_id = %d AND pm2.meta_value = 'instock' AND posts.post_status = 'publish' AND term_taxonomy.taxonomy = 'product_type' AND terms.slug = 'auction' ", $user_id );
			$join     = " LEFT JOIN {$wpdb->postmeta} AS pm1 ON ( watchlist.auction_id = pm1.post_id ) LEFT JOIN {$wpdb->postmeta} AS pm2 ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_stock_status') LEFT JOIN {$wpdb->posts} AS posts ON ( watchlist.auction_id = posts.ID ) ";

			$inner_join_tax = " INNER JOIN {$wpdb->term_relationships} AS term_relationships ON ( watchlist.auction_id = term_relationships.object_id ) INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy ON ( term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id )
								INNER JOIN {$wpdb->terms} AS terms ON ( term_taxonomy.term_id = terms.term_id ) ";

			$select = "SELECT auction_id FROM $this->table_watchlist AS watchlist ";

			$query = $select . $join . $inner_join_tax . $where . $group_by . $orderby . $limit;

			$results = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

			/**
			 * APPLY_FILTERS: yith_wcact_get_watchlist_auctions_by_user_results
			 *
			 * Filter the query results for the products in the watchlist for a specific user.
			 *
			 * @param array  $results Query results
			 * @param int    $user_id User ID
			 * @param string $limit   Query limit
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_get_watchlist_auctions_by_user_results', $results, $user_id, $limit );
		}

		/**
		 *  Get users that have the product in the watchlist.
		 *
		 * @param  int $auction_id Auction ID.
		 * @since  2.0.0
		 * @return null|object
		 */
		public function get_users_count_product_on_watchlist( $auction_id ) {
			global $wpdb;

			$query   = $wpdb->prepare( "SELECT count( distinct ( user_id )) FROM $this->table_watchlist WHERE auction_id = %d ", $auction_id ); // phpcs:ignore
			$results = $wpdb->get_var( $query ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Check if user has product in whatchlist
		 *
		 * @param  int $auction_id Auction ID.
		 * @param  int $user_id User ID.
		 * @since  2.0.0
		 * @return null|object
		 */
		public function is_product_in_watchlist( $auction_id, $user_id ) {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT user_id FROM $this->table_watchlist WHERE auction_id = %d AND user_id = %d GROUP by user_id", $auction_id, $user_id ); // phpcs:ignore

			$result = $wpdb->get_var( $query ); // phpcs:ignore

			return $result;
		}

		/**
		 *  Add product to the watchlist
		 *
		 * @param  int $auction_id Auction ID.
		 * @param  int $user_id User ID.
		 * @since  2.0.0
		 * @return boolean
		 */
		public function add_product_to_watchlist( $auction_id, $user_id ) {
			global $wpdb;

			$insert_query = "INSERT INTO $this->table_watchlist (`user_id`, `auction_id`) VALUES ('" . $user_id . "', '" . $auction_id . "' )";
			$wpdb->query( $insert_query ); // phpcs:ignore

			return true;
		}

		/**
		 *  Remove product to the watchlist
		 *
		 * @param  int $auction_id Auction ID.
		 * @param  int $user_id User ID.
		 * @since  2.0.0
		 * @return boolean
		 */
		public function remove_product_to_watchlist( $auction_id, $user_id ) {
			global $wpdb;

			$query = $wpdb->prepare( "DELETE FROM $this->table_watchlist WHERE auction_id=%d AND user_id=%d", $auction_id, $user_id ); // phpcs:ignore
			$wpdb->query( $query ); // phpcs:ignore

			return true;
		}

		/* == Followers table methods to interact with the database  ==*/
		/**
		 *  Insert user in the follower list
		 *
		 * @param  int    $auction_id Auction id.
		 * @param  string $email User email.
		 * @param  int    $user_id User id.
		 * @param  string $hash Hash.
		 * @param  string $is_bidder Is bidder.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function insert_follower( $auction_id, $email, $user_id, $hash, $is_bidder = '' ) {
			global $wpdb;

			$user_id      = ( $user_id ) ? $user_id : null;
			$insert_query = "INSERT INTO $this->table_followers (`user_id`,`email`,`auction_id`,`hash`, `is_bidder`) VALUES ('" . $user_id . "', '" . $email . "', '" . $auction_id . "', '" . $hash . "', '" . $is_bidder . "' )";
			$wpdb->query( $insert_query ); // phpcs:ignore

			return true;
		}

		/**
		 *  Update user in the follower list
		 *
		 * @param  int    $auction_id Auction id.
		 * @param  string $email User email.
		 * @param  string $is_bidder Is bidder.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function update_follower_as_bidder( $auction_id, $email, $is_bidder ) {
			global $wpdb;

			$query           = $wpdb->prepare( "UPDATE $this->table_followers SET is_bidder = %d WHERE auction_id=%d AND email=%s", $is_bidder, $auction_id, $email ); // phpcs:ignore
			$update_follower = $wpdb->query( $query ); // phpcs:ignore

			return $update_follower;
		}

		/**
		 *  Delete user from the follower list
		 *
		 * @param  int    $auction_id Auction id.
		 * @param  string $email User email.
		 * @param string $user_id User id.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function delete_follower( $auction_id, $email = false, $user_id = false ) {
			global $wpdb;

			$value = false;

			if ( $user_id && $user_id > 0 ) {
				$query = $wpdb->prepare( "DELETE FROM $this->table_followers WHERE auction_id=%d AND user_id=%d", $auction_id, $user_id ); // phpcs:ignore
			} else {
				$query = $wpdb->prepare( "DELETE FROM $this->table_followers WHERE auction_id=%d AND email=%d", $auction_id, $email ); // phpcs:ignore
			}

			if ( $query ) {
				$value = $wpdb->query( $query ); // phpcs:ignore
			}

			return $value;
		}

		/**
		 *  Delete users from the follower list of a specific product
		 *
		 * @param  int $auction_id Auction id.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function delete_followers( $auction_id ) {
			global $wpdb;

			if ( $auction_id && $auction_id > 0 ) {
				$query = $wpdb->prepare( "DELETE FROM $this->table_followers WHERE auction_id=%d", $auction_id ); // phpcs:ignore

				$wpdb->query( $query ); // phpcs:ignore
			}

			return true;
		}

		/**
		 *  Check if an email is in follower list of a specific product
		 *
		 * @param  int    $auction_id Auction id.
		 * @param  string $email User email.
		 * @param  int    $user_id User ID.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function is_a_follower( $auction_id, $email, $user_id = '' ) {
			$result = false;

			if ( $email && $auction_id ) {
				global $wpdb;

				if ( $user_id ) {
					$query = $wpdb->prepare( "SELECT email,is_bidder FROM $this->table_followers WHERE auction_id = %d AND email = %s GROUP by email", $auction_id, $email ); // phpcs:ignore
				} else {
					$query = $wpdb->prepare( "SELECT email,hash FROM $this->table_followers WHERE auction_id = %d AND email = %s GROUP by email", $auction_id, $email ); // phpcs:ignore
				}

				$result = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore
			}

			return $result;
		}

		/**
		 *  Check if a pair email => hash is in the follower list
		 *
		 * @param  int    $auction_id Auction id.
		 * @param  string $email User email.
		 * @param  string $hash Hash.
		 * @since  3.0.0
		 * @return boolean
		 */
		public function is_a_valid_follower( $auction_id, $email, $hash ) {
			$result = false;

			if ( $email && $auction_id ) {
				global $wpdb;

				$query = $wpdb->prepare( "SELECT email FROM $this->table_followers WHERE auction_id = %d AND email = %s AND hash = %s GROUP by email", $auction_id, $email, $hash ); // phpcs:ignore

				$result = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore
			}

			return $result;
		}

		/**
		 *  Get users that have the product in the follower list.
		 *
		 * @param  int $auction_id Auction ID.
		 * @since  3.0.0
		 * @return null|object
		 */
		public function get_users_count_product_on_follower_list( $auction_id ) {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT email,hash FROM $this->table_followers WHERE auction_id = %d AND is_bidder != 1 GROUP by email", $auction_id ); // phpcs:ignore
			// Store as key the first value, in this case the email.
			$results = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

			return $results;
		}

		/**
		 *  Get follower auctions from specific email
		 *
		 * @param  string $email User email.
		 * @since  3.0.0
		 * @return null|object
		 */
		public function get_auction_follower_list( $email ) {
			global $wpdb;

			$query  = $wpdb->prepare( "SELECT auction_id FROM $this->table_followers LEFT JOIN $wpdb->postmeta ON ( $this->table_followers.auction_ID = $wpdb->postmeta.post_id ) WHERE $this->table_followers.email = %s AND  $wpdb->postmeta.meta_value > %d", $email, strtotime( 'now' ) ); // phpcs:ignore
			$result = $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

			return $result;
		}
	}
}
