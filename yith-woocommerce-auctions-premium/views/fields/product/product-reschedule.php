<?php
/**
 * Product reschedule view
 *
 * @package YITH\Auctions\Views\Fields\Product
 */

$number = $field['yith-wcact-values']['automatic_reschedule'];
$unit   = $field['yith-wcact-values']['automatic_reschedule_unit'];

$options = $field['yith-wcact-values']['options'];

?>

<div class="ywcact-general-reschedule-for-another" id="<?php echo esc_html( $field['id'] ); ?>">
	<input type="number" class="ywcact-input-text" id="_yith_wcact_auction_automatic_reschedule"  name="_yith_wcact_auction_automatic_reschedule" min="0" value="<?php echo esc_attr( $number ); ?>">
	<select id="_yith_wcact_automatic_reschedule_auction_unit" name="_yith_wcact_automatic_reschedule_auction_unit" class="wc-enhanced-select ywact-select">
		<?php
		foreach ( $options as $key => $item ) :
			?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $unit ); ?> ><?php echo wp_kses_post( $item ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
