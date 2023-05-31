=== YITH WooCommerce Auctions ===

== Changelog ==

= 3.8.0 - Released on 6 July 2022 =

* New: support for WooCommerce 6.7
* Update: YITH plugin framework
* Fix: avoid sending outbid emails when the auction is sealed
* Fix: hide message with the correct amount to enter when the auction is sealed

= 3.7.0 - Released on 16 June 2022 =

* New: support for WooCommerce 6.6
* Update: YITH plugin framework
* Dev: added new action yith_wcact_add_bid

= 3.6.0 - Released on 11 May 2022 =

* New: support for WordPress 6.0
* New: support for WooCommerce 6.5
* Update: YITH plugin framework
* Fix: error when using WPML Multi-Currency that affects the first entry of the list bids table
* Fix: blink timeleft in auction overtime
* Dev: added extra check to prevent creation of auction products in some cases
* Dev: added new filter yith_wcact_show_advanced_options_section
* Dev: added new filter yith_wcact_build_custom_css_loop

= 3.5.1 - Released on 12 April 2022 =

* Fix: fix timeleft on shop loop
* Fix: fix auction timeleft calculation
* Fix: add a auction_current_price to prevent calculate always the price when is not needed

= 3.5.0 - Released on 06 April 2022 =

* New: Support for WooCommerce 6.4
* Update: YITH plugin framework
* Tweak: improve the calculation of countdowns in loop page ande related products & fix to the query in category pages
* Tweak: add a control check for prevent show timeleft on loop if the auction is not closed but product is out of stock
* Tweak prevent update auction stock if auction is sold by buynow
* Tweak: retrieve current date from the server to avoid problems with the timeleft
* Tweak: improve cron arguments for improve the performance
* Fix: update time via ajax and fix the problem getting the time from server
* Fix: hide auctions purchased via buy now button on current_acution shortcode
* Fix: modify query loop also on category page
* Dev: new filter "ywcact_get_commission_fee_value"

= 3.4.0 - Released on 08 March 2022 =

* New: Support for WooCommerce 6.3
* Update: YITH plugin framework

= 3.3.0 - Released on 15 February 2022 =

* New: support for WooCommerce 6.2
* Update: language files
* Tweak: method to print the string
* Tweak: improved content of end auction email
* Fix: fixed the background color in the countdown with small blocks
* Fix: Fixing Buy Now button compatibility with WPML currency switcher
* Fix: changed plugin-fw method to avoid fatal error in multisites
* Fix: fixed the sending of the end auction email for auction followers
* Fix: checkboxes in the widgets not being saved correctly
* Dev: added new filter yith_wcact_show_auction_type_text
* Dev: added a new action yith_wcact_add_fields_after_end_label_inside_div
* Dev: added new filter yith_wcact_show_pay_now_button
* Dev: added new filters yith_wcact_pay_item_label and yith_wcact_show_pay_now_button_email

= 3.2.0 - Released on 19 January 2022 =

* New: support for WordPress 5.9
* Tweak: changed the way to add query vars to prevent some errors with Elementor
* Tweak: improved the styles of the Buy now button
* Tweak: filter by query parameter
* Update: language files
* Fix: fixed conditional when creating cron events
* Fix: ordering by date on out of date shortcode

= 3.1.1 - Released on 05 January 2022 =

* Update: YITH plugin framework
* Update: .pot file
* Fix: Calculated bid up increment
* Fix: Fixed decimals when placing a bid

= 3.1.0 - Released on 30 December 2021 =

* New: Support for WooCommerce 6.1
* Update: YITH plugin framework
* Fix: Remove auction meta data when product is duplicated
* Fix: Fatal error getting current bid when WPML is enabled

= 3.0.0 - Released on 22 December 2021 =

