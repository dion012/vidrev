<?php
/**
 * YITH Vendors Modules Tab
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$tab_settings = array(
	'modules' => array(
		'modules-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_vendors_modules_tab',
			'show_container' => true,
			'title'          => _x( 'Add-ons', 'Addons tab title', 'yith-woocommerce-product-vendors' ),
		),
	),
);

// If there are modules active with settings, change the tab layout to multi_tab.
if ( apply_filters( 'yith_wcmv_have_active_add_ons_settings', false ) ) {
	$tab_settings = array(
		'modules' => array(
			'modules-options' => array(
				'type'     => 'multi_tab',
				'sub-tabs' => apply_filters(
					'yith_wcmv_add_ons_settings_sub_tabs',
					array(
						'modules-modules' => array(
							'title' => _x( 'All Modules', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						),
					)
				),
			),
		),
	);
}

return $tab_settings;
