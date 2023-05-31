<?php
/**
 * Unsubscribe content template
 *
 * @package YITH\Auctions\Templates\Frontend\Unsubscribe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>
<div class="yith-wcact-unsubscribe-content">
	<div class="entry-content">
		<h2 class="has-text-align-center"><?php esc_html_e( 'Unsubscribe', 'yith-auctions-for-woocommerce' ); ?></h2>
		<?php
		the_content(
			sprintf(
				wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'yith-auctions-for-woocommerce' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			)
		);
		?>
	</div>
<div>