* New: Auction editing panel style, with new UI/UX
* New: Allow to send different emails to followers
* New: Show "unsubscribe" link on some email notifications
* New: Automatically create a “Pending payment” order assigned to the winner
* New: Charge a commission fee to winners of auction products
* New: Notice in the Payment Method section that users are forced to add a credit card before bidding
* New: Notice to shown in the Payment Method section of automatic charges
* New: Show bid list on auction list page
* New: Bidder emails and followers emails
* New: E-mails for charging attempts of the winners' credit card with the YITH WooCommerce Stripe Premium integration
* New: Followers data table
* New: Unsubscribe page
* New: Commission fee to winning customer
* Tweak: Charge the winner's credit card automatically after winning the auction on integration with YITH WooCommerce Stripe Premium
* Tweak: Auction ended section moved to a new template
* Tweak: Improve the way to filter products on loop page
* Tweak: Labels on auction endpoint on My account page
* Tweak: Auction widget style
* Tweak: CSS code
* Update: YITH plugin framework
* Update: .pot file
* Dev: action yith_wcact_after_automatically_create_order
* Dev: filter yith_wcact_automatically_create_order
* Dev: filter yith_wcact_reschedule_not_paid_cron

= 2.5.0 - Released on 09 December 2021 =

* New: Support for WooCommerce 6.0
* Update: YITH plugin framework
* Dev: added new filter yith_wcact_pay_now_url
* Dev: added new filter yith_wcact_get_watchlist_auctions_by_user_results

= 2.4.0 - Released on 29 October 2021 =

* New: Support for WooCommerce 5.9
* New: Ajax add-to-cart option for auction products listed in "My auctions" on My account page
* Update: YITH plugin framework
* Update: .pot file
* Fix: Translatable auction column table for small devices on my account page

= 2.3.0 - Released on 07 October 2021 =

* New: Support for WooCommerce 5.8
* Update: YITH plugin framework
* Tweak: Prevent problems in the timeleft on inactive tabs.

= 2.2.3 - Released on 27 September 2021 =

* Update: YITH plugin framework
* Fix: debug info feature removed for all logged in users
* Fix: js issue in combination with old browsers (i.e. Internet Explorer)

= 2.2.2 - Released on 24 September 2021 =

* Fix: Remove an unnecessary string on list bid section

= 2.2.1 - Released on 17 September 2021 =

* Fix: Send again winner email when fail

= 2.2.0 - Released on 17 September 2021 =

* New: Support for WooCommerce 5.7
* Update: YITH plugin framework
* Tweak: Improved enqueue styles when future and ended widgets are activated.
* Dev: filter yith_wcact_timeleft_loop

= 2.1.1 - Released on 14 August 2021 =

* New: Support for WooCommerce 5.6
* New: added search box on auction list table
* Update: YITH plugin framework
* Dev: fixed domain name for export CSV button
* Dev: added sorting by current bid and product counter

= 2.1.0 - Released on 06 July 2021 =

* New: Support for WooCommerce 5.5
* New: Support for WordPress 5.8
* New: Added help tab
* Update: YITH plugin framework
* Fix: Method to get products added to watchlist

= 2.0.16 - Released on 10 June 2021 =

* New: Support for WooCommerce 5.4
* Update: YITH plugin framework
* Fix: fixed pagination for non started auctions and ended auctions shortcodes.
* Dev: filter yith_wcact_after_automatic_reschedule_time
* Dev: filter yith_wcact_get_auctions_by_user_results

= 2.0.15 - Released on 10 May 2021 =

* New: Support for WooCommerce 5.3
* Update: YITH plugin framework
* Fix: fixed selector in the WooCommerce products page
* Fix: fixed an issue with non-auction type products in the watchlist
* Dev: yith_wcact_time_left_to_start_text filter

= 2.0.14 - Released on 14 April 2021 =

* New: added new option to show Privacy checkbox when customer try to follow the auction
* Update: YITH plugin framework
* Fix: fixed buy now string in the auction page

= 2.0.13 - Released on 12 April 2021 =

* New: Support for WooCommerce 5.2
* Update: YITH plugin framework
* Update: translation files
* Fix: missing parameter in filter yith_wcact_show_fee_message
* Fix: error with automatic bids when matching amount
* Fix: fixed a wrong format in the date ends using the "y" in the date format
* Fix: added auction check before sending winner email
* Fix: added reschedule bids on automatic reschedule option
* Dev: filter yith_wcact_show_form_bid
* Dev: filter yith_wcact_auction_list_query_args
* Dev: filter yith_wcact_is_valid_bid
* Dev: filter yith_wcact_display_username
* Dev: filter yith_wcact_frontend_current_bid_message
* Dev: filter 'yith_wcact_not_delete_bids_for_reschedule_auctions'

= 2.0.12 - Released on 10 March 2021 =

