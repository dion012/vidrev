<?php
/**
 * Admin modules list
 *
 * @since   4.0.0
 * @author  Francesco Licandro
 * @package YITH WooCommerce Multi Vendor Premium
 * @var array $modules An array of modules.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<p>
	<?php echo esc_html_x( 'Modules help you extend your marketplace with advanced features. We included some powerful modules FOR FREE, as well as more advanced plugins we have developed to be fully integrated with our Multi Vendor plugin.', 'Addons tab content description', 'yith-woocommerce-product-vendors' ); ?>
</p>

<div id="modules-container">
	<?php
	foreach ( $modules as $module => $data ) :
		if ( ! empty( $data['hidden'] ) ) { // Skip hidden modules.
			continue;
		}

		$available = YITH_Vendors_Modules_Handler::instance()->is_module_available( $module );
		$active    = YITH_Vendors_Modules_Handler::instance()->is_module_active( $module );
		?>
		<div class="module" data-module="<?php echo esc_attr( $module ); ?>">
			<header>
				<?php if ( isset( $data['title'] ) ) : ?>
					<h3><?php echo esc_html( $data['title'] ); ?></h3>
				<?php endif; ?>

				<?php if ( ! $available ) : ?>
					<a href="<?php echo isset( $data['landing_uri'] ) ? esc_url( $data['landing_uri'] ) : '#'; ?>" class="button" target="_blank">
						<?php echo esc_html_x( 'Get it', 'Link label for plugin module landing', 'yith-woocommerce-product-vendors' ); ?>
					</a>
					<?php
				else :

					yith_plugin_fw_get_field(
						array(
							'id'                => $module . '_active',
							'name'              => $module . '_active',
							'type'              => 'onoff',
							'default'           => 'no',
							'class'             => 'on-off-module',
							'value'             => $active ? 'yes' : 'no',
							'custom_attributes' => array( 'data-module' => $module ),
						),
						true,
						false
					);
				endif;
				?>
			</header>
			<?php if ( ! isset( $data['name'] ) ) : ?>
				<p class="module-free"><?php echo esc_html_x( 'Free', 'String that indicated that an addon is available for free', 'yith-woocommerce-product-vendors' ); ?></p>
			<?php elseif ( ! $available ) : ?>
				<p class="module-required">
					<?php
					// translators: %s is the required plugin for activate the module.
					echo esc_html( sprintf( _x( 'Needs %s', 'Vendor addons: %s is the plugin name requested by the addon', 'yith-woocommerce-product-vendors' ), $data['name'] ) );
					?>
				</p>
			<?php endif; ?>
			<?php if ( isset( $data['option_desc'] ) ) : ?>
				<p class="module-description"><?php echo wp_kses_post( $data['option_desc'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
