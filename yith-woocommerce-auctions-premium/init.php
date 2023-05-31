<?php
/**
 * Plugin Name: YITH Auctions for WooCommerce Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-auctions/
 * Description: With<code><strong>YITH Auctions for WooCommerce Premium</strong></code>, your customers can purchase products at the best price ever taking full advantage of the online auction system as the most popular portals, such as eBay, can do. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>.
 * Author: YITH
 * Text Domain: yith-auctions-for-woocommerce
 * Version: 3.8.0
 * Author URI: https://yithemes.com/
 * WC requires at least: 6.5
 * WC tested up to: 6.7
 *
 * @package YITH\Auctions
 * @author YITH
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_wcact_install_woocommerce_admin_notice' ) ) {
	/**
	 * Print an admin notice if WooCommerce is deactivated
	 *
	 * @author Carlos Rodriguez <carlos.rodriguez@yithemes.com>
	 * @since  1.0
	 * @return void
	 * @use    admin_notices hooks
	 */
	function yith_wcact_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php echo esc_html_x( 'YITH WooCommerce Auctions is enabled but not effective. It requires WooCommerce in order to work.', 'Alert Message: WooCommerce requires', 'yith-auctions-for-woocommerce' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_wcact_install' ) ) {
	/**
	 * Check if WooCommerce is activated
	 *
	 * @author Carlos Rodriguez <carlos.rodriguez@yithemes.com>
	 * @since  1.0
	 * @return void
	 * @use    admin_notices hooks
	 */
	function yith_wcact_install() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcact_install_woocommerce_admin_notice' );
		} else {
			if ( ! class_exists( 'YITH_WCACT_DB' ) ) {
				include_once YITH_WCACT_PATH . 'includes/class.yith-wcact-auction-db.php';
			}

			YITH_WCACT_DB::install();

			/**
			 * DO_ACTION: yith_wcact_init
			 *
			 * Allows the plugin initialization.
			 */
			do_action( 'yith_wcact_init' );
		}
	}

	add_action( 'plugins_loaded', 'yith_wcact_install', 11 );
}


if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	include_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_WCACT_FREE_INIT', plugin_basename( __FILE__ ) );


/* === DEFINE === */
! defined( 'YITH_WCACT_VERSION' ) && define( 'YITH_WCACT_VERSION', '3.8.0' );
! defined( 'YITH_WCACT_INIT' ) && define( 'YITH_WCACT_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCACT_SLUG' ) && define( 'YITH_WCACT_SLUG', 'yith-woocommerce-auctions' );
! defined( 'YITH_WCACT_SECRETKEY' ) && define( 'YITH_WCACT_SECRETKEY', 'zd9egFgFdF1D8Azh2ifK' );
! defined( 'YITH_WCACT_FILE' ) && define( 'YITH_WCACT_FILE', __FILE__ );
! defined( 'YITH_WCACT_PATH' ) && define( 'YITH_WCACT_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCACT_URL' ) && define( 'YITH_WCACT_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_WCACT_ASSETS_URL' ) && define( 'YITH_WCACT_ASSETS_URL', YITH_WCACT_URL . 'assets/' );
! defined( 'YITH_WCACT_TEMPLATE_PATH' ) && define( 'YITH_WCACT_TEMPLATE_PATH', YITH_WCACT_PATH . 'templates/' );
! defined( 'YITH_WCACT_WC_TEMPLATE_PATH' ) && define( 'YITH_WCACT_WC_TEMPLATE_PATH', YITH_WCACT_PATH . 'templates/woocommerce/' );
! defined( 'YITH_WCACT_OPTIONS_PATH' ) && define( 'YITH_WCACT_OPTIONS_PATH', YITH_WCACT_PATH . 'plugin-options' );
! defined( 'YITH_WCACT_PREMIUM' ) && define( 'YITH_WCACT_PREMIUM', true );

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCACT_PATH . 'plugin-fw/init.php' ) ) {
	include_once YITH_WCACT_PATH . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_WCACT_PATH );


// Cronjob resend winner email.
register_activation_hook( YITH_WCACT_FILE, 'start_auction_send_winner_email_scheduling' );
register_deactivation_hook( YITH_WCACT_FILE, 'end_auction_send_winner_email_scheduling' );

if ( ! function_exists( 'start_auction_send_winner_email_scheduling' ) ) {
	/**
	 * Start scheduling to send the email to the auction winner
	 */
	function start_auction_send_winner_email_scheduling() {
		wp_schedule_event( time(), 'daily', 'yith_wcact_cron_winner_email_notification' );
	}
}
if ( ! function_exists( 'end_auction_send_winner_email_scheduling' ) ) {
	/**
	 * End scheduling to send the email to the auction winner
	 */
	function end_auction_send_winner_email_scheduling() {
		wp_clear_scheduled_hook( 'yith_wcact_cron_winner_email_notification' );
	}
}

if ( ! wp_next_scheduled( 'yith_wcact_cron_winner_email_notification' ) ) { // delete in next version 1.2.3.
	wp_schedule_event( time(), 'daily', 'yith_wcact_cron_winner_email_notification' );
}

// Onboarding registration hook.
if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

/**
 * Let's start the game
 */
function yith_wcact_init_premium() {
	load_plugin_textdomain( 'yith-auctions-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	if ( ! function_exists( 'YITH_Auctions' ) ) {
		/**
		 * Unique access to instance of YITH_Auction class
		 *
		 * @return YITH_Auctions
		 * @since  1.0.0
		 */
		function YITH_Auctions() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
			include_once YITH_WCACT_PATH . 'includes/class.yith-wcact-auction.php';

			if ( defined( 'YITH_WCACT_PREMIUM' ) && file_exists( YITH_WCACT_PATH . 'includes/class.yith-wcact-auction-premium.php' ) ) {
				include_once YITH_WCACT_PATH . 'includes/class.yith-wcact-auction-premium.php';

				return YITH_Auctions_Premium::instance();
			}

			return YITH_Auctions::instance();
		}
	}

	// Let's start the game!
	YITH_Auctions();
}

add_action( 'yith_wcact_init', 'yith_wcact_init_premium' );