* New: support for WordPress 5.7
* New: Support for WooCommerce 5.1
* Update: YITH plugin framework
* Fix: Enqueue the shortcode script outside product page
* Tweak: Check if the auction has not bids in the widget
* Tweak: Added an option to deactivate countdown reload with ajax (overtime)
* Dev: Filter yith_wcact_widget_current_bid_message
* Dev: Filter yith_wcact_add_cart_item
* Dev: Filter yith_wcact_get_cart_item_from_session
* Dev: Filter yith_wcact_auction_winner_content_message
* Dev: Filter yith_wcact_auction_list_per_page
* Dev: Filter yith_wcact_auction_amount_export_csv_per_page
* Dev: Filter yith_wcact_is_sealed_on_list_bids
* Dev: Filter yith_wcact_set_categories_query_args

= 2.0.11 - Released on 09 February 2021 =

* New: Support to WooCommerce 5.0
* Update: YITH plugin framework
* Fix: plugin strings
* Dev: New js triggers on fontend.js

= 2.0.10 - Released on 19 January 2021 =

* New: Support to WooCommerce 4.9
* New: Added do nothing option for second step of non-paid auction option
* Update: Plugin-fw
* Fix order_loop parameter
* Fix problem when reschedule general option are disabled but the product reschedule option are enabled
* Fix: make the strings translatable from auction options

= 2.0.9 - Released on 02 December 2020 =

* New: Support to WooCommerce 4.8
* New: Support to WordPress 5.6
* New export CSV on auction list
* Update: Plugin-fw
* Fix: Issue during save option value
* Fix: Error ajax refresh on my watchlist section
* Fix: Prevent send winner email if auction is not closed
* Dev: yith_wcact_auction_type_product_options_default
* Dev: yith_wcact_auction_type_product_options
* Dev: yith_wcact_follow_auction_message
* Dev: yith_wcact_fee_amount_message
* Dev: yith_wcact_fee_amount_message_modal

= 2.0.8 - Released on 13 November 2020 =

* Update: Plugin-fw
* Fix: Problem with auction shortcode display

= 2.0.7 - Released on 04 November 2020 =

* New: Support to WooCommerce 4.7
* New: Support to WordPress 5.6
* New: Add auction ended label on product loop
* New: Override min increment amount if you have enabled automatic bids
* New: Shortcode yith_wcact_out_of_stock
* Update: Plugin-fw
* Update: language files
* Fix: Minimum value to bid when there is no bids in the auction
* Fix: Timeleft of start/end auctions
* Fix: Untranslated strings
* Fix: Refresh current bid on sealed auctions
* Fix: Depecrated method get_product_from_item
* Dev: filter yith_wcact_add_bid_on_sealed_auction_without_control
* Dev: filter yith_wcact_register_bid
* Dev: filter yith_wcact_current_bid_bidup_increment

= 2.0.6 - Released on 06 October 2020 =

* Update: language files

= 2.0.5 - Released on 02 October 2020 =

* New: Support to WooCommerce 4.6
* Update: Plugin-fw
* Fix first message for user that doesn't have the better bid
* Fix popup style for laptop
* Fix check if option is enable before reschedule the auction
* Fix display html bid label on archive pages
* Dev yith_wcact_after_max_bidder_section

= 2.0.4 - Released on 28 September 2020 =

* Update: Plugin-fw
* Tweak: min increment amount value on plugin buttons
* Fix: Check auction is closed before send winner email
* Fix: Allow translation on some strings.
* Dev: filter yith_wcact_show_always_buy_now_button
* Dev: filter yith_wcact_current_bid_first_bid
* Dev: filter yith_wcact_current_bid_is_auto_bid
* Dev: filter yith_wcact_no_display_closed_auctions_on_my_auction_index

= 2.0.3 - Released on 24 September 2020 =

* Fix: Strings and current price on better bid email
* Fix: Show winner badge only when the auction is closed
* Fix: Automatic bids on reverse auctions
* Fix: Translation on some strings
* Update: Plugin-fw
* Tweak: Succesfully bid email


= 2.0.2 - Released on 15 September 2020 =

* New: Support to WooCommerce 4.5
* New: Support to WordPress 5.5
* New: Not reached reserve price for max bidder
* Update: Plugin-fw
* Tweak: Prevent execute cron if options are disabled
* Tweak: Add category parameter for auction shortcodes
* Fix: Currency position on popup
* Fix: Problem with pagination on auction list section
* Fix: Wrong date format conversion
* Dev: Filter yith_wcact_confirmation_popup_message_before
* Dev: Filter yith_wcact_confirmation_popup_message_after
* Dev: Filter yith_wcact_bid_button_label

