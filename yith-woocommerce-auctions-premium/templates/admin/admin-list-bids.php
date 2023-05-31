<?php
/**
 * Admin list bids template
 *
 * @package YITH\Auctions\Templates\Admin
 */

if ( isset( $product ) && $product ) {
	$auction_list_number = count( $auction_list );
	$bidders_count       = ( isset( $bidders_count ) && $bidders_count ) ? $bidders_count : 0;

	?>
	<input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php echo esc_attr( $product->get_id() ); ?> ">
	<input type="hidden" id="yith-wcact-bids-count" name="yith-wcact-bids-count" value="<?php echo esc_attr( $bidders_count ); ?>">

	<?php

	if ( 0 === $auction_list_number ) {
		?>
		<p id="single-product-no-bid"><?php esc_html_e( 'There is no bid for this item', 'yith-auctions-for-woocommerce' ); ?></p>
		<?php
	} else {
		?>
		<table id="datatable" class="yith-wcact-auction-list-bids-table">
			<tr>
				<th class="toptable column-bidder"><?php echo esc_html__( 'Bidder', 'yith-auctions-for-woocommerce' ); ?></th>
				<th class="toptable column-bid-amount"><?php echo esc_html__( 'Bid amount', 'yith-auctions-for-woocommerce' ); ?></th>
				<th class="toptable column-bid-time"><?php echo esc_html__( 'Date', 'yith-auctions-for-woocommerce' ); ?></th>
				<th class="toptable column-actions"><?php echo ''; ?></th>
			</tr>

			<?php
			foreach ( $auction_list as $object => $id ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$user       = get_user_by( 'id', $id->user_id );
				$username   = ( $user ) ? $user->data->user_nicename : 'anonymous';
				$bid        = $id->bid;
				$data_attr  = array(
					'user-id'    => absint( ( $id->user_id ) ),
					'date-time'  => $id->date,
					'product-id' => $post_id,
					'bid'        => $id->bid,
					'delete-id'  => $id->id,
				);
				$icon_array = array(
					'icon'       => 'trash',
					'title'      => esc_html__( 'Show details', 'yith-auctions-for-woocommerce' ),
					'type'       => 'action-button',
					'class'      => 'yith-wcact-delete-bid',
					'icon_class' => 'yith-icon yith-icon-trash yith-wcact-icon',
					'data'       => $data_attr,
				);

				$icon = yith_plugin_fw_get_component( $icon_array, false );

				?>
				<tr class="yith-wcact-row">
					<td class="yith-wcact-bidder row-bidder"><a target="_blank" href="user-edit.php?user_id=<?php echo absint( $id->user_id ); ?>"><?php echo wp_kses_post( $username ); ?></a></td>
					<td class="row-bid-amount"><?php echo wp_kses_post( wc_price( $bid ) ); ?></td>
					<td class="yith_auction_datetime row-bid-time"><?php echo wp_kses_post( get_date_from_gmt( $id->date ) ); ?></td>
					<td class="yith-wcact-delete-bid-row bid-row-actions"><?php echo wp_kses_post( $icon ); ?></td>
				</tr>
				<?php
			}

			/**
			 * APPLY_FILTERS: yith_wcact_show_start_auction_admin_table
			 *
			 * Filter whether to show the start price in the bids table in the backend.
			 *
			 * @param bool $show_start_price Whether to show the start price or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_show_start_auction_admin_table', false ) && $product->is_start() && $auction_list ) {
				?>
				<tr class="yith-wcact-row">
					<td><?php esc_html_e( 'Start auction', 'yith-auctions-for-woocommerce' ); ?></td>
					<td class="row-bid-amount"><?php echo wp_kses_post( wc_price( $product->get_start_price() ) ); ?></td>
					<td></td>
				</tr>
				<?php
			}
			?>

		</table>
		<?php
		if ( 0 === $auction_list_number ) {
			?>
			<p id="single-product-no-bid"><?php esc_html_e( 'There is no bid for this item', 'yith-auctions-for-woocommerce' ); ?></p>
			<?php
		}
	}

	if ( $pagination ) {
		echo '<div class="yith-wcact-pagination-section" data-current-page="' . esc_attr( $current_page ) . '">';
		echo wp_kses_post(
			paginate_links(
				array(
					'base'      => str_replace( 'big', '%#%', esc_url( get_pagenum_link( 'big' ) ) ),
					'format'    => '?paged=%#%',
					'current'   => max( 1, $current_page ),
					'total'     => $total_pages,
					'prev_text' => '«',
					'next_text' => '»',
				)
			)
		);
		echo '</div>';
	}
}
