<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_AUCTION_WIDGET_Watchlist
 *
 * Widget related functions and widget registration.
 *
 * @author  YITH
 * @version 2.0.0
 * @package YITH\Auctions\Includes\Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class YITH_WCACT_Auction_Widget_Watchlist
 *
 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
class YITH_WCACT_Auction_Widget_Watchlist extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'yith-wcact-auction-watchlist',
			__( 'YITH Auction Watchlist', 'yith-auctions-for-woocommerce' ),
			array( 'description' => __( 'A list of products in the user\'s watchlist', 'yith-auctions-for-woocommerce' ) )
		);
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : '';
		$style      = isset( $instance['style'] ) && in_array( $instance['style'], array( 'mini', 'extended' ), true ) ? $instance['style'] : 'extended';
		$show_count = ( isset( $instance['show_count'] ) && 'yes' === $instance['show_count'] );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'yith-auctions-for-woocommerce' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" <?php checked( $style, 'extended' ); ?> value="extended"/>
				<?php esc_html_e( 'Show extended widget', 'yith-auctions-for-woocommerce' ); ?>
			</label>
			<br/>
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" <?php checked( $style, 'mini' ); ?> value="mini"/>
				<?php esc_html_e( 'Show mini widget', 'yith-auctions-for-woocommerce' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_count ); ?> />
				<?php esc_html_e( 'Show items count', 'yith-auctions-for-woocommerce' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title']      = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['style']      = ( isset( $new_instance['style'] ) && in_array( $new_instance['style'], array( 'mini', 'extended' ), true ) ) ? $new_instance['style'] : 'extended';
		$instance['show_count'] = ( isset( $new_instance['show_count'] ) && yith_plugin_fw_is_true( $new_instance['show_count'] ) ) ? 'yes' : 'no';

		return $instance;
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $default_args Args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $default_args, $instance ) {
		$bids = YITH_Auctions()->bids;

		$user_id            = get_current_user_id();
		$watchlist_products = is_user_logged_in() ? $bids->get_watchlist_product_by_user( $user_id ) : array();

		/**
		 * APPLY_FILTERS: yith_wcact_widget_watchlist_args
		 *
		 * Filter the array with the arguments sent to the template for the watchlist widget.
		 *
		 * @param array $args Array of arguments
		 *
		 * @return array
		 */
		$args = apply_filters(
			'yith_wcact_widget_watchlist_args',
			array(
				'user_id'            => $user_id,
				'watchlist_products' => $watchlist_products,
				/**
				 * APPLY_FILTERS: yith_wcact_watchlist_icon
				 *
				 * Filter the watchlist icon.
				 *
				 * @param string $watchlist_icon Watchlist icon
				 *
				 * @return string
				 */
				'heading_icon'       => apply_filters( 'yith_wcact_watchlist_icon', YITH_WCACT_ASSETS_URL . 'images/icon/auctionheart.png' ),
				'instance'           => $instance,
				'watchlist_url'      => yith_wcact_get_watchlist_url(),
			)
		);

		$args = array_merge( $args, $default_args );

		wc_get_template( 'widgets/ywcact-watchlist.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );
	}
}

/**
 * Register Widgets.
 *
 * @since 2.0.0
 */
function yith_wcact_register_watchlist_widget() {
	register_widget( 'YITH_WCACT_Auction_Widget_Watchlist' );
}
add_action( 'widgets_init', 'yith_wcact_register_watchlist_widget' );
