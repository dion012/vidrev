<?php
/**
 * Vendors Report Abuse sub-tab options array
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'modules-report-abuse' => array(
		'reported_abuse_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'reported_abuse',
			'wp-list-style' => 'classic',
		),
	),
);
