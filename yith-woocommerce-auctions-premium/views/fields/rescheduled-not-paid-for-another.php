<?php
/**
 * View to load the options to handle the reschedule of non paid auctions
 *
 * @package YITH\Auctions\Views\Fields
 */

$number = get_option( 'ywcact_settings_reschedule_not_paid_number', 1 );
$unit   = get_option( 'ywcact_settings_reschedule_not_paid_number_unit', 'days' );

$options = yith_wcact_get_select_time_values();

?>

<div class="ywcact-general-rescheduled-not-paid-for-another" id="<?php echo esc_html( $field['id'] ); ?>">
	<input type="number" class="ywcact-input-text"  name="ywcact_settings_reschedule_not_paid_number" min="0" value="<?php echo esc_html( $number ); ?>">
	<select
			id="ywcact_settings_reschedule_not_paid_number_unit"
			name="ywcact_settings_reschedule_not_paid_number_unit"
			class="wc-enhanced-select ywact-select">
		<?php
		foreach ( $options as $key => $item ) :
			?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $unit ); ?> ><?php echo wp_kses_post( $item ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