= 2.0.1 - Released on 25 August 2020 =

* Update plugin framework
* Update language file
* Fix: removed 'Guest Checkout for Easy Login' register form when customer tries to bid
* Fix: fixed a string after redirecting to my account page when trying to bid on a product (as a guest)
* Fix: fixed 'Fatal error' when checking product type
* Fix: get the correct last bid placed by the current user in the auctions tables
* Dev: added 'last bid' argument in the outbid email before saving new bid
* Dev: added new filter 'yith_wcact_current_max_bid_message'

= 2.0.0 - Released on 13 August 2020 =

* New: Panel restyling
* New: Add new options on auction settings panel
* New: Option to display countdown on shop loop
* New: Option to pay a fee before placing a bid on auction product.
* New: Option to select 4 types different of countdown.
* New: Possibility to add a time zone label on end date
* New: Integration with YITH Easy login & register popup for WooCommerce
* New: Integration with YITH WooCommerce Stripe Premium
* New: Possibility to create reverse auctions.
* New: Possibility to create sealed auctions.
* New: Watchlist endpoint.
* New: Allow customer to add their auction product on their watchlist.
* New: Confirm bid dialog box
* New: Dialog box explaining how bidup works and winner
* New: Winner badge
* New: Set label for button in e-mail notification
* New: Choose where to redirect the winner with the e-mail notification
* New: Hide auction badge on auction page
* New: Show or hide items conditions
* New: Show or hide auction's end date
* New: Show or hide the product stock
* New: Show how the auction was ended
* New: Show or hide the next amount the user can bid
* New: "Advanced override" section to set different options and override options in general
* New: Support WooCommerce 4.4
* New: Support for WordPress 5.5
* Tweak: Allow to create virtual auctions.
* Tweak: Allow to create downloadable auctions.
* Tweak: Reschedule auction ended and not paid.
* Tweak: Reschedule auction ended and reserve price is not met
* Tweak: Create advanced bid up
* Tweak: Override general options at product level.
* Update plugin-fw
* Dev: Filter yith_wcact_auction_button_bid_class
* Dev: Filter yith_wcact_product_add_to_cart
* Dev: Filter yith_wcact_get_product_permalink_redirect_to_my_account_watchlist
* Dev: Action yith_wcact_after_add_button_bid
* Dev: Action yith_wcact_after_no_start_auction
* Dev: Action yith_wcact_in_to_form_add_to_cart
* Dev: Action yith_wcact_order_item_data

= 1.4.4 - Released on 07 July 2020 =

* New: Support to WooCommerce 4.3
* Update plugin-fw
* Dev: Filter yith_wcact_auction_button_bid_class

= 1.4.3 - Released on 28 May 2020 =

* New: Support to WooCommerce 4.2
* Update plugin-fw
* Fix error when try to duplicate a product
* Fix Prevent to launch an auction product if it's close by buy now
* Update .pot file

= 1.4.2 - Released on 05 May 2020 =

* Fix: Send winner email multiple times

= 1.4.1 - Released on 04 May 2020 =

* New: Support to WooCommerce 4.1.0
* Tweak remove code for remove price when switch from simple to auction
* Update plugin-fw
* Update Greek file
* Update .pot file

= 1.4.0 - Released on 14 April 2020 =

* New: Add some class to manage auction product follow CRUD
* Tweak: Change email class to a dedicated folder
* Tweak: Improvements on templates for show better ui on Proteo theme
* Tweak: Add more information when the winner email is not send to a customer
* Update: Plugin-fw
* Update: .pot file
* Dev: New hook yith_wcact_actual_bid_value
* Dev: New parameter on yith_wcact_winner_email_pay_now_url hook
* Dev: New filter yith_wcact_get_checkout_url
* Dev: New parameter on yith_wcact_better_bid hook
* Dev: New parameter on yith_wcact_query_loop hook
* Dev: New hook yith_wcact_get_price_for_customers_buy_now

= 1.3.4 - Released on 05 March 2020 =

