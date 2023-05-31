<?php
/**
 * Quick Info widget.
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendor_Quick_Info_Widget' ) ) {
	/**
	 * YITH_Vendor_Quick_Info_Widget
	 *
	 * @since  1.0.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YITH_Vendor_Quick_Info_Widget extends WP_Widget {

		/**
		 * The widget instance
		 *
		 * @var array
		 */
		public $instance = array();

		/**
		 * An array of form submit response message
		 *
		 * @var array
		 */
		public $response = array();

		/**
		 * Array of default widget arguments.
		 *
		 * @var array
		 */
		public $default = array();

		/**
		 * Construct
		 */
		public function __construct() {
			$id_base        = 'yith-vendor-quick-info';
			$name           = __( 'YITH Vendor Contact Form', 'yith-woocommerce-product-vendors' );
			$widget_options = array(
				'description' => __( "Add a quick info contact form to the vendors' store page and to the single product pages.", 'yith-woocommerce-product-vendors' ),
			);

			$this->default = array(
				'title'                => __( 'Quick Info', 'yith-woocommerce-product-vendors' ),
				'description'          => __( 'Do you need more information? Write to us!', 'yith-woocommerce-product-vendors' ),
				'hide_from_guests'     => false,
				'cc_to_admin'          => false,
				'show_in_vendor_store' => true,
				'show_in_single'       => false,
				'submit_label'         => __( 'Submit', 'yith-woocommerce-product-vendors' ),
			);

			$this->instance = wp_parse_args( $this->instance, $this->default );

			$this->response = apply_filters(
				'yith_wcmv_quick_info_widget_response',
				array(
					0 => array(
						'message' => __( 'Unable to send email. Please, try again.', 'yith-woocommerce-product-vendors' ),
						'class'   => 'error',
					),
					1 => array(
						'message' => __( 'Email sent successfully.', 'yith-woocommerce-product-vendors' ),
						'class'   => 'message',
					),
				)
			);

			add_action( 'init', array( $this, 'send_mail' ), 20 );

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
			$instance         = wp_parse_args( $instance, $this->default );
			$hide_from_guests = ! empty( $instance['hide_from_guests'] );

			if ( $hide_from_guests && ! is_user_logged_in() ) {
				return;
			}

			$show_on_single       = ! empty( $instance['show_in_single'] );
			$show_on_vendor_store = ! empty( $instance['show_in_vendor_store'] );

			$is_vendor_page = yith_wcmv_is_vendor_page();
			$is_singular    = is_singular( 'product' );

			if ( ( $is_vendor_page && $show_on_vendor_store ) || ( $is_singular && $show_on_single ) ) {
				global $product;
				$object      = null;
				$object_type = null;

				if ( $is_vendor_page ) {
					$object      = get_query_var( 'term' );
					$object_type = 'vendor';
				} elseif ( $is_singular ) {
					$object      = $product;
					$object_type = 'product';
				}

				$vendor = yith_wcmv_get_vendor( $object, $object_type );

				if ( $vendor && $vendor->is_valid() ) {
					$vendor_email = $vendor->get_meta( 'store_email' );

					if ( empty( $vendor_email ) ) {
						$vendor_owner = get_user_by( 'id', absint( $vendor->get_owner() ) );
						$vendor_email = $vendor_owner instanceof WP_User ? $vendor_owner->user_email : false;
					}

					if ( ! empty( $vendor_email ) ) {
						$args = array(
							'instance'       => $instance,
							'vendor'         => $vendor,
							'current_user'   => wp_get_current_user(),
							'widget'         => $this,
							'is_singular'    => $is_singular,
							'is_vendor_page' => $is_vendor_page,
							'object'         => $object,
						);

						if ( $is_singular ) {
							$args['product'] = $product;
						}

						$this->instance = $instance;

						yith_wcmv_get_template( 'quick-info', $args, 'widgets' );
					}
				}
			}
		}

		/**
		 * Output the settings update form.
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param array $instance Current settings.
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->default );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>" value="<?php echo esc_attr( $instance['description'] ); ?>" class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'submit_label' ) ); ?>"><?php esc_html_e( 'Submit button label text', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'submit_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'submit_label' ) ); ?>" value="<?php echo esc_attr( $instance['submit_label'] ); ?>" class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_from_guests' ) ); ?>"><?php esc_html_e( 'Hide from guests', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide_from_guests' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_from_guests' ) ); ?>" value="1" <?php checked( $instance['hide_from_guests'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_in_vendor_store' ) ); ?>"><?php esc_html_e( "Show on the vendor's store page", 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_in_vendor_store' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_in_vendor_store' ) ); ?>" value="1" <?php checked( $instance['show_in_vendor_store'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_in_single' ) ); ?>"><?php esc_html_e( 'Show on the single product page', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_in_single' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_in_single' ) ); ?>" value="1" <?php checked( $instance['show_in_single'], 1 ); ?> class="widefat"/>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'cc_to_admin' ) ); ?>"><?php esc_html_e( 'Send a copy to the website owner', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'cc_to_admin' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cc_to_admin' ) ); ?>" value="1" <?php checked( $instance['cc_to_admin'], 1 ); ?> class="widefat"/>
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
			$instance                         = $old_instance;
			$instance['title']                = wp_strip_all_tags( $new_instance['title'] );
			$instance['description']          = wp_strip_all_tags( $new_instance['description'] );
			$instance['hide_from_guests']     = ( isset( $new_instance['hide_from_guests'] ) && 1 === absint( $new_instance['hide_from_guests'] ) ) ? 1 : 0; // Backward compatibility value.
			$instance['show_in_vendor_store'] = ( isset( $new_instance['show_in_vendor_store'] ) && 1 === absint( $new_instance['show_in_vendor_store'] ) ) ? 1 : 0; // Backward compatibility value.
			$instance['show_in_single']       = ( isset( $new_instance['show_in_single'] ) && 1 === absint( $new_instance['show_in_single'] ) ) ? 1 : 0; // Backward compatibility value.
			$instance['cc_to_admin']          = ( isset( $new_instance['cc_to_admin'] ) && 1 === absint( $new_instance['cc_to_admin'] ) ) ? 1 : 0; // Backward compatibility value.
			$instance['submit_label']         = wp_strip_all_tags( $new_instance['submit_label'] );

			return $instance;
		}

		/**
		 * Send the quick info form mail
		 *
		 * @since 1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function send_mail() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( $this->check_form() ) {
				// Sanitize form value.
				$vendor = yith_wcmv_get_vendor( absint( $_POST['quick_info']['vendor_id'] ) ); // phpcs:ignore
				$to     = sanitize_email( $vendor->get_meta( 'store_email' ) );

				if ( empty( $to ) ) {
					$vendor_owner = get_user_by( 'id', absint( $vendor->get_owner() ) );
					$to           = $vendor_owner instanceof WP_User ? sanitize_email( $vendor_owner->user_email ) : false;
				}

				$from_email   = sanitize_email( wp_unslash( $_POST['quick_info']['email'] ) );
				$subject      = sanitize_text_field( wp_unslash( $_POST['quick_info']['subject'] ) );
				$from         = sanitize_text_field( wp_unslash( $_POST['quick_info']['name'] ) );
				$user_message = sanitize_text_field( wp_unslash( $_POST['quick_info']['message'] ) );

				$message = sprintf(
					"%s: %s\n%s: %s\n%s:\n%s",
					_x( 'Name', 'Placeholder like "Name: Andrea', 'yith-woocommerce-product-vendors' ),
					$from,
					_x( 'Email', 'Placeholder like "Email: andrea@yithemes.com', 'yith-woocommerce-product-vendors' ),
					$from_email,
					_x( 'Message', 'Placeholder like "Message: Lorem ipsume dolor sit amet', 'yith-woocommerce-product-vendors' ),
					$user_message
				);

				$message     = nl2br( $message );
				$admin_name  = get_option( 'woocommerce_email_from_name' );
				$admin_email = get_option( 'woocommerce_email_from_address' );

				$headers['content-type'] = 'Content-type: text/html; charset="UTF-8"; format=flowed';
				$headers['from']         = sprintf( 'From: %s <%s>', $from, $from_email );
				$headers['reply']        = sprintf( 'Reply: %s <%s>', $from, $from_email );

				$widget_options = get_option( 'widget_yith-vendor-quick-info' );

				if ( $widget_options[ $this->number ]['cc_to_admin'] ) {
					if ( $admin_email && $admin_name ) {
						$headers['cc']    = "Cc: {$admin_name} <{$admin_email}>";
						$headers['reply'] = ", {$admin_name} <{$admin_email}>";
					}
				}

				$headers = apply_filters( 'yith_wcmv_widget_quick_form_email_headers', $headers );
				$message = apply_filters( 'yith_wcmv_widget_quick_form_email_message', $message );

				// Send Mail.
				$check = apply_filters( 'yith_wcmv_vendor_quick_info_email_default_check', false );

				if ( apply_filters( 'yith_wcmv_send_vendor_quick_info_email', true ) ) {
					$check = wp_mail( $to, $subject, $message, $headers );
					do_action( 'yith_wcmv_after_send_vendor_quick_info_email', $_POST );
				}

				// Prevent resubmit form.
				$url = ! empty( $_POST['quick_info']['product_id'] ) ? get_permalink( absint( $_POST['quick_info']['product_id'] ) ) : $vendor->get_url();
				unset( $_POST );

				wp_safe_redirect( esc_url( add_query_arg( array( 'message' => $check ? 1 : 0 ), $url ) ) );
				exit;
				// phpcs:enable WordPress.Security.NonceVerification
			}
		}

		/**
		 * Check form information
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return boolean
		 */
		public function check_form() {
			$check =
				! empty( $_POST['yith_vendor_quick_info_submitted'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yith_vendor_quick_info_submitted'] ) ), 'yith_vendor_quick_info_submitted' ) &&
				! empty( $_POST['quick_info'] ) &&
				! empty( $_POST['quick_info']['name'] ) &&
				! empty( $_POST['quick_info']['subject'] ) &&
				! empty( $_POST['quick_info']['email'] ) &&
				! empty( $_POST['quick_info']['message'] ) &&
				! empty( $_POST['quick_info']['vendor_id'] ) &&
				empty( $_POST['quick_info']['spam'] );

			if ( apply_filters( 'yith_wcmv_quick_info_form_validation', $check ) ) {
				$subject = sanitize_text_field( wp_unslash( $_POST['quick_info']['subject'] ) );
				// Is valid email?
				$subject_is_email = is_email( $subject );
				// Is valid url?
				$subject_is_url =
					filter_var( $subject, FILTER_VALIDATE_URL )
					||
					filter_var( $subject, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED )
					||
					filter_var( $subject, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED );

				if ( $subject_is_email || $subject_is_url ) {
					$check = false;
				}
			}

			return $check;
		}
	}
}
