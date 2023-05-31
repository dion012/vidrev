<?php
/**
 * General settings (Auction options, Auctions rescheduling, Auction winner options).
 *
 * @package YITH\Auctions\PluginOptions
 * @since   3.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$general_settings = array(
	'general' => array(
		'general-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'general-auctions'              => array(
					'title' => esc_html__( 'Auction Options', 'yith-auctions-for-woocommerce' ),
				),
				'general-auctions-winner'       => array(
					'title' => esc_html__( 'Auctions Payments', 'yith-auctions-for-woocommerce' ),
				),
				'general-auctions-rescheduling' => array(
					'title' => esc_html__( 'Auctions Rescheduling', 'yith-auctions-for-woocommerce' ),
				),
			),
		),
	),
);

/**
 * APPLY_FILTERS: yith_wcact_general_settings_options
 *
 * Filter the options available in the General settings tab.
 *
 * @param array $general_settings General settings options
 *
 * @return array
 */
return apply_filters( 'yith_wcact_general_settings_options', $general_settings );
