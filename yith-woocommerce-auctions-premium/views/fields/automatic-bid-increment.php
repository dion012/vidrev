<?php
/**
 * Automatic bid increment view
 *
 * @package YITH\Auctions\Views\Fields
 */

$auctomatic_bid_increment = get_option( 'yith_wcact_settings_automatic_bid_increment', 0 );
$automatic_bid_type       = get_option( 'yith_wcact_settings_automatic_bid_type', 'simple' );

?>

<div class="ywcact-automatic-bid-type" id="<?php echo esc_html( $field['id'] ); ?>">

	<div class="ywcact-automatic-bid-increment-simple <?php echo ( 'simple' === $automatic_bid_type ) ? 'ywcact-show' : 'ywcact-hide'; ?>">
		<label for="ywcact_bid_increment_simple" class="ywcact-span">
			<?php
				// translators: %s is the currency symbol.
				echo wp_kses_post( sprintf( esc_html_x( 'Set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
			?>
		</label>
		<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" id="ywcact_bid_increment_simple" name="ywcact_automatic_bid_simple" value="<?php echo esc_attr( is_array( $auctomatic_bid_increment ) ? 0 : $auctomatic_bid_increment ); ?>">
	</div>
	<div class="ywcact-automatic-bid-increment-advanced <?php echo ( 'advanced' === $automatic_bid_type ) ? 'ywcact-show' : 'ywcact-hide'; ?>">
		<?php

		if ( 'advanced' === $automatic_bid_type ) {
			$auctomatic_bid_increment = maybe_unserialize( $auctomatic_bid_increment );

			if ( ! empty( $auctomatic_bid_increment ) && is_array( $auctomatic_bid_increment ) ) {
				$size       = count( $auctomatic_bid_increment ) - 1;
				$html_block = '';

				foreach ( $auctomatic_bid_increment as $key => $value ) {
					if ( 0 === $key ) {
						$html_block .= '<div class="ywcact-automatic-bid-increment-advanced-start ywcact-bid-increment-row">
                                <label for="ywcact_automatic_bid_advanced[0][end]" class="ywcact-span">'
									.
									sprintf(
										// translators: %s is the currency symbol.
										esc_html_x(
											'- With a current bid from start price to %s ',
											'Set an automatic bid increment of €',
											'yith-auctions-for-woocommerce'
										),
										get_woocommerce_currency_symbol()
									)
									. '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="end"  name="ywcact_automatic_bid_advanced[0][end]" value="'
									. $value['end'] . '">
                                <label for="ywcact_automatic_bid_advanced[0][value]" class="ywcact-span">'
									.
									sprintf(
										// translators: %s is the currency symbol.
										esc_html_x(
											'set an automatic bid increment of %s ',
											'Set an automatic bid increment of €',
											'yith-auctions-for-woocommerce'
										),
										get_woocommerce_currency_symbol()
									)
									. '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="value"  name="ywcact_automatic_bid_advanced[0][value]" value="'
									. $value['value'] . '">
                            </div>';
					} elseif ( $size === $key ) {
						$html_block .= '<div class="ywcact-automatic-bid-increment-advanced-end ywcact-bid-increment-row">
                                <label for="ywcact_automatic_bid_advanced[][start]" class="ywcact-span">'
							.
							sprintf(
								// translators: %s is the currency symbol.
								esc_html_x(
									'- Finally, with a current bid from %s ',
									'Set an automatic bid increment of €',
									'yith-auctions-for-woocommerce'
								),
								get_woocommerce_currency_symbol()
							) . '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="start"  name="ywcact_automatic_bid_advanced['
							. $key . '][start]" value="'
							. $value['start'] . '">
                                <label for="ywcact_automatic_bid_advanced[][value]" class="ywcact-span">'
							.
							sprintf(
								// translators: %s is the currency symbol.
								esc_html_x(
									'onwards set an automatic bid increment of %s ',
									'Set an automatic bid increment of €',
									'yith-auctions-for-woocommerce'
								),
								get_woocommerce_currency_symbol()
							) . '</label>
                                <input type="number" step="0.01"  class="ywcact-input-text ywcact-input-number-inline" data-input-type="value"  name="ywcact_automatic_bid_advanced['
							. $key . '][value]" value="'
							. $value['value'] . '">
                            </div>';
					} else {
						$html_block .= '<div class="ywcact-automatic-bid-increment-advanced-rule ywcact-bid-increment-row">
                                <label for="ywcact_automatic_bid_advanced[][]" class="">'
							.
							sprintf(
								// translators: %s is the currency symbol.
								esc_html_x(
									'- With a current bid from %s ',
									'Set an automatic bid increment of €',
									'yith-auctions-for-woocommerce'
								),
								get_woocommerce_currency_symbol()
							) . '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="from"  name="ywcact_automatic_bid_advanced['
							. $key . '][from]" value="' . $value['from']
							. '">
                                <label for="ywcact_automatic_bid_advanced[][]" class="">'
							.
							sprintf(
								// translators: %s is the currency symbol.
								esc_html_x(
									'to %s ',
									'Set an automatic bid increment of €',
									'yith-auctions-for-woocommerce'
								),
								get_woocommerce_currency_symbol()
							) . '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="to"  name="ywcact_automatic_bid_advanced['
							. $key . '][to]" value="' . $value['to'] . '">
                                <label for="ywcact_automatic_bid_advanced[][]" class="">'
							.
							sprintf(
								// translators: %s is the currency symbol.
								esc_html_x(
									'set an automatic bid increment of %s ',
									'Set an automatic bid increment of €',
									'yith-auctions-for-woocommerce'
								),
								get_woocommerce_currency_symbol()
							) . '</label>
                                <input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="value"  name="ywcact_automatic_bid_advanced['
							. $key . '][value]" value="'
							. $value['value'] . '">
                                <span class="yith-icon yith-icon-trash ywcact-remove-rule"></span>
                            </div>';
					}
				}

				echo $html_block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			?>
			<div>
				<!--First Rule-->
				<div class="ywcact-automatic-bid-increment-advanced-start ywcact-bid-increment-row">
					<label for="ywcact_automatic_bid_advanced[0][end]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( '- With a current bid from start price to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="end"  name="ywcact_automatic_bid_advanced[0][end]" value="">
					<label for="ywcact_automatic_bid_advanced[0][value]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="value" name="ywcact_automatic_bid_advanced[0][value]" value="">
				</div>

				<!-- Here the other labels-->
				<div class="ywcact-automatic-bid-increment-advanced-end ywcact-bid-increment-row">
					<label for="ywcact_automatic_bid_advanced[][start]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( '- Finally, with a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="start"  name="ywcact_automatic_bid_advanced[1][start]" value="">
					<label for="ywcact_automatic_bid_advanced[][value]" class="ywcact-span">
					<?php
						// translators: %s is the currency symbol.
						echo esc_html( sprintf( _x( 'onwards set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
					?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="value"  name="ywcact_automatic_bid_advanced[1][value]" value="">
				</div>

				<?php } ?>
				<!--Hidden rule-->

				<div class="ywcact-automatic-bid-increment-advanced-rule ywcact-hide">
					<label for="ywcact_automatic_bid_advanced[][]" class="">
						<?php
							// translators: %s is the currency symbol.
							echo esc_html( sprintf( _x( '- With a current bid from %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
						?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="from"  name="ywcact_automatic_bid_advanced_dummy[][from]" value="">
					<label for="ywcact_automatic_bid_advanced[][]" class="">
						<?php
							// translators: %s is the currency symbol.
							echo esc_html( sprintf( _x( 'to %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
						?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="to"  name="ywcact_automatic_bid_advanced_dummy[][to]" value="">
					<label for="ywcact_automatic_bid_advanced[][]" class="">
						<?php
							// translators: %s is the currency symbol.
							echo esc_html( sprintf( _x( 'set an automatic bid increment of %s ', 'Set an automatic bid increment of €', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
						?>
					</label>
					<input type="number" step="0.01" class="ywcact-input-text ywcact-input-number-inline" data-input-type="value"  name="ywcact_automatic_bid_advanced_dummy[][value]" value="">
					<span class="yith-icon yith-icon-trash ywcact-remove-rule"></span>
				</div>
				<div>
					<a class="ywcact-add-rule">+ <?php echo esc_html__( 'add rule', 'yith-auctions-for-woocommerce' ); ?></a>
				</div>
			</div>
	</div>
<div>
