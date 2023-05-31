<?php
/**
 * Custom functions
 *
 * @author  YITH
 * @package YITH\Auctions\Includes
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_wcact_product_metabox_form_field' ) ) {
	/**
	 * Print a form field for product metabox
	 *
	 * @param array $field The field.
	 * @since 2.0.0
	 */
	function yith_wcact_product_metabox_form_field( $field ) {
		$defaults = array(
			'class'     => '',
			'title'     => '',
			'label_for' => '',
			'desc'      => '',
			'data'      => array(),
			'fields'    => array(),
		);

		/**
		 * APPLY_FILTERS: yith_wcact_product_metabox_form_field_args
		 *
		 * Filter the array of the arguments for the fields in the product metabox.
		 *
		 * @param array $args  Array of arguments
		 * @param array $field Field
		 *
		 * @return array
		 */
		$field = apply_filters( 'yith_wcact_product_metabox_form_field_args', wp_parse_args( $field, $defaults ), $field );

		/**
		 * Variable information for extract
		 *
		 * @var string $class
		 * @var string $title
		 * @var string $label_for
		 * @var string $desc
		 * @var array  $data
		 * @var array  $fields
		 */
		extract( $field ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( ! $label_for && $fields ) {
			$first_field = current( $fields );

			if ( isset( $first_field['id'] ) ) {
				$label_for = $first_field['id'];
			}
		}

		$data_html = '';

		foreach ( $data as $key => $value ) {
			$data_html .= "data-{$key}='{$value}' ";
		}

		$html  = '';
		$html .= "<div class='yith-wcact-form-field {$class}' {$data_html}>";
		$html .= "<label class='yith-wcact-form-field__label' for='{$label_for}'>{$title}</label>";

		$html .= "<div class='yith-wcact-form-field__container'>";
		ob_start();
		yith_plugin_fw_get_field( $fields, true ); // Print field using plugin-fw.
		$html .= ob_get_clean();
		$html .= '</div><!-- yith-wcact-form-field__container -->';

		if ( $desc ) {
			$html .= "<div class='yith-wcact-form-field__description'>{$desc}</div>";
		}

		$html .= '</div><!-- yith-wcact-form-field -->';

		/**
		 * APPLY_FILTERS: yith_wcact_product_metabox_form_field_html
		 *
		 * Filter the HTML for the fields in the product metabox.
		 *
		 * @param string $html  Field HTML
		 * @param array  $field Field
		 *
		 * @return string
		 */
		echo apply_filters( 'yith_wcact_product_metabox_form_field_html', $html, $field ); // phpcs:ignore
	}
}

