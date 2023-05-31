<?php
/**
 * Display the list with enabled vendors widget.
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Woocommerce_Vendors_Widget' ) ) {
	/**
	 * YITH_Woocommerce_Vendors_Widget
	 *
	 * @since  1.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YITH_Woocommerce_Vendors_Widget extends WP_Widget {

		/**
		 * Construct
		 */
		public function __construct() {
			$id_base        = 'yith-vendors-list';
			$name           = __( 'YITH Vendors List', 'yith-woocommerce-product-vendors' );
			$widget_options = array( 'description' => __( 'Display a list of enabled vendors.', 'yith-woocommerce-product-vendors' ) );

			parent::__construct( $id_base, $name, $widget_options );
		}

		/**
		 * Echo the widget content.
		 * Subclasses should over-ride this function to generate their widget code.
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
		 * @param array $instance The settings for the particular instance of the widget.
		 */
		public function widget( $args, $instance ) {
			$hide = ! empty( $instance['hide_on_vendor_page'] ) && is_product_taxonomy();
			if ( ! $hide ) {
				$defaults = array(
					'title'               => '',
					'hide_on_vendor_page' => '',
					'show_product_number' => '',
					'hide_empty'          => '',
				);
				$args     = wp_parse_args( $instance, $defaults );
				yith_wcmv_get_template( 'vendors-list', $args, 'widgets' );
			}

		}

		/**
		 * Output the settings update form.
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $instance Current settings.
		 * @return void
		 */
		public function form( $instance ) {
			$defaults = array(
				'title'               => __( 'Vendors List', 'yith-woocommerce-product-vendors' ),
				'hide_on_vendor_page' => '',
				'show_product_number' => '',
				'hide_empty'          => '',
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_vendor_page' ) ); ?>"><?php esc_html_e( 'Hide this widget on the vendor page', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide_on_vendor_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_vendor_page' ) ); ?>" value="1" <?php checked( $instance['hide_on_vendor_page'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_product_number' ) ); ?>"><?php esc_html_e( 'Show the vendors\' products quantity', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_product_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_product_number' ) ); ?>" value="1" <?php checked( $instance['show_product_number'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>"><?php esc_html_e( 'Hide vendors that do not have products', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" value="1" <?php checked( $instance['hide_empty'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<?php
		}

		/**
		 * Update a particular instance.
		 * This function should check that $new_instance is set correctly. The newly-calculated
		 * value of `$instance` should be returned. If false is returned, the instance won't be
		 * saved/updated.
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $new_instance New settings for this instance as input by the user via.
		 * @param array $old_instance Old settings for this instance.
		 * @return array Settings to save or bool false to cancel saving.
		 * @see    WP_Widget::form()
		 */
		public function update( $new_instance, $old_instance ) {
			$defaults = array(
				'title'               => '',
				'hide_on_vendor_page' => '',
				'show_product_number' => '',
				'hide_empty'          => '',
			);

			$new_instance = wp_parse_args( $new_instance, $defaults );

			$instance                        = $old_instance;
			$instance['title']               = wp_strip_all_tags( $new_instance['title'] );
			$instance['hide_on_vendor_page'] = wp_strip_all_tags( $new_instance['hide_on_vendor_page'] );
			$instance['show_product_number'] = wp_strip_all_tags( $new_instance['show_product_number'] );
			$instance['hide_empty']          = wp_strip_all_tags( $new_instance['hide_empty'] );

			return $instance;
		}
	}
}
