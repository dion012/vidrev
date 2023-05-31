<?php
/**
 * YITH Vendors Gateway functions and utils
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yith_wcmv_add_commissions_options' ) ) {
	/**
	 * Add  Stripe Connect General options for commissions
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 * @param array $options Array of commissions options.
	 * @return array Stripe Connect option array
	 */
	function yith_wcmv_add_commissions_options( $options ) {
		$vendors_option = array(
			'vendor-product-commissions' => array(
				'title'   => __( 'Exclude vendors\' products from commissions', 'yith-stripe-connect-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'If enabled, the receivers will not earn any commissions on vendors products', 'yith-stripe-connect-for-woocommerce' ),
				'default' => 'yes',
				'id'      => 'vendor_product_commissions',
			),
		);

		$keys    = array_keys( $options );
		$offset  = array_search( 'commissions-exceeded', $keys );
		$first   = array_slice( $options, 0, $offset + 1 );
		$last    = array_slice( $options, $offset + 1, count( $options ) );
		$options = array_merge( $first, $vendors_option, $last );

		return $options;
	}
}

add_filter( 'yith_wcsc_general_settings', 'yith_wcmv_add_commissions_options' );