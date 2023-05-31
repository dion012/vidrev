<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_DB Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCACT_DB' ) ) {
	/**
	 * YITH WooCommerce Booking Database
	 *
	 * @since 1.0.0
	 */
	class YITH_WCACT_DB {

		/**
		 * DB version
		 *
		 * @var string
		 */
		public static $version = '1.2.3';

		/**
		 * Auction table
		 *
		 * @var string
		 */
		public static $auction_table = 'yith_wcact_auction';

		/**
		 * Fee table
		 *
		 * @var string
		 */
		public static $fee_table = 'yith_wcact_fee';

		/**
		 * Watchlist table
		 *
		 * @var string
		 */
		public static $watchlist_table = 'yith_wcact_watchlist';

		/**
		 * Followers table
		 *
		 * @var string
		 */
		public static $follow_auctions = 'yith_wcact_followers';

		/**
		 * Constructor
		 *
		 * @return YITH_WCACT_DB
		 */
		private function __construct() {

		}

		/**
		 * Create DB Table
		 *
		 * @return void
		 */
		public static function install() {
			self::create_db_table();
		}

		/**
		 * Create Auction bids table
		 *
		 * @param bool $force it's necessary to force the db update.
		 */
		public static function create_db_table( $force = false ) {
			global $wpdb;

			$current_version = get_option( 'yith_wcact_db_version' );

			if ( $force || $current_version !== self::$version ) {
				$wpdb->hide_errors();

				if ( ! function_exists( 'dbDelta' ) ) {
					include_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}

				$table_name     = $wpdb->prefix . self::$auction_table;
				$fee_table_name = $wpdb->prefix . self::$fee_table;

				$watchlist_table_name = $wpdb->prefix . self::$watchlist_table;

				$followers_table_name = $wpdb->prefix . self::$follow_auctions;

				$charset_collate = $wpdb->get_charset_collate();

				// Create auction table.
				$sql = "CREATE TABLE $table_name (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) NOT NULL,
                    `auction_id` bigint(20) NOT NULL,
                    `bid` varchar(255) NOT NULL,
                    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY auction_id (auction_id),
                    KEY auction_bid ( auction_id, user_id )
                    ) $charset_collate;";

				dbDelta( $sql );

				// Create Fee table.
				$sql = "CREATE TABLE $fee_table_name (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) NOT NULL,
                    `auction_id` bigint(20) NOT NULL,
                    `order_id` bigint(20) NOT NULL,
                    `fee_amount` varchar(255) NOT NULL,
                    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                     KEY auction_fee ( auction_id, user_id )
                    ) $charset_collate;";

				dbDelta( $sql );

				// Create Watchlist table.
				$sql = "CREATE TABLE $watchlist_table_name (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) NOT NULL,
                    `auction_id` bigint(20) NOT NULL,
					`dateadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                    ) $charset_collate;";

				dbDelta( $sql );

				// Create followers table.
				$table_followers = "CREATE TABLE $followers_table_name (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20),
                    `email`  varchar(255) NOT NULL,
                    `auction_id` bigint(20) NOT NULL,
					`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`hash` varchar (255) NOT NULL,
					`is_bidder` tinyint(1),
                    PRIMARY KEY (id)
                    ) $charset_collate;";

				dbDelta( $table_followers );

				update_option( 'yith_wcact_db_version', self::$version );
			}
		}
	}
}
