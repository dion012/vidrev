<?php
/**
 * General overtime view
 *
 * @package YITH\Auctions\Views\Fields
 */

$overtime_before = get_option( 'yith_wcact_settings_overtime_option', 0 );
$overtime        = get_option( 'yith_wcact_settings_overtime', 0 );
?>

<div class="ywcact-general-overtime" id="<?php echo esc_attr( $field['id'] ); ?>">
	<div class="ywcact-general-overtime ywcact-row">
		<label for="ywcact_general_overtime_before" class="ywcact-span">
			<?php echo esc_html__( 'If someone adds a bid  ', 'yith-auctions-for-woocommerce' ); ?>
		</label>
		<input type="number" class="ywcact-input-text ywcact-input-number-inline" min="0" data-input-type="start"  name="ywcact_general_overtime_before" value="<?php echo esc_attr( $overtime_before ); ?>">
		<label for="ywcact_general_overtime" class="ywcact-span">
			<?php echo esc_html__( 'minutes before the auction ends, extend the auction for another ', 'yith-auctions-for-woocommerce' ); ?>
		</label>
		<input type="number" class="ywcact-input-text ywcact-input-number-inline" min="0" data-input-type="value"  name="ywcact_general_overtime" value="<?php echo esc_attr( $overtime ); ?>">
		<span class="ywcact-span"><?php echo esc_html__( 'minutes', 'yith-auctions-for-woocommerce' ); ?></span>
	</div>
</div>
