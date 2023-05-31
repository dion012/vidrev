<?php
/**
 * View to load the options to handle the reschedule of non paid auctions
 *
 * @package YITH\Auctions\Views\Fields
 */

$reschedule_not_pay_array = get_option( 'ywcact_settings_reschedule_auction_not_paid', array() );
$options                  = yith_wcact_get_select_time_values();

$options_max_select_reminder = array(
	'reschedule'    => esc_html_x( 'reschedule the auction', 'Admin option: Reschedule the auction', 'yith-auctions-for-woocommerce' ),
	'send_reminder' => esc_html_x( 'send a payment reminder', 'Admin option: Send a payment reminder', 'yith-auctions-for-woocommerce' ),
);

$options_after_select_reminder = array(
	'do_nothing'                      => esc_html_x( 'do nothing', 'Admin option: Do nothing', 'yith-auctions-for-woocommerce' ),
	'reschedule'                      => esc_html_x( 'reschedule the auction', 'Admin option: Reschedule the auction', 'yith-auctions-for-woocommerce' ),
	'send_winner_email_second_bidder' => esc_html_x( 'send the winner email to the 2nd highest bidder', 'Admin option: send the winner email to the 2nd highest bidder', 'yith-auctions-for-woocommerce' ),
);

extract( $reschedule_not_pay_array ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$pay_max_unit             = isset( $pay_max_unit ) ? $pay_max_unit : 'days';
$pay_max_select_reminder  = isset( $pay_max_select_reminder ) ? $pay_max_select_reminder : 'reschedule';
$after_unit               = isset( $after_unit ) ? $after_unit : 'days';
$after_select_reminder    = isset( $after_select_reminder ) ? $after_select_reminder : 'reschedule';
$after_second_winner_unit = isset( $after_second_winner_unit ) ? $after_second_winner_unit : 'hours';
?>
<div class="ywcact-general-reschedule-for-another" id="<?php echo esc_html( $field['id'] ); ?>">

	<div class="ywcact-general-reschedule-for-another__section1 ywcact-general-reschedule-for-another-section" >
		<span class="ywcact-span"><?php echo esc_html__( 'If winning bidder does not pay in', 'yith-auctions-for-woocommerce' ); ?></span>
		<input type="number" class="ywcact-input-text ywcact-input-number-inline"  name="ywcact_settings_reschedule_auction_not_paid[pay_max_number]" min="0" value="<?php echo isset( $pay_max_number ) ? esc_html( $pay_max_number ) : 5; ?>">
		<select
				id="ywcact_settings_reschedule_auction_not_paid_max_unit"
				name="ywcact_settings_reschedule_auction_not_paid[pay_max_unit]"
				class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $options as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $pay_max_unit ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="ywcact-span"><?php echo esc_html__( 'then', 'yith-auctions-for-woocommerce' ); ?></span>
		<select
				id="ywcact_settings_reschedule_auction_not_paid_max_select_reminder"
				name="ywcact_settings_reschedule_auction_not_paid[pay_max_select_reminder]"
				class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $options_max_select_reminder as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $pay_max_select_reminder ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="ywcact-general-reschedule-for-another__section2 ywcact-general-reschedule-for-another-section" >
		<span class="ywcact-span"><?php echo esc_html__( 'And after', 'yith-auctions-for-woocommerce' ); ?></span>
		<input type="number" class="ywcact-input-text ywcact-input-number-inline"  name="ywcact_settings_reschedule_auction_not_paid[after_number]" min="0" value="<?php echo isset( $after_number ) ? esc_html( $after_number ) : ''; ?>">
		<select
				id="ywcact_settings_reschedule_auction_not_paid_after_unit"
				name="ywcact_settings_reschedule_auction_not_paid[after_unit]"
				class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $options as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $after_unit ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="ywcact-span"><?php echo esc_html__( 'if the winning item is still not paid', 'yith-auctions-for-woocommerce' ); ?></span>
		<select
				id="ywcact_settings_reschedule_auction_not_paid_after_select_reminder"
				name="ywcact_settings_reschedule_auction_not_paid[after_select_reminder]"
				class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $options_after_select_reminder as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $after_select_reminder ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>

	</div>
	<div class="ywcact-general-reschedule-for-another__section3 ywcact-general-reschedule-for-another-section" >
		<span class="ywcact-span"><?php echo esc_html__( 'After', 'yith-auctions-for-woocommerce' ); ?></span>
		<input type="number" class="ywcact-input-text ywcact-input-number-inline"  name="ywcact_settings_reschedule_auction_not_paid[after_second_winner_number]" min="0" value="<?php echo isset( $after_second_winner_number ) ? esc_html( $after_second_winner_number ) : ''; ?>">
		<select
				id="ywcact_settings_reschedule_auction_not_paid_after_second_winner_unit"
				name="ywcact_settings_reschedule_auction_not_paid[after_second_winner_unit]"
				class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $options as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $after_second_winner_unit ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="ywcact-span"><?php echo esc_html__( 'if the auction product is still not paid, then reschedule the auction.', 'yith-auctions-for-woocommerce' ); ?></span>

	</div>
	<span class="description"><?php echo esc_html__( 'Set how to manage unpaid auctions.', 'yith-auctions-for-woocommerce' ); ?></span>
</div>
