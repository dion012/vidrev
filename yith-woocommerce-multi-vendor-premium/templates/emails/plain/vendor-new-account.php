<?php
/**
 * Vendor new account
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 *
 * @var string   $email_heading The email heading.
 * @var string   $blogname      The blogname.
 * @var string   $admin_url     Admin url.
 * @var WC_Email $email         The email object.
 * @var bool     $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool     $plain_text    True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

// translators: %s is the blogname.
echo esc_html( sprintf( __( 'Your vendor account on %s has been approved.', 'yith-woocommerce-product-vendors' ), $blogname ) ) . "\n\n";

// translators: %s is the vendor dashboard url.
echo esc_html( sprintf( __( 'From your vendor dashboard you can view your recent commissions, the sales report and manage your store and payment settings. Click <a href="%s">here</a> to access <strong>store dashboard</strong>.', 'yith-woocommerce-product-vendors' ), $admin_url ) ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
