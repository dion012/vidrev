<?php
/**
 * Vendor dashboard settings tab template
 *
 * @since   4.0.0
 * @author  Francesco Licandro
 * @package YITH WooCommerce Multi Vendor Premium
 * @var array   $fields    The form fields.
 * @var integer $vendor_id The current vendor id.
 * @var YITH_Vendor $vendor The current vendor.
 * @var string  $section   The current section shown.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<div class="yith-plugin-fw-panel-custom-tab-container">
	<form method="POST">

		<h3><?php esc_html_e( 'Manual Payments Information', 'yith-woocommerce-product-vendors' ); ?></h3>

		<?php do_action( 'yith_wcmv_vendor_dashboard_payments_before_fields', $vendor ); ?>

		<div class="vendor-fields-container">
			<?php
			foreach ( $fields as $field_id => $field ) :
				yith_wcmv_print_vendor_admin_fields( $field_id, $field );
			endforeach;
			?>
			<input type="hidden" id="vendor_id" name="vendor_id" value="<?php echo absint( $vendor_id ); ?>">
		</div>

		<?php do_action( 'yith_wcmv_vendor_dashboard_payments_after_fields', $vendor ); ?>

		<div class="submit">
			<input type="hidden" id="action" name="action" value="<?php echo esc_attr( YITH_Vendors_Admin_Vendor_Dashboard_Panel::FORM_ACTION ); ?>">
			<input type="hidden" id="section" name="section" value="payments">
			<?php wp_nonce_field( YITH_Vendors_Admin_Vendor_Dashboard_Panel::FORM_ACTION ); ?>
			<input class="button-primary" id="main-save-button" type="submit" value="<?php echo esc_html_x( 'Save Payments Info', '[Admin]Button Label', 'yith-woocommerce-product-vendors' ); ?>">
		</div>
	</form>
</div>
