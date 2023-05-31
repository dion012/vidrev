<?php
/**
 * Vendors Announcements subtab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'modules-announcements' => array(
		'announcement_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'announcement',
			'wp-list-style' => 'classic',
		),
	),
);
