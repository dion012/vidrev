<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Product_Auction_Data_Store_CPT Class.
 *
 * @package YITH\Auctions\Includes\DataStores
 */

! defined( 'ABSPATH' ) && exit;

/**
 * YITH Auction Product Data Store: Stored in CPT.
 *
 * @since 1.3.4
 */
class YITH_WCACT_Product_Auction_Data_Store_CPT extends WC_Product_Data_Store_CPT implements WC_Object_Data_Store_Interface {

	/**
	 * Meta keys and how they transfer to CRUD props.
	 *
	 * @var array
	 */
	protected $auction_meta_key_to_props = array(
		// ------ Auction Settings --------------------------------------------------
		'_yith_auction_start_price'                     => 'start_price',
		'_yith_auction_bid_increment'                   => 'bid_increment', // bid increment for simple value.
		'_yith_auction_minimum_increment_amount'        => 'minimum_increment_amount',
		'_yith_auction_reserve_price'                   => 'reserve_price',
		'_yith_auction_buy_now'                         => 'buy_now',
		'_yith_check_time_for_overtime_option'          => 'check_time_for_overtime_option',
		'_yith_overtime_option'                         => 'overtime_option',
		'_yith_wcact_auction_automatic_reschedule'      => 'automatic_reschedule',
		'_yith_wcact_automatic_reschedule_auction_unit' => 'automatic_reschedule_auction_unit',
		'_yith_wcact_show_upbid'                        => 'upbid_checkbox',
		'_yith_wcact_show_overtime'                     => 'overtime_checkbox',
		'_yith_auction_for'                             => 'start_date',
		'_yith_auction_to'                              => 'end_date',
		'_yith_is_in_overtime'                          => 'is_in_overtime',
		'_yith_auction_closed_buy_now'                  => 'is_closed_by_buy_now',
		'_yith_auction_paid_order'                      => 'auction_paid_order',
		'_yith_wcact_order_id'                          => 'order_id',
		'_yith_wcact_item_condition'                    => 'item_condition',
		'_yith_wcact_auction_type'                      => 'auction_type',
		'_yith_wcact_auction_sealed'                    => 'auction_sealed',
		'_yith_auction_buy_now_onoff'                   => 'buy_now_onoff',
		'_yith_auction_bid_type_onoff'                  => 'bid_type_onoff',
		'_yith_wcact_bid_type_set_radio'                => 'bid_type_set_radio',
		'_yith_wcact_bid_type_radio'                    => 'bid_type_radio',
		'_yith_auction_fee_onoff'                       => 'fee_onoff',
		'_yith_auction_fee_ask_onoff'                   => 'fee_ask_onoff',
		'_yith_auction_fee_amount'                      => 'fee_amount',
		'_yith_auction_reschedule_onoff'                => 'reschedule_onoff',
		'_yith_auction_reschedule_closed_without_bids_onoff' => 'reschedule_closed_without_bids_onoff',
		'_yith_auction_reschedule_reserve_no_reached_onoff' => 'reschedule_reserve_no_reached_onoff',
		'_yith_auction_overtime_onoff'                  => 'overtime_onoff',
		'_yith_auction_overtime_set_onoff'              => 'overtime_set_onoff',
		'_yith_auction_commission_fee_onoff'            => 'commission_fee_onoff',
		'_yith_auction_commission_apply_fee_onoff'      => 'commission_apply_fee_onoff',
		'_yith_auction_commission_fee'                  => 'commission_fee',
		'_yith_auction_commission_label'                => 'commission_fee_label',
		'_yith_auction_bid_increment_advanced'          => 'bid_increment_advanced',

		// ------ Email props --------------------------------------------------.
		'_yith_wcact_send_winner_email'                 => 'send_winner_email',
		'_yith_wcact_send_admin_winner_email'           => 'send_admin_winner_email',

		'_yith_wcact_payment_gateway'                   => 'payment_gateway',
	);

	/**
	 * Date props
	 *
	 * @var array
	 */
	private $auction_date_props = array(
		'start_date',
		'end_date',
	);

	/**
	 * Decimal props
	 *
	 * @var array
	 */
	private $auction_decimal_props = array(
		'start_price',
		'bid_increment',
		'minimum_increment_amount',
		'reserve_price',
		'buy_now',
		'check_time_for_overtime_option',
		'overtime_option',
		'automatic_reschedule',
		'fee_amount',
	);

	/**
	 * Yes_no props
	 *
	 * @var array
	 */
	private $auction_yes_no_props = array(
		'upbid_checkbox',
		'overtime_checkbox',
		'auction_sealed',
		'buy_now_onoff',
		'bid_type_onoff',
		'fee_onoff',
		'fee_ask_onoff',
		'reschedule_onoff',
		'reschedule_closed_without_bids_onoff',
		'reschedule_reserve_no_reached_onoff',
		'overtime_onoff',
		'overtime_set_onoff',
		'commission_fee_onoff',
		'commission_apply_fee_onoff',
	);

