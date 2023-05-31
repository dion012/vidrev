<?php
/**
 * Add image product and title in notification email
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails\ProductEmails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="yith-wcact-auction-thumbnail-email" style=" margin: 20px 0px !important; padding: 10px; background-color:#f5f5f5">
	<table>
		<tr>
			<td>
				<?php
				/**
				 * APPLY_FILTERS: yith_wcact_email_auction_thumbnail
				 *
				 * Filter the auction product image in the emails.
				 *
				 * @param string $auction_thumbnail Auction thumbnail
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'yith_wcact_email_auction_thumbnail', '<img src="' . ( $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' ) ) : wc_placeholder_img_src() ) . '" alt="' . esc_attr__( 'Item Image', 'yith-auctions-for-woocommerce' ) . '"width="150px" style="vertical-align:middle; margin-right: 10px;" />', $product ) );
				?>
			</td>
			<td>
				<a style="text-decoration: none; target="_blank" href="<?php echo esc_url( $url ); ?>"><?php echo wp_kses_post( $product_name ); ?></a></br>
				<?php
				if ( isset( $content_image ) && $content_image ) {
					echo wp_kses_post( $content_image );
				} else {
					?>
						<p class="ywcat-image-price" style="display: block; margin-bottom: 0px;"><span style="font-weight: 800 !important;"> <?php echo esc_html__( 'Current bid:', 'yith-auctions-for-woocommerce' ); ?> </span> <span> <?php echo wp_kses_post( isset( $max_bid ) ? wc_price( $max_bid ) : wc_price( $product->get_price() ) ); ?> </span></p>
					<?php

					if ( isset( $show_auction_end ) && $show_auction_end ) {
						?>
						<p>
							<span style="font-weight: 800 !important;"><?php echo esc_html( sprintf( __( 'Ends in:', 'yith-auctions-for-woocommerce' ), $auction_end_number, $auction_end_time ) ); ?></span>
							<span><?php echo wp_kses_post( sprintf( '%1$s %2$s', $auction_end_number, $auction_end_time ) ); ?></span>
						</p>
						<?php
					}
				}
				?>
			</td>
		</tr>
	</table>
</div>
