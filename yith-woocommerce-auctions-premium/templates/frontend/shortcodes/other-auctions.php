<?php
/**
 * Other auctions template
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\Frontend\Shortcodes
 */

$item_args = array(
	'color' => isset( $color ) && $color ? $color : false,
);

?>

<div class="ywcact-other-auctions-section">
	<?php
	if ( $heading_message && ! empty( $heading_message ) ) {
		?>
			<p class="ywcact-other-auction__heading"><?php echo wp_kses_post( $heading_message ); ?></p>
		<?php
	}
	?>

	<div class="ywcact-other-auctions-container">
		<ul class="ywcact-other-auctions-list">
			<?php
			foreach ( $items as $item ) {

				$item_args['product'] = $item;
				wc_get_template( 'frontend/shortcodes/other-auctions-item.php', $item_args, '', YITH_WCACT_TEMPLATE_PATH );
			}
			?>
		</ul>
	</div>
</div>
