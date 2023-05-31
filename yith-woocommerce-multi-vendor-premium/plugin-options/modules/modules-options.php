<?php
/**
 * YITH Vendors Modules Tab
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'modules-modules' => array(
		'modules-modules-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_vendors_modules_tab',
			'show_container' => true,
		),
	),
);