if ( ! function_exists( 'yith_wcact_field_onoff_value' ) ) {
	/**
	 * Check for onoff fields where the fields associated are set before 2.0 version
	 *
	 * @param  string     $field The field.
	 * @param  string     $dependency The dependency of the field.
	 * @param  WC_Product $product The product where apply the value.
	 * @return string    $position
	 * @since  2.0.0
	 */
	function yith_wcact_field_onoff_value( $field, $dependency, $product ) {
		$position = 'no';

		$value = $product->{"get_$field"}();

		if ( $value ) {
			$position = 'yes' === $value ? 'yes' : 'no';
		} else {
			$value_son = $product->{"get_$dependency"}();

			if ( isset( $value_son ) && $value_son > 0 ) {
				$position = 'yes';
			}
		}

		/**
		 * APPLY_FILTERS: yith_wcact_field_onoff_value_filter
		 *
		 * Filter the value of the onoff fields in the product metabox.
		 *
		 * @param string     $position   Position
		 * @param string     $field      Field
		 * @param string     $dependency Dependency
		 * @param WC_Product $product    Product object
		 * @param string     $value      Field value
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wcact_field_onoff_value_filter', $position, $field, $dependency, $product, $value );
	}
}

if ( ! function_exists( 'yith_wcact_field_radio_value' ) ) {
	/**
	 * Check for radio fields where the fields associated are set before 2.0 version
	 *
	 * @param  string     $field The field.
	 * @param  string     $dependency The dependency of the field.
	 * @param  WC_Product $product The product where apply the value.
	 * @param  string     $default_value The default where apply the value.
	 * @param  string     $dependecy_value Dependency value by default.
	 * @return string $position
	 * @since  2.0.0
	 */
	function yith_wcact_field_radio_value( $field, $dependency, $product, $default_value = '', $dependecy_value = '' ) {
		$position = isset( $default_value ) && $default_value ? $default_value : '';

		$value = $product->{"get_$field"}();

		if ( $value ) {
			$position = $value;
		} else {
			$value_son = $product->{"get_$dependency"}();

			if ( isset( $value_son ) && $value_son ) {
				$position = $dependecy_value;
			}
		}

		return $position;
	}
}

if ( ! function_exists( 'yith_wcact_get_select_time_values' ) ) {
	/**
	 * Check for radio fields where the fields associated are set before 2.0 version
	 *
	 * @return array $values
	 * @since  2.0.0
	 */
	function yith_wcact_get_select_time_values() {
		$values = array(
			'days'    => esc_html_x( 'days', 'Admin option: days', 'yith-auctions-for-woocommerce' ),
			'hours'   => esc_html_x( 'hours', 'Admin option: hours', 'yith-auctions-for-woocommerce' ),
			'minutes' => esc_html_x( 'minutes', 'Admin option: hours', 'yith-auctions-for-woocommerce' ),
		);

		return $values;
	}
}

if ( ! function_exists( 'yith_wcact_get_current_url' ) ) {
	/**
	 * Retrieves current url
	 *
	 * @return string Current url
	 * @since  2.0.0
	 */
	function yith_wcact_get_current_url() {
		global $wp;

		return add_query_arg( $wp->query_vars, home_url( $wp->request ) );
	}
}

if ( ! function_exists( 'yith_wcact_get_watchlist_url' ) ) {
	/**
	 * Retrieves watchlist url
	 *
	 * @return string watchlist url
	 * @since  2.0.0
	 */
	function yith_wcact_get_watchlist_url() {
		$my_auction_url = wc_get_endpoint_url( 'my-auction', '', wc_get_page_permalink( 'myaccount' ) );
		$watchlist_url  = add_query_arg( 'my-auction', 'watchlist', $my_auction_url );

		return $watchlist_url;
	}
}

if ( ! function_exists( 'yith_wcact_get_payment_method_url' ) ) {
	/**
	 * Retrieves payment method url
	 *
	 * @param bool $add_payment_method Add payment method.
	 * @since 2.0.0
	 *
	 * @return string watchlist url
	 */
	function yith_wcact_get_payment_method_url( $add_payment_method = false ) {
		$endpoint = 'payment-methods';

		if ( $add_payment_method ) {
			$endpoint = 'add-payment-method';
		}

		$payment_method_url = wc_get_endpoint_url( $endpoint, '', wc_get_page_permalink( 'myaccount' ) );

		return $payment_method_url;
	}
}

if ( ! function_exists( 'yith_wcact_get_dropdown' ) ) {
	/**
	 * Generate a dropdown
	 *
	 * @param  array $args Arguments to create the dropdown.
	 * @return string
	 * @since  2.0.0
	 */
	function yith_wcact_get_dropdown( $args = array() ) {
		$default_args = array(
			'id'       => '',
			'name'     => '',
			'class'    => '',
			'style'    => '',
			'options'  => array(),
			'value'    => '',
			'disabled' => '',
			'multiple' => '',
			'echo'     => false,
		);

		$args = wp_parse_args( $args, $default_args );

		/**
		 * Variable information for the extract
		 *
		 * @var string $id
		 * @var string $name
		 * @var string $class
		 * @var string $style
		 * @var array  $options
		 * @var string $value
		 * @var bool   $echo
		 * @var string $disabled
		 */
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$html = "<select id='$id' name='$name' class='$class' $multiple style='$style'>";

		foreach ( $options as $option_key => $option_label ) {
			$selected = selected( $option_key === $value, true, false );
			$disabled = disabled( $option_key === $disabled, true, false );
			$html    .= "<option value='$option_key' $selected $disabled >$option_label</option>";
		}

		$html .= '</select>';

		if ( $echo ) {
			echo $html; // phpcs:ignore
		} else {
			return $html; // phpcs:ignore
		}
	}
}

if ( ! function_exists( 'yith_wcact_auction_message' ) ) {
	/**
	 * Retrieves Auction message for ajax call
	 *
	 * @param int        $type Message type.
	 * @param WC_Product $product Product to apply the message.
	 * @return string $message
	 * @since 2.0.0
	 */
	function yith_wcact_auction_message( $type, $product = 'false' ) {
		$message = '';

		switch ( $type ) {
			case 0:
				$message = esc_html__( 'You have successfully bid', 'yith-auctions-for-woocommerce' );
				break;

			case 1:
				/* Translators: %s: amount for example 15€*/
				$message = $product && 'yes' === $product->get_auction_sealed() ? esc_html__( 'Error: there is a lower current bid for this auction. Try with a new offer', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Enter %s or less to be able to bid', 'yith-auctions-for-woocommerce' );
				break;

			case 2:
				$message = esc_html__( 'Please enter a valid bid. Negative bid are not available', 'yith-auctions-for-woocommerce' );
				break;

			case 3:
				$message = esc_html__( 'You have successfully bid but there is a higher current bid for this auction. Try with a new offer', 'yith-auctions-for-woocommerce' );
				break;

			case 4:
				/* Translators: %s: amount for example 15€*/
				$message = $product && 'yes' === $product->get_auction_sealed() ? esc_html__( 'Error: there is a higher current bid for this auction. Try with a new offer', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Enter %s or more to be able to bid', 'yith-auctions-for-woocommerce' );
				break;

			case 5:
				$message = esc_html__( 'You have successfully bid but there is a lower current bid for this auction. Try with a new offer', 'yith-auctions-for-woocommerce' );
				break;
		}

		/**
		 * APPLY_FILTERS: yith_wcact_auction_message_response
		 *
		 * Filter the message used in the AJAX call.
		 *
		 * @param string     $message Message
		 * @param int        $type    Message type
		 * @param WC_Product $product Product object
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wcact_auction_message_response', $message, $type, $product ); // phpcs:ignore
	}
}

if ( ! function_exists( 'yith_wcact_auction_compare_bids' ) ) {
	/**
	 * Compare two values with operator
	 *
	 * @param float  $first_value First value to compare.
	 * @param string $operator Logic operator to compare.
	 * @param float  $second_value Second value to compare.
	 * @return string $message
	 * @since 2.0.0
	 */
	function yith_wcact_auction_compare_bids( $first_value, $operator, $second_value ) {
		$value = false;

		switch ( $operator ) {
			case '>':
				$value = ( $first_value > $second_value );
				break;

			case '<':
				$value = ( $first_value < $second_value );
				break;
		}

		return $value;
	}
}

if ( ! function_exists( 'yith_wcact_auction_get_status_icon' ) ) {
	/**
	 * Get icon based status
	 *
	 * @param string $status Status id.
	 * @return string $icon
	 *
	 * @since 3.0.0
	 */
	function yith_wcact_auction_get_status_icon( $status ) {
		$icon = false;

		if ( $status ) {
			/**
			 * APPLY_FILTERS: yith_wcact_status_icon
			 *
			 * Filter the array with the icons for the different auction statuses.
			 *
			 * @param array $icons Icons
			 *
			 * @return array
			 */
			$available_icons = apply_filters(
				'yith_wcact_status_icon',
				array(
					'non-started'              => array(
						'icon'       => 'calendar-schedule',
						'title'      => esc_html__( 'Scheduled', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-calendar-schedule yith-wcact-icon yith-auction-non-start',
					),
					'started'                  => array(
						'icon'       => 'check-progress-circle',
						'title'      => esc_html__( 'Started', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-check-progress-circle yith-wcact-icon yith-auction-started',
					),
					'finished'                 => array(
						'icon'       => 'check',
						'title'      => esc_html__( 'Ended', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-check yith-wcact-icon yith-auction-finished',
					),
					'started-reached-reserve'  => array(
						'icon'       => 'check-progress-circle',
						'title'      => esc_html__( 'Started and not exceeded the reserve price', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-check-progress-circle yith-wcact-icon yith-auction-started-reached-reserve',
					),
					'finished-reached-reserve' => array(
						'icon'       => 'check',
						'title'      => esc_html__( 'Finished and not exceeded the reserve price', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-check yith-wcact-icon yith-auction-finished-reached-reserve',
					),
					'finnish-buy-now'          => array(
						'icon'       => 'check',
						'title'      => esc_html__( 'Purchased through buy now', 'yith-auctions-for-woocommerce' ),
						'type'       => 'action-button',
						'class'      => 'yith-wcact-auction-status yith-plugin-fw__action-button--visible',
						'icon_class' => 'yith-icon yith-icon-check yith-wcact-icon yith-auction-finnish-buy-now',
					),
				)
			);

			$current_status = $available_icons[ $status ];

			if ( $current_status ) {
				$icon = yith_plugin_fw_get_component( $current_status );
			}
		}

		return $icon;
	}
}

if ( ! function_exists( 'yith_wcact_head' ) ) {
	/**
	 * Get head for unsubscribe page
	 *
	 * @since 3.0.0
	 */
	function yith_wcact_unsubscribe_head() {
		/**
		 * DO_ACTION: yith_wcact_unsubscribe_head
		 *
		 * Allow to render some content in the header of the unsubscribe page.
		 */
		do_action( 'yith_wcact_unsubscribe_head' );
	}
}

if ( ! function_exists( 'yith_wcact_unsubscribe_body_class' ) ) {
	/**
	 * Get the unsubscribe body classes page.
	 *
	 * @return array
	 */
	function yith_wcact_unsubscribe_body_class() {
		$classes = array( 'yith-wcact-unsubscribe-page' );

		/**
		 * APPLY_FILTERS: yith_wcact_unsubscribe_body_classes
		 *
		 * Filter the array with classes for the unsubscribe body.
		 *
		 * @param array $classes CSS Classes
		 *
		 * @return array
		 */
		$classes = apply_filters( 'yith_wcact_unsubscribe_body_classes', $classes );
		$classes = array_map( 'esc_attr', $classes );

		return array_unique( $classes );
	}
}

if ( ! function_exists( 'yith_wcact_unsubscribe_footer' ) ) {
	/**
	 * POS footer.
	 */
	function yith_wcact_unsubscribe_footer() {
		/**
		 * DO_ACTION: yith_wcact_unsubscribe_footer
		 *
		 * Allow to render some content in the footer of the unsubscribe page.
		 */
		do_action( 'yith_wcact_unsubscribe_footer' );
	}
}


if ( ! function_exists( 'yith_wcact_get_label' ) ) {
	/**
	 * Get default label.
	 *
	 * @param string $key Key for return the default label.
	 * @return string
	 */
	function yith_wcact_get_label( $key ) {
		$label  = '';
		$labels = array(
			'default_commission_fee'   => __( 'Commission fee', 'yith-auctions-for-woocommerce' ),
			'multiple_commissions_fee' => __( 'Commission\'s fee', 'yith-auctions-for-woocommerce' ),
		);

		if ( isset( $labels[ $key ] ) ) {
			$label = $labels[ $key ];
		}

		/**
		 * APPLY_FILTERS: yith_wcac_get_default_label
		 *
		 * Filter the default label.
		 *
		 * @param string $label  Label
		 * @param string $key    Label key
		 * @param array  $labels Array of labels
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wcac_get_default_label', $label, $key, $labels );
	}
}

if ( ! function_exists( 'yith_wcact_default_automatic_charge_notice_stripe' ) ) {
	/**
	 * Get default label.
	 *
	 * @return string
	 */
	function yith_wcact_default_automatic_charge_notice_stripe() {
		$notice = implode(
			'<br />',
			array(
				esc_html__( 'By adding a credit card you authorize us to charge it for the costs of the item won and all related charges including optional fees, shipping cost and taxes.', 'yith-auctions-for-woocommerce' ),
				esc_html__( 'The debit will take place at the end of the auction.', 'yith-auctions-for-woocommerce' ),

			)
		);

		return $notice;
	}
}
if ( ! function_exists( 'yith_wcact_default_force_notice_stripe' ) ) {
	/**
	 * Get default label.
	 *
	 * @return string
	 */
	function yith_wcact_default_force_notice_stripe() {
		$site_title = get_bloginfo( 'name' );

		$notice = implode(
			'<br />',
			array(
				wp_kses_post( '<b>' . __( 'Why do I have to enter credit card details to make an offer?', 'yith-auctions-for-woocommerce' ) . '</b>' ),
				// translators: %s is the site title.
				sprintf( esc_html__( 'To ensure the safety and authenticity of all offers, %s requires registration of a credit or debit card through Stripe.', 'yith-auctions-for-woocommerce' ), $site_title ),
				esc_html__( 'Stripe will store your payment details in a safe and secure process and your future payments - in case of winnings - will be encrypted.', 'yith-auctions-for-woocommerce' ),

			)
		);

		return $notice;
	}
}

if ( ! function_exists( 'ywcact_create_order' ) ) {
	/**
	 * Create an order on fly for auction products
	 *
	 * @param WC_Product $product Auction product.
	 * @param int        $user_id Winner user id.
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 * @since  3.0.0
	 * @return mixed
	 */
	function ywcact_create_order( $product, $user_id ) {
		$order_created = 0;

		$order_data = array(
			/**
			 * APPLY_FILTERS: yith_wcact_default_order_status
			 *
			 * Filter the default status for the order created automatically for the auction.
			 *
			 * @param string $default_order_status Default order status
			 *
			 * @return string
			 */
			'status'      => apply_filters( 'yith_wcact_default_order_status', 'pending' ),
			'customer_id' => $user_id,
			'created_via' => 'yith_auction',

		);

		$order = wc_create_order( $order_data );

		if ( $order ) {
			$order_id = $order->get_id();

			do_action( 'woocommerce_new_order', $order_id );

			$item_id = $order->add_product(
				$product,
				1
			);

			if ( ! $item_id ) {
				return false;
			}

			/**
			 * The order Item.
			 *
			 * @var WC_Order_Item_Product $item
			 */
			$item = $order->get_item( $item_id );

			// Allow plugins to add order item meta.
			do_action( 'woocommerce_new_order_item', $item->get_id(), $item, $item->get_order_id() );

			$price = $item->get_total();

			// Add also auction fee.
			$commission_fee = yith_wcact_calculate_commission_fee( $product, $price );

			if ( is_array( $commission_fee ) && ! empty( $commission_fee ) ) {
				$fee = new WC_Order_Item_Fee();
				$fee->set_amount( $commission_fee['value'] );
				$fee->set_total( $commission_fee['value'] );
				$fee->set_name( $commission_fee['label'] );

				$order->add_item( $fee );
			}

			// Set address.

			$customer = new WC_Customer( $user_id );

			$billing_address  = array(
				'first_name' => $customer->get_billing_first_name(),
				'last_name'  => $customer->get_billing_last_name(),
				'company'    => $customer->get_billing_company(),
				'address_1'  => $customer->get_billing_address_1(),
				'address_2'  => $customer->get_billing_address_2(),
				'city'       => $customer->get_billing_city(),
				'state'      => $customer->get_billing_state(),
				'postcode'   => $customer->get_billing_postcode(),
				'country'    => $customer->get_billing_country(),
				'email'      => $customer->get_billing_email(),
				'phone'      => $customer->get_billing_phone(),
			);
			$shipping_address = array(
				'first_name' => $customer->get_shipping_first_name(),
				'last_name'  => $customer->get_shipping_last_name(),
				'company'    => $customer->get_shipping_company(),
				'address_1'  => $customer->get_shipping_address_1(),
				'address_2'  => $customer->get_shipping_address_2(),
				'city'       => $customer->get_shipping_city(),
				'state'      => $customer->get_shipping_state(),
				'postcode'   => $customer->get_shipping_postcode(),
				'country'    => $customer->get_shipping_country(),
			);

			$order->set_address( $billing_address, 'billing' );
			$order->set_address( $shipping_address, 'shipping' );

			$order->calculate_totals();

			/**
			 * DO_ACTION: yith_wcact_before_order_save
			 *
			 * Allow to fire some action before saving the order created for the auction product.
			 *
			 * @param int   $order_id Order ID
			 * @param array $args     Array or arguments
			 */
			do_action( 'yith_wcact_before_order_save', $order_id, array() );

			$order->save();

			/**
			 * DO_ACTION: yith_wcact_after_order_save
			 *
			 * Allow to fire some action after saving the order created for the auction product.
			 *
			 * @param int   $order_id Order ID
			 * @param array $args     Array or arguments
			 */
			do_action( 'yith_wcact_after_order_save', $order_id, array() );

			$order_created = $order_id;
		}

		return $order_created;
	}
}

if ( ! function_exists( 'ywcact_logs' ) ) {
	/**
	 * Display logs message
	 *
	 * @param string $message Log message.
	 */
	function ywcact_logs( $message ) {
		/**
		 * APPLY_FILTERS: yith_wcact_show_logs
		 *
		 * Filter whether to show the logs created by the plugin.
		 *
		 * @param bool $show_logs Whether to show logs or not
		 *
		 * @return bool
		 */
		if ( apply_filters( 'yith_wcact_show_logs', false ) ) {
			error_log( print_r( $message, true ) ); // phpcs:ignore
		}
	}
}
