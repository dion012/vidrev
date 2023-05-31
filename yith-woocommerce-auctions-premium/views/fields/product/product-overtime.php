<?php
/**
 * Product overtime view
 *
 * @package YITH\Auctions\Views\Fields\Product
 */

$overtime_before = $field['yith-wcact-values']['minutes_before_overtime'];
$overtime        = $field['yith-wcact-values']['overtime_minutes'];

?>

<div class="">
	<div class="ywcact-row">
		<span for="ywcact_general_overtime_before" class="">
			<?php echo esc_html__( 'If someone adds a bid  ', 'yith-auctions-for-woocommerce' ); ?>
		</span>
		<input type="number" class="ywcact-input-product-number" min="0" id="_yith_check_time_for_overtime_option" name="_yith_check_time_for_overtime_option" value="<?php echo esc_html( $overtime_before ); ?>">
		<span for="ywcact_general_overtime" class="">
			<?php echo esc_html__( 'minutes before the auction ends, extend the auction for another ', 'yith-auctions-for-woocommerce' ); ?>
		</span>
		<input type="number" class="ywcact-input-product-number" min="0" id="_yith_overtime_option"  name="_yith_overtime_option" value="<?php echo esc_html( $overtime ); ?>">
		<span class=""><?php echo esc_html__( 'minutes', 'yith-auctions-for-woocommerce' ); ?></span>
	</div>
</div>