	/**
	 * Boolean props
	 *
	 * @var array
	 */
	private $auction_boolean_props = array(
		'is_in_overtime',
		'is_closed_by_buy_now',
		'auction_paid_order',
		'send_winner_email',
		'send_admin_winner_email',
	);

	/**
	 * YITH_WCBK_Product_Auction_Data_Store_CPT constructor.
	 */
	public function __construct() {
		if ( is_callable( 'parent::__construct' ) ) {
			parent::__construct();
		}

		$this->internal_meta_keys = array_merge( $this->internal_meta_keys, array_keys( $this->auction_meta_key_to_props ) );
	}

	/**
	 * Force meta values on save.
	 *
	 * @param WC_Product_Auction $product Auction product.
	 */
	protected function force_meta_values( &$product ) {
		$product->set_regular_price( '' );
		$product->set_sale_price( '' );
		$product->set_manage_stock( true );
		$product->set_stock_status( 'instock' );
		$product->set_stock_quantity( 1 );
	}

	/**
	 * Method to create a new product in the database.
	 *
	 * @param WC_Product_Auction $product Auction product.
	 */
	public function create( &$product ) {
		parent::create( $product );

		$this->force_meta_values( $product );
	}

	/**
	 * Method to update a product in the database.
	 *
	 * @param WC_Product_Auction $product Auction product.
	 * @since 1.3.4
	 */
	public function update( &$product ) {
		parent::update( $product );

		$this->force_meta_values( $product );
	}

	/**
	 * Helper method that updates all the post meta for a product based on it's settings in the WC_Product class.
	 *
	 * @param WC_Product $product Product.
	 * @param bool       $force Force all props to be written even if not changed. This is used during creation.
	 * @since 1.3.4
	 */
	public function update_post_meta( &$product, $force = false ) {
		parent::update_post_meta( $product, $force );

		$props_to_update = $force ? $this->auction_meta_key_to_props : $this->get_props_to_update( $product, $this->auction_meta_key_to_props );

		// ToDo clean props in onoff is in off value.
		foreach ( $props_to_update as $meta_key => $prop ) {
			if ( is_callable( array( $product, "get_$prop" ) ) ) {
				$value = $product->{"get_$prop"}( 'edit' );

				if ( $this->is_decimal_prop( $prop ) ) {
					$value = wc_format_decimal( wc_clean( $value ) );
				} elseif ( $this->is_boolean_prop( $prop ) ) {
					$value = wc_bool_to_string( $value );
				}

				$updated = $this->update_or_delete_post_meta( $product, $meta_key, $value );

				if ( $updated ) {
					$this->updated_props[] = $prop;
				}
			}
		}
	}

	/**
	 * Read product data. Can be overridden by child classes to load other props.
	 *
	 * @param WC_Product $product Product.
	 * @since 1.3.4
	 */
	public function read_product_data( &$product ) {
		parent::read_product_data( $product );

		$props_to_set = array();

		foreach ( $this->auction_meta_key_to_props as $meta_key => $prop ) {
			if ( metadata_exists( 'post', $product->get_id(), $meta_key ) ) {
				$value = get_post_meta( $product->get_id(), $meta_key, true );

				$props_to_set[ $prop ] = $this->is_boolean_prop( $prop ) ? wc_string_to_bool( $value ) : $value;
			}
		}

		$product->set_props( $props_to_set );
	}

	/**
	 * Check if a prop is a date
	 *
	 * @param  string $prop Prop key.
	 * @return bool
	 * @since  1.3.4
	 */
	public function is_date_prop( $prop ) {
		return in_array( $prop, $this->auction_date_props, true );
	}

	/**
	 * Check if a prop is a decimal
	 *
	 * @param  string $prop Prop key.
	 * @return bool
	 * @since  1.3.4
	 */
	public function is_decimal_prop( $prop ) {
		return in_array( $prop, $this->auction_decimal_props, true );
	}

	/**
	 * Check if a prop is a decimal
	 *
	 * @param  string $prop Prop key.
	 * @return bool
	 * @since  1.3.4
	 */
	public function is_yes_no_prop( $prop ) {
		return in_array( $prop, $this->auction_yes_no_props, true );
	}

	/**
	 * Check if a prop is boolean
	 *
	 * @param  string $prop Prop key.
	 * @return bool
	 */
	public function is_boolean_prop( $prop ) {
		return in_array( $prop, $this->auction_boolean_props, true );
	}

	/**
	 * Return meta key to prop array
	 *
	 * @return array
	 * @since  1.3.4
	 */
	public function get_auction_meta_key_to_props() {
		return $this->auction_meta_key_to_props;
	}
}
