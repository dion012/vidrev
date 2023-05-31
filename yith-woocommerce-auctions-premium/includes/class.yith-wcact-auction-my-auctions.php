<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auctions_My_Auctions Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class to manage auction endpoint on My account page.
 *
 * @class   YITH_Auctions_My_Auctions
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
class YITH_Auctions_My_Auctions {

	/**
	 * Single instance of the class
	 *
	 * @var   \YITH_Auctions_My_Auctions
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return YITH_Auctions_My_Auctions
	 * @since  1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'my-auction';

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );

		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint', array( $this, 'endpoint_content' ) );
		add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint', array( $this, 'currency_section' ), 15 );

	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param  array $vars Array with query variables allowed before processing.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param  string $title Endpoint title.
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$value_endpoint = $wp_query->query_vars[ self::$endpoint ];
			$title          = $value_endpoint;

			if ( ! empty( $value_endpoint ) ) {
				if ( 'auctions-list' === $value_endpoint ) {
					$title = esc_html__( 'My auctions list', 'yith-auctions-for-woocommerce' );
				}

				if ( 'watchlist' === $value_endpoint ) {
					$title = esc_html__( 'My watchlist', 'yith-auctions-for-woocommerce' );
				}
			} else {
				$title = esc_html__( 'My auctions', 'yith-auctions-for-woocommerce' );
			}

			// Remove filter to prevent duplicated.
			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Get current endpoint
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp_query;

		$current_endpoint = false;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint ) {
			$current_endpoint = self::$endpoint;
		}

		return $current_endpoint;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param  array $items enpoint items.
	 * @return array
	 */
	public function new_menu_items( $items ) {
		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

		// Insert your custom endpoint.
		$items[ self::$endpoint ] = esc_html__( 'Auctions', 'yith-auctions-for-woocommerce' );

		// Insert back the logout item.
		$items['customer-logout'] = $logout;

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @param string $value endpoint key.
	 */
	public function endpoint_content( $value ) {
		$default_url = wc_get_endpoint_url( 'my-auction' );
		$instance    = YITH_Auctions()->bids;
		$user_id     = get_current_user_id();
		$args        = array(
			'currency'           => get_woocommerce_currency(),
			'default_url'        => wc_get_endpoint_url( 'my-auction' ),
			'auctions_list_url'  => add_query_arg( self::$endpoint, 'auctions-list', $default_url ),
			'watchlist_list_url' => add_query_arg( self::$endpoint, 'watchlist', $default_url ),
			'instance'           => $instance,
			'user_id'            => $user_id,
		);

		if ( isset( $value ) && ! empty( $value ) ) {
			if ( 'auctions-list' === $value ) {
				$auctions_by_user = $instance->get_auctions_by_user( $user_id );

				$args['auctions_by_user'] = $this->populate_auction_user( $auctions_by_user, $user_id, $instance );

				wc_get_template( 'my-account-my-auction.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/my-account/' );
			}

			if ( 'watchlist' === $value ) {
				$watchlist_by_user = $instance->get_watchlist_product_by_user( $user_id );

				$args['auctions_by_user'] = $this->populate_watchlist_user( $watchlist_by_user, $user_id, $instance );

				wc_get_template( 'my-auctions-my-watchlist.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/my-account/' );
			}
		} else {
			/**
			 * APPLY_FILTERS: yith_wcact_my_account_table_limit
			 *
			 * Filter the number to limit how many actuions will be displayed in the My Account page.
			 *
			 * @param int $limit Limit
			 *
			 * @return int
			 */
			$limit = apply_filters( 'yith_wcact_my_account_table_limit', 3 );

			$auctions_by_user  = $instance->get_auctions_by_user( $user_id, $limit, 'started' );
			$watchlist_by_user = $instance->get_watchlist_product_by_user( $user_id, $limit );

			$auctions = $this->populate_auction_user( $auctions_by_user, $user_id, $instance );

			$args['limit']             = $limit;
			$args['auctions_by_user']  = $auctions;
			$args['watchlist_by_user'] = $this->populate_watchlist_user( $watchlist_by_user, $user_id, $instance );

			$args['total_auctions']  = count( $instance->get_auctions_by_user( $user_id ) );
			$args['total_watchlist'] = count( $instance->get_watchlist_product_by_user( $user_id ) );

			// Print here the default endpoint value.
			wc_get_template( 'my-auctions-index.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/my-account/' );
		}
	}

	/**
	 * Populate auction user
	 * Return an array with all options to pass to the template.
	 *
	 * @param array       $auctions_by_user Array with options retrieve from the database.
	 * @param int         $user_id          Userid.
	 * @param bool/object $instance  Class to interact with the database.
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 * @version 3.0
	 * @return array
	 */
	public function populate_auction_user( $auctions_by_user, $user_id, $instance = false ) {
		$auctions = array();

		if ( ! empty( $auctions_by_user ) ) {
			$show_add_to_cart = ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_show_add_to_cart_in_auction_product', 'no' ) );

			foreach ( $auctions_by_user as $product ) {
				$auction = wc_get_product( $product->auction_id );

				if ( 'auction' !== $auction->get_type() ) {
					continue;
				}

				$auction_product_type = $auction->get_auction_type();

				$max_bid = $auction_product_type && 'reverse' === $auction_product_type ? $instance->get_min_bid( $product->auction_id ) : $instance->get_max_bid( $product->auction_id );

				$is_closed = $auction->is_closed() ? true : false;

				$label = $is_closed ? esc_html__( 'Closed', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Started', 'yith-auctions-for-woocommerce' );

				$button = false;

				if ( (int) $max_bid->user_id === (int) $user_id ) {
					$color = 'yith-wcact-max-bidder';

					if ( $is_closed && ! $auction->get_auction_paid_order() && ( ! $auction->has_reserve_price() || ( $auction->has_reserve_price() && $max_bid->bid >= $auction->get_reserve_price() ) ) ) {
						$is_winner = true;
						$order_id  = $auction->get_order_id();

						if ( $order_id && $order_id > 0 ) {  // Product is associated to an order.
							$order = wc_get_order( $order_id );

							if ( $order ) {
								$url          = $order->get_checkout_payment_url();
								$button_label = $order->needs_payment() ? esc_html__( 'Pay order', 'yith-auctions-for-woocommerce' ) : esc_html__( 'View order', 'yith-auctions-for-woocommerce' );
								$button_class = 'auction_add_to_cart_button button alt auction_pay_order';
							}
						} else {
							/**
							 * APPLY_FILTERS: yith_wcact_get_checkout_url
							 *
							 * Filter the URL to redirect to pay the won auction.
							 *
							 * @param string $url        URL
							 * @param int    $product_id Product ID
							 *
							 * @return string
							 */
							$url          = $show_add_to_cart ? add_query_arg( array( 'add-to-cart' => $auction->get_id() ), '' ) : add_query_arg( array( 'yith-wcact-pay-won-auction' => $auction->get_id() ), apply_filters( 'yith_wcact_get_checkout_url', wc_get_checkout_url(), $auction->get_id() ) );
							$button_label = $show_add_to_cart ? esc_html__( 'Add to cart', 'yith-auctions-for-woocommerce' ) : esc_html__( 'Pay now', 'yith-auctions-for-woocommerce' );
							$button_class = implode(
								' ',
								array_filter(
									array(
										'auction_add_to_cart_button button alt',
										'product_type_auction',
										$show_add_to_cart && $auction->is_purchasable() && $auction->is_in_stock() ? 'add_to_cart_button' : '',
										$show_add_to_cart && $auction->supports( 'ajax_add_to_cart_on_my_account' ) && $auction->is_purchasable() && $auction->is_in_stock() ? 'ajax_add_to_cart' : '',
									)
								)
							);
						}

						$button = array(
							'url'          => esc_url( $url ),
							'button_label' => $button_label,
							'button_class' => $button_class,
							'attributes'   => wc_implode_html_attributes(
								array(
									'data-product_id'  => $auction->get_id(),
									'data-product_sku' => $auction->get_sku(),
									'aria-label'       => $auction->add_to_cart_description(),
									'rel'              => 'nofollow',
								)
							),
						);

						/**
						 * APPLY_FILTERS: yith_wcact_my_account_congratulation_message
						 *
						 * Filter the message shown in the My Account page when the user has won the auction.
						 *
						 * @param string $message Message
						 *
						 * @return string
						 */
						$label = apply_filters( 'yith_wcact_my_account_congratulation_message', esc_html__( 'You won this auction', 'yith-auctions-for-woocommerce' ) );
					}
				} else {
					$color = 'yith-wcact-outbid-bidder';
				}

				$auctions[] = array(
					'product'              => $auction,
					'product_name'         => get_the_title( $product->auction_id ),
					'product_url'          => get_the_permalink( $product->auction_id ),
					'image'                => $auction->get_image(),
					'auction_product_type' => $auction_product_type,
					'max_bid'              => $max_bid,
					'max_bid_value'        => $max_bid ? $max_bid->bid : 0,
					'last_bid_user'        => $instance->get_last_bid_user( $user_id, $product->auction_id ),
					'color'                => $color,
					'is_closed'            => $is_closed,
					'status'               => $is_closed ? 'closed' : 'started',
					'label'                => $label,
					'button'               => $button,
					'is_winner'            => isset( $is_winner ) ? $is_winner : false,
				);
			}
		}

		/**
		 * APPLY_FILTERS: yith_wcact_populate_auction_user
		 *
		 * Filter the array with the data to be used in the My Account page.
		 *
		 * @param array $auctions Auctions data
		 * @param int   $user_id  User ID
		 *
		 * @return array
		 */
		return apply_filters( 'yith_wcact_populate_auction_user', $auctions, $user_id );
	}

	/**
	 * Populate watchlist user
	 * Return an array with all options to pass to the template.
	 *
	 * @param array       $watchlist_by_user Array with options retrieve from the database.
	 * @param int         $user_id          Userid.
	 * @param bool/object $instance  Class to interact with the database.
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 * @version 3.0
	 * @return array
	 */
	public function populate_watchlist_user( $watchlist_by_user, $user_id, $instance = false ) {
		$auctions = array();

		foreach ( $watchlist_by_user as $watchlist_product ) {
			$product = wc_get_product( $watchlist_product->auction_id );

			if ( ! $product || 'auction' !== $product->get_type() ) {
				continue;
			}

			$auction_product_type = $product->get_auction_type();
			$max_bid              = $auction_product_type && 'reverse' === $auction_product_type ? $instance->get_min_bid( $watchlist_product->auction_id ) : $instance->get_max_bid( $watchlist_product->auction_id );

			$auction_date = $product->is_start() ? $product->get_end_date() : $product->get_start_date();

			if ( $max_bid && (int) $max_bid->user_id === (int) $user_id ) {
				$color = 'yith-wcact-max-bidder';
			} else {
				$color = 'yith-wcact-outbid-bidder';
			}

			$auctions[] = array(
				'product'              => $product,
				'product_name'         => get_the_title( $watchlist_product->auction_id ),
				'product_url'          => get_the_permalink( $watchlist_product->auction_id ),
				'image'                => $product->get_image(),
				'auction_product_type' => $auction_product_type,
				'max_bid'              => $max_bid,
				'max_bid_value'        => $max_bid ? $max_bid->bid : 0,
				'last_bid_user'        => $instance->get_last_bid_user( $user_id, $watchlist_product->auction_id ),
				'color'                => $color,
				'auction_date'         => $auction_date,
			);
		}

		return $auctions;
	}

	/**
	 * Add WooCommerce currency on page load.
	 */
	public function currency_section() {
		?>
		<div class="yith-wcact-currency">
			<input type="hidden" id="yith_wcact_currency" name="yith_wcact_currency" value="<?php echo esc_html( get_woocommerce_currency() ); ?>">
		</div>
		<?php
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public static function install() {
		flush_rewrite_rules();
	}
}

// Flush rewrite rules on plugin activation.
register_activation_hook( __FILE__, array( 'YITH_Auctions_My_Auctions', 'install' ) );
