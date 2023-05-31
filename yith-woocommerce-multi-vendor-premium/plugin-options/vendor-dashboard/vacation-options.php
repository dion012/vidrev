<?php
/**
 * YITH Vendors Vendors Tab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'vacation' => array(
		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Vacation Settings', '[Admin]Vacation module tab title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'vacation_settings_title',
		),

		array(
			'title'     => _x( 'Enable vacation mode', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'vacation_enabled',
		),

		array(
			'title'     => _x( 'Schedule vacation mode', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
			'id'        => 'vacation_schedule_enabled',
			'deps'      => array(
				'id'    => 'vacation_enabled',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'Schedule vacation', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text-array',
			'fields'    => array(
				'from' => _x( 'From', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'to'   => _x( 'To', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'inline'    => 'yes',
			'id'        => 'vacation_schedule',
			'deps'      => array(
				'id'    => 'vacation_schedule_enabled',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'During the vacation period:', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Choose how to manage your shop during vacation mode.', '[Admin]Vacation module option description', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'disabled' => _x( 'Prevent sales - users will not be able to purchase your products', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
				'enabled'  => _x( 'Keep the shop open', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'disabled',
			'id'        => 'vacation_selling',
			'deps'      => array(
				'id'    => 'vacation_enabled',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'Vacation message', '[Admin]Vacation module option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Enter a message to notify your customers you\'re in vacation mode.', '[Admin]Vacation module option description', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'textarea-editor',
			'default'   => '',
			'id'        => 'vacation_message',
			'deps'      => array(
				'id'    => 'vacation_enabled',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),
	),
);
