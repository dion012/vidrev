<?php
/**
 * Auction rescheduling options tab.
 *
 * @package YITH\Auctions
 * @since   3.0.0
 * @author  YITH
 */

/**
 * APPLY_FILTERS: yith_wcact_general_options_auction_rescheduling
 *
 * Filter the options available in the General Auctions Rescheduling tab.
 *
 * @param array $general_auction_rescheduling General auctions rescheduling options
 *
 * @return array
 */
return apply_filters(
	'yith_wcact_general_options_auction_rescheduling',
	array(
		'general-auctions-rescheduling' => array(
			// Auction Rescheduling.
			'settings_automatic_reschedule_auctions_start' => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_automatic_reschedule_auctions_start',
			),
			'settings_automatic_reschedule_auctions_title' => array(
				'title' => esc_html_x( 'Auction rescheduling', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_settings_automatic_reschedule_auctions_title',
			),
			'settings_auction_reschedule_without_bid'      => array(
				'title'     => esc_html__( 'Reschedule ended auctions without bids', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to automatically reschedule ended auctions without a bid', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_reschedule_auctions_without_bids',
				'default'   => 'no',
			),
			'settings_auction_reschedule_reserve_price_reached' => array(
				'title'     => esc_html__( 'Reschedule ended auctions with the reserve price not reached', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to automatically reschedule ended auctions if the reserve price was not reached by any submitted bids.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_reschedule_auctions_reserve_price_reached',
				'default'   => 'no',
			),
			'settings_auction_reschedule_for_another'      => array(
				'id'              => 'yith_wcact_reschedule_for_another',
				'title'           => esc_html__( 'Auctions will be rescheduled for another', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'rescheduled-for-another',
				'desc'            => esc_html__( 'Set the length of time for which the auction will run again. The auction will reset itself to the original auction product settings and all previous bids will be removed.', 'yith-auctions-for-woocommerce' ),
				'action'          => 'yith_wcact_general_custom_fields',
			),
			'settings_auction_reschedule_not_paid'         => array(
				'title'     => esc_html__( 'Manage unpaid auctions', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enable to choose how to manage unpaid auctions (reschedule, contact the 2nd highest bidder, etc.)', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'If disabled, when an auction is not paid, nothing happen.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'id'        => 'yith_wcact_settings_reschedule_auctions_not_paid',
				'default'   => 'no',
			),
			'settings_auction_reschedule_how_to_not_paid'  => array(
				'id'              => 'yith_wcact_auction_reschedule_how_to_not_paid',
				'title'           => esc_html__( 'Unpaid auctions options', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'rescheduled-not-paid',
				'action'          => 'yith_wcact_general_custom_fields',
				'class'           => 'yith-wcact-deps-charge-automatically',
				'deps'            => array(
					'id'    => 'yith_wcact_settings_reschedule_auctions_not_paid',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_auction_reschedule_not_paid_change_for_another' => array(
				'id'              => 'yith_wcact_auction_reschedule_not_paid_change_for_another',
				'title'           => esc_html__( 'Unpaid auctions will be rescheduled for another ', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'rescheduled-not-paid-for-another',
				'desc'            => esc_html__( 'Set the length of time for which the auction will run again. The auction will reset itself to the original auction product settings and all previous bids will be removed.', 'yith-auctions-for-woocommerce' ),
				'action'          => 'yith_wcact_general_custom_fields',
				'deps'            => array(
					'id'    => 'yith_wcact_settings_reschedule_auctions_not_paid',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_auction_send_email_when_is_reschedule' => array(
				'title'     => esc_html__( 'Send email to admin when an auction is rescheduled', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to notify admin by email when an auction is automatically rescheduled', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_auction_is_reschedule',
				'default'   => 'no',
			),
			'settings_automatic_reschedule_auctions_end'   => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_automatic_reschedule_auctions_end',
			),
			// End Auction Rescheduling.
		),
	)
);
