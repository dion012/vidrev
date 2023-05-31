<?php
/**
 * Plugin Name: YITH WooCommerce Multi Vendor Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/
 * Description: <code><strong>YITH WooCommerce Multi Vendor</strong></code> turns your website into a real marketplace, where it's your partners who will add new products independently while you earn a percentage commission on every sale. Take advantage of this great opportunity to steadily increase your earnings in a simple way. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Author: YITH
 * Text Domain: yith-woocommerce-product-vendors
 * Version: 4.1.0
 * Author URI: https://yithemes.com/
 * WC requires at least: 6.6
 * WC tested up to: 6.8
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'WC' ) && ! function_exists( 'install_premium_woocommerce_admin_notice' ) ) {
	/**
	 * Print an admin notice if woocommerce is deactivated
	 *
	 * @since 1.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 * @return void
	 * @use admin_notices hooks
	 */
	function install_premium_woocommerce_admin_notice() { ?>
		<div class="error">
			<p><?php echo 'YITH WooCommerce Multi Vendor ' . esc_html__( 'is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-product-vendors' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'install_premium_woocommerce_admin_notice' );
	return;
}

! defined( 'YITH_WPV_PREMIUM' ) && define( 'YITH_WPV_PREMIUM', '1' );
! defined( 'YITH_WPV_INIT' ) && define( 'YITH_WPV_INIT', plugin_basename( __FILE__ ) );

// Check if a free version currently active and try disabling before activating this one.
if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_WPV_FREE_INIT', YITH_WPV_INIT );

// Stop activation if the premium version of the same plugin is still active.
if ( defined( 'YITH_WPV_VERSION' ) ) {
	return;
}

! defined( 'YITH_WPV_VERSION' ) && define( 'YITH_WPV_VERSION', '4.1.0' );
! defined( 'YITH_WPV_DB_VERSION' ) && define( 'YITH_WPV_DB_VERSION', '1.1.14' );
! defined( 'YITH_WPV_SLUG' ) && define( 'YITH_WPV_SLUG', 'yith-woocommerce-product-vendors' );
! defined( 'YITH_WPV_SECRET_KEY' ) && define( 'YITH_WPV_SECRET_KEY', '6NBH2Snt7DFU4J02vtgl' );
! defined( 'YITH_WPV_FILE' ) && define( 'YITH_WPV_FILE', __FILE__ );
! defined( 'YITH_WPV_PATH' ) && define( 'YITH_WPV_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WPV_URL' ) && define( 'YITH_WPV_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_WPV_ASSETS_URL' ) && define( 'YITH_WPV_ASSETS_URL', YITH_WPV_URL . 'assets/' );
! defined( 'YITH_WPV_TEMPLATE_PATH' ) && define( 'YITH_WPV_TEMPLATE_PATH', YITH_WPV_PATH . 'templates/' );
! defined( 'YITH_WPV_MODULE_PATH' ) && define( 'YITH_WPV_MODULE_PATH', YITH_WPV_PATH . 'includes/modules/' );
! defined( 'YITH_WPV_REST_NAMESPACE' ) && define( 'YITH_WPV_REST_NAMESPACE', 'yith-wcmv' );

// Load plugin-fw.
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WPV_PATH . 'plugin-fw/init.php' ) ) {
	require_once YITH_WPV_PATH . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_WPV_PATH );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}

// Init default plugin settings.
if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}

// Require plugin autoload.
if ( ! class_exists( 'YITH_Vendors_Autoloader' ) ) {
	require_once YITH_WPV_PATH . 'includes/class-yith-vendors-autoloader.php';
}

if ( ! function_exists( 'YITH_Vendors' ) ) {
	/**
	 * Unique access to instance of YITH_Vendors class
	 *
	 * @since 1.0.0
	 * @return YITH_Vendors|YITH_Vendors_Premium
	 */
	function YITH_Vendors() { // phpcs:ignore
		if ( defined( 'YITH_WPV_PREMIUM' ) && file_exists( YITH_WPV_PATH . 'includes/class-yith-vendors-premium.php' ) ) {
			return YITH_Vendors_Premium::instance();
		}

		return YITH_Vendors::instance();
	}
}

if ( ! function_exists( 'yith_wcmv_load' ) ) {
	/**
	 * Instance main plugin class
	 *
	 * @since 4.0.0
	 * @author Francesco Licandro
	 */
	function yith_wcmv_load() {
		load_plugin_textdomain( 'yith-woocommerce-product-vendors', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		YITH_Vendors();
	}
}
add_action( 'plugins_loaded', 'yith_wcmv_load' );

// Activation/Deactivation hooks.
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );
register_activation_hook( YITH_WPV_FILE, 'YITH_Vendors_Install::set_activation_flag' );
register_deactivation_hook( YITH_WPV_FILE, 'YITH_Vendors_Install::deactivate' );
