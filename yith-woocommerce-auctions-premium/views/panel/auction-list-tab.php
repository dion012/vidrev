<?php
/**
 * Auction list tab panel
 *
 * @package YITH\Auctions\Views
 * @since   2.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Auction_Product_List_Table' ) ) {
	require_once YITH_WCACT_PATH . 'includes/admin-tables/class-yith-auction-product-table-list.php';
}
$list_table = new YITH_Auction_Product_List_Table();

$add_new_url    = add_query_arg( 'ywcact-create-first-auction', true, admin_url( 'post-new.php?post_type=product' ) );
$add_new_button = __( 'Start new auction', 'yith-auctions-for-woocommerce' )

?>
<div id="yith-auction-list-table" class="yith-plugin-fw-panel-custom-tab-container">
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Auction Products', 'yith-auctions-for-woocommerce' ); ?></h1>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action yith-add-button">
			<?php echo esc_html( $add_new_button ); ?>
		</a>
		<hr class="wp-header-end">
	</div>
	<?php
	$list_table->prepare_items();
	$list_table->views();
	?>
	<form id="yith-wcact-auction-table" class="auction-table  yith-plugin-ui--classic-wp-list-style"  method="get">
		<input type="hidden" name="page" value="yith_wcact_panel_product_auction" />
		<input type="hidden" name="tab" value="auction-list" />
		<?php
		$list_table->search_box( esc_html__( 'Search', 'yith-auctions-for-woocommerce' ), 'yith-auction-search' );
		$list_table->display();
		?>
	</form>
</div>
