<?php
/**
 * Product bid increment view
 *
 * @package YITH\Auctions\Views\Fields\Product
 */

$automatic_bid_increment_simple   = $field['yith-wcact-values']['automatic_bid_increment_simple'];
$automatic_bid_increment_advanced = $field['yith-wcact-values']['automatic_bid_increment_advanced'];
$automatic_bid_type               = $field['yith-wcact-values']['automatic_bid_type'];

?>

<div class="ywcact-automatic-bid-type " id="<?php echo esc_html( $field['id'] ); ?>">
	<div class="ywcact-automatic-product-bid-increment-simple ywcact_show_if_simple ywcact-row <?php echo ( 'simple' === $automatic_bid_type ) ? 'ywcact-show' : 'ywcact-hide'; ?>">
		<span for="ywcact_bid_increment_simple" class="">
			<?php
				// translators: %s is the currency symbol.
				echo esc_html( sprintf( _x( 'Set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
			?>
		</span>
		<input type="number" step="0.01" class="ywcact-input-product-number" id="ywcact_automatic_product_bid_simple" name="_yith_auction_bid_increment" value="<?php echo esc_attr( $automatic_bid_increment_simple ); ?>">
	</div>
	<div class="ywcact-automatic-product-bid-increment-advanced ywcact_show_if_advanced ywcact-row <?php echo ( 'advanced' === $automatic_bid_type ) ? 'ywcact-show' : 'ywcact-hide'; ?>">
	<?php

	if ( ! empty( $automatic_bid_increment_advanced ) && is_array( $automatic_bid_increment_advanced ) && 'advanced' === $automatic_bid_type ) {
		$automatic_bid_increment = ! is_array( $automatic_bid_increment_advanced ) ? maybe_unserialize( $automatic_bid_increment_advanced ) : $automatic_bid_increment_advanced;
		$size                    = count( $automatic_bid_increment_advanced ) - 1;
		$html_block              = '';

		foreach ( $automatic_bid_increment_advanced as $key => $value ) {
			if ( 0 === $key ) {
				?>
					<div class="ywcact-automatic-product-bid-increment-advanced-start ywcact-bid-increment-row">
						<span for="_yith_auction_bid_increment_advanced[0][end]" class="">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'With a current bid from start price to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="end"  name="_yith_auction_bid_increment_advanced[0][end]" value="<?php echo esc_attr( $value['end'] ); ?>">
						<span for="_yith_auction_bid_increment_advanced[0][value]" class="">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value"  name="_yith_auction_bid_increment_advanced[0][value]" value="<?php echo esc_attr( $value['value'] ); ?>">
					</div>
				<?php
			} elseif ( $size === $key ) {
				?>
					<div class="ywcact-automatic-product-bid-increment-advanced-end ywcact-bid-increment-row">
						<span for="_yith_auction_bid_increment_advanced[][start]" class="ywcact-span">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'Finally, with a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="start"  name="_yith_auction_bid_increment_advanced[<?php echo esc_attr( $key ); ?>][start]" value="<?php echo esc_attr( $value['start'] ); ?>">
						<span for="_yith_auction_bid_increment_advanced[][value]" class="ywcact-span">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'onwards set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value"  name="_yith_auction_bid_increment_advanced[<?php echo esc_attr( $key ); ?>][value]" value="<?php echo esc_attr( $value['value'] ); ?>">
					</div>
				<?php
			} else {
				?>
					<div class="ywcact-automatic-product-bid-increment-advanced-rule ywcact-bid-increment-row">
						<span for="_yith_auction_bid_increment_advanced[][]" class="ywcact-span">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'With a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="from"  name="_yith_auction_bid_increment_advanced[<?php echo esc_attr( $key ); ?>][from]" value="<?php echo esc_attr( $value['from'] ); ?>">
						<span for="_yith_auction_bid_increment_advanced[][]" class="ywcact-span">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="to"  name="_yith_auction_bid_increment_advanced[<?php echo esc_attr( $key ); ?>][to]" value="<?php echo esc_attr( $value['to'] ); ?>">
						<span for="_yith_auction_bid_increment_advanced[][]" class="ywcact-span">
							<?php
								// translators: %s is the currency symbol.
								echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
							?>
						</span>
						<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value"  name="_yith_auction_bid_increment_advanced[<?php echo esc_attr( $key ); ?>][value]" value="<?php echo esc_attr( $value['value'] ); ?>">
						<span class="yith-icon yith-icon-trash ywcact-remove-rule"></span>
					</div>
				<?php
			}
		}
	} else {
		?>
			<div class="ywcact-automatic-product-bid-increment-advanced-start ywcact-bid-increment-row">
				<span for="_yith_auction_bid_increment_advanced[0][end]" class="">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'With a current bid from start price to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
				</span>
				<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="end"  name="_yith_auction_bid_increment_advanced[0][end]" value="">
				<span for="_yith_auction_bid_increment_advanced[0][value]" class="">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
				</span>
				<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value" name="_yith_auction_bid_increment_advanced[0][value]" value="">
			</div>

			<!-- Here the other labels-->
			<div class="ywcact-automatic-product-bid-increment-advanced-end ywcact-bid-increment-row">
				<span for="_yith_auction_bid_increment_advanced[][start]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'Finally, with a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
				</span>
				<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="start"  name="_yith_auction_bid_increment_advanced[1][start]" value="">
				<span for="_yith_auction_bid_increment_advanced[][value]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'onwards set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
				</span>
				<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value"  name="_yith_auction_bid_increment_advanced[1][value]" value="">
			</div>
		<?php
	}

	?>
		<!--Hidden rule-->
		<div class="ywcact-automatic-product-bid-increment-advanced-rule ywcact-hide">
			<span for="_yith_auction_bid_increment_advanced[][]" class="">
				<?php
					// translators: %s is the currency symbol.
					echo esc_html( sprintf( _x( 'With a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
				?>
			</span>
			<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="from"  name="_yith_auction_bid_increment_advanced_dummy[][from]" value="">
			<span for="_yith_auction_bid_increment_advanced[][]" class="">
				<?php
					// translators: %s is the currency symbol.
					echo esc_html( sprintf( _x( 'to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
				?>
			</span>
			<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="to"  name="_yith_auction_bid_increment_advanced_dummy[][to]" value="">
			<span for="_yith_auction_bid_increment_advanced[][]" class="">
				<?php
					// translators: %s is the currency symbol.
					echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
				?>
			</span>
			<input type="number" step="0.01" class="ywcact-input-product-number" data-input-type="value"  name="_yith_auction_bid_increment_advanced_dummy[][value]" value="">
			<span class="yith-icon yith-icon-trash ywcact-remove-rule"></span>
		</div>
		<div>
			<a class="ywcact-product-add-rule">+ <?php echo esc_html__( 'add rule', 'yith-auctions-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
