<?php
/**
 * Announcement message template
 *
 * @since 4.0.0
 * @author Francesco Licandro
 * @package YITH WooCommerce Multi Vendor Premium
 * @var integer $id The announcement ID.
 * @var string $text The announcement content.
 * @var boolean $dismissible True if the announcement is dismissible, false if not.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="yith-wcmv-announcement-<?php echo esc_attr( $id ); ?>" class="yith-wcmv-announcement" data-id="<?php echo esc_attr( $id ); ?>">
	<div class="svg-data">
		<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="35" height="35">
			<path d="M15.5 4a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-1 0v1a.5.5 0 0 0 .5.5zM20.5 9h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1zM18.5 6a.47.47 0 0 0 .35-.15l1-1a.49.49 0 1 0-.7-.7l-1 1a.48.48 0 0 0 0 .7.47.47 0 0 0 .35.15zM16.94 7.06a23.4 23.4 0 0 0-5.46-4.2c-1.89-1-3.28-1.13-4-.39A1.74 1.74 0 0 0 7 3.75v6.51A2.55 2.55 0 0 1 6.27 12l-3.08 3.11a4 4 0 0 0 0 5.7 4 4 0 0 0 5.7 0l.11-.1.53.52a2.57 2.57 0 0 0 1.85.77A2.63 2.63 0 0 0 14 19.38a2.57 2.57 0 0 0-.77-1.85l-.36-.37a2.58 2.58 0 0 1 .87-.16h6.76a.54.54 0 0 0 .21-.05 1.47 1.47 0 0 0 .82-.42c.74-.73.6-2.12-.39-4a23.4 23.4 0 0 0-4.2-5.47zM8.17 3.17A.85.85 0 0 1 8.75 3a5.39 5.39 0 0 1 2.25.74 23.11 23.11 0 0 1 5.22 4 23.11 23.11 0 0 1 4 5.22c.86 1.65.84 2.57.57 2.85a.78.78 0 0 1-.58.17 8.11 8.11 0 0 1-3.75-1.63c-.39-.27-.76-.55-1.12-.83a3.59 3.59 0 0 0 .16-1 4 4 0 0 0-5.09-3.88c-.27-.36-.55-.73-.82-1.12A8.11 8.11 0 0 1 8 3.75a.78.78 0 0 1 .17-.58zm6.34 9.66c-.6-.5-1.18-1-1.75-1.59s-1.09-1.15-1.59-1.75a3 3 0 0 1 3.37 3c0 .13-.02.23-.03.34zM3.89 20.11a3 3 0 0 1 0-4.3l.17-.17a5 5 0 0 0 4.3 4.3l-.17.17a3.11 3.11 0 0 1-4.3 0zm8.64-1.88a1.65 1.65 0 0 1 .47 1.15 1.64 1.64 0 0 1-.47 1.15 1.69 1.69 0 0 1-2.3 0L9.71 20 12 17.73zM13.74 16a3.44 3.44 0 0 0-2.47 1l-1.96 2H9a4 4 0 0 1-4-4 3 3 0 0 1 0-.31l2-2a3.48 3.48 0 0 0 1-2.47V6.75c.26.47.54.92.81 1.31.39.58.79 1.11 1.2 1.61a29 29 0 0 0 2.05 2.27c.73.73 1.49 1.42 2.26 2.05.51.41 1 .8 1.62 1.2.39.27.84.55 1.31.81z" fill="#b54f00" class="fill-000000"></path>
		</svg>
	</div>
	<div class="announcement-text">
		<?php echo wp_kses_post( $text ); ?>
	</div>
	<?php if ( $dismissible ) : ?>
		<button type="button" class="announcement-dismiss"><i class="yith-icon yith-icon-close"></i></button>
	<?php endif; ?>
</div>
<?php
