<?php
/**
 * View to select the color for the countdown when the auction is ending
 *
 * @package YITH\Auctions\Views\Fields
 */

$number   = get_option( 'yith_wcact_customization_countdown_color_numbers', 24 );
$overtime = get_option( 'yith_wcact_customization_countdown_color_unit', 'hours' );

$color = get_option( 'yith_wcact_customization_countdown_color_style', '#fhf3933' );

$options = yith_wcact_get_select_time_values();

extract( $field ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$post_field = array(
	'id'      => 'yith_wcact_customization_countdown_color_style',
	'name'    => 'yith_wcact_customization_countdown_color_style',
	'type'    => 'colorpicker',
	'default' => $default,
	'class'   => $class,
	'value'   => $color,
);

?>

<div class="ywcact-customization-countdown-color-ending-auctions" id="<?php echo esc_html( $field['id'] ); ?>">
	<span class="ywcact-span"><?php echo esc_html__( 'When there is less than ', 'yith-auctions-for-woocommerce' ); ?></span>
	<input type="number" class="ywcact-input-text ywcact-input-number-inline"  name="yith_wcact_customization_countdown_color_numbers" min="0" value="<?php echo esc_html( $number ); ?>">
	<select id="yith_wcact_customization_countdown_color_unit" name="yith_wcact_customization_countdown_color_unit" class="wc-enhanced-select ywact-select ywcact-select-inline">
		<?php
		foreach ( $options as $key => $item ) :
			?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $overtime ); ?> ><?php echo wp_kses_post( $item ); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="ywcact-span"><?php echo esc_html__( 'before the auction ends', 'yith-auctions-for-woocommerce' ); ?></span><br><br>
	<span class="ywcact-span"><?php echo esc_html__( 'change text color to', 'yith-auctions-for-woocommerce' ); ?></span>

	<?php yith_plugin_fw_get_field( $post_field, true ); ?>
</div>
