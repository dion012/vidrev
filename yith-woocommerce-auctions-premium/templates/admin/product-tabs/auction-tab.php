<?php
/**
 * Auction tab product template
 *
 * @package YITH\Auctions\Templates\ProductTabs
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

global $post;

$auction_product = wc_get_product( $post );
$post_id         = ''; // phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited

if ( $auction_product instanceof WC_Product ) {
	$post_id = $auction_product->get_id();

} else {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['product_id'] ) ) {
		$auction_product = wc_get_product( intval( $_GET['product_id'] ) );
		$post_id         = ( isset( $auction_product ) && $auction_product instanceof WC_Product ) ? $auction_product->get_id() : '';
	}
}

?>

<div>
	<h3><?php esc_html_e( 'Auction Settings', 'yith-auctions-for-woocommerce' ); ?></h3>
</div>

<?php

/**
 * DO_ACTION: yith_before_auction_tab
 *
 * Allow to render some content before the tab content in the product in the backend.
 *
 * @param int $post_id Product ID
 */
do_action( 'yith_before_auction_tab', $post_id );

if ( $auction_product && 'auction' === $auction_product->get_type() ) {
	yit_delete_prop( $auction_product, 'yith_wcact_new_bid' );

	$datetime     = $auction_product->get_start_date();
	$from_auction = $datetime ? absint( $datetime ) : '';
	$from_auction = $from_auction ? get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $from_auction ) ) : '';
	$datetime     = $auction_product->get_end_date();
	$to_auction   = $datetime ? absint( $datetime ) : '';
	$to_auction   = $to_auction ? get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $to_auction ) ) : '';
} else {
	$from_auction = '';
	$to_auction   = '';
}

/**
 * APPLY_FILTERS: yith_wcact_show_auction_dates
 *
 * Filter whether to show the auction date fields in the product in the backend.
 *
 * @param bool       $show_auction_dates Whether to show if the winner email has been sent or not
 * @param WC_Product $auction_product    Product object
 *
 * @return bool
 */
if ( apply_filters( 'yith_wcact_show_auction_dates', true, $auction_product ) ) {
	echo '<div class=" yith-wcact-form-field form-field wc_auction_field yith-plugin-ui wc_auction_dates">
			<label for="wc_auction_dates_from">' . esc_html__( 'Auction Dates', 'yith-auctions-for-woocommerce' ) . ' <span class="required ywcact-required">*</span></label>
			<input type="text" name="_yith_auction_for" class="wc_auction_datepicker ywcact-data-validation" id="_yith_auction_for" value="' . esc_attr( $from_auction ) . '" placeholder="' . esc_html__( 'From', 'yith-auctions-for-woocommerce' ) . '"
			title="YYYY-MM-DD hh:mm:ss" data-related-to="#_yith_auction_to" data-title-field="' . esc_html__( 'Start time', 'yith-auctions-for-woocommerce' ) . '" data-validation="has_value">
			<input type="text" name="_yith_auction_to" class="wc_auction_datepicker ywcact-data-validation" id="_yith_auction_to" value="' . esc_attr( $to_auction ) . '" placeholder="' . esc_html__( 'To', 'yith-auctions-for-woocommerce' ) . '"
			title="YYYY-MM-DD hh:mm:ss" data-title-field="' . esc_html__( 'End time', 'yith-auctions-for-woocommerce' ) . '" data-validation="has_value">
			<div class="yith-wcact-form-field__description">' . esc_html__( 'Set a start and end time for this auction', 'yith-auctions-for-woocommerce' ) . '</div>
		</div>';

}

/**
 * APPLY_FILTERS: yith_wcact_show_advanced_options_section
 *
 * Filter whether to show the advanced options title in the product in the backend.
 *
 * @param bool       $show_advanced_options Whether to show if the winner email has been sent or not
 * @param WC_Product $auction_product       Product object
 *
 * @return bool
 */
if ( apply_filters( 'yith_wcact_show_advanced_options_section', false, $auction_product ) ) {
	?>
		<div class="ywcact-advanced-options-section">
			<h3><?php esc_html_e( 'Advanced options', 'yith-auctions-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'In this section you can override the plugin general settings and set specific settings for this auction product.', 'yith-auctions-for-woocommerce' ); ?></p>
		</div>
	<?php
}

/**
 * DO_ACTION: yith_after_auction_tab
 *
 * Allow to render some content after the tab content in the product in the backend.
 *
 * @param int $post_id Product ID
 */
do_action( 'yith_after_auction_tab', $post_id );

wp_nonce_field( 'yith-wcact-auction-form', 'yith_wcact_auction_form' );
