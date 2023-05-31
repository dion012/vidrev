<?php
/**
 * YITH_Woocommerce_Vendors_Widget template
 *
 * @package YITH WooCommerce Multi Vendor
 * @author YITH
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$vendors = yith_wcmv_get_vendors(
	array(
		'enabled_selling' => true,
		'hide_empty'      => $hide_empty,
	)
);
?>

<div class="clearfix widget vendors-list">
	<h3 class="widget-title"><?php echo esc_html( $title ); ?></h3>
	<?php
	if ( ! empty( $vendors ) ) :
		?>
		<ul>
		<?php
		foreach ( $vendors as $vendor ) :
			/**
			 * Foreach variables.
			 *
			 * @var YITH_Vendor $vendor The vendor instance.
			 */
			$product_number = count( $vendor->get_products() );
			if ( ! empty( $hide_empty ) && empty( $product_number ) || empty( $vendor->get_owner() ) ) {
				continue;
			}
			?>
			<li>
				<a class="vendor-store-url" href="<?php echo esc_url( $vendor->get_url() ); ?>">
					<?php echo esc_html( $vendor->get_name() ); ?>
				</a>
				<?php
				if ( isset( $show_product_number ) && ! empty( $show_product_number ) ) {
					echo " ({$product_number}) "; // phpcs:ignore
				}
				?>
			</li>
			<?php
		endforeach;
		?>
		</ul>
	<?php endif; ?>
</div>
