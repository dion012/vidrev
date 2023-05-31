<?php
/**
 * Admin vendors list table
 *
 * @since 4.0.0
 * @author Francesco Licandro
 * @package YITH WooCommerce Multi Vendor Premium
 * @var YITH_Vendors_Vendors_List_Table $vendors_table YITH_Vendors_Vendors_List_Table class instance.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


$singular_label = YITH_Vendors_Taxonomy::get_singular_label( 'strtolower' );
$plural_label   = YITH_Vendors_Taxonomy::get_plural_label( 'strtolower' );

?>
<div class="wrap custom-list-table vendors-list-table-wrapper">
	<div class="list-table-title">
		<h1 class="wp-heading-inline">
			<?php
			// translators: %s stand for the plural label for vendor taxonomy. Default is Vendors.
			echo esc_html( sprintf( _x( '%s List', '[Admin]: Vendors list table section title.', 'yith-woocommerce-product-vendors' ), ucfirst( $plural_label ) ) );
			?>
		</h1>
		<a href="#" class="page-title-action create-vendor">
			<?php
			// translators: %s stand for the singular label for vendor taxonomy. Default is vendor.
			echo esc_html( sprintf( __( 'Add %s', 'yith-woocommerce-product-vendors' ), $singular_label ) );
			?>
		</a>
	</div>

	<?php if ( ! $vendors_table->has_items() ) : ?>
		<div class="yith-plugin-fw__list-table-blank-state">
			<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/store-alt.svg" width="65" alt="" />
			<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo esc_html_x( 'No vendor store created yet.', '[Admin]Vendor table empty message', 'yith-woocommerce-product-vendors' ); ?></div>
		</div>
	<?php else : ?>
		<form id="vendors-list-table" method="GET">
			<input type="hidden" name="page" value="<?php echo ! empty( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; ?>"/>
			<input type="hidden" name="tab" value="<?php echo ! empty( $_GET['tab'] ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : ''; ?>"/>

			<?php
			// translators: %s stand for the plural label for vendor taxonomy. Default is vendors.
			$vendors_table->add_search_box( sprintf( __( 'Search %s', 'yith-woocommerce-product-vendors' ), $plural_label ), 's' );
			?>
			<?php $vendors_table->display(); ?>
		</form>
	<?php endif; ?>

</div>
