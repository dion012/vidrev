<?php
/**
 * Time format view
 *
 * @package YITH\Auctions\Views\Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

extract( $field ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$class = isset( $class ) ? $class : '';
$js    = isset( $js ) ? $js : false;
$class = 'yith-plugin-fw-radio ' . $class;

$options = $format;
$custom  = true;
?>
<div class="<?php echo esc_attr( $class ); ?> yith-plugin-fw-time-format" id="<?php echo esc_attr( $id ); ?>"
	<?php echo esc_attr( $custom_attributes ); ?>
	<?php
	if ( isset( $data ) ) {
		yith_plugin_fw_html_data_to_string( $data, true );
	}
	?>
	value="<?php echo esc_attr( $value ); ?>">
	<?php
	foreach ( $options as $key => $label ) :
		$checked  = '';
		$radio_id = $id . '-' . str_replace( ' ', '', $key );

		if ( $value === $key ) {
			$checked = " checked='checked'";
			$custom  = false;
		}

		?>
		<div class="yith-plugin-fw-radio__row">
			<input type="radio" id="<?php echo esc_attr( $radio_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $checked ); ?> />
			<label for="<?php echo esc_attr( $radio_id ); ?>">
				<?php echo esc_html( date_i18n( $label ) ); ?>
				<code><?php echo esc_html( $key ); ?></code>
			</label>
		</div>
	<?php endforeach; ?>
	<?php $radio_id = sanitize_key( $id . '-custom' ); ?>
	<div class="yith-plugin-fw-radio__row">
		<input type="radio" id="<?php echo esc_attr( $radio_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="yith_wcact_custom_value" <?php checked( $custom ); ?> />
		<label for="<?php echo esc_attr( $radio_id ); ?>"> <?php esc_html_e( 'Custom:', 'yith-plugin-fw' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $name . '_text' ); ?>" id="<?php echo esc_attr( $radio_id ); ?>_text" value="<?php echo esc_attr( $value ); ?>" class="small-text"/>
	</div>
</div>