* Update: Italian language
* Update: Spanish language
* Update: Plugin-fw
* Fix: Query loop
* Fix Prevent error if $product doesn't exists
* Tweak: Prevent to send auction email if auction is not closed
* Tweak: Allow format date g and G for auction times
* Tweak: All strings scaped
* Dev: Add new parameter to yith_wcact_check_bid_increment filter
* Dev filter yith_wcact_get_product_permalink_redirect_to_my_account

= 1.3.3 - Released on 23 December 2019 =

* New: Support to WooCommerce 3.9.0
* Update: plugin-fw
* tweak: Prevent error when product doesn't exists
* Dev filter yith_wcact_message_successfully
* Dev filter yith_wcact_message_successfully_notice_type

= 1.3.2 - Released on 04 November 2019 =

* Update: plugin-fw

= 1.3.1 - Released on 24 October 2019 =

* New: Support to WooCommerce 3.8.0 RC1
* New: plugin panel style
* Tweak: Prevent fatal error when product doesn't exists
* Update: plugin-fw
* Dev: filter 'yith_wcact_show_buttons_auction_end'

= 1.3.0 - Released on 05 August 2019 =

* New: Support to WooCommerce 3.7.0 RC2

* Tweak: Show auction start and end time on backend in WordPress timezone
* Tweak: Add new parameter to the filter yith_wcact_get_price_for_customers
* Tweak: Show auction data on backend using the same dateformat as frontend
* Tweak: Check if the product is auction before send the email
* Tweak: Add new parameters to the yith_wcact_shortcode_catalog_orderby filter
* Update: Italian language
* Update: plugin-fw
* Update: Pot file
* Dev: filter yith_wcact_winner_email_pay_now_url

= 1.2.9 - Released on 20 May 2019 =

* Fix: enqueue correct script for shortcodes
* Dev: filter yith_wcact_actual_bid_add_value
* Dev: filter yith_wcact_load_auction_price_html
* Dev: filter yith_wcact_check_bid_increment

= 1.2.8 - Released on 11 Abr 2019 =

* New: Support to WooCommerce 3.6.0 RC2
* Update: Plugin-Fw
* Update: Dutch language
* Tweak: add woocommerce_before_add_to_cart_button on auction template
* Dev: action yith_wcact_auction_end_start
* Dev: filter yith_wcact_check_if_add_bid

= 1.2.7 - Released on 21 March 2019 =

* New: Customer email when a bid is deleted
* New: Admin email when a bid is deleted
* New: Ban customer for make bids
* Update: Plugin-Fw
* Fix: Check if the current user can manage WooCommerce
* Tweak: Add current user id on args variable
* Tweak: Save data-time for prevent problems with YITH WooCommerce Pre Order
* Dev: Filter yith_wcact_interval_minutes
* Dev: Filter yith_wcact_new_date_finish
* Dev: Filter yith_wcact_change_button_auction_shop_text
* Dev: Filter yith_wcact_auction_not_available_message

= 1.2.6 - Released on 15 Feb 2019 =

* New: Integration with YITH WooCommerce Quick View
* Update: Spanish translation
* Update: Dutch translation
* Tweak: Prevent some warnings related to product ID
* Tweak: Prevent warning on non-auction products when trying to call bid list template
* Update: Plugin-fw

= 1.2.5 - Released on 05 Dic 2018 =

* New: Support to WordPress 5.0
* New: Gutenberg block for auction shortcodes
* Update: plugin framework
* Update: Italian language

= 1.2.4 - Released on 23 October 2018 =

* Update : Plugin framework
* Tweak: Prevent send emails when auction trash
* Dev: yith_wcact_render_product_columns_dateinic
* Dev: yith_wcact_render_product_columns_dateclose


= 1.2.3 - Released on 02 October 2018 =

* New : Send notification to customer who lost auction
* New : Daily cronjob to resend failed emails to winners
* Tweak : Improve slow queries
* Update : Dutch language
* Fix : Time format on related product section
* Dev : Filter yith_wcact_check_email_is_send
* Dev : Filter yith_wcact_congratulation_message
* Dev : Filter yith_wcact_my_account_congratulation_message
* Dev : Filter yith_wcact_product_exceeded_reserve_price_message
* Dev : Filter yith_wcact_product_has_reserve_price_message
* Dev : Filter yith_wcact_product_does_not_have_a_reserve_price_message
* Dev : Action yith_wcact_auction_status_my_account_closed
* Dev : Action yith_wcact_auction_status_my_account_started

