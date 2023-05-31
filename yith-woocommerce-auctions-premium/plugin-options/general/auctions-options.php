<?php
/**
 * Auction options tab.
 *
 * @package YITH\Auctions
 * @since   3.0.0
 * @author  YITH
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$yith_wceasy_landing = 'https://yithemes.com/themes/plugins/yith-easy-login-register-popup-for-woocommerce/';
$unsubscribe_page_id = get_option( 'yith_wcact_unsubscribe_page' );
$unsubscribe_page    = ( $unsubscribe_page_id ) ? get_edit_post_link( $unsubscribe_page_id ) : '';

/**
 * APPLY_FILTERS: yith_wcact_general_options_auction_options
 *
 * Filter the options available in the General Auction Options tab.
 *
 * @param array $general_auction_options General auction options
 *
 * @return array
 */
return apply_filters(
	'yith_wcact_general_options_auction_options',
	array(
		'general-auctions' => array(
			'settings_options_start'                      => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_options_start',
			),
			'settings_options_title'                      => array(
				'title' => esc_html_x( 'General options', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_settings_options_title',
			),
			'settings_show_auctions_shop_page'            => array(
				'title'     => esc_html_x( 'Show auctions on the shop page', 'Admin option: Show auctions on the shop page', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enable to show auction products in the shop page.', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'If disabled, all auctions will be shown only in the page with the auction shortcode.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'id'        => 'yith_wcact_show_auctions_shop_page',
				'default'   => 'yes',
			),
			'settings_hide_auctions_out_of_stock'         => array(
				'title'     => esc_html_x( 'Hide out-of-stock auctions', 'Admin option: Hide out-of-stock auctions', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html_x( 'Enable to hide out-of-stock auctions in the shop pages', 'Admin option description: Enable to hide out-of-stock auctions in shop pages', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_hide_auctions_out_of_stock',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wcact_show_auctions_shop_page',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_hide_auctions_closed'               => array(
				'title'     => esc_html_x( 'Hide ended auctions', 'Admin option: Hide ended auctions', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html_x( 'Enable to hide ended auctions in the shop page', 'Admin option description: Enable to hide ended auctions in shop page', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_hide_auctions_closed',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wcact_show_auctions_shop_page',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_hide_auctions_not_started'          => array(
				'title'     => esc_html_x( 'Hide future auctions', 'Admin option: Hide future auctions', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html_x( 'Enable to hide auctions, that have not yet started, in the shop page', 'Admin option description: Enable to hide auctions not started yet in shop page', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_hide_auctions_not_started',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wcact_show_auctions_shop_page',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_show_countdown_in_loop'             => array(
				'title'     => esc_html__( 'Show countdown in loop', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enable to show auctions countdown or end time also in the shop pages.', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'If disabled, countdown will only be shown in the auction page.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'id'        => 'yith_wcact_show_countdown_in_loop',
				'default'   => 'no',
			),
			'settings_hide_buy_now_bid_exceed_buy_now_price' => array(
				'title'     => esc_html__( 'Hide \'Buy Now\' when a bid exceeds the buy now price', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enable to hide \'Buy Now\' button when a user bids an amount that exceeds the', 'yith-auctions-for-woocommerce' ),
						esc_html__( '\'Buy Now\' price.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'id'        => 'yith_wcact_settings_hide_buy_now_price_exceed',
				'default'   => 'no',
			),
			'settings_hide_buy_now_after_first_bid'       => array(
				'title'     => esc_html__( 'Hide \'Buy Now\' after the first bid', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to hide \'Buy Now\' button when a user places the first bid.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_hide_buy_now_after_first_bid',
				'default'   => 'no',
			),
			'settings_set_bid_type'                       => array(
				'title'     => esc_html__( 'Set bid type', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'manual'    => esc_html__( 'Manual', 'yith-auctions-for-woocommerce' ),
					'automatic' => esc_html__( 'Automatic', 'yith-auctions-for-woocommerce' ),
				),
				'default'   => 'manual',
				'id'        => 'yith_wcact_settings_bid_type',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'With the automatic bidding, the user enters the maximum amount it\'s willing to pay for the item.', 'yith-auctions-for-woocommerce' ),
						esc_html_x( 'The system will automatically bid, with the smallest amount possible each time, until the user\'s maximum limit has been reached.', 'The system will automatically bid for him with the smallest amount posible every time, once his maximun limit is reached', 'yith-auctions-for-woocommerce' ),
					)
				),
			),
			'settings_automatic_bid_type'                 => array(
				'title'     => esc_html__( 'Automatic bid type', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'simple'   => esc_html__( 'Simple', 'yith-auctions-for-woocommerce' ),
					'advanced' => esc_html__( 'Advanced', 'yith-auctions-for-woocommerce' ),
				),
				'default'   => 'simple',
				'id'        => 'yith_wcact_settings_automatic_bid_type',
				'desc'      => implode(
					'<br />',
					array(
						esc_html_x( 'With the simple type you can set only one bid increment amount, independently from the current bid value.', 'The system will automatically bid for him with the smallest amount posible every time, once his maximun limit is reached.', 'yith-auctions-for-woocommerce' ),
						esc_html__( 'With the advanced type you can set different automatic bid increments based on the current bid value.', 'yith-auctions-for-woocommerce' ),
					)
				),
				'deps'      => array(
					'id'    => 'yith_wcact_settings_bid_type',
					'value' => 'automatic',
					'type'  => 'hide',
				),
			),
			'settings_automatic_bid_increment'            => array(
				/* translators: %s: Currency symbol */
				'title'           => sprintf( esc_html_x( 'Automatic bid increment (%s)', 'Automatic bid increment ($)', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ),
				'id'              => 'yith_wcact_settings_automatic_bid_increment',
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'automatic-bid-increment',
				'desc'            => esc_html__( 'Set the bidding increment for automatic bidding. You can create more rules to set different bid increments based on the auction\'s current bid and then set a last rule to cover all the offers made after the last current bid step ', 'yith-auctions-for-woocommerce' ),
				'action'          => 'yith_wcact_general_custom_fields',
				'deps'            => array(
					'id'    => 'yith_wcact_settings_bid_type',
					'value' => 'automatic',
					'type'  => 'hide',
				),
			),
			'settings_show_bid_increment_in_the_page'     => array(
				'title'     => esc_html__( 'Show the bid increments in the page', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show the automatic bid increment info on the page.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_show_bid_increment_in_the_page',
				'default'   => 'no',
			),
			'settings_show_automatic_bidding_modal_info'  => array(
				'title'     => esc_html__( 'Show automatic bidding modal info', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show a modal that explains how automatic bidding works.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_show_automatic_bidding_modal_info',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wcact_settings_bid_type',
					'value' => 'automatic',
					'type'  => 'hide',
				),
			),
			'settings_ask_confirmation_before_to_bid'     => array(
				'title'     => esc_html__( 'Ask for approval before a bid is confirmed', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'If enabled, bidders will see a modal window that asks them to confirm the bid before publishing it.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_ask_confirmation_before_to_bid',
				'default'   => 'no',
			),
			'settings_ask_fee_payment_before_to_bid'      => array(
				'title'     => esc_html__( 'Ask fee payment before bidding', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to ask users to pay a fee before placing a bid.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_ask_fee_before_to_bid',
				'default'   => 'no',
			),
			'settings_default_fee_amount'                 => array(
				/* translators: %s: Currency symbol */
				'title'     => sprintf( esc_html_x( 'Default fee amount (%s)', 'Default fee amount ($)', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ),
				'id'        => 'yith_wcact_settings_fee_amount',
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'default'   => 10,
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Set the default fee that users have to pay before bidding.', 'yith-auctions-for-woocommerce' ),
						esc_html_x( 'This value can be overwritten in all auction products if you want to set a different fee for a specific auction. ', 'This value can be overwrite in all auction products if you want to set a different fee for a specific auction. ', 'yith-auctions-for-woocommerce' ),
					)
				),
				'deps'      => array(
					'id'    => 'yith_wcact_settings_ask_fee_before_to_bid',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_set_overtime'                       => array(
				'title'     => esc_html__( 'Set Overtime', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to extend the auction duration if someone places a bid when the auction is about to end.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_set_overtime',
				'default'   => 'no',
			),
			'settings_set_overtime_values'                => array(
				'id'              => 'yith_wcact_settings_set_overtime_values',
				'title'           => esc_html__( 'Overtime settings', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'general-overtime',
				'desc'            => esc_html__( 'Set the overtime rule when the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'action'          => 'yith_wcact_general_custom_fields',
				'deps'            => array(
					'id'    => 'yith_wcact_settings_set_overtime',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_show_higher_bidder_modal'           => array(
				'title'     => esc_html__( 'Show higher bidder modal', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to show a modal to the highest bidder to suggest him to refresh the page.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_show_higher_bidder_modal',
				'default'   => 'no',
			),
			'settings_enable_watchlist'                   => array(
				'title'     => esc_html__( 'Enable watchlist', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to allow logged in users to create a watchlist with auctions they are interested in.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_enable_watchlist',
				'default'   => 'no',
			),
			'settings_options_end'                        => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_options_end',
			),
			// CRON SETTINGS AND USER NOTIFICATIONS.
			'settings_cron_auction_options_start'         => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_cron_auction_start',
			),
			'settings_cron_auction_options_title'         => array(
				'title' => esc_html_x( 'Cron settings & user notifications', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_settings_cron_auction_title',
			),
			'settings_cron_auction_users_follow_auction'  => array(
				'title'     => esc_html__( 'Allow users to follow auctions', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'If enabled, users can leave their email in the auction page and receive a notification when auction is about to end', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_tab_auction_allow_subscribe',
				'default'   => 'no',
			),
			'settings_show_privacy_field'                 => array(
				'title'     => esc_html__( 'Show Privacy field', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Show "Privacy" checkbox on subscribe form', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_show_privacy_field',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wcact_settings_tab_auction_allow_subscribe',
					'value' => 'yes',
				),
			),
			'settings_privacy_checkbox_text'              => array(
				'title'         => esc_html__( 'Privacy checkbox text', 'yith-auctions-for-woocommerce' ),
				'type'          => 'yith-field',
				'yith-type'     => 'textarea-editor',
				'media_buttons' => false,
				'desc'          => esc_html__( 'Enter the privacy checkbox text', 'yith-auctions-for-woocommerce' ),
				'id'            => 'yith_wcact_privacy_checkbox_text',
				'default'       => '',
				'deps'          => array(
					'id'    => 'yith_wcact_show_privacy_field',
					'value' => 'yes',
				),
				'class'         => 'yith_wcact_privacy_fields',
			),
			/* == DEV CREATE DEPS PROGRAMATICALLY == */
			'settings_send_email_to_followers_new_bids'   => array(
				'name'          => esc_html__( 'Send email to followers', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'id'            => 'yith_wcact_notify_followers_on_new_bids',
				'desc'          => esc_html__( 'To notify any new bid in the auction they follow', 'yith-auctions-for-woocommerce' ),
				'default'       => 'yes',
			),
			'settings_send_email_to_followers_about_to_end' => array(
				'name'          => esc_html__( 'When the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'id'            => 'yith_wcact_notify_followers_auction_about_to_end',
				'desc'          => esc_html__( 'When the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'default'       => 'yes',
			),
			'settings_send_email_to_followers_closed_by_buy_now' => array(
				'name'          => esc_html__( 'When the auction is closed by buy now', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'id'            => 'yith_wcact_notify_followers_auction_closed_by_buy_now',
				'desc'          => esc_html__( 'When the auction is closed by buy now', 'yith-auctions-for-woocommerce' ),
				'default'       => 'no',
			),
			/* == =========================== == */
			'settings_send_email_to_bidders_any_new_bid'  => array(
				'name'          => esc_html__( 'Send email to bidders', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'id'            => 'yith_wcact_email_bidders_new_bid',
				'desc'          => esc_html__( 'To notify any new bid', 'yith-auctions-for-woocommerce' ),
				'default'       => 'yes',
			),
			'settings_send_email_to_bidders_auction_about_to_end' => array(
				'name'          => esc_html__( 'When the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'id'            => 'yith_wcact_settings_cron_auction_send_emails',
				'desc'          => esc_html__( 'When the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'default'       => 'yes',
			),
			'settings_send_email_to_bidders_auction_lose' => array(
				'name'          => esc_html__( 'When they lose the auction', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'id'            => 'yith_wcact_settings_tab_auction_no_winner_email',
				'desc'          => esc_html__( 'When they lose the auction', 'yith-auctions-for-woocommerce' ),
				'default'       => 'yes',
			),
			'settings_send_email_to_bidders_auction_closed_by_buy_now' => array(
				'name'          => esc_html__( 'When the auction is closed by buy now', 'yith-auctions-for-woocommerce' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'id'            => 'yith_wcact_email_bidders_closed_by_buy_now',
				'desc'          => esc_html__( 'When the auction is closed by buy now', 'yith-auctions-for-woocommerce' ),
				'default'       => 'no',
			),
			'settings_cron_send_email_to_bidders'         => array(
				'id'              => 'yith_wcact_settings_cron_send_email_to_bidders',
				'title'           => esc_html__( 'Send notifications to notify the ending auction', 'yith-auctions-for-woocommerce' ),
				'type'            => 'yith-field',
				'yith-type'       => 'custom',
				'yith-wcact-type' => 'send-email-bidders',
				'desc'            => esc_html__( 'Set when to send the email to notify bidders and followers that the auction is about to end', 'yith-auctions-for-woocommerce' ),
				'action'          => 'yith_wcact_general_custom_fields',
			),
			'settings_cron_show_unsubscribe_link'         => array(
				'id'        => 'yith_wcact_display_unsubscribe_link',
				'title'     => esc_html__( 'Show unsubscribe link in email notifications', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => implode(
					'<br />',
					array(
						esc_html__( 'Enable to add an unsubscribe link in the email notifications for bidders and followers.', 'yith-auctions-for-woocommerce' ),
						/* translators: %1$s: Link unsubscribe page*/
						sprintf( esc_html__( 'They will be redirected to the "Unsubscribe" page that you can find and customize in %s', 'yith-auctions-for-woocommerce' ), '<a href="' . esc_url( $unsubscribe_page ) . '" target="_blank">' . esc_html__( 'Pages', 'yith-auctions-for-woocommerce' ) . '</a>' ),
					)
				),
				'default'   => 'yes',
			),
			'settings_cron_show_unsubscribe_link_text'    => array(
				'id'        => 'yith_wcact_unsubscribe_link_text',
				'title'     => esc_html__( 'Unsubscribe link label', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => esc_html__( 'Set the label for the Unsubscribe link in email notifications', 'yith-auctions-for-woocommerce' ),
				'default'   => esc_html__( 'Unsubscribe', 'yith-auctions-for-woocommerce' ),
				'deps'      => array(
					'id'    => 'yith_wcact_display_unsubscribe_link',
					'value' => 'yes',
				),
			),
			'settings_cron_auction_options_end'           => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_cron_auction_end',
			),
			// End CRON Settings.
			// Ajax Refresh.
			'settings_ajax_refresh_auction_options_start' => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_live_auction_start',
			),
			'settings_ajax_refresh_auction_options_title' => array(
				'title' => esc_html_x( 'Ajax Refresh', 'Panel: page title', 'yith-auctions-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcact_settings_live_auction_title',
			),
			'settings_ajax_refresh_auction_product_page_onoff' => array(
				'title'     => esc_html__( 'Automatically refresh the auction page in Ajax', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to automatically refresh the auction page in Ajax. In this way, the users will see the auction updates without having to reload the page.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_ajax_refresh_auction_product_page',
				'default'   => 'no',
			),
			'settings_ajax_refresh_auction_product_page'  => array(
				'title'     => esc_html__( 'Refresh auction page each (seconds)', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'number',
				'class'     => 'ywcact-input-text',
				'desc'      => esc_html__( 'Set how often to automatically refresh the auction page', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_live_auction_product_page',
				'step'      => 1,
				'min'       => 0,
				'default'   => 0,
				'deps'      => array(
					'id'    => 'yith_wcact_ajax_refresh_auction_product_page',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_ajax_refresh_auction_my_auctions_onoff' => array(
				'title'     => esc_html__( 'Automatically refresh info in My Account > My auctions in Ajax', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => esc_html__( 'Enable to automatically refresh the "My auctions" section in "My account" in Ajax. In this way, the users will see the auction updates before reloading the page.', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_ajax_refresh_auction_my_acutions_page',
				'default'   => 'no',
			),
			'settings_ajax_refresh_auction_my_auctions'   => array(
				'title'     => esc_html_x( 'Refresh "My auctions" section each (seconds)', 'Admin option: Overtime', 'yith-auctions-for-woocommerce' ),
				'type'      => 'yith-field',
				'yith-type' => 'number',
				'class'     => 'ywcact-input-text',
				'desc'      => esc_html__( 'Set how often to automatically refresh the "My Auctions" section', 'yith-auctions-for-woocommerce' ),
				'id'        => 'yith_wcact_settings_live_auction_my_auctions',
				'step'      => 1,
				'min'       => 0,
				'default'   => 0,
				'deps'      => array(
					'id'    => 'yith_wcact_ajax_refresh_auction_my_acutions_page',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			'settings_ajax_refresh_auction_options_end'   => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_regenerate_auction_end',
			),
			// End Ajax Refresh.
			// Enable Easy Login Popup on auction page.
			'settings_login_start'                        => array(
				'type' => 'sectionstart',
				'id'   => 'yith_wcact_settings_login_start',
			),
			'settings_login_title'                        => array(
				'name' => __( 'Login/register modal', 'yith-auctions-for-woocommerce' ),
				'type' => 'title',
				/* translators: %1$s: Link landing page. %2$2s: Plugin name */
				'desc' => ( ! defined( 'YITH_WELRP' ) ) ? sprintf( __( 'To enable this option you need to install our <a href="%1$s" target="_blank">%2$2s</a> plugin.', 'yith-auctions-for-woocommerce' ), $yith_wceasy_landing, 'YITH Easy Login & Register Popup for WooCommerce' ) : '',
				'id'   => 'yith_wcact_settings_allow_register_or_login_title',
			),
			'settings_login_integration'                  => array(
				'name'            => esc_html__( 'Show a login/register modal', 'yith-auctions-for-woocommerce' ),
				'desc'            => esc_html__( 'Enable this option in order to allow users to login or register directly from auction page without the need to be redirected to my account page', 'yith-auctions-for-woocommerce' ),
				'id'              => 'yith_wcact_enable_login_popup',
				'extra_row_class' => defined( 'YITH_WELRP' ) && YITH_WELRP ? '' : 'yith-disabled',
				'default'         => 'no',
				'type'            => 'yith-field',
				'yith-type'       => 'onoff',
			),
			'settings_login_end'                          => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcact_settings_login_end',
			),
			// End Enable Easy Login Popup on auction page.
		),
	)
);
