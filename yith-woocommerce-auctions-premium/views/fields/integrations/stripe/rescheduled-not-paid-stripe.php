<?php
/**
 * View to load the options to handle the reschedule of non paid auctions using Stripe
 *
 * @package YITH\Auctions\Views\Integrations\Stripe
 */

$reschedule_not_pay_array = get_option( 'ywcact_settings_reschedule_auction_not_paid_stripe', array() );

$first_step_options = array(
	'change_second_bidder' => esc_html_x( 'Try to charge the 2nd highest bidder', 'Admin option: Send a payment reminder', 'yith-auctions-for-woocommerce' ),
	'reschedule'           => esc_html_x( 'Reschedule the auction', 'Admin option: Reschedule the auction', 'yith-auctions-for-woocommerce' ),
);

$second_step_options = array(
	'do_nothing' => esc_html_x( 'Do nothing', 'Admin option: Do nothing', 'yith-auctions-for-woocommerce' ),
	'reschedule' => esc_html_x( 'Reschedule the auction', 'Admin option: Reschedule the auction', 'yith-auctions-for-woocommerce' ),
);

extract( $reschedule_not_pay_array ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$first_step  = isset( $first_step ) ? $first_step : 'change_second_bidder';
$second_step = isset( $second_step ) ? $second_step : 'do_nothing';

?>
<div class="ywcact-general-reschedule-for-another-stripe" id="<?php echo esc_attr( $field['id'] ); ?>">
	<div class="ywcact-general-reschedule-for-another__sectionstripe1 ywcact-general-reschedule-for-another-section-stripe" >
		<span class="ywcact-span"><?php echo esc_html__( 'If the Stripe payment fails after the 3rd attempt, ', 'yith-auctions-for-woocommerce' ); ?></span>
		<select id="ywcact_settings_reschedule_auction_not_paid_stripe_fist_step" name="ywcact_settings_reschedule_auction_not_paid_stripe[first_step]" class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $first_step_options as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $first_step ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="ywcact-general-reschedule-for-another__sectionstripe2 ywcact-general-reschedule-for-another-section-stripe" >
		<span class="ywcact-span"><?php echo esc_html__( 'And if also the 2nd one does not pay,', 'yith-auctions-for-woocommerce' ); ?></span>
		<select id="ywcact_settings_reschedule_auction_not_paid_stripe_second_step" name="ywcact_settings_reschedule_auction_not_paid_stripe[second_step]" class="wc-enhanced-select ywact-select ywcact-select-inline">
			<?php
			foreach ( $second_step_options as $key => $item ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $second_step ); ?> ><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<span class="description"><?php echo esc_html__( 'Set how to manage unpaid auctions.', 'yith-auctions-for-woocommerce' ); ?></span>
</div>