= 1.2.2 - Released on 27 June 2018 =

* New: Admin option to resend failed emails to winners
* New: Daily cronjob to resend failed emails to winners
* Update: Italian language
* Update: Spanish language
* Tweak: Possibility to change recipient email
* Dev : Filter yith_wcact_check_email_is_send


= 1.2.1 - Released on 21 May 2018 =

* New: Support to WordPress 4.9.6 RC2
* New: Support to WooCommerce 3.4.0 RC1
* New: Metabox auction status
* New: Possibility to resend auction email
* Update: Plugin Framework
* Dev: Filter yith_wcact_show_time_in_customer_time
* Dev: Filter yith_wcact_tab_auction_show_name
* Dev: Filter yith_wcact_display_user_anonymous_name

= 1.2.0 - Released on 03 April 2018 =

* New: Shortcode [yith_auction_non_stated] to show no started auctions
* New: Support WPML Currency Switcher
* Tweak: WPML Currency Language
* Update: Plugin Framework
* Fix: Problem WPML products in cart and checkout page
* Fix: Problem with buy now button

= 1.1.14 - Released on 09 Feb 2018 =

* New: support to WordPress 4.9.4
* New: support to WooCommerce 3.3.1
* New: shortcode [yith_auction_current] to show current live auctions
* Tweak: pagination in auction shortcodes
* Tweak: select number of columns and product per page in auction shortcodes

= 1.1.13 - Released on 30 January 2018 =

* New: support to WordPress 4.9.2
* New: support to WooCommerce 3.3.0-RC2
* Update: plugin framework 3.0.11

= 1.1.12 - Released on 10 January 2018 =

* New: Product parameter in end-auction email
* Fix: notice in wp-query
* Fix: problem check stock in auction.php template
* Update: Plugin core
* Update: Spanish translation
* Dev: filter yith_wcact_max_bid_manual
* Dev: filter yith_wcact_auction_product_id
* Dev: filter yith_wcact_show_buy_now_button
* Dev: action yith_wcact_auction_auction_reserve_price
* Dev: action yith_wcact_after_auction_end

= 1.1.11 - Released on 24 October 2017 =

* New: added new successfully bid email
* Update: Plugin core

= 1.1.10 - Released on 20 October 2017 =

* Fix: error get image and bids on auction product page
* Update: Plugin core

= 1.1.9 - Released on 17 October 2017 =

* Fix: error load bid table
* Dev: added filter yith_wcact_show_list_bids

* New: Support to WooCommerce 3.2.0 RC2
* Update: Plugin core

= 1.1.8 - Released on 10 October 2017 =

* New: Support to WooCommerce 3.2.0 RC2
* Update: Plugin core

= 1.1.7 - Released on 02 October 2017 =

* Fix:  Issue with timeleft
* Fix : Issue send admin winner email
* Fix : Get right url using WPML
* Dev : Added action yith_wcact_before_add_to_cart_form

= 1.1.6 - Released on 28 August 2017 =

* Fix: Show products on shop page when out of stock general option is enabled
* Fix : Style issue on my auctions chart

= 1.1.5 - Released on 16 August 2017 =
* New: Dutch translation
* Fix: Send multiple emails when the auction is in overtime.
* Dev: Added filter yith_wcact_display_watchlist

= 1.1.4 - Released on 14 August 2017 =

* New: add more than one recipient to the winner email sent to the admin
* New: added tax class and tax status
* New: added new label when the auction is closed and no customer won the auction
* New: shortcode [yith_auction_out_of_date] to show out of date auctions
* Fix: URL encode to prevent redirect error.
* Fix: check if the auction has ended when a customer bids
* Fix: count auction product in shop loop.
* Fix: show pay-now button when an auction is rescheduled.
* Dev: added filter yith_wcact_datetime_table
* Dev: added filter yith_wcact_bid_tab_title
* Dev: added filter yith_wcact_priority_bid_tab
* Dev: added filter yith_wcact_bid_tab

= 1.1.3 - Released on 07 July 2017 =

* New: Compatibility with  YITH Infinite Scrooling Premium
* Fix: remove auction product on shop loop when option is disabled
* Fix: remove Pay now button when the bid doesn't exceed the reserve price on my account page
* Dev: added action yith_wcact_render_product_columns
* Dev: added filter yith_wcact_product_columns

