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

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// translators: %s is the blogname.
	echo esc_html( sprintf( __( 'Your vendor account on %s has been approved.', 'yith-woocommerce-product-vendors' ), $blogname ) );
	?>
</p>

<p>
	<?php
	// translators: %s is the vendor dashboard url.
	echo wp_kses_post( sprintf( __( 'From your vendor dashboard you can view your recent commissions, the sales report and manage your store and payment settings. Click <a href="%s">here</a> to access <strong>store dashboard</strong>.', 'yith-woocommerce-product-vendors' ), $admin_url ) );
	?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
