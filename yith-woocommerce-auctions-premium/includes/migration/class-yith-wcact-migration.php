<?php
/**
 * YITH_WCACT_Migration Class.
 *
 * @package YITH\Auctions\Includes\Migration
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Main Class YITH_WCACT_Migration
 *
 * @class   YITH_WCACT_Migration
 * @package Yithemes
 * @since   Version 3.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */

if ( ! class_exists( 'YITH_WCACT_Migration' ) ) {
	/**
	 * Class YITH_WCACT_Migration
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Migration {

		/**
		 * Main Instance
		 *
		 * @var    YITH_WCACT_Migration
		 * @since  1.0
		 * @access protected
		 */
		protected static $instance = null;

		/**
		 * Bidders table
		 *
		 * @var string
		 */
		public $table_bidders = '';

		/**
		 * Followers table
		 *
		 * @var string
		 */
		public $table_followers = '';

		/**
		 * Followers table
		 *
		 * @var string
		 */
		public $table_users = '';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Migration
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
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		private function __construct() {
			global $wpdb;

			$this->table_bidders   = $wpdb->prefix . YITH_WCACT_DB::$auction_table;
			$this->table_followers = $wpdb->prefix . YITH_WCACT_DB::$follow_auctions;
			$this->table_users     = $wpdb->prefix . 'users';

			/* == Migrate followers */
			add_action( 'yith_wcact_followers_migration', array( $this, 'process_migration_followers' ) );
			add_action( 'yith_wcact_followers_users_migration', array( $this, 'migrate_followers' ), 10, 2 );

			/* == Assign auction status == */
			add_action( 'yith_wcact_assign_auction_status', array( $this, 'process_assign_auction_status' ) );

			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'handle_custom_query_var' ), 10, 2 );
		}

		/**
		 * Run followers migration
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function followers() {
			$this->migrate_bidders_on_followers_table();

			add_action( 'init', array( $this, 'run_followers_migration' ) );
		}

		/**
		 * Generate the recurring cron for migrate followers
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function run_followers_migration() {
			$has_hook_scheduled = WC()->queue()->get_next( 'yith_wcact_followers_migration' );

			if ( $has_hook_scheduled ) {
				WC()->queue()->cancel_all( 'yith_wcact_followers_migration' );
			}

			WC()->queue()->schedule_recurring( strtotime( 'now' ), 10 * MINUTE_IN_SECONDS, 'yith_wcact_followers_migration' );
		}

		/**
		 * Create single cronjob for each product for add followers in the database
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function process_migration_followers() {
			$products = wc_get_products(
				apply_filters(
					'yith_wcact_migrate_followers_query_args',
					array(
						'type'                          => 'auction',
						'limit'                         => apply_filters( 'yith_wcact_limit_migrate_followers_products', 100 ),
						'return'                        => 'ids',
						'yith_wcact_followers_products' => true,
					)
				)
			);

			if ( ! empty( $products ) && is_array( $products ) ) {
				foreach ( $products as $product_id ) {
					$schedule_time = time();
					$followers     = get_post_meta( $product_id, 'yith_wcact_auction_watchlist', true );
					$group         = 'yith_wcact_migration_followers_' . $product_id;

					if ( ! empty( $followers ) && is_array( $followers ) ) {
						$followers = array_chunk( $followers, apply_filters( 'yith_wcact_migration_followers_chunk', 50 ) );

						do {
							$temp_followers = array_shift( $followers );

							WC()->queue()->schedule_single(
								$schedule_time,
								'yith_wcact_followers_users_migration',
								array(
									'product_id' => $product_id,
									'followers'  => $temp_followers,
								),
								$group
							);
							$schedule_time += 2 * apply_filters( 'yith_wcact_migration_followers_schedule_time', MINUTE_IN_SECONDS );

						} while ( ! empty( $followers ) );
					}

					// Update meta in the product to know that it's processed.
					update_post_meta( $product_id, '_yith_wcact_migration_processed', true );
				}
			} else {
				WC()->queue()->cancel_all( 'yith_wcact_followers_migration' );
			}
		}

		/**
		 * Migrate followers from postmeta to database
		 *
		 * @param int   $auction_id Auction id.
		 * @param array $followers array of email followers.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function migrate_followers( $auction_id, $followers ) {
			$instance = YITH_Auctions()->bids;

			foreach ( $followers as $follower_mail ) {
				if ( filter_var( $follower_mail, FILTER_VALIDATE_EMAIL ) ) {
					$user = get_user_by( 'email', $follower_mail );

					// Check if the email is a registered customer or not.
					$user_id = ( $user && isset( $user->ID ) ) ? $user->ID : false;

					$is_a_follower = $instance->is_a_follower( $auction_id, $follower_mail );

					if ( empty( $is_a_follower ) ) {
						// Insert in the database.
						$hash = wp_generate_password();
						$instance->insert_follower( $auction_id, $follower_mail, $user_id, $hash );
					}
				}
			}
		}

		/**
		 * Handle a custom 'customvar' query var to get products with the 'customvar' meta.
		 *
		 * @param  array $query      - Args for WP_Query.
		 * @param  array $query_vars - Query vars from WC_Product_Query.
		 * @return array modified $query
		 */
		public function handle_custom_query_var( $query, $query_vars ) {
			// Query vars for followers migration.
			if ( ! empty( $query_vars['yith_wcact_followers_products'] ) ) {
				$query['meta_query'][] = array(
					'relation' => 'AND',
					array(
						'key'     => 'yith_wcact_auction_watchlist',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => '_yith_wcact_migration_processed',
						'compare' => 'NOT EXISTS',
					),

				);
			}

			return $query;
		}

		/**
		 * Migrate bidders database on followers database
		 *
		 * This migration will allow to handle all notification from followers database.
		 */
		public function migrate_bidders_on_followers_table() {
			$val = get_option( 'yith_wcact_test_migration_bidders_to_followers', false );

			if ( ! $val ) {
				global $wpdb;

				// Insert from bidders table to followers.
				$query = $wpdb->prepare( "INSERT IGNORE INTO $this->table_followers ( user_id, email, auction_id, is_bidder ) SELECT DISTINCT $this->table_bidders.user_id, $this->table_users.user_email, $this->table_bidders.auction_id,%d FROM $this->table_bidders LEFT JOIN $this->table_users ON $this->table_bidders.user_id = $this->table_users.ID", 1 ); // phpcs:ignore
				$value = $wpdb->query( $query ); // phpcs:ignore

				// Add hash to new bidders.
				if ( $value ) {
					$query_migrate = $wpdb->query( "UPDATE $this->table_followers SET hash = SUBSTRING(MD5(RAND()) FROM 1 FOR 10) WHERE hash IS NULL OR hash = ''" ); // phpcs:ignore
				}

				update_option( 'yith_wcact_test_migration_bidders_to_followers', 'yes' );
			}
		}

		/* == Register terms for each auction product == */
		/**
		 * Run followers migration
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function init_action_status() {
			add_action( 'init', array( $this, 'run_auction_status' ), 30 );
		}

		/**
		 * Generate the recurring action scheduler for assign term to old auctions
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function run_auction_status() {
			$has_hook_scheduled = WC()->queue()->get_next( 'yith_wcact_assign_auction_status' );

			if ( $has_hook_scheduled ) {
				WC()->queue()->cancel_all( 'yith_wcact_assign_auction_status' );
			}

			WC()->queue()->schedule_recurring( strtotime( 'now' ), 30 * MINUTE_IN_SECONDS, 'yith_wcact_assign_auction_status' );
		}

		/**
		 * Process auction status for each auction product that doesn't have auction term.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function process_assign_auction_status() {
			$auction_products = get_posts(
				array(
					'post_type'   => 'product',
					'numberposts' => 50,
					'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'relation' => 'AND',
						array(
							'taxonomy' => 'yith_wcact_auction_status',
							'operator' => 'NOT EXISTS',
						),
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => 'auction',
							'operator' => 'IN',
						),
					),
				)
			);

			if ( ! empty( $auction_products ) ) {
				foreach ( $auction_products as $post ) {
					$auction = wc_get_product( $post );

					if ( $auction ) {
						$auction->update_auction_status( true );

						do_action( 'yith_wcact_auction_status_migration', $auction->get_id() );
					}
				}
			}
		}
	}
}
