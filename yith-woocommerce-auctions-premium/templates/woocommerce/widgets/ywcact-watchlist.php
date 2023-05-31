<?php
/**
 * Watchlist widget
 *
 * @author  YITH
 * @package YITH\Auctions\Templates\WooCommerce\Widgets
 * @version 2.0.0
 */

/**
 * Template variables:
 *
 * @var $before_widget          string HTML to print before widget
 * @var $after_widget           string HTML to print after widget
 * @var $user_id                int user id
 * @var $watchlist_products     array Array of items that were added to watchlist; each item refers to a product.
 * @var $heading_icon           string Heading icon HTML tag
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$instance['style'] = ! empty( $instance['style'] ) ? $instance['style'] : 'mini';

?>

<?php if ( 'yes' === get_option( 'yith_wcact_settings_enable_watchlist', 'no' ) ) : ?>
	<?php
		/**
		 * APPLY_FILTERS: yith_wcact_before_watchlist_widget
		 *
		 * Filter the content to show before the widget.
		 *
		 * @param string $before_widget Content before the widget
		 *
		 * @return string
		 */
		echo apply_filters( 'yith_wcact_before_watchlist_widget', $before_widget ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>

	<?php if ( ! empty( $instance['title'] ) ) : ?>
		<h3 class="widget-title"><?php echo esc_html( $instance['title'] ); ?></h3>
	<?php endif; ?>

	<div class="content <?php echo esc_attr( $instance['style'] ); ?> " >
		<div class="heading">
			<div class="items-counter">
				<?php if ( 'mini' === $instance['style'] ) : ?>
					<a href="<?php echo esc_url( $watchlist_url ); ?>">
				<?php endif; ?>

				<span class="heading-icon">
					<img class="yith-wcact-widget-watchlist-icon" src="<?php echo esc_url( $heading_icon ); ?>">
				</span>

				<?php if ( isset( $instance['show_count'] ) && 'yes' === $instance['show_count'] ) : ?>
					<span class="items-count">
						<?php echo esc_html( count( $watchlist_products ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( 'mini' === $instance['style'] ) : ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( isset( $instance['style'] ) && 'extended' === $instance['style'] ) : ?>
				<?php
				/**
				 * APPLY_FILTERS: yith_watchlist_items_extended_title
				 *
				 * Filter the title of the watchlist widget when using the extended style.
				 *
				 * @param string $widget_title Content before the widget
				 *
				 * @return string
				 */
				?>
				<h3 class="heading-title"><?php echo esc_html( apply_filters( 'yith_watchlist_items_extended_title', __( 'Watchlist', 'yith-auctions-for-woocommerce' ) ) ); ?></h3>
			<?php endif; ?>
		</div>
		<div class="ywcact-watchlist-widget-content">
			<?php if ( isset( $instance['show_count'] ) && 'yes' === $instance['show_count'] && 'mini' === $instance['style'] && count( $watchlist_products ) ) : ?>
				<?php // translators: %d is the count of the products in the watchlist. ?>
				<p class="items-count"><?php echo esc_html( sprintf( __( '%d items in watchlist', 'yith-auctions-for-woocommerce' ), count( $watchlist_products ) ) ); ?></p>
			<?php endif; ?>

			<div class="list">
				<?php
					$args = array(
						'watchlist_products' => $watchlist_products,
						'instance'           => $instance,
						'user_id'            => $user_id,
					);

					wc_get_template( 'widgets/ywcact-watchlist-products.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );
					?>
			</div>
		</div>
	</div>

	<?php
		/**
		 * APPLY_FILTERS: yith_wcact_after_watchlist_widget
		 *
		 * Filter the content to show after the widget.
		 *
		 * @param string $after_widget Content after the widget
		 *
		 * @return string
		 */
		echo apply_filters( 'yith_wcact_after_watchlist_widget', $after_widget ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
<?php endif ?>
