<?php
/**
 * My account custom dashboard content.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 * @var boolean $is_pending True if current vendor is in pending, false otherwise.
 * @var string $vendor_name The current vendor name.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<h2>
	<?php
	// translators: %s means vendor.
	echo esc_html( sprintf( __( 'My %s Dashboard', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_singular_label( 'ucfirst' ) ) );
	?>
</h2>

<p class="myaccount_vendor_dashboard">
	<?php
	if ( $is_pending ) {
		esc_html_e( 'You\'ll be able to access your dashboard as soon as the administrator approves your vendor account.', 'yith-woocommerce-product-vendors' );
		echo '<br/>';
	}

	// translators: %s stand for vendor label.
	echo wp_kses_post( sprintf( __( 'From your %s dashboard you can view your recent commissions, view the sales report and manage your store and payment settings.', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_singular_label( 'strtolower' ) ) );

	if ( ! $is_pending ) {
		echo '<br/>';
		// translators: %1$s is the vendor dashboard url, %2$s is the vendor name.
		echo wp_kses_post( sprintf( __( 'Click <a href="%1$s">here</a> to access <strong>%2$s dashboard</strong>.', 'yith-woocommerce-product-vendors' ), apply_filters( 'yith_wcmv_my_vendor_dashboard_uri', esc_url( admin_url() ) ), $vendor_name ) );
	}
	?>
</p>
