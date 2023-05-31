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
	'shipping' => array(
		'shipping-tab' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'settings'   => array(
					'title' => _x( 'Shipping Options', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'zones'      => array(
					'title' => _x( 'Shipping Zones', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'extra-cost' => array(
					'title' => _x( 'Extra Costs', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'polices'    => array(
					'title' => _x( 'Policies', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
			),
		),
	),
);
