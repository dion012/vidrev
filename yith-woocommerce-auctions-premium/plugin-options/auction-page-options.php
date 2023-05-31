<?php
/**
 * Auction list tab.
 *
 * @package YITH\Auctions\PluginOptions
 * @since   2.0.0
 * @author  YITH
 */

return array(
	'auction-page' => array(
		'auction_page_options_start'                       => array(
			'type' => 'sectionstart',
			'id'   => 'yith_wcact_auction_page_options_start',
		),
		'auction_page_options_title'                       => array(
			'title' => esc_html_x( 'Auction page', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith_wcact_settings_options_title',
		),
		'auction_page_show_auction_badge'                  => array(
			'title'     => esc_html_x( 'Show auction badge on product image', 'Admin option: Hide auction badge on product image', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to show the auction badge in the auction product page', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_show_badge_product_page',
			'default'   => 'yes',
		),
		'auction_page_show_item_condition'                 => array(
			'title'     => esc_html__( 'Show items condition', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to show items condition', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_show_item_condition',
			'default'   => 'yes',
		),
		'auction_page_show_product_stock'                  => array(
			'title'     => esc_html__( 'Show product stock', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to show the product stock', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_show_product_stock',
			'default'   => 'yes',
		),
		'auction_page_show_reserve_price_reached'          => array(
			'title'     => esc_html__( 'Show if the reserve price has been reached', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to show a notice if the reserve price has been reached', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_show_reserve_price_reached',
			'default'   => 'yes',
		),
		'auction_page_show_in_overtime'                    => array(
			'title'     => esc_html__( 'Show if the auction is in overtime', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to show a notice if the auction is in overtime.', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_show_in_overtime',
			'default'   => 'yes',
		),
		'auction_page_quantity_buttons'                    => array(
			'title'     => esc_html__( 'Quantity buttons in bid amount fields', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'hide'   => esc_html__( 'Hide quantity buttons', 'yith-auctions-for-woocommerce' ),
				'theme'  => esc_html__( 'Use theme style buttons', 'yith-auctions-for-woocommerce' ),
				'custom' => esc_html__( 'Use plugin style buttons', 'yith-auctions-for-woocommerce' ),
			),
			'default'   => 'theme',
			'id'        => 'yith_wcact_settings_tab_auction_show_button',
			'desc'      => esc_html__( 'Choose to show or hide the buttons to increase or decrease bid near the input field and which style to use', 'yith-auctions-for-woocommerce' ),
		),
		'auction_page_show_next_available_amount'          => array(
			'title'     => esc_html__( 'Show the next available amount in the bid input field', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'yith_wcact_show_next_available_amount',
			'desc'      => implode(
				'<br />',
				array(
					esc_html__( 'If enabled, the next suggested bid ( current bid + minimal amount increment ) will be shown in the bid input field', 'yith-auctions-for-woocommerce' ),
					esc_html__( 'If disabled, the input field will be empty', 'yith-auctions-for-woocommerce' ),
				)
			),
			'default'   => 'no',
		),
		'auction_page_bid_tab_show'                        => array(
			'title'     => esc_html__( 'In bid tab show', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'yes' => esc_html__( 'Full username of bidders', 'yith-auctions-for-woocommerce' ),
				'no'  => esc_html__( 'Only first and last letter ( A****E )', 'yith-auctions-for-woocommerce' ),
			),
			'default'   => 'no',
			'id'        => 'yith_wcact_settings_tab_auction_show_name',
			'desc'      => implode(
				'<br />',
				array(
					esc_html__( 'Choose whether to show the full username of bidders or only to set the first and last letters', 'yith-auctions-for-woocommerce' ),
					esc_html__( 'Note: in sealed auctions the bids list will be hidden and this option will not be applied.', 'yith-auctions-for-woocommerce' ),
				)
			),
		),
		'auction_page_options_end'                         => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcact_auction_page_options_end',
		),
		/* == Ended auction page == */
		'ended_auction_page_options_start'                 => array(
			'type' => 'sectionstart',
			'id'   => 'yith_wcact_ended_auction_page_options_start',
		),
		'ended_auction_page_options_title'                 => array(
			'title' => esc_html_x( 'Ended auction page', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith_wcact_ended_settings_options_title',
		),
		'ended_auction_page_how_auction_ended'             => array(
			'title'     => esc_html__( 'Show how the auction has ended in the auction page', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'If enabled, the reason of why the auction has ended will be shown in the auction page', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_how_auction_ended',
			'default'   => 'yes',
		),
		'ended_auction_suggest_other_auction_ended'        => array(
			'title'     => esc_html__( 'Suggest other auctions in the ended auction page', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html__( 'Enable to suggest other auctions to customers that open an auction product page', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_ended_suggest_other_auction',
			'default'   => 'yes',
		),
		'ended_auction_suggest_other_active_acution'       => array(
			'title'     => esc_html__( 'Suggest active auctions', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'same_category' => esc_html__( 'of same category of the ended auctions', 'yith-auctions-for-woocommerce' ),
				'all'           => esc_html__( 'of all categories', 'yith-auctions-for-woocommerce' ),
			),
			'default'   => 'all',
			'id'        => 'yith_wcact_ended_suggest_active_auctions',
			'desc'      => esc_html__( 'Choose to suggest active auctions of all categories or only auctions of same category.', 'yith-auctions-for-woocommerce' ),
			'deps'      => array(
				'id'    => 'yith_wcact_ended_suggest_other_auction',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),
		'ended_auction_suggest_other_auction_number_ended' => array(
			'title'     => esc_html__( 'Auctions to suggest', 'yith-auctions-for-woocommerce' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'desc'      => esc_html__( 'Set how many auctions to suggest', 'yith-auctions-for-woocommerce' ),
			'id'        => 'yith_wcact_ended_suggest_other_auction_number',
			'min'       => 0,
			'default'   => '',
			'deps'      => array(
				'id'    => 'yith_wcact_ended_suggest_other_auction',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),
		'ended_auction_page_options_end'                   => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcact_ended_auction_page_options_end',
		),
	),
);
