<?php
/**
 * Create your fist auction product
 *
 * @package YITH\Auctions\Views
 * @since   2.0.0
 * @author  YITH
 */

?>
<div id="yith-wcact-create-your-first-auction-product" class='yith-plugin-fw-panel-custom-tab-container'>
	<div id="yith-wcact-create-your-first-auction-product__image"><img src="<?php echo esc_url( YITH_WCACT_ASSETS_URL . '/images/icon/auctionicon.png' ); ?>"/></div>
	<div id="yith-wcact-create-your-first-auction-product__message">
		<?php
		echo implode(
			'<br />',
			array(
				/* Translators: %s: Plugin name*/
				sprintf( esc_html__( 'Thanks for choosing %s!', 'yith-auctions-for-woocommerce' ), '<strong>YITH Auctions for WooCommerce</strong>' ),
				esc_html__( 'Now, the first step is to create an auction product.', 'yith-auctions-for-woocommerce' ),
			)
		);
		?>
	</div>

	<div id="yith-wcact-create-your-first-auction-product__call-to-action">
		<a href="<?php echo esc_url( add_query_arg( 'ywcact-create-first-auction', true, admin_url( 'post-new.php?post_type=product' ) ) ); ?>" id="yith-wcact-create-your-first-auction-product__button" class="yith-wcact-create-your-first-auction-product-button"><?php esc_html_e( 'Create your first auction product', 'yith-auctions-for-woocommerce' ); ?></a>
	</div>
</div>

</div>
<div class="hidden">