= 1.1.2 - Released on 04 May 2017 =

* New: Admin can delete customer's bid
* New: Customers register to a watchlist for each auction product and be notified by email when auction is about to end
* New: Minimum amount to increase manual bids
* New: added wc_notice in product page
* Fix: Auction product price not changing when clicking on buy now
* Fix: show a NaN number in timeleft when auction has not started
* Dev: added action yith_wcact_before_form_auction_product
* Dev: added filter yith_wcact_load_script_widget_everywhere

= 1.1.1 - Released on 04 April 2017 =

* New: support to WooCommerce 3.0.0-RC2
* New: possibility to add auction product and other products to cart in the same order
* New: reschedule auction without bids automatically

= 1.1.0 - Released on 10 March 2017 =
* New: support to WooCommerce 2.7.0-RC1
* New: live auctions on My account page
* New: live auctions on product page
* New: compatibility with WPML
* New: bid list on admin product page
* Update: YITH Plugin Framework

= 1.0.14 - Released on 07 Feb 2017 =

* New: show upbid and overtime in product page
* New: tooltip info in product page
* New: message info when auction is in overtime
* New: shortcode named [yith_auction_products] that allows you to show the auctions on any page.
* Dev: added action yith_wcact_before_add_button_bid

= 1.0.13 - Released on 23 December 2016 =

* Fixed: Issue with date time in bid tab

= 1.0.12 - Released on 16 December 2016 =

* Added: Overtime option in general settings
* Fixed: Issue with bid button
* Fixed: Product issues

= 1.0.11 - Released on 13 December 2016 =

* Added: Admin option to regenerate auction prices.
* Added: Pay now option from My account.
* Added: Possibility to add overtime to an auction.
* Updated: name and text domain.
* Updated: language file.
* Fixed: Issues with admin emails.
* Fixed: Reschedule auction when product has buy now status.
* Dev: added yith_wcact_auction_price_html filter.

= 1.0.10 - Released on 17 October 2016 =

* Fixed: "Buy Now" issue

= 1.0.9 - Released on 04 October 2016 =

* Fixed: Sending email issue.

= 1.0.8 - Released on 28 September 2016 =

* Fixed: Datetime format in product page.
* Fixed: Missing arguments in order page.
* Fixed: Username in product page problem.

= 1.0.7 - Released on 20 September 2016 =

* Added: Notification email to admin when an auction ends and has a winner.
* Added: Possibility to filter by auction status.
* Fixed: Enable/Disable email notifications.
* Fixed: Show Datetime in local time

= 1.0.6 - Released on 13 September 2016 =

* Added: Option in product settings to show buttons in product page to increase or Decemberrease the bid.
* Fixed: Problems with the translation in emails.
* Fixed: Problems with tab bid.
* Fixed: Prevent issues with manage stock.
* Fixed: Problems with order by price in shop loop.
* Added: Admin setting that show or not the pay now button in product page when the auction is ends.

= 1.0.5 - Released on 01 September 2016 =

* Fixed: username in winner email
* Fixed: timeleft in shop

= 1.0.4 - Released on 30 August 2016 =

* Fixed: enqueue script issues
* Fixed: Pay now button in winner email
* Fixed: translation issues

= 1.0.2 - Released on 22 August 2016 =

* Fixed: updated textdomain for untranslatable strings
* Fixed: Problems with pay-now button in winner email when users are not logged in.
* Fixed: Problems with product text link in winner email.
* Updated: yith-auctions-for-woocommerce.pot


= 1.0.1 - Released on 18 August 2016 =

* Added: Marchgin button in auction widget
* Fixed: Problems when not exist reserve price in auctions with automatic bids.
* Fixed: Problems with the translation.

= 1.0.0 - Released on 10 August 2016 =

* First release

== Suggestions ==

If you have suggestions about how to improve YITH WooCommerce Auctions, you can [write us](mailto:plugins@yithemes.com "Your Inspiration Themes") so we can bundle them into the next release of the plugin.

== Translators ==

If you have created your own language pack, or have an update for an existing one, you can send [gettext PO and MO file](http://codex.wordpress.org/Translating_WordPress "Translating WordPress")
[use](http://yithemes.com/contact/ "Your Inspiration Themes") so we can bundle it into YITH WooCommerce Auctions languages.

 = Available Languages =
 * English
 * Spanish
