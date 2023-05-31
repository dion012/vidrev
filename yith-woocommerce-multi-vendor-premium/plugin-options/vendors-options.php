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
	'vendors' => array(
		'vendors-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'vendors-list'   => array(
					'title' => _x( 'Vendors List', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'vendors-registration' => array(
					'title' => _x( 'Vendors Registration', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'vendors-permissions'  => array(
					'title' => _x( 'Vendors Permissions', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
			),
		),
	),
);
