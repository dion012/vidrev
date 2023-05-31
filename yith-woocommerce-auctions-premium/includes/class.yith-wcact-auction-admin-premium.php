<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Admin_Premium Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Premium Admin Class.
 *
 * @class   YITH_Auction_Admin_Premium
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */

if ( ! class_exists( 'YITH_Auction_Admin_Premium' ) ) {
	/**
	 * Class YITH_Auction_Admin_Premium
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auction_Admin_Premium extends YITH_Auction_Admin {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			/* === Register Panel Settings === */
			$this->show_premium_landing = false;

			add_action( 'yith_before_auction_tab', array( $this, 'yith_before_auction_tab' ) );
			add_action( 'yith_after_auction_tab', array( $this, 'yith_after_auction_tab' ) );

			// Save data products.
			add_action( 'woocommerce_admin_process_product_object', array( $this, 'set_product_meta_before_saving' ) );
			add_action( 'woocommerce_process_product_meta_' . self::$prod_type, array( $this, 'save_auction_data' ) );

			add_action( 'pre_get_posts', array( $this, 'auction_orderby' ) );

			add_action( 'pre_get_posts', array( $this, 'filter_by_auction_status' ) );

			add_action( 'add_meta_boxes', array( $this, 'admin_list_bid' ), 30 );

			/*Duplicate products*/
			add_action( 'woocommerce_product_duplicate', array( $this, 'duplicate_products' ), 10, 2 );

			// Profile Screen Update methods.
			add_action( 'show_user_profile', array( $this, 'render_auction_extra_fields' ), 20 );
			add_action( 'edit_user_profile', array( $this, 'render_auction_extra_fields' ), 20 );
			add_action( 'personal_options_update', array( $this, 'save_auction_extra_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_auction_extra_fields' ) );

			if ( isset( $_REQUEST['yith-wcact-action-resend-email'] ) && 'send_auction_winner_email' === $_REQUEST['yith-wcact-action-resend-email'] ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_action( 'admin_init', array( $this, 'yith_wcact_send_auction_winner_email' ) );
			}

			add_action( 'woocommerce_process_product_meta', array( $this, 'check_if_an_auction_product' ), 10, 2 );

			add_action( 'woocommerce_admin_field_yith_wcact_html', array( $this, 'yith_regenerate_prices' ) );

			add_action( 'init', array( $this, 'gutengerg_support' ) );

			add_action( 'yith_wcact_general_custom_fields', array( $this, 'general_custom_fields' ) );
			add_action( 'yith_wcact_product_custom_fields', array( $this, 'product_custom_fields' ) );

			add_action( 'yith_wcact_auction_list_tab', array( $this, 'auction_list_tab' ) );

			add_action( 'yit_panel_wc_after_update', array( $this, 'save_general_settings' ) );

			/**
			 * Add an option to let the admin set the auction as a physical good or digital goods
			 */
			add_filter( 'product_type_options', array( $this, 'add_type_option' ) );

			add_action( 'admin_init', array( $this, 'export_csv' ) );

			parent::__construct();
		}

		/**
		 * Create gutenberg blocks
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function gutengerg_support() {
			/* === Gutenberg Support === */
			$blocks = array(
				'yith-wcact-auction-products'      => array(
					'style'          => 'yith-wcact-frontend-css',
					'script'         => 'yith_wcact_frontend_shop_premium',
					'title'          => esc_html_x( 'Auction products', '[gutenberg]: block name', 'yith-auctions-for-woocommerce' ),
					'label'          => esc_html_x( 'Print auction products', '[gutenberg]: block description', 'yith-auctions-for-woocommerce' ),
					'shortcode_name' => 'yith_auction_products',
					'keywords'       => array(),
				),
				'yith-wcact-auction-out-of-date'   => array(
					'style'          => 'yith-wcact-frontend-css',
					'script'         => 'yith_wcact_frontend_shop_premium',
					'title'          => esc_html_x( 'Auction Expired Products', '[gutenberg]: block name', 'yith-auctions-for-woocommerce' ),
					'label'          => esc_html_x( 'Print out of date auction products ', '[gutenberg]: block description', 'yith-auctions-for-woocommerce' ),
					'shortcode_name' => 'yith_auction_out_of_date',
					'keywords'       => array(),
				),
				'yith-wcact-auction-current'       => array(
					'style'          => 'yith-wcact-frontend-css',
					'script'         => 'yith_wcact_frontend_shop_premium',
					'title'          => esc_html_x( 'Auction Active Products', '[gutenberg]: block name', 'yith-auctions-for-woocommerce' ),
					'label'          => esc_html_x( 'Print current auction products ', '[gutenberg]: block description', 'yith-auctions-for-woocommerce' ),
					'shortcode_name' => 'yith_auction_current',
					'keywords'       => array(),
				),
				'yith-wcact-auction-show-list-bid' => array(
					'style'          => 'yith-wcact-auction-show-list-bid',
					'title'          => esc_html_x( 'Auction Show bidding list', '[gutenberg]: block name', 'yith-auctions-for-woocommerce' ),
					'label'          => esc_html_x( 'Show the  bidding list of an auction product', '[gutenberg]: block description', 'yith-auctions-for-woocommerce' ),
					'shortcode_name' => 'yith_auction_show_list_bid',
					'keywords'       => array(),
					'attributes'     => array(
						'id' => array(
							'type'    => 'text',
							'label'   => esc_html_x( 'Product id to show list bid', '[gutenberg]: Option title', 'yith-auctions-for-woocommerce' ),
							'default' => '',
						),
					),
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * YITH before auction tab
		 *
		 * @param int $post_id Post id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.11
		 */
		public function yith_before_auction_tab( $post_id ) {
			$product = wc_get_product( $post_id );

			$auction_product = ( $product && 'auction' === $product->get_type() ) ? true : false;

			$minimun_increment_amount_title       = ( $auction_product && 'reverse' === $product->get_auction_type() ) ? esc_html__( 'Minimum decrement amount', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Minimum increment amount', 'yith-auctions-for-woocommerce' );
			$minimun_increment_amount_description = ( $auction_product && 'reverse' === $product->get_auction_type() ) ? esc_html__( 'Set the minimum decrement amount for manual bids', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Set the minimum increment amount for manual bids', 'yith-auctions-for-woocommerce' );

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Item condition', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Optional: Enter the item condition (new, used, damaged...)', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class' => 'ywcact-product-metabox-text',
						'type'  => 'text',
						'value' => $product && $auction_product ? $product->get_item_condition( 'edit' ) : '',
						'id'    => '_yith_wcact_item_condition',
						'name'  => '_yith_wcact_item_condition',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact-product-metabox-radio-container',
					'title'  => esc_html__( 'Auction type', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Choose the auction type. In a normal auction, the higher bid wins, in a reverse auction, the lower bid wins.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-radio',
						'type'    => 'radio',
						/**
						 * APPLY_FILTERS: yith_wcact_auction_type_product_options_default
						 *
						 * Filter the default auction type value.
						 *
						 * @param string $default_value Default value
						 *
						 * @return string
						 */
						'value'   => $product && $auction_product ? $product->get_auction_type( 'edit' ) : apply_filters( 'yith_wcact_auction_type_product_options_default', 'normal' ),
						'id'      => '_yith_wcact_auction_type',
						'name'    => '_yith_wcact_auction_type',
						/**
						 * APPLY_FILTERS: yith_wcact_auction_type_product_options
						 *
						 * Filter the array with the auction type options.
						 *
						 * @param array $auction_type_options Auction type options
						 *
						 * @return array
						 */
						'options' => apply_filters(
							'yith_wcact_auction_type_product_options',
							array(
								'normal'  => esc_html__( 'Normal', 'yith-auctions-for-woocommerce' ),
								'reverse' => esc_html__( 'Reverse', 'yith-auctions-for-woocommerce' ),
							)
						),
						'default' => 'normal',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Make sealed', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable if you want to make this a sealed auction. All bids will be hidden.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						/**
						 * APPLY_FILTERS: yith_wcact_metabox_default_value
						 *
						 * Filter the default field value in the metabox.
						 *
						 * @param string $value Value
						 * @param string $field Field
						 *
						 * @return string
						 */
						'value'   => $product && $auction_product ? $product->get_auction_sealed( 'edit' ) : apply_filters( 'yith_wcact_metabox_default_value', 'no', 'auction_sealed' ),
						'id'      => '_yith_wcact_auction_sealed',
						'name'    => '_yith_wcact_auction_sealed',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Starting Price', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ') <span class="required ywcact-required">*</span>',
					'desc'   => esc_html__( 'Set a starting price for this auction', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-price wc_input_price ywcact-data-validation',
						'type'              => 'text',
						'value'             => $product && $auction_product ? $product->get_start_price( 'edit' ) : '',
						'id'                => '_yith_auction_start_price',
						'name'              => '_yith_auction_start_price',
						'custom_attributes' => array(
							'data-title-field' => esc_html__( 'Starting Price', 'yith-auctions-for-woocommerce' ),
							'data-validation'  => 'has_value',
						),

					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact-minimun-increment-amount',
					'title'  => $minimun_increment_amount_title . ' (' . get_woocommerce_currency_symbol() . ')',
					'desc'   => implode(
						'<br />',
						array(
							'<span class="ywcact-min-incr-amount">' . $minimun_increment_amount_description . '</span>',
							esc_html__( 'Note: If you set automatic bidding, this value will be overridden by the value of "Automatic bid increment".', 'yith-auctions-for-woocommerce' ),
						)
					),
					'fields' => array(
						'class' => 'ywcact-product-metabox-price wc_input_price',
						'type'  => 'text',
						'value' => $product && $auction_product ? $product->get_minimum_increment_amount( 'edit' ) : '',
						'id'    => '_yith_auction_minimum_increment_amount',
						'name'  => '_yith_auction_minimum_increment_amount',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_auction_normal',
					'title'  => esc_html__( 'Reserve price', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'desc'   => esc_html__( 'Set the reserve price for this auction.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class' => 'ywcact-product-metabox-price wc_input_price',
						'type'  => 'text',
						'value' => $product && $auction_product ? $product->get_reserve_price( 'edit' ) : '',
						'id'    => '_yith_auction_reserve_price',
						'name'  => '_yith_auction_reserve_price',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_auction_normal',
					'title'  => esc_html__( 'Show \'Buy Now\' button', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to show a \'Buy Now\' button to allow users to buy this product without to bid', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'buy_now_onoff', 'buy_now', $product ) : apply_filters( 'yith_wcact_metabox_default_value', 'no', 'buy_now_onoff' ),
						'id'      => '_yith_auction_buy_now_onoff',
						'name'    => '_yith_auction_buy_now_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_buy_now',
					'title'  => esc_html__( 'Buy it now price', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'desc'   => esc_html__( 'Set the \'Buy Now\' price for this auction.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-price wc_input_price ywcact-data-validation',
						'type'              => 'text',
						'value'             => $product && $auction_product ? $product->get_buy_now( 'edit' ) : '',
						'id'                => '_yith_auction_buy_now',
						'name'              => '_yith_auction_buy_now',
						'custom_attributes' => array(
							'data-title-field' => esc_html__( 'Buy it now price', 'yith-auctions-for-woocommerce' ),
							'data-validation'  => 'has_dependencies',
							'data-dependency'  => '#_yith_auction_buy_now_onoff',
							'data-value'       => 'yes',
						),
					),
				)
			);
		}

		/**
		 * YITH after auction tab
		 * Add input in auction tab
		 *
		 * @param  int $post_id Post id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.11
		 */
		public function yith_after_auction_tab( $post_id ) {
			$product = wc_get_product( $post_id );

			$auction_product = ( $product && 'auction' === $product->get_type() ) ? true : false;

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Override bid type options', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to override the global options and set specific bid type options for this auction', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'bid_type_onoff', 'bid_increment', $product ) : 'no',
						'id'      => '_yith_auction_bid_type_onoff',
						'name'    => '_yith_auction_bid_type_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_bid_up ywcact-product-metabox-radio-container',
					'title'  => esc_html__( 'Set bid type', 'yith-auctions-for-woocommerce' ),
					'desc'   => implode(
						'<br />',
						array(
							esc_html__( 'With the automatic bidding, the user enters the maximum amount it\'s willing to pay for the item.', 'yith-auctions-for-woocommerce' ),
							esc_html_x( 'The system will automatically bid for the user with the smallest amount possible every time, once his maximum limit is reached', 'The system will automatically bid for him with the smallest amount posible every time, once his maximun limit is reached', 'yith-auctions-for-woocommerce' ),
						)
					),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-radio',
						'type'    => 'radio',
						'value'   => $product && $auction_product ? yith_wcact_field_radio_value( 'bid_type_set_radio', 'bid_increment', $product, 'manual', 'automatic' ) : 'manual',
						'id'      => '_yith_wcact_bid_type_set_radio',
						'name'    => '_yith_wcact_bid_type_set_radio',
						'options' => array(
							'manual'    => esc_html__( 'Manual', 'yith-auctions-for-woocommerce' ),
							'automatic' => esc_html__( 'Automatic', 'yith-auctions-for-woocommerce' ),
						),
						'default' => 'normal',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_bid_up ywcact_show_if_bid_up_set ywcact-product-metabox-radio-container',
					'title'  => esc_html__( 'Auction bid type', 'yith-auctions-for-woocommerce' ),
					'desc'   => implode(
						'<br />',
						array(
							esc_html_x( 'With the simple type you can set only one bid increment amount, independently from the current bid value.', 'The system will automatically bid for him with the smallest amount posible every time, once his maximun limit is reached', 'yith-auctions-for-woocommerce' ),
							esc_html__( 'With the advanced type you can set different auctomatic bid increments based on the current bid value.', 'yith-auctions-for-woocommerce' ),
						)
					),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-radio',
						'type'    => 'radio',
						'value'   => $product && $auction_product ? yith_wcact_field_radio_value( 'bid_type_radio', 'bid_increment', $product, 'simple' ) : 'simple',
						'id'      => '_yith_wcact_bid_type_radio',
						'name'    => '_yith_wcact_bid_type_radio',
						'options' => array(
							'simple'   => esc_html__( 'Simple', 'yith-auctions-for-woocommerce' ),
							'advanced' => esc_html__( 'Advanced', 'yith-auctions-for-woocommerce' ),
						),
						'default' => 'normal',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'yith-plugin-ui ywcact_show_if_bid_up ywcact_show_if_bid_up_set',
					'title'  => esc_html__( 'Automatic bid increment', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'desc'   => esc_html__( 'Set the bidding increment for automatic bidding. You can create more rules to set different bid increments based on the auction\'s current bid and then set a last rule to cover all the offers made after the last current bid step', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-custom',
						'type'              => 'custom',
						'yith-wcact-type'   => 'product-bid-increment',
						'yith-wcact-values' => array(
							'automatic_bid_type' => $product && $auction_product ? yith_wcact_field_radio_value( 'bid_type_radio', 'bid_increment', $product, 'simple' ) : 'simple',
							'automatic_bid_increment_simple' => $product && $auction_product ? $product->get_bid_increment() : 0,
							'automatic_bid_increment_advanced' => $product && $auction_product ? $product->get_bid_increment_advanced() : array(),
						),
						'action'            => 'yith_wcact_product_custom_fields',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Override fee options', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to override the global options and set specific fee options for this auction', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? $product->get_fee_onoff( 'edit' ) : 'no',
						'id'      => '_yith_auction_fee_onoff',
						'name'    => '_yith_auction_fee_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_fee',
					'title'  => esc_html__( 'Ask fee payment before bidding', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to ask users to pay a fee before placing a bid.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? $product->get_fee_ask_onoff( 'edit' ) : 'no',
						'id'      => '_yith_auction_fee_ask_onoff',
						'name'    => '_yith_auction_fee_ask_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_fee ywcact_show_if_ask_fee',
					'title'  => esc_html__( 'Fee amount', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'desc'   => esc_html__( 'Set the fee for this auction, a user needs to pay, before being able to place a bid', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-price wc_input_price ywcact-data-validation',
						'type'              => 'text',
						'value'             => $product && $auction_product ? $product->get_fee_amount( 'edit' ) : '',
						'id'                => '_yith_auction_fee_amount',
						'name'              => '_yith_auction_fee_amount',
						'custom_attributes' => array(
							'data-title-field' => esc_html__( 'Fee amount', 'yith-auctions-for-woocommerce' ),
							'data-validation'  => 'has_dependencies',
							'data-dependency'  => '#_yith_auction_fee_ask_onoff',
							'data-value'       => 'yes',
						),
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Override commissions fee options', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to override the global options and set specific commission fee options for this auction', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? $product->get_commission_fee_onoff( 'edit' ) : 'no',
						'id'      => '_yith_auction_commission_fee_onoff',
						'name'    => '_yith_auction_commission_fee_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_commission_fee',
					'title'  => esc_html__( 'Apply commission fee for winner auction', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to apply a specific commission fee for auction winner', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? $product->get_commission_apply_fee_onoff( 'edit' ) : 'no',
						'id'      => '_yith_auction_commission_apply_fee_onoff',
						'name'    => '_yith_auction_commission_apply_fee_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_commission_fee ywcact_show_if_apply_commission_fee',
					'title'  => esc_html__( 'Commission fee', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Set the commission fee for auction winner', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'type'    => 'inline-fields',
						'fields'  => array(
							'value' => array(
								'std'  => '',
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
						'value'   => array(
							'value' => $product && $auction_product ? $product->get_commission_fee( 'edit' )['value'] : '',
							'unit'  => $product && $auction_product ? $product->get_commission_fee( 'edit' )['unit'] : '',
						),
						'id'      => '_yith_auction_commission_fee',
						'name'    => '_yith_auction_commission_fee',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_commission_fee ywcact_show_if_apply_commission_fee',
					'title'  => esc_html__( 'Commission label', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enter a label to identify the commission in checkout and product page. This will override general option', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'text',
						'value'   => $product && $auction_product ? $product->get_commission_fee_label( 'edit' ) : '',
						'id'      => '_yith_auction_commission_label',
						'name'    => '_yith_auction_commission_label',
						'default' => '',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Override rescheduling options', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to override the global options and set specific rescheduling options for this auction.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'reschedule_onoff', 'automatic_reschedule', $product ) : apply_filters( 'yith_wcact_metabox_default_value', 'no', 'reschedule_onoff' ),
						'id'      => '_yith_auction_reschedule_onoff',
						'name'    => '_yith_auction_reschedule_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_reschedule',
					'title'  => esc_html__( 'Reschedule ended auctions without bids', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to automatically reschedule ended auctions without bid.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'reschedule_closed_without_bids_onoff', 'automatic_reschedule', $product ) : apply_filters( 'yith_wcact_metabox_default_value', 'no', 'reschedule_closed_without_bids_onoff' ),
						'id'      => '_yith_auction_reschedule_closed_without_bids_onoff',
						'name'    => '_yith_auction_reschedule_closed_without_bids_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_reschedule',
					'title'  => esc_html__( 'Reschedule ended auctions with the reserve price not reached', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to automatically reschedule ended auctions if the reserve price was not reached by any submitted bids.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? $product->get_reschedule_reserve_no_reached_onoff() : apply_filters( 'yith_wcact_metabox_default_value', 'no', 'reschedule_reserve_no_reached_onoff' ),
						'id'      => '_yith_auction_reschedule_reserve_no_reached_onoff',
						'name'    => '_yith_auction_reschedule_reserve_no_reached_onoff',
						/**
						 * APPLY_FILTERS: yith_wcact_auction_reschedule_reserve_no_reached_onoff_default
						 *
						 * Filter the default value of the field to set whether reschedule ended auctions when the reserve price has not been reached.
						 *
						 * @param string $default_value Default value
						 *
						 * @return string
						 */
						'default' => apply_filters( 'yith_wcact_auction_reschedule_reserve_no_reached_onoff_default', 'no' ),
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_reschedule ywcact_show_if_reschedule_reserve_price ywcact_show_if_reschedule_without_bids',
					'title'  => esc_html__( 'Auctions will be rescheduled for another', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Set the length of time for which the auction will run again. The auction will reset itself to the original auction product settings and all previous bids will be removed.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-custom',
						'type'              => 'custom',
						'yith-wcact-type'   => 'product-reschedule',
						'yith-wcact-values' => array(
							'automatic_reschedule'      => $product && $auction_product ? $product->get_automatic_reschedule( 'edit' ) : '',
							'automatic_reschedule_unit' => $product && $auction_product ? $product->get_automatic_reschedule_auction_unit( 'edit' ) : '',
							'options'                   => yith_wcact_get_select_time_values(),
						),
						'action'            => 'yith_wcact_product_custom_fields',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui',
					'title'  => esc_html__( 'Override overtime options', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to override the global options and set specific overtime options for this auction.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'overtime_onoff', 'check_time_for_overtime_option', $product ) : 'no',
						'id'      => '_yith_auction_overtime_onoff',
						'name'    => '_yith_auction_overtime_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_overtime',
					'title'  => esc_html__( 'Set overtime', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Enable to extend the auction duration if someone puts a bid when the auction is about to end.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'   => 'ywcact-product-metabox-onoff',
						'type'    => 'onoff',
						'value'   => $product && $auction_product ? yith_wcact_field_onoff_value( 'overtime_set_onoff', 'check_time_for_overtime_option', $product ) : 'no',
						'id'      => '_yith_auction_overtime_set_onoff',
						'name'    => '_yith_auction_overtime_set_onoff',
						'default' => 'no',
					),
				)
			);

			yith_wcact_product_metabox_form_field(
				array(
					'class'  => 'form-field wc_auction_field yith-plugin-ui ywcact_show_if_overtime ywcact_show_if_overtime_set',
					'title'  => esc_html__( 'Override settings', 'yith-auctions-for-woocommerce' ),
					'desc'   => esc_html__( 'Set the overtime rule when the auction is about to end.', 'yith-auctions-for-woocommerce' ),
					'fields' => array(
						'class'             => 'ywcact-product-metabox-custom',
						'type'              => 'custom',
						'yith-wcact-type'   => 'product-overtime',
						'yith-wcact-values' => array(
							'minutes_before_overtime' => $product && $auction_product ? $product->get_check_time_for_overtime_option( 'edit' ) : '',
							'overtime_minutes'        => $product && $auction_product ? $product->get_overtime_option( 'edit' ) : '',
						),
						'action'            => 'yith_wcact_product_custom_fields',
					),
				)
			);

			if ( $product && 'auction' === $product->get_type() && ( $product->is_closed() || 'outofstock' === $product->get_stock_status() ) ) {
				echo '<div id="yith-reshedule">';
				echo '<p class="form-field wc_auction_reshedule"><input type="button" class="button" id="reshedule_button" value="' . esc_html__( 'Re-schedule', 'yith-auctions-for-woocommerce' ) . '"></p>';
				echo '<p class="form-field" id="yith-reshedule-notice-admin">' . esc_html__( ' Change the dates and click on the update button to re-schedule the auction', 'yith-auctions-for-woocommerce' ) . '</p>';
				echo '</div>';
			}
		}

		/**
		 * Save the data input into the auction product box
		 *
		 * @param  int $post_id Post id.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0.11
		 */
		public function save_auction_data( $post_id ) {
			$product = wc_get_product( $post_id );

			if ( isset( $_POST['yith_wcact_auction_form'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yith_wcact_auction_form'] ) ), 'yith-wcact-auction-form' ) ) {
				if ( $product && 'auction' === $product->get_type() ) {
					if ( ! $product->get_is_closed_by_buy_now() ) {
						if ( isset( $_POST['_yith_auction_to'] ) ) {
							$bids           = YITH_Auctions()->bids;
							$exist_auctions = $bids->get_max_bid( $post_id );

							// Clear all Product CronJob.
							if ( wp_next_scheduled( 'yith_wcact_send_emails', array( $post_id ) ) ) {
								wp_clear_scheduled_hook( 'yith_wcact_send_emails', array( $post_id ) );
							}

							// Create the CronJob //when the auction is about to end.
							/**
							 * DO_ACTION: yith_wcact_register_cron_email
							 *
							 * Allow to register the cron event to send the emails.
							 *
							 * @param int $post_id Product ID
							 */
							do_action( 'yith_wcact_register_cron_email', $post_id );

							// Clear all Product CronJob.
							if ( wp_next_scheduled( 'yith_wcact_send_emails_auction', array( $post_id ) ) ) {
								wp_clear_scheduled_hook( 'yith_wcact_send_emails_auction', array( $post_id ) );
							}

							// Create the CronJob //when the auction end, winner and vendors.
							/**
							 * DO_ACTION: yith_wcact_register_cron_email_auction
							 *
							 * Allow to register the cron event to send the emails.
							 *
							 * @param int $post_id Product ID
							 */
							do_action( 'yith_wcact_register_cron_email_auction', $post_id );

							// Prevent issues with orderby in shop loop.
							if ( ! $exist_auctions && isset( $_POST['_yith_auction_start_price'] ) ) {
								yit_save_prop( $product, '_price', sanitize_text_field( wp_unslash( $_POST['_yith_auction_start_price'] ) ) );

								$product->update_meta_data( 'current_bid', sanitize_text_field( wp_unslash( $_POST['_yith_auction_start_price'] ) ) );
							}

							$product->set_stock_status( 'instock' );
						}
					}

					// Update auction status taxonomy.
					$product->update_auction_status( true );

					$product->save();
				}
			}
		}

		/**
		 * Auction Order By
		 *
		 * Order by start date or end date in datatable products
		 *
		 * @param WP_Query $query query database.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function auction_orderby( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby' );

			switch ( $orderby ) {
				case 'yith_auction_start_date':
					$query->set( 'meta_key', '_yith_auction_for' );
					$query->set( 'orderby', 'meta_value' );
					break;

				case 'yith_auction_end_date':
					$query->set( 'meta_key', '_yith_auction_to' );
					$query->set( 'orderby', 'meta_value' );
					break;
			}
		}

		/**
		 * Filter by auction status
		 *
		 * @param WP_Query $query query database.
		 * @return void
		 * @since  1.0.7
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function filter_by_auction_status( $query ) {
			global $post_type;

			if ( ! is_admin() ) {
				return;
			}

			if ( 'product' === $post_type ) {
				if ( isset( $_GET['auction_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$orderby = sanitize_key( wp_unslash( $_GET['auction_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

					switch ( $orderby ) {
						case 'non-started':
							$query->set(
								'meta_query',
								array(
									array(
										'key'     => '_yith_auction_for',
										'value'   => strtotime( 'now' ),
										'compare' => '>',
									),
								)
							);
							break;

						case 'started':
							$query->set(
								'meta_query',
								array(
									'relation' => 'AND',
									array(
										'key'     => '_yith_auction_for',
										'value'   => strtotime( 'now' ),
										'compare' => '<',
									),
									array(
										'key'     => '_yith_auction_to',
										'value'   => strtotime( 'now' ),
										'compare' => '>',
									),
								)
							);
							break;

						case 'finished':
							$query->set(
								'meta_query',
								array(
									array(
										'key'     => '_yith_auction_to',
										'value'   => strtotime( 'now' ),
										'compare' => '<',
									),
								)
							);
							break;
					}
				}
			}
		}

		/**
		 * Create metabox for auction product
		 *
		 * @param string $post_type current post type.
		 * @return void
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function admin_list_bid( $post_type ) {
			global $post;

			if ( isset( $post ) ) {
				$post_types = array( 'product' );     // limit meta box to certain post types.
				$product    = wc_get_product( $post->ID );

				if ( in_array( $post_type, $post_types, true ) && ( 'auction' === $product->get_type() ) ) {
					add_meta_box( 'yith-wcgpf-auction-bid-list', esc_html__( 'Auction bid list', 'yith-auctions-for-woocommerce' ), array( $this, 'auction_bid_list' ), $post_type, 'normal', 'low' );
					add_meta_box( 'yith-wcgpf-auction-information', esc_html__( 'Auction status', 'yith-auctions-for-woocommerce' ), array( $this, 'auction_bid_status' ), $post_type, 'side', 'low' );
				}
			}
		}

		/**
		 * Create metabox with list of bid for each product
		 *
		 * @param WP_Post $post current post object.
		 * @return void
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function auction_bid_list( $post ) {
			$product = wc_get_product( $post );

			if ( $product ) {
				$instance     = YITH_Auctions()->bids;
				$auction_list = $instance->get_bids_auction( $product->get_id() );

				$args = array(
					'post_id'      => $post->ID,
					'auction_list' => $auction_list,
					'product'      => $product,
					'pagination'   => false,
				);

				wc_get_template( 'admin-list-bids.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'admin/' );
			}
		}

		/**
		 * Create metabox with Auction information
		 *
		 * @param WP_Post $post current post object.
		 * @return void
		 * @since  2.0.1
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function auction_bid_status( $post ) {
			$args = array(
				'post_id' => $post->ID,
				'post'    => $post,
			);

			wc_get_template( 'admin-auction-status.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'admin/' );
		}

		/**
		 * Control overtime product
		 *
		 * @param WC_Product $product_new New Product.
		 * @param WC_Product $product Product duplicated.
		 * @return void
		 * @since  1.0.14
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function duplicate_products( $product_new, $product ) {
			if ( $product_new && 'auction' === $product_new->get_type() ) {
				$product_new->set_is_closed_by_buy_now( false );
				$product_new->set_is_in_overtime( false );
				$product_new->set_auction_paid_order( false );

				$product_new->set_send_winner_email( false );
				$product_new->set_send_admin_winner_email( false );
				yit_delete_prop( $product_new, 'yith_wcact_send_admin_not_reached_reserve_price', false );
				yit_delete_prop( $product_new, 'yith_wcact_send_admin_without_any_bids', false );

				// delete winner email user prop (since v2.0.1).
				yit_delete_prop( $product_new, 'yith_wcact_winner_email_is_send', false );
				yit_delete_prop( $product_new, 'yith_wcact_winner_email_send_custoner', false );
				yit_delete_prop( $product_new, '_yith_wcact_winner_email_max_bidder', false );
				yit_delete_prop( $product_new, 'yith_wcact_winner_email_is_not_send', false );
				yit_delete_prop( $product_new, 'current_bid', false );

				$product_new->save();
			}
		}

		/**
		 * Regenerate auction prices
		 *
		 * Regenerate auction prices for each product
		 *
		 * @return void
		 * @since  1.2.2
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function yith_wcact_send_auction_winner_email() {
			if ( current_user_can( 'manage_options' ) ) {
				$args = array(
					'post_type'   => 'product',
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'relation' => 'AND',
						array(
							'key'     => 'yith_wcact_winner_email_is_not_send',
							'value'   => '1',
							'compare' => '=',
						),
						array(
							'key'     => '_yith_auction_to',
							'value'   => strtotime( 'now' ),
							'compare' => '<=',
						),
					),
				);

				// Get all Auction ids.
				$auction_ids = get_posts( $args );

				if ( $auction_ids ) {
					foreach ( $auction_ids as $auction_id ) {
						$product    = wc_get_product( $auction_id );
						$instance   = YITH_Auctions()->bids;
						$max_bidder = $instance->get_max_bid( $product->get_id() );

						if ( 'auction' === $product->get_type() && $max_bidder && $product->is_closed() ) {
							$user = get_user_by( 'id', $max_bidder->user_id );
							$product->set_send_winner_email( false );
							yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );

							$product->save();

							WC()->mailer();

							/**
							 * DO_ACTION: yith_wcact_auction_winner
							 *
							 * Allow to fire some action when the auction has ended and has a winner.
							 *
							 * @param WC_Product $product Product object
							 * @param WP_User    $user    User object
							 * @param object     $max_bid Max bid object
							 */
							do_action( 'yith_wcact_auction_winner', $product, $user, $max_bidder );
						}
					}
				}
			}
		}

		/**
		 * Plugin Row Meta
		 *
		 * @param array  $new_row_meta_args Meta args.
		 * @param array  $plugin_meta Plugin meta.
		 * @param string $plugin_file Plugin file.
		 * @param string $plugin_data Plugin data.
		 * @param string $status status.
		 * @param string $init_file Init file.
		 * @return array
		 * @since  1.2.3
		 * @author Carlos Rodríguez <carlos.rodriguez@youirinspiration.it>
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCACT_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Action links
		 *
		 * @param  array $links links.
		 * @return array
		 * @since  1.2.3
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, true, YITH_WCACT_SLUG );

			return $links;
		}

		/* === EDIT PROFILE METHODS === */

		/**
		 * Render auction fields on user profile
		 *
		 * @param  WP_User $user \WP_User User object.
		 * @return void
		 * @since  1.0.0
		 */
		public function render_auction_extra_fields( $user ) {
			/**
			 * APPLY_FILTERS: yith_wcact_panel_capability
			 *
			 * Filter the capability for the plugin panel.
			 *
			 * @param string $capability Capability
			 *
			 * @return string
			 */
			if ( ! current_user_can( apply_filters( 'yith_wcact_panel_capability', 'manage_woocommerce' ) ) ) {
				return;
			}

			$is_banned   = get_user_meta( $user->ID, '_yith_wcact_user_ban', true );
			$ban_message = get_user_meta( $user->ID, '_yith_wcact_ban_message', true );

			?>
			<hr />
			<h3><?php esc_html_e( 'Auction details', 'yith-auctions-for-woocommerce' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="banned"><?php esc_html_e( 'Banned', 'yith-auctions-for-woocommerce' ); ?></label></th>
					<td>
						<input type="checkbox" name="yith_wcact_banned" id="yith_wcact_banned" value="1" <?php checked( $is_banned, true ); ?> />
						<span class="description"><?php esc_html_e( 'Check this option if you want to ban user from bidding', 'yith-auctions-for-woocommerce' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="yith_wcact_banned_message"><?php esc_html_e( 'Ban Message', 'yith-auctions-for-woocommerce' ); ?></label></th>
					<td>
						<textarea name="yith_wcact_banned_message" id="yith_wcact_banned_message" cols="50" rows="10"><?php echo esc_textarea( $ban_message ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optionally you can show a message, explaining why the user has been banned', 'yith-auctions-for-woocommerce' ); ?></p>
					</td>
				</tr>
			</table>
			<?php
			wp_nonce_field( 'yith-wcact-banned-customer-form', 'yith_wcact_banned_customer_form' );
		}

		/**
		 * Save auction fields on user profile
		 *
		 * @param int $user_id int User id.
		 * @return bool Whether method actually saved option or not
		 * @since  1.0.0
		 */
		public function save_auction_extra_fields( $user_id ) {
			if ( ! current_user_can( apply_filters( 'yith_wcact_panel_capability', 'manage_woocommerce' ) ) ) {
				return;
			}

			if ( isset( $_POST['yith_wcact_banned_customer_form'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yith_wcact_banned_customer_form'] ) ), 'yith-wcact-banned-customer-form' ) ) {
				$is_banned      = isset( $_POST['yith_wcact_banned'] ) ? sanitize_key( wp_unslash( $_POST['yith_wcact_banned'] ) ) : 0;
				$banned_message = isset( $_POST['yith_wcact_banned_message'] ) ? wp_kses_post( wp_unslash( $_POST['yith_wcact_banned_message'] ) ) : false;

				update_user_meta( $user_id, '_yith_wcact_user_ban', $is_banned );
				update_user_meta( $user_id, '_yith_wcact_ban_message', $banned_message );
			}
		}

		/**
		 * If product is not an auction product, remove meta related to _yith_is_an_auction_product
		 *
		 * @param int     $post_id Post id.
		 * @param WP_Post $post post object.
		 * @since 1.3.1
		 */
		public function check_if_an_auction_product( $post_id, $post ) {
			$product = wc_get_product( $post_id );

			if ( $product ) {
				$is_an_auction_product = get_post_meta( $post_id, '_yith_is_an_auction_product', true );

				if ( 'auction' !== $product->get_type() && $is_an_auction_product ) {
					delete_post_meta( $post_id, '_yith_is_an_auction_product', true );

					wp_delete_object_term_relationships( $post_id, 'yith_wcact_auction_status' ); // Remove auction status relationships if product is not an auction.
				} else {
					if ( 'auction' === $product->get_type() && ! $is_an_auction_product ) {
						update_post_meta( $post_id, '_yith_is_an_auction_product', true );
					}
				}
			}
		}

		/**
		 * Set the product meta before saving the product
		 *
		 * @param WC_Product_Auction $product Auction product.
		 * @since 1.3.4
		 */
		public function set_product_meta_before_saving( $product ) {
			if ( ! $product instanceof WC_Product ) {
				$product = wc_get_product( $product );
			}

			if ( isset( $_POST['yith_wcact_auction_form'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yith_wcact_auction_form'] ) ), 'yith-wcact-auction-form' ) ) {
				if ( $product->is_type( self::$prod_type ) && ! $product->get_is_closed_by_buy_now() ) {
					try {
						/**
						 *  Data store object for auction product.
						 *
						 * @var YITH_WCACT_Product_Auction_Data_Store_CPT $data_store
						 */
						$data_store        = WC_Data_Store::load( 'product-auction' );
						$meta_key_to_props = $data_store->get_auction_meta_key_to_props();

						foreach ( $meta_key_to_props as $key => $prop ) {
							$setter = "set_{$prop}";

							if ( is_callable( array( $product, $setter ) ) ) {
								if ( $data_store->is_date_prop( $prop ) ) {
									$gmt_date = ( isset( $_POST[ $key ] ) ? strtotime( get_gmt_from_date( $_POST[ $key ] ) ) : '' ); // phpcs:ignore
									$product->$setter( $gmt_date );
								} elseif ( $data_store->is_decimal_prop( $prop ) ) {
									if ( isset( $_POST[ $key ] ) ) {
										$product->$setter( wc_format_decimal( wc_clean( $_POST[ $key ] ) ) ); // phpcs:ignore
									}
								} elseif ( $data_store->is_yes_no_prop( $prop ) ) {
									$value = isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ? 'yes' : 'no';

									$product->$setter( $value );
								} elseif ( isset( $_POST[ $key ] ) ) {
									$product->$setter( $_POST[ $key ] ); // phpcs:ignore
								}
							}
						}
					} catch ( Exception $e ) {
						$message = sprintf( 'Error when trying to set product meta before saving for auction product with id %s1. Exception: %s2', $product->get_id(), $e->getMessage() );
					}

					$product->save();
				}
			}
		}

		/**
		 * Print custom fields for general tab.
		 *
		 * @param array $field field type for show general custom field.
		 */
		public function general_custom_fields( $field ) {
			$path = YITH_WCACT_PATH . 'views/fields/' . $field['yith-wcact-type'] . '.php';

			if ( file_exists( $path ) ) {
				include $path;
			}
		}

		/**
		 * Print custom fields for product tab.
		 *
		 * @param array $field field type for show custom field on edit product page.
		 */
		public function product_custom_fields( $field ) {
			$path = YITH_WCACT_PATH . 'views/fields/product/' . $field['yith-wcact-type'] . '.php';

			if ( file_exists( $path ) ) {
				include $path;
			}
		}

		/**
		 * Print auction list table.
		 */
		public function auction_list_tab() {
			$query_args = array(
				'type' => 'auction',
			);
			$items      = wc_get_products( $query_args );

			if ( ! empty( $items ) ) {
				$path = YITH_WCACT_PATH . 'views/panel/auction-list-tab.php';
			} else {
				$path = YITH_WCACT_PATH . 'views/panel/create-your-first-auction-product.php';
			}

			if ( file_exists( $path ) ) {
				include $path;
			}
		}

		/**
		 * Save general fields custom
		 */
		public function save_general_settings() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing

			if ( isset( $_POST['yith_wcact_settings_bid_type'] ) && isset( $_POST['yith_wcact_settings_automatic_bid_type'] ) && 'automatic' === $_POST['yith_wcact_settings_bid_type'] ) {
				$bid_type = sanitize_text_field( wp_unslash( $_POST['yith_wcact_settings_automatic_bid_type'] ) );
				$value    = false;

				switch ( $bid_type ) {
					case 'simple':
						$value = isset( $_POST['ywcact_automatic_bid_simple'] ) ? intval( wp_unslash( $_POST['ywcact_automatic_bid_simple'] ) ) : 0;
						break;

					case 'advanced':
						$value = isset( $_POST['ywcact_automatic_bid_advanced'] ) && is_array( $_POST['ywcact_automatic_bid_advanced'] ) ? serialize( wp_unslash( $_POST['ywcact_automatic_bid_advanced'] ) ) : serialize( array() ); // phpcs:ignore
						break;
				}

				if ( $value ) {
					update_option( 'yith_wcact_settings_automatic_bid_increment', $value );
				}
			}

			if ( isset( $_POST['ywcact_general_overtime_before'] ) && ! empty( $_POST['ywcact_general_overtime_before'] ) ) {
				update_option( 'yith_wcact_settings_overtime_option', sanitize_text_field( wp_unslash( $_POST['ywcact_general_overtime_before'] ) ) );
			}

			if ( isset( $_POST['ywcact_general_overtime'] ) && ! empty( $_POST['ywcact_general_overtime'] ) ) {
				update_option( 'yith_wcact_settings_overtime', sanitize_text_field( wp_unslash( $_POST['ywcact_general_overtime'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_cron_number'] ) && ! empty( $_POST['ywcact_settings_cron_number'] ) ) {
				update_option( 'yith_wcact_settings_cron_auction_number_days', sanitize_text_field( wp_unslash( $_POST['ywcact_settings_cron_number'] ) ) );
			}

			if ( isset( $_POST['yith_wcact_settings_cron_auction_type_numbers'] ) && ! empty( $_POST['yith_wcact_settings_cron_auction_type_numbers'] ) ) {
				update_option( 'yith_wcact_settings_cron_auction_type_numbers', sanitize_text_field( wp_unslash( $_POST['yith_wcact_settings_cron_auction_type_numbers'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_reschedule_number'] ) && ! empty( $_POST['ywcact_settings_reschedule_number'] ) ) {
				update_option( 'yith_wcact_settings_automatic_reschedule_auctions_number', sanitize_text_field( wp_unslash( $_POST['ywcact_settings_reschedule_number'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_reschedule_unit'] ) && ! empty( $_POST['ywcact_settings_reschedule_unit'] ) ) {
				update_option( 'yith_wcact_settings_automatic_reschedule_auctions_unit', sanitize_text_field( wp_unslash( $_POST['ywcact_settings_reschedule_unit'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_reschedule_not_paid_number'] ) && ! empty( $_POST['ywcact_settings_reschedule_not_paid_number'] ) ) {
				update_option( 'ywcact_settings_reschedule_not_paid_number', sanitize_text_field( wp_unslash( $_POST['ywcact_settings_reschedule_not_paid_number'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_reschedule_not_paid_number_unit'] ) && ! empty( $_POST['ywcact_settings_reschedule_not_paid_number_unit'] ) ) {
				update_option( 'ywcact_settings_reschedule_not_paid_number_unit', sanitize_text_field( wp_unslash( $_POST['ywcact_settings_reschedule_not_paid_number_unit'] ) ) );
			}

			if ( isset( $_POST['yith_wcact_general_time_format'] ) && isset( $_POST['yith_wcact_general_time_format_text'] ) && 'yith_wcact_custom_value' === $_POST['yith_wcact_general_time_format'] ) {
				update_option( 'yith_wcact_general_time_format', sanitize_text_field( wp_unslash( $_POST['yith_wcact_general_time_format_text'] ) ) );
			}

			if ( isset( $_POST['yith_wcact_customization_countdown_color_unit'] ) && ! empty( $_POST['yith_wcact_customization_countdown_color_unit'] ) ) {
				update_option( 'yith_wcact_customization_countdown_color_unit', sanitize_text_field( wp_unslash( $_POST['yith_wcact_customization_countdown_color_unit'] ) ) );
			}

			if ( isset( $_POST['yith_wcact_customization_countdown_color_numbers'] ) && ! empty( $_POST['yith_wcact_customization_countdown_color_numbers'] ) ) {
				update_option( 'yith_wcact_customization_countdown_color_numbers', sanitize_text_field( wp_unslash( $_POST['yith_wcact_customization_countdown_color_numbers'] ) ) );
			}

			if ( isset( $_POST['yith_wcact_customization_countdown_color_style'] ) && ! empty( $_POST['yith_wcact_customization_countdown_color_style'] ) ) {
				update_option( 'yith_wcact_customization_countdown_color_style', sanitize_text_field( wp_unslash( $_POST['yith_wcact_customization_countdown_color_style'] ) ) );
			}

			if ( isset( $_POST['ywcact_settings_reschedule_auction_not_paid'] ) && ! empty( $_POST['ywcact_settings_reschedule_auction_not_paid'] ) ) {
				update_option( 'ywcact_settings_reschedule_auction_not_paid', array_map( 'sanitize_text_field', wp_unslash( $_POST['ywcact_settings_reschedule_auction_not_paid'] ) ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Add an option to let the admin set the Auction as a physical good or digital goods.
		 *
		 * @param array $array option array.
		 *
		 * @return mixed
		 * @author Carlos Rodríguez
		 * @since  2.0.0
		 */
		public function add_type_option( $array ) {
			$array['virtual']['wrapper_class']      .= ' show_if_auction';
			$array['downloadable']['wrapper_class'] .= ' show_if_auction';

			/**
			 * APPLY_FILTERS: yith_wcact_auction_type_options
			 *
			 * Filter the auction product type options.
			 *
			 * @param array $product_type_options Product type options
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_auction_type_options', $array );
		}

		/**
		 * Process export, and generate csv file to download with auction information
		 *
		 * @return void
		 * @since  2.0.9
		 */
		public function export_csv() {
			$query_arg = array();

			if ( isset( $_REQUEST['yith_wcact_auction_product_list'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['yith_wcact_auction_product_list'] ) ), 'yith-wcact-auction-product-list' ) ) {
				if ( ! isset( $_REQUEST['page'] ) || 'yith_wcact_panel_product_auction' !== $_REQUEST['page'] || ! isset( $_REQUEST['tab'] ) || 'auction-list' !== $_REQUEST['tab'] || ! isset( $_REQUEST['export_action'] ) ) {
					return;
				}

				if ( ! empty( $_GET['status'] ) && 'all' !== $_GET['status'] ) {
					$query_arg['status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
				}

				/**
				 * APPLY_FILTERS: yith_wcact_auction_amount_export_csv_per_page
				 *
				 * Filter the number of items that will be exported per page in the CSV.
				 *
				 * @param int $per_page Number of items per page
				 *
				 * @return int
				 */
				$per_page     = apply_filters( 'yith_wcact_auction_amount_export_csv_per_page', 15 );
				$current_page = isset( $_REQUEST['paged'] ) ? max( 1, absint( $_REQUEST['paged'] ) ) : 1;

				/**
				 * APPLY_FILTERS: yith_wcact_auction_list_columns
				 *
				 * Filter the columns of the auctions table.
				 *
				 * @param array $columns Columns
				 *
				 * @return array
				 */
				$headings = apply_filters(
					'yith_wcact_auction_list_columns',
					array(
						'auction'       => esc_html__( 'Auction', 'yith-auctions-for-woocommerce' ),
						'started_on'    => esc_html__( 'Started on', 'yith-auctions-for-woocommerce' ),
						'start_price'   => esc_html__( 'Start price', 'yith-auctions-for-woocommerce' ),
						'current_bid'   => esc_html__( 'Current bid', 'yith-auctions-for-woocommerce' ),
						'bids'          => esc_html__( 'Bids', 'yith-auctions-for-woocommerce' ),
						'bidders'       => esc_html__( 'Bidders', 'yith-auctions-for-woocommerce' ),
						'followers'     => esc_html__( 'Followers', 'yith-auctions-for-woocommerce' ),
						'watchers'      => esc_html__( 'Watchers', 'yith-auctions-for-woocommerce' ),
						'reserve_price' => esc_html__( 'Reserve price', 'yith-auctions-for-woocommerce' ),
						'end_on'        => esc_html__( 'End on', 'yith-auctions-for-woocommerce' ),
						'status'        => esc_html__( 'Status', 'yith-auctions-for-woocommerce' ),
					)
				);

				$query_args = array();

				$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['auction_type'] ) ) : '';

				if ( ! empty( $auction_type ) ) {
					$query_args['ywcact_auction_type'] = $auction_type;
				}

				$products = wc_get_products(
					array_merge(
						array(
							'type'   => 'auction',
							'limit'  => $per_page,
							'offset' => ( ( $current_page - 1 ) * $per_page ),
							's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
						),
						$query_args
					)
				);

				if ( ! empty( $products ) ) {
					$instance = YITH_Auctions()->bids;

					$sitename  = sanitize_key( get_bloginfo( 'name' ) );
					$sitename .= ( ! empty( $sitename ) ) ? '-' : '';
					$filename  = $sitename . 'auction-products-' . gmdate( 'Y-m-d' ) . '.csv';

					header( 'Content-Description: File Transfer' );
					header( 'Content-Disposition: attachment; filename=' . $filename );
					header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

					$df = fopen( 'php://output', 'w' );

					fputcsv( $df, $headings );

					foreach ( $products as $product ) {
						$values = array();

						foreach ( $headings as $key => $heading ) {
							switch ( $key ) {
								case 'auction':
									$output = $product->get_title();
									break;

								case 'started_on':
									$format_date = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
									$format_time = get_option( 'yith_wcact_general_time_format', 'h:i:s' );

									$format = $format_date . ' ' . $format_time;

									$output = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $product->get_start_date() ), $format );
									break;

								case 'start_price':
									$output = $product->get_start_price();
									break;

								case 'current_bid':
									$output = $product->get_current_bid();
									break;

								case 'bids':
									$bids = $instance->get_bids_auction( $product->get_id() );

									if ( $bids && is_array( $bids ) && ! empty( $bids ) ) {
										$bids_value = count( $bids );
									} else {
										$bids_value = 0;
									}

									$output = $bids_value;
									break;

								case 'bidders':
									$users = $instance->get_bidders( $product->get_id() );

									$output = ! empty( $users ) ? $users : 0;
									break;

								case 'followers':
									$followers = $instance->get_users_count_product_on_follower_list( $product->get_id() );

									if ( $followers && is_array( $followers ) && ! empty( $followers ) ) {
										$output = count( $followers );
									} else {
										$output = 0;
									}
									break;

								case 'watchers':
									$users_watchlist = $instance->get_users_count_product_on_watchlist( $product->get_id() );
									$output          = ! empty( $users_watchlist ) ? $users_watchlist : 0;
									break;

								case 'reserve_price':
									$reserve_price = $product->get_reserve_price();

									if ( $reserve_price > 0 ) {
										$output = $reserve_price;
									} else {
										$output = esc_html( '-' );
									}
									break;

								case 'end_on':
									$end_date = $product->get_end_date();
									$time_now = time();

									if ( $end_date > $time_now ) {
										$format_date = get_option( 'yith_wcact_general_date_format', 'j/n/Y' );
										$format_time = get_option( 'yith_wcact_general_time_format', 'h:i:s' );
										$format      = $format_date . ' ' . $format_time;

										$output = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $product->get_end_date() ), $format );
									} else {
										$output = esc_html__( 'Ended', 'yith-auctions-for-woocommerce' );
									}
									break;

								case 'status':
									$product_status = $product->get_status();

									if ( 'draft' === $product_status ) {
										$output = esc_html__( 'Draft', 'yith-auctions-for-woocommerce' );
										break;
									} else {
										$value_type = '';
										$type       = $product->get_auction_status();

										switch ( $type ) {
											case 'non-started':
												$value_type = esc_attr__( 'Scheduled', 'yith-auctions-for-woocommerce' );
												break;

											case 'started':
												$value_type = esc_attr__( 'Started', 'yith-auctions-for-woocommerce' );
												break;

											case 'finished':
												$value_type = esc_attr__( 'Ended', 'yith-auctions-for-woocommerce' );
												break;

											case 'started-reached-reserve':
												$value_type = esc_attr__( 'Started and not exceeded the reserve price', 'yith-auctions-for-woocommerce' );
												break;

											case 'finished-reached-reserve':
												$value_type = esc_attr__( 'Ended and not exceeded the reserve price', 'yith-auctions-for-woocommerce' );
												break;

											case 'finnish-buy-now':
												$value_type = esc_attr__( 'Purchased through buy now', 'yith-auctions-for-woocommerce' );
												break;
										}

										$output = $value_type;
									}
									break;

								default:
									/**
									 * APPLY_FILTERS: yith_wcact_auction_list_output_column
									 *
									 * Filter the content of the default column in the auctions table.
									 *
									 * @param string     $output      Column output
									 * @param string     $column_name Column name
									 * @param WC_Product $product     Product object
									 */
									$output = apply_filters( 'yith_wcact_auction_list_output_column', '', $key, $product );
							}

							$values[] = $output;
						}

						fputcsv( $df, $values );
					}

					fclose( $df ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

					die();
				}
			}
		}
	}
}
