<?php
/**
 * Customization options
 *
 * @package YITH\Auctions\PluginOptions
 **/

/**
 * APPLY_FILTERS: yith_wcact_customization_options
 *
 * Filter the options available in the Customization tab.
 *
 * @param array $customization_options Customization options
 *
 * @return array
 */
return array(
	'customization' => apply_filters(
		'yith_wcact_customization_options',
		array(
			'customization_options_start'       => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_appearance_options_start',
			),
			'customization_options_title'       => array(
				'title' => esc_html_x(
					'Customization',
					'Panel: Customization',
					'yith-auctions-for-woocommerce'
				),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_appearance_options_title',
			),
			'customization_show_auctions_badge' => array(
				'title'     => esc_html_x( 'Show auction badge', 'Admin option: Show auction badge', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show a badge to identify the auctions product', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_show_auction_badge',
				'default'   => 'yes',
			),
			'customization_upload_badge'        => array(
				'name'      => esc_html_x(
					'Upload auction badge',
					'Admin option: Upload or Select a badge image',
					'yith-auctions-for-woocommerce'
				),
				'type'      => 'yith-field',
				'yith-type' => 'upload',
				'id'        => 'yith_wcact_appearance_button',
				'default'   => YITH_WCACT_ASSETS_URL . 'images/badge.svg',
				'desc'      => esc_html_x(
					'Upload a graphic badge to identify the auctions products',
					'Admin option: Select an image to show in auctions products',
					'yith-auctions-for-woocommerce'
				),
				'deps'      => array(
					'id'    => 'yith_wcact_show_auction_badge',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'customization_options_end'         => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_appearance_options_end',
			),
			'countdown_options_start'           => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_countdown_options_start',
			),
			'countdown_options_title'           => array(
				'title' => esc_html_x(
					'Countdown section',
					'Panel: Countdown',
					'yith-auctions-for-woocommerce'
				),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_countdown_options_title',
			),
			'auction_page_show_end_date'        => array(
				'title'     => esc_html__( 'Show end date of auctions on product page', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show the end date of auctions on product page', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_show_end_date_auctions',
				'default'   => 'yes',
			),
			'countdown_time_zone_option'        => array(
				'name'      => esc_html__( 'Time zone', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'id'        => 'yith_wcact_general_time_zone',
				'desc'      => esc_html__( 'Enter an optional time zone code to show with the auction end date.', 'yith-auctions-for-woocommerce' ),
				'deps'      => array(
					'id'    => 'yith_wcact_show_end_date_auctions',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_date_format_option'      => array(
				'name'      => esc_html__( 'Date format', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'date-format',
				'id'        => 'yith_wcact_general_date_format',
				'js'        => false,
				'desc'      => esc_html__( 'Set date format for countdown', 'yith-auctions-for-woocommerce' ),
				'default'   => 'j/n/Y',
				'deps'      => array(
					'id'    => 'yith_wcact_show_end_date_auctions',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_time_format_option'      => array(
				'name'            => esc_html__( 'Time format', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'time-format',
				'id'              => 'yith_wcact_general_time_format',
				'action'          => 'yith_wcact_general_custom_fields',
				'js'              => true,
				'desc'            => esc_html__( 'Set time format for countdown', 'yith-auctions-for-woocommerce' ),
				'default'         => 'h:i:s',
				'format'          => array(
					'h:i:s' => 'h:i:s',
					'g:i a' => 'g:i a',
					'g:i A' => 'g:i A',
					'H:i'   => 'H:i',
				),
				'deps'            => array(
					'id'    => 'yith_wcact_show_end_date_auctions',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_show_countdown'          => array(
				'title'     => esc_html_x( 'Show countdown', 'Admin option: Show countdown', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show the countdown', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_show_general_countdown',
				'default'   => 'yes',
			),
			'countdown_style_format'            => array(
				'id'        => 'yith_wcact_countdown_style',
				'name'      => esc_html__( 'Countdown style', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'select-images',
				'options'   => array(
					'default'      => array(
						'label' => esc_html__( 'Default', 'yith-auctions-for-woocommerce' ),
						'image' => YITH_WCACT_ASSETS_URL . 'images/icon/default.svg',
					),
					'compact'      => array(
						'label' => esc_html__( 'Compact', 'yith-auctions-for-woocommerce' ),
						'image' => YITH_WCACT_ASSETS_URL . 'images/icon/compact.svg',
					),
					'big-blocks'   => array(
						'label' => esc_html__( 'Big blocks', 'yith-auctions-for-woocommerce' ),
						'image' => YITH_WCACT_ASSETS_URL . 'images/icon/big_blocks.svg',
					),
					'small-blocks' => array(
						'label' => esc_html__( 'Small blocks', 'yith-auctions-for-woocommerce' ),
						'image' => YITH_WCACT_ASSETS_URL . 'images/icon/small_blocks.svg',
					),
				),
				'std'       => 'default',
				'desc'      => esc_html__( 'Choose a countdown style', 'yith-auctions-for-woocommerce' ),
				'default'   => 'default',
				'deps'      => array(
					'id'    => 'yith_wcact_show_general_countdown',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_color_format'            => array(
				'name'         => esc_html__( 'Countdown color', 'yith-auctions-for-woocommerce' ),
				'id'           => 'yith_wcact_countdown_color',
				'type'         => 'yith-field',
				'yith-type'    => 'multi-colorpicker',
				'colorpickers' => array(
					array(
						'desc' => esc_html__( 'Set the colors for the countdown', 'yith-auctions-for-woocommerce' ),
						array(
							'name'    => esc_html__( 'Text', 'yith-auctions-for-woocommerce' ),
							'id'      => 'text',
							'default' => '#fhf3933',
						),
						array(
							'name'    => esc_html__( 'Section background', 'yith-auctions-for-woocommerce' ),
							'id'      => 'section',
							'default' => '#f5f5f5',
						),
						array(
							'name'    => esc_html__( 'Blocks background', 'yith-auctions-for-woocommerce' ),
							'id'      => 'blocks',
							'default' => '#ffffff',
						),
					),
				),
				'deps'         => array(
					'id'    => 'yith_wcact_show_general_countdown',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_color_ending'            => array(
				'name'            => esc_html__( 'Countdown color for auctions that are ending soon', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'countdown-color-ending-auctions',
				'id'              => 'yith_wcact_countdown_color_ending_auctions',
				'action'          => 'yith_wcact_general_custom_fields',
				'class'           => 'yith-plugin-fw-colorpicker color-picker ywcact-ending-color-picker',
				'default'         => '#fhf3933',
				'desc'            => esc_html__( 'Change countdown text color if the auction is near the end ( example: red text if the auction ends in less than 24 hours )', 'yith-auctions-for-woocommerce' ),
				'deps'            => array(
					'id'    => 'yith_wcact_show_general_countdown',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'countdown_options_end'             => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_countdown_options_end',
			),
		)
	),
);
