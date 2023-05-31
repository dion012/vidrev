<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Current_Auctions_Elementor_Widget Class.
 *
 * @package YITH\Auctions\Includes\Compatibility\Elementor
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Button;
use ElementorPro\Modules\QueryControl\Module;

/**
 *  Class Current Auctions
 *
 * @class   YITH_WCACT_Compatibility
 * @package Yithemes
 * @since   Version 2.0.0
 * @author  Your Inspiration Themes
 */
class YITH_WCACT_Current_Auctions_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Get element name.
	 *
	 * Retrieve the element name.
	 *
	 * @since 2.0.0
	 * @access public*
	 * @return string The name.
	 */
	public function get_name() {
		return 'yith-wcact-current-auctions';
	}

	/**
	 * Get element title.
	 *
	 * Retrieve the element title.
	 *
	 * @since 2.0.0
	 *
	 * @return string Element title.
	 */
	public function get_title() {
		return esc_html__( 'Current Auctions', 'yith-auctions-for-woocommerce' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve the widget icon.
	 *
	 * @since 2.0.0
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fas fa-gavel';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the widget categories.
	 *
	 * @since 2.0.0
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'yith' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the widget keywords.
	 *
	 * @since 2.0.0
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'woocommerce', 'product', 'form', 'auction' );
	}

	/**
	 * Register YITH_WCACT_Current_Auctions_Elementor_Widget widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @deprecated Elementor 2.9.0
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_order_header',
			array(
				'label'       => esc_html__( 'Current Auctions', 'yith-auctions-for-woocommerce' ),
				'description' => esc_html__( 'Widget to show current auctions', 'yith-auctions-for-woocommerce' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render YITH_WCACT_Auction_Form_Elementor_Widget widget output on the frontend.
	 *
	 * @since  2.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$product_id = ! empty( $settings['product_id'] ) ? $settings['product_id'] : '';

		echo '<div class="yith-wcact-current-auctions-elementor-widget">';
		echo is_callable( 'apply_shortcodes' ) ? apply_shortcodes( '[yith_auction_current]' ) : do_shortcode( '[yith_auction_current]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}
}
