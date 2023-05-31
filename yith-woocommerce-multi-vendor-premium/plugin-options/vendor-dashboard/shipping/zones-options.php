<?php
/**
 * Shipping polices subtab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'zones' => array(
		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Shipping Zones', '[Admin]Shipping module tab title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'shipping_zones_title',
		),

		array(
			'type'             => 'yith-field',
			'yith-type'        => 'shipping-zones',
			'yith-display-row' => false,
			'default'          => array(),
			'id'               => 'zone_data',
		),

		array(
			'type' => 'sectionend',
		),
	),
);
