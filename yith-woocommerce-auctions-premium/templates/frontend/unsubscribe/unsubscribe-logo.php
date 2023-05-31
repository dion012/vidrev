<?php
/**
 * Unsubscribe logo template
 *
 * @package YITH\Auctions\Templates\Frontend\Unsubscribe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>
<div class="yith-wcact-unsubscribe-header">
	<div class="yith-wcact-unsubscribe-header wp-block-image aligncenter">
		<?php echo wp_kses_post( $logo ); ?>
	</div>
</div>
