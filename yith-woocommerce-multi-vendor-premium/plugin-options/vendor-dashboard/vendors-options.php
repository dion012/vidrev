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
		'vendors-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmv_vendor_dashboard_settings_tab',
		),
	),
);
