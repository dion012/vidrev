<?php
/**
 * Auction winner options tab.
 *
 * @package YITH\Auctions
 * @since   3.0.0
 * @author  YITH
 */

$resend_winner_email_url = add_query_arg( array( 'yith-wcact-action-resend-email' => 'send_auction_winner_email' ) );
$yith_wcstripe_landing   = 'https://yithemes.com/themes/plugins/yith-woocommerce-stripe/';
$stripe_enabled          = defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM;

/**
 * APPLY_FILTERS: yith_wcact_general_options_auction_winner
 *
 * Filter the options available in the General Auctions Payments tab.
 *
 * @param array $general_auction_payments General auctions payments options
 *
 * @return array
 */
return apply_filters(
	'yith_wcact_general_options_auction_winner',
	array(
		'general-auctions-winner' => array(
			// Verify payment method of bidders.
			'settings_verify_payment_method_start'        => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_verify_payment_method_start',
			),
			'settings_verify_payment_method_title'        => array(
				'name' => __( 'Auctions payment - Stripe options', 'yith-auctions-for-woocommerce' ),
				'type' => 'title',
				/* translators: %1$s: Link landing page. %2$2s: Plugin name */
				'desc' => ( ! defined( 'YITH_WCSTRIPE_PREMIUM' ) ) ? sprintf( __( 'To enable this option you need to install our <a href="%1$s" target="_blank">%2$2s</a> plugin.', 'yith-auctions-for-woocommerce' ), $yith_wcstripe_landing, 'YITH WooCommerce Stripe Premium' ) : '',
				'id'   => 'yith_wcact_settings_verify_payment_method_title',
			),
			'settings_verify_payment_method_integration'  => array(
				'name'            => esc_html__( 'Force users to add a credit card before to bid', 'yith-auctions-for-woocommerce' ),
				'desc'            => esc_html__( 'Enable this option to check if a customer has at least one card saved in the My account > Payment method section. Without at least one card saved, the customer will not be able to bid on an auction.', 'yith-auctions-for-woocommerce' ),
				'id'              => 'yith_wcact_verify_payment_method',
				'default'         => 'no',
				'type'            => 'yith-field',
				'extra_row_class' => $stripe_enabled ? '' : 'yith-disabled',
				'yith-type'       => 'onoff',
			),
			'settings_force_credit_card_message_integration' => array(
				'title'           => esc_html__( 'Notice to show in Payment Method section', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'textarea-editor',
				'media_buttons'   => false,
				'desc'            => esc_html__( 'Enter a text to explain your bidder why they have to add a credit card before to bid', 'yith-auctions-for-woocommerce' ),
				'id'              => 'yith_wcact_stripe_note_force_users_notice',
				'extra_row_class' => $stripe_enabled ? '' : 'yith-disabled',
				'default'         => yith_wcact_default_force_notice_stripe(),
				'deps'            => array(
					'id'    => 'yith_wcact_verify_payment_method',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_charge_automatically_auction_price_to_winner_integration' => array(
				'name'            => esc_html__( 'Charge automatically the auction price to winner\'s credit card', 'yith-auctions-for-woocommerce' ),
				'desc'            => esc_html__( 'Enable to automatically charge the auction price to winner\'s credit card', 'yith-auctions-for-woocommerce' ),
				'id'              => 'yith_wcact_stripe_charge_automatically_price',
				'default'         => 'no',
				'type'            => 'yith-field',
				'extra_row_class' => $stripe_enabled ? '' : 'yith-disabled',
				'yith-type'       => 'onoff',
				'deps'            => array(
					'id'    => 'yith_wcact_verify_payment_method',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_charge_automatically_auction_price_to_winner_message_integration' => array(
				'title'           => esc_html__( 'Notice of automatic charge to show in Payment Method section', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'textarea-editor',
				'media_buttons'   => false,
				'desc'            => esc_html__( 'Enter a text to alert your bidder that the item price will be automatically charged in his credit card, in case of winning the auction', 'yith-auctions-for-woocommerce' ),
				'id'              => 'yith_wcact_stripe_note_automatic_charge',
				'extra_row_class' => $stripe_enabled ? '' : 'yith-disabled',
				'default'         => yith_wcact_default_automatic_charge_notice_stripe(),
			),
			'settings_verify_payment_method_end'          => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_verify_payment_method_end',
			),
			// End Verify payment method of bidders.
			// Auction winner options.
			'settings_auction_winner_options_start'       => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_auction_winner_options_start',
			),
			'settings_auction_winner_options_title'       => array(
				'title' => esc_html_x( 'Auction winner options', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_settings_auction_winner_options_title',
			),
			'settings_auction_winner_automatically_create_an_order' => array(
				'title'     => esc_html__( 'Automatically create a “Pending payment” order assigned to winner', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to automatically create an order in status “Pending Payment” assigned to auction’s winner.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_auction_winner_create_order',
				'default'   => 'no',
				'class'     => 'yith-wcact-deps-charge-automatically',
			),
			'settings_auction_winner_redirect_auction_winner' => array(
				'title'     => esc_html__( 'Within the email notification, redirect the auction\'s winner', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'product'  => esc_html__( 'to auction page', 'yith-auctions-for-woocommerce' ),
					'cart'     => esc_html__( 'to cart page (With auction product in cart)', 'yith-auctions-for-woocommerce' ),
					'checkout' => esc_html__( 'to checkout page', 'yith-auctions-for-woocommerce' ),
				),
				'default'   => 'checkout',
				'desc'      => '',
				'id'        => 'yith_wcact_settings_auction_winner_email_redirect',
				'class'     => 'yith-wcact-deps-charge-automatically yith-wcact-deps-winner-create-order',
			),
			'settings_auction_winner_show_label_button'   => array(
				'title'     => esc_html__( 'Button label in email notification', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => esc_html__( 'Enter the label for the button shown in the email sent to auction\'s winner', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_auction_winner_label_pay_now',
				'default'   => esc_html__( 'Pay now', 'yith-auctions-for-woocommerce' ),
				'class'     => 'yith-wcact-deps-charge-automatically yith-wcact-deps-winner-create-order',
			),
			'settings_auction_winner_show_winner_badge_option' => array(
				'title'     => esc_html__( 'Show winner badge on auction image', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show a winner badge on auction image to the user who won the auction', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_auction_winner_show_winner_badge',
				'default'   => 'no',
			),
			'settings_auction_winner_show_winner_badge_custom' => array(
				'name'      => esc_html_x(
					'Winner badge',
					'Admin option: Winner badge',
					'yith-auctions-for-woocommerce'
				),
				'type'      => 'yith-field',
				'yith-type' => 'upload',
				'id'        => 'yith_wcact_winner_badge_custom',
				'default'   => YITH_WCACT_ASSETS_URL . 'images/icon/winner-logo.svg',
				'deps'      => array(
					'id'    => 'yith_wcact_auction_winner_show_winner_badge',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_auction_winner_show_winner_message_optional' => array(
				'title'     => esc_html_x( 'Custom message to the winner', 'Admin option: Custom message to winner.', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'textarea-editor',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enter an optional message to show to the user who won the auction.', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'You can use the following placeholders:', 'yith-auctions-for-woocommerce' ),
						/* translators: %username%: Username of maximum bid. */
						esc_html__( '%username% => Username of maximum bid.', 'yith-auctions-for-woocommerce' ),
						esc_html__( '%price% => Final price of auction product. ', 'yith-auctions-for-woocommerce' ),
					)
				),
				'wpautop'   => false,
				'id'        => 'yith_wcact_auction_winner_custom_message',
				'default'   => implode(
					'<br />',
					array(
						esc_html__( 'You won this auction with the final price of %price%', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'Be sure to pay for the product within 3 days to avoid losing it!', 'yith-auctions-for-woocommerce' ),
					)
				),
			),
			'settings_auction_winner_show_button_pay_now' => array(
				'title'     => esc_html_x( 'Show \'Pay Now\' button in the product page', 'Admin option: Show button pay now in product page', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html_x( 'Check this option to show \'Pay Now\' button in the product when the auction ends', 'Admin option description: Check this option to show \'Pay Now\' button in the product when the auction ends ', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_tab_auction_show_button_pay_now',
				'default'   => 'yes',
			),
			'settings_auction_winner_show_button_add_to_cart_instead_of_pay_now' => array(
				'title'     => esc_html_x( 'Possibility to add auction product with other products ', 'Admin option: Possibility to add to cart auction product', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html_x( 'Check this option to allow adding auction products and other product types to cart in the same order', 'Admin option description: Check this option to show pay now button in product when the auction ends', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_tab_auction_show_add_to_cart_in_auction_product',
				'default'   => 'no',
			),
			'settings_resend_winner_auction_options_button' => array(
				'title' => esc_html__( 'Resend winners email', 'yith-auctions-for-woocommerce' ),
				'id'    => 'yith_wcact_settings_resend_winner_auction_button',
				'desc'  => esc_html__( 'Click to resend the email to the winner in case the sending failed', 'yith-auctions-for-woocommerce' ),
				'type'  => 'yith_wcact_html',
				'html'  => '<a class="button-primary" href="' . $resend_winner_email_url . '">' . esc_html__( 'Resend email', 'yith-auctions-for-woocommerce' ) . '</a>',
			),
			'settings_charge_commission_fee_to_winners'   => array(
				'title'     => esc_html__( 'Charge a commission fee to winners of auction products', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to require the payment of an extra commission fee to users that win an auction', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_general_charge_commission_fee_onoff',
				'default'   => 'no',
				'class'     => 'ywcact_charge_commission_fee_to_winners',
			),
			'settings_charge_commission_default_fee'      => array(
				'id'        => 'yith_wcact_general_default_commission_fee',
				'name'      => 'yith_wcact_general_default_commission_fee',
				'type'      => 'yith-field',
				'yith-type' => 'inline-fields',
				'title'     => esc_html__( 'Default commission fee', 'yith-auctions-for-woocommerce' ),
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Set the default commission fee for all auctions winners.', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'This value can be overwritten in all auction products if you want to set a different amount for a specific auction.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'fields'    => array(
					'value' => array(
						'std'  => '150',
						'type' => 'number',
						'min'  => 0,
					),
					'unit'  => array(
						'std'     => 'px',
						'type'    => 'select',
						'options' => array(
							'fixed'      => get_woocommerce_currency_symbol() . ' - ' . esc_html__( 'Fixed price', 'yith-auctions-for-woocommerce' ),
							'percentage' => esc_html__( '% of winner bid', 'yith-auctions-for-woocommerce' ),
						),
						'class'   => 'wc-enhanced-select',
					),
				),
				'default'   => array(
					'value' => '',
					'unit'  => 'fixed',
				),
				'class'     => 'yith-wcact-default-commision-fee',
				'deps'      => array(
					'id'    => 'yith_wcact_general_charge_commission_fee_onoff',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			/* == Show Commissions == */
			'settings_show_commissions_in_the_auction_product_page' => array(
				'name'      => esc_html__( 'Show commission', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'all'           => esc_html__( 'In product page, Cart & Checkout', 'yith-auctions-for-woocommerce' ),
					'cart-checkout' => esc_html__( 'Only in Cart & Checkout', 'yith-auctions-for-woocommerce' ),
				),
				'default'   => 'all',
				'id'        => 'yith_wcact_general_show_commission_fee',
				'desc'      => esc_html__( 'Choose where to show the commission fee info', 'yith-auctions-for-woocommerce' ),
				'deps'      => array(
					'id'    => 'yith_wcact_general_charge_commission_fee_onoff',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_show_commission_label'              => array(
				'title'             => esc_html__( 'Default commission label', 'yith-auctions-for-woocommerce' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'desc'              => esc_html__( 'Enter a label to identify the commission in checkout and product page', 'yith-auctions-for-woocommerce' ),
				'id'                => 'yith_wcact_general_single_commission_label',
				'deps'              => array(
					'id'    => 'yith_wcact_general_charge_commission_fee_onoff',
					'value' => 'yes',
					'type'  => 'hide',
				),
				'custom_attributes' => array(
					'placeholder' => yith_wcact_get_label( 'default_commission_fee' ),
				),
			),
			'settings_show_multiple_commission_label'     => array(
				'title'             => esc_html__( 'Multiple commissions label', 'yith-auctions-for-woocommerce' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'desc'              => esc_html__( 'Enter a label to identify the total amount of multiple commissions in cart and checkout page', 'yith-auctions-for-woocommerce' ),
				'id'                => 'yith_wcact_general_multiple_commissions_label',
				'deps'              => array(
					'id'    => 'yith_wcact_general_charge_commission_fee_onoff',
					'value' => 'yes',
					'type'  => 'hide',
				),
				'custom_attributes' => array(
					'placeholder' => yith_wcact_get_label( 'multiple_commissions_fee' ),
				),
			),
			'settings_auction_winner_options_end'         => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_auction_winner_options_end',
			),
			// End Auction winner options.
		),
	)
);
