<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_AUCTION_WIDGET
 *
 * Widget related functions and widget registration.
 *
 * @author  YITH
 * @version 2.3.0
 * @package YITH\Auctions\Includes\Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class YITH_WCACT_Auction_Widget
 *
 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */
class YITH_WCACT_Auction_Widget extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass = 'yith-wcact-auction-widget';
		$this->widget_id       = 'yith_woocommerce_auctions';
		$this->widget_name     = esc_html__( 'YITH Auctions', 'yith-auctions-for-woocommerce' );
		$this->settings        = array(
			'title'               => array(
				'type'  => 'text',
				'std'   => '',
				'label' => esc_html__( 'Title', 'yith-auctions-for-woocommerce' ),
			),
			'show'                => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => esc_html__( 'Show', 'yith-auctions-for-woocommerce' ),
				'options' => array(
					'last'     => esc_html__( 'Last Auctions', 'yith-auctions-for-woocommerce' ),
					'featured' => esc_html__( 'Featured Auctions', 'yith-auctions-for-woocommerce' ),
				),
			),
			'non_started_auction' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__( 'Show not-started auctions', 'yith-auctions-for-woocommerce' ),
			),
			'number'              => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => esc_html__( 'Number of auctions to show', 'yith-auctions-for-woocommerce' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Query the products and return them.
	 *
	 * @param  array $args Args.
	 * @param  array $instance Widget instance.
	 * @return WP_Query
	 */
	public function get_products( $args, $instance ) {
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
		$show   = ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : $this->settings['show']['std'];

		$query_args = array(
			'posts_per_page' => $number,
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'no_found_rows'  => 1,
			'meta_query'     => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
		);

		$query_args['tax_query']    = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'auction',
			),
		);
		$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery

		if ( ! empty( $instance['non_started_auction'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_yith_auction_to',
				'value'   => strtotime( 'now' ),
				'compare' => '>',
			);
		} else {
			$query_args['meta_query'][] = array(
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
				),
			);
		}

		switch ( $show ) {
			case 'featured':
				$query_args['meta_query'][] = array(
					'key'   => '_featured',
					'value' => 'yes',
				);
				break;

			case 'last':
				$query_args['order']   = 'DESC';
				$query_args['orderby'] = 'date';
				break;
		}

		$query_args['yith_wcact_query_auction_widget'] = true;

		/**
		 * APPLY_FILTERS: yith_wcact_products_widget_query_args
		 *
		 * Filter the array with the query args for the query in the widget.
		 *
		 * @param array $query_args Query args
		 *
		 * @return array
		 */
		return new WP_Query( apply_filters( 'yith_wcact_products_widget_query_args', $query_args ) );
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args Args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		$products = $this->get_products( $args, $instance );

		if ( $products && $products->have_posts() ) {
			$this->widget_start( $args, $instance );

			/**
			 * APPLY_FILTERS: yith_wcact_before_widget_product_list
			 *
			 * Filter the HTML displayed before the products list in the widget.
			 *
			 * @param string $html_content HTML content
			 *
			 * @return string
			 */
			echo wp_kses_post( apply_filters( 'yith_wcact_before_widget_product_list', '<ul class="yith-auction-list-widget">' ) );

			while ( $products->have_posts() ) {
				$products->the_post();

				wc_get_template( 'widgets/yith-wcact-content-widget-auction.php', array(), '', YITH_WCACT_TEMPLATE_PATH . 'woocommerce/' );
			}

			/**
			 * APPLY_FILTERS: yith_wcact_after_widget_product_list
			 *
			 * Filter the HTML displayed after the products list in the widget.
			 *
			 * @param string $html_content HTML content
			 *
			 * @return string
			 */
			echo wp_kses_post( apply_filters( 'yith_wcact_after_widget_product_list', '</ul>' ) );

			$this->widget_end( $args );
		}

		wp_reset_postdata();

		echo $this->cache_widget( $args, ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Register Widgets.
 *
 * @since 1.0.0
 */
function yith_wcact_register_widgets() {
	register_widget( 'YITH_WCACT_Auction_Widget' );
}
add_action( 'widgets_init', 'yith_wcact_register_widgets' );
