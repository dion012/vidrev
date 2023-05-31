<?php
/**
 * YITH_Vendors_Vacation_Admin class
 *
 * @since      4.0.0
 * @author     YITH
 * @package    YITH WooCommerce Multi Vendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Vacation_Admin' ) ) {
	/**
	 * YITH Vendors Vacation Admin class.
	 *
	 * @class      YITH_Vendors_Vacation_Admin
	 * @since      1.9.17
	 * @author     YITH
	 * @package    YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Vacation_Admin {

		/**
		 * Tab slug
		 *
		 * @var string
		 */
		protected $tab = 'vacation';

		/**
		 * Construct
		 *
		 * @since  1.9.17
		 * @author Francesco Licandro
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
			add_filter( 'yith_wcmv_admin_vendor_dashboard_tabs', array( $this, 'add_vendor_tab' ), 10, 2 );
			add_action( 'yith_wcmv_vendor_dashboard_panel_value', array( $this, 'filter_panel_value' ), 10, 3 );
			// Skip wc_clean for vacation message.
			add_filter( 'yith_wcmv_skip_wc_clean_for_fields_array', array( $this, 'skip_vacation_message_sanitize' ), 10, 1 );
		}

		/**
		 * Add admin vacation scripts
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_register_script( 'yith-wcmv-vendors-vacation', YITH_WPV_ASSETS_URL . 'js/admin/' . yit_load_js_file( 'vacation.js' ), array( 'jquery', 'jquery-ui-datepicker' ), YITH_WPV_VERSION, true );

			if ( yith_wcmv_is_plugin_panel( $this->tab ) ) {

				wp_enqueue_script( 'yith-wcmv-vendors-vacation' );
				wp_localize_script(
					'yith-wcmv-vendors-vacation',
					'yith_wcmv_vacation',
					array(
						'dateFormat' => apply_filters( 'yith_wcmv_vacation_module_datepicker_format', 'yy-mm-dd' ),
					)
				);
			}
		}

		/**
		 * Get panel tab label
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param string      $tabs   An array of dashboard tabs.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return string
		 */
		public function add_vendor_tab( $tabs, $vendor ) {
			$tabs[ $this->tab ] = _x( 'Vacation', '[Admin]Vacation module admin tab title', 'yith-woocommerce-product-vendors' );
			return $tabs;
		}

		/**
		 * Filter default panel options value to get Vendor data
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param mixed  $value Current field value.
		 * @param array  $field The field data.
		 * @param string $id    The field ID.
		 * @return mixed
		 */
		public function filter_panel_value( $value, $field, $id ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( isset( $id ) && 'vacation_schedule' === $id && empty( $value ) && $vendor && $vendor->is_valid() ) {
				$value = YITH_Vendors_Vacation()->backward_schedule_compatibility( $vendor );
			}

			return $value;
		}

		/**
		 * Skip vacation message sanitize wc_clean.
		 *
		 * @since  4.0.0
		 * @author Francesco Licandro
		 * @param array $keys An array of meta and data keys to skip wc_clean.
		 * @return array
		 */
		public function skip_vacation_message_sanitize( $keys ) {
			$keys[] = 'vacation_message';
			return $keys;
		}
	}
}
