<?php
/**
 * YITH Vendors Store & Product Pages Tab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'frontend-pages' => array(
		'frontend-pages-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'frontend-pages-general' => array(
					'title' => _x( 'General', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'frontend-pages-product' => array(
					'title' => _x( 'Product Page', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'frontend-pages-store'   => array(
					'title' => _x( 'Store Page', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
			),
		),
	),
);
