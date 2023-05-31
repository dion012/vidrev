<?php
/**
 * Send email bidders view
 *
 * @package YITH\Auctions\Views\Fields
 */

$number   = get_option( 'yith_wcact_settings_cron_auction_number_days', 1 );
$overtime = get_option( 'yith_wcact_settings_cron_auction_type_numbers', 'days' );

$options = yith_wcact_get_select_time_values();

?>

<div class="ywcact-general-cron-send-email-bidders" id="<?php echo esc_html( $field['id'] ); ?>">
	<input type="number" class="ywcact-input-text ywcact-input-number-inline"  name="ywcact_settings_cron_number" min="0" value="<?php echo esc_html( $number ); ?>">
	<select id="yith_wcact_settings_cron_auction_type_numbers" name="yith_wcact_settings_cron_auction_type_numbers" class="wc-enhanced-select ywact-select ywcact-select-inline">
		<?php
		foreach ( $options as $key => $item ) :
			?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $overtime ); ?> ><?php echo wp_kses_post( $item ); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="ywcact-span"><?php echo esc_html__( 'before auction ends', 'yith-auctions-for-woocommerce' ); ?></span>
</div>
