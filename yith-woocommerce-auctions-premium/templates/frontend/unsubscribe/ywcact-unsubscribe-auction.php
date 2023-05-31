<?php
/**
 * Unsubscribe Auction template
 *
 * @package YITH\Auctions\Templates\Frontend\Unsubscribe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// phpcs:disable WordPress.Security.NonceVerification.Recommended

global $post;

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<link rel="profile" href="https://gmpg.org/xfn/11"/>
	<?php yith_wcact_unsubscribe_head(); ?>
</head>

<body <?php yith_wcact_unsubscribe_body_class(); ?>>
	<div class="container yith-wcact-unsubscribe-auction-page">
		<?php
		/**
		 * DO_ACTION: yith_wcact_unsubscribe_body
		 *
		 * Allow to render some content in the Unsubscribe body template.
		 *
		 * @param WP_Post $post Post
		 */
		do_action( 'yith_wcact_unsubscribe_body', $post );
		?>
	</div>

	<?php yith_wcact_unsubscribe_footer(); ?>
</body>
</html>
