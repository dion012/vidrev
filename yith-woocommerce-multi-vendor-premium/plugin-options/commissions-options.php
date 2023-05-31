<?php
/**
 * YITH Vendors Commissions Tab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'commissions' => array(
		'commissions-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'commissions-list' => array(
					'title' => _x( 'Commissions', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'commissions-gateways'   => array(
					'title' => _x( 'Gateways', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'commissions-settings'   => array(
					'title' => _x( 'Commissions Settings', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
			),
		),
	),
);
