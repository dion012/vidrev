<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_Auction_Admin Class.
 *
 * @package YITH\Auctions\Includes
 */
if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Class YITH_Auction_Admin
 *
 * @class   YITH_Auctions_Admin
 * @package Yithemes
 * @since   Version 1.0.0
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 */

if ( ! class_exists( 'YITH_Auction_Admin' ) ) {
	/**
	 * Class YITH_Auctions_Admin
	 *
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_Auction_Admin {

		/**
		 * Panel object
		 *
		 * @var object Panel
		 */
		protected $panel = null;

		/**
		 * Panel page
		 *
		 * @var Panel page
		 */
		protected $panel_page = 'yith_wcact_panel_product_auction';

		/**
		 * Premium landing page
		 *
		 * @var bool Show the premium landing page
		 */
		public $show_premium_landing = true;

		/**
		 * Plugin documentation
		 *
		 * @var string Official plugin documentation
		 */
		protected $official_documentation = 'https://docs.yithemes.com/yith-woocommerce-auctions/';

		/**
		 * Premium landing url
		 *
		 * @var string
		 */
		protected $premium_landing_url = 'http://yithemes.com/themes/plugins/yith-woocommerce-auctions/';

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_Auction_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Auction Product Type Name
		 *
		 * @var string
		 */
		public static $prod_type = 'auction';

		/**
		 * Auction Product Meta
		 *
		 * @var array
		 */
		public $product_meta_array = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_Auction_Admin
		 * @since  1.0.0
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			if ( is_null( $self::$instance ) ) {
				$self::$instance = new $self();
			}

			return $self::$instance;
		}

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			/* === Register Panel Settings === */
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			/* === Premium Tab === */
			add_action( 'yith_wcact_premium_tab', array( $this, 'show_premium_landing' ) );
			// Enqueue Scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			// Add Auction product to WC product type selector.
			add_filter( 'product_type_selector', array( $this, 'product_type_selector' ) );
			// Add tabs for product auction.
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_auction_tab' ) );
			// Add options to general product data tab.
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_data_panels' ) );

			add_filter( 'woocommerce_free_price_html', array( $this, 'change_free_price_product' ), 10, 2 );

			/* === Show Plugin Information === */
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCACT_PATH . '/' . basename( YITH_WCACT_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			add_filter( 'woocommerce_get_price_html', array( $this, 'change_product_price_display' ), 10, 2 );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return void
		 * @since  1.0
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @use    /Yit_Plugin_Panel class
		 * @see    plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcact_admin_tabs
			 *
			 * Filter the available tabs in the plugin panel.
			 *
			 * @param array $tabs Admin tabs
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters(
				'yith_wcact_admin_tabs',
				array(
					'auction-list'  => esc_html__( 'Auction list', 'yith-auctions-for-woocommerce' ),
					'general'       => esc_html__( 'General', 'yith-auctions-for-woocommerce' ),
					'auction-page'  => esc_html__( 'Auction page', 'yith-auctions-for-woocommerce' ),
					'customization' => esc_html__( 'Customization', 'yith-auctions-for-woocommerce' ),
				)
			);

			$args = array(
				'create_menu_page'   => true,
				'parent_slug'        => '',
				'page_title'         => 'YITH Auctions for WooCommerce',
				'menu_title'         => 'Auctions',
				'capability'         => 'manage_options',
				'plugin_description' => esc_html__( 'Your customers can purchase products at the best price ever taking full advantage of the online auction system as the most popular portals, such as eBay, can do.', 'yith-auctions-for-woocommerce' ),
				'parent'             => '',
				'parent_page'        => 'yith_plugin_panel',
				'class'              => yith_set_wrapper_class(),
				'page'               => $this->panel_page,
				'admin-tabs'         => $admin_tabs,
				'options-path'       => YITH_WCACT_OPTIONS_PATH,
				'links'              => $this->get_sidebar_link(),
				'plugin_slug'        => YITH_WCACT_SLUG,
				'help_tab'           => array(
					'main_video' => array(
						'desc' => _x( 'Check this video to learn how to <b>create and set up an online auction:</b>', '[HELP TAB] Video title', 'yith-auctions-for-woocommerce' ),
						'url'  => array(
							'en' => 'https://www.youtube.com/embed/4phhN0hsq7k',
							'it' => 'https://www.youtube.com/embed/E_emjUDAj5U',
							'es' => 'https://www.youtube.com/embed/A_0Oo1SAs_w',
						),
					),
					'playlists'  => array(
						'en' => 'https://www.youtube.com/watch?v=4phhN0hsq7k&list=PLDriKG-6905lnu9dJslCrJtsi4gesDtnN',
						'it' => 'https://www.youtube.com/watch?v=E_emjUDAj5U&list=PL9c19edGMs0-dd5ZXiJk-OmKc8YDtw67n',
						'es' => 'https://www.youtube.com/watch?v=A_0Oo1SAs_w&list=PL9Ka3j92PYJP1kws31zV1gFSlf5CCmSnR',
					),
					'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474858-YITH-AUCTIONS-FOR-WOOCOMMERCE',
				),
			);

			/* === Fixed: not updated theme/old plugin framework  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				include_once 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_yith_auctions_upload', array( $this->panel, 'yit_upload' ), 10, 1 );
		}

		/**
		 * Sidebar links
		 *
		 * @return array The links
		 * @since  1.2.1
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function get_sidebar_link() {
			$links = array(
				array(
					'title' => esc_html__( 'Plugin documentation', 'yith-auctions-for-woocommerce' ),
					'url'   => $this->official_documentation,
				),
				array(
					'title' => esc_html__( 'Help Center', 'yith-auctions-for-woocommerce' ),
					'url'   => 'http://support.yithemes.com/hc/en-us/categories/202568518-Plugins',
				),
				array(
					'title' => esc_html__( 'Support platform', 'yith-auctions-for-woocommerce' ),
					'url'   => 'https://yithemes.com/my-account/support/dashboard/',
				),
				array(
					'title' => sprintf( '%s (%s %s)', esc_html__( 'Changelog', 'yith-auctions-for-woocommerce' ), esc_html__( 'current version', 'yith-auctions-for-woocommerce' ), YITH_WCACT_VERSION ),
					'url'   => 'https://docs.yithemes.com/yith-woocommerce-auctions/category/changelog/',
				),
			);

			return $links;
		}

		/**
		 * Enqueue Scripts
		 *
		 * Register and enqueue scripts for Admin
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function enqueue_scripts() {
			$screen     = get_current_screen();
			$is_product = 'product' === $screen->id;

			$settings_section_dependencies = $is_product ? array( 'jquery', 'yith-plugin-fw-fields' ) : array( 'jquery' );

			global $post;
			/* === CSS === */
			wp_register_style( 'yith-wcact-admin-settings-sections', YITH_WCACT_ASSETS_URL . 'css/admin-settings-sections.css', array(), YITH_WCACT_VERSION );
			wp_register_style( 'yith-wcact-admin-css', YITH_WCACT_ASSETS_URL . 'css/admin.css', array( 'yith-plugin-fw-fields', 'yith-wcact-admin-settings-sections' ), YITH_WCACT_VERSION );
			wp_register_style( 'yith-wcact-timepicker-css', YITH_WCACT_ASSETS_URL . 'css/timepicker.css', array(), YITH_WCACT_VERSION );
			wp_register_style( 'yith-wcact-auction-font', YITH_WCACT_ASSETS_URL . '/fonts/icons-font/style.css', array(), YITH_WCACT_VERSION );

			/* === Script === */
			wp_register_script( 'yith-wcact-datepicker', YITH_WCACT_ASSETS_URL . 'js/datepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), YITH_WCACT_VERSION, 'true' );
			wp_register_script( 'yith-wcact-timepicker', YITH_WCACT_ASSETS_URL . 'js/timepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), YITH_WCACT_VERSION, 'true' );

			wp_register_script( 'yith-wcact-admin', YITH_WCACT_ASSETS_URL . 'js/admin-premium.js', array( 'jquery' ), YITH_WCACT_VERSION, true );
			wp_localize_script(
				'yith-wcact-admin',
				'object',
				array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'confirm_delete_bid' => esc_html__( 'Are you sure you want to delete the customer\'s bid?', 'yith-auctions-for-woocommerce' ),
					'id'                 => $post,
					'auction_by_default' => isset( $_GET['ywcact-create-first-auction']  ) ? true : false, // phpcs:ignore
					/* Translators: %s: Error message*/
					'reschedule_product' => wp_create_nonce( 'reschedule-product' ),
					'delete_bid'         => wp_create_nonce( 'delete-bid' ),
					'display_bids'       => wp_create_nonce( 'display-bids' ),
					// translators: %s is the field missing to fill when saving the auction product.
					'error_validation'   => esc_html__( 'Error: You have to set the %s for this auction.', 'yith-auctions-for-woocommerce' ),
					'loader'             => YITH_WCACT_ASSETS_URL . '/images/loading.gif',
					'stripe_enabled'     => defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM,
				)
			);

			wp_register_script( 'yith-wcact-admin-settings-sections', YITH_WCACT_ASSETS_URL . 'js/admin-settings-sections.js', $settings_section_dependencies, YITH_WCACT_VERSION, true );
			wp_localize_script(
				'yith-wcact-admin-settings-sections',
				'admin_settings_section',
				array(
					'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
					'minimun_increment_amount'      => esc_html__( 'Minimum increment amount', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'minimun_increment_amount_desc' => esc_html__( 'Set the minimum increment amount for manual bids', 'yith-auctions-for-woocommerce' ),
					'minimun_decrement_amount'      => esc_html__( 'Minimum decrement amount', 'yith-auctions-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'minimun_decrement_amount_desc' => esc_html__( 'Set the minimum decrement amount for manual bids', 'yith-auctions-for-woocommerce' ),
				)
			);

			if ( $is_product ) {
				/* === CSS === */
				wp_enqueue_style( 'yith-wcact-timepicker-css' );
				wp_enqueue_style( 'yith-wcact-admin-css' );
				wp_enqueue_style( 'yith-wcact-auction-font' );
				/* === Script === */
				wp_enqueue_script( 'yith-wcact-datepicker' );
				wp_enqueue_script( 'yith-wcact-timepicker' );
				wp_enqueue_script( 'yith-wcact-admin' );
				wp_enqueue_script( 'yith-wcact-admin-settings-sections' );

				wp_deregister_script( 'acf-timepicker' );
			}

			if ( isset( $_GET['page'] ) && 'yith_wcact_panel_product_auction' === $_GET['page'] ) { // phpcs:ignore
				wp_enqueue_script( 'yith-wcact-admin' );
				wp_enqueue_script( 'yith-wcact-admin-settings-sections' );
				wp_enqueue_style( 'yith-wcact-admin-css' );
			}

			/**
			 * DO_ACTION: yith_wcact_enqueue_scripts
			 *
			 * Allow to fire some action after the scripts has been enqueued.
			 */
			do_action( 'yith_wcact_enqueue_scripts' );
		}

		/**
		 * Add Auction Product type in product type selector [in product wc-metabox]
		 *
		 * @access public
		 * @param array $types Product types.
		 * @since  1.0.0
		 * @return array
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function product_type_selector( $types ) {
			$types['auction'] = esc_html_x( 'Auction', 'Admin: product type', 'yith-auctions-for-woocommerce' );

			/**
			 * APPLY_FILTERS: yith_wcact_product_type_selector
			 *
			 * Filter the product types after adding the Auction type.
			 *
			 * @param array $types Product types
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcact_product_type_selector', $types );
		}

		/**
		 * Add tab for auction products
		 *
		 * @param array $tabs Array of tabs.
		 *
		 * @return array
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function product_auction_tab( $tabs ) {
			$new_tabs = array(
				'yith_Auction' => array(
					'label'    => esc_html__( 'Auction', 'yith-auctions-for-woocommerce' ),
					'target'   => 'yith_auction_settings',
					'class'    => array( 'show_if_auction active' ),
					'priority' => 15,
				),
			);
			$tabs     = array_merge( $new_tabs, $tabs );

			return $tabs;
		}

		/**
		 * Add panels for auction products
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function add_product_data_panels() {
			$tabs = array(
				'auction' => 'yith_auction_settings',
			);

			foreach ( $tabs as $key => $tab_id ) {
				echo "<div id='{$tab_id}' class='panel woocommerce_options_panel'>"; // phpcs:ignore WordPress.Security.EscapeOutput
				include YITH_WCACT_TEMPLATE_PATH . 'admin/product-tabs/' . $key . '-tab.php';
				echo '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		/**
		 * Change price Free to 0.00 in admin product datatable
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 *
		 * @param string     $price Product price.
		 * @param WC_Product $product Product.
		 * @since  1.0
		 * @return $price
		 */
		public function change_free_price_product( $price, $product ) {
			if ( 'auction' === $product->get_type() ) {
				$price = wc_price( 0 );
			}

			return $price;
		}

		/**
		 * Create link that resend winner emails
		 *
		 * @param array $value Options.
		 * @return void
		 * @since  1.0.11
		 * @author Carlos Rodríguez <carlos.rodriguez@youirinspiration.it>
		 */
		public function yith_regenerate_prices( $value ) {
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>">
					<?php echo $value['html']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="description">
						<?php echo esc_html( $value['desc'] ); ?>
					</span>
				</td>
			</tr>
			<?php
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
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCACT_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCACT_SLUG;
			}

			return $new_row_meta_args;
		}

		/**
		 * Change product price display
		 *
		 * @param string     $price Product price.
		 * @param WC_Product $product Product.
		 * @return array
		 * @since  1.2.3
		 * @author Carlos Rodríguez <carlos.rodriguez@youirinspiration.it>
		 */
		public function change_product_price_display( $price, $product ) {
			/**
			 * APPLY_FILTERS: yith_wcact_load_acution_price_html
			 *
			 * Filter whether to show the auction price.
			 *
			 * @param bool $show_auction_price Whether to show the auction price or not.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_load_acution_price_html', false ) ) {
				if ( $product && 'auction' === $product->get_type() ) {
					$auction_secret = $product->get_auction_sealed();

					if ( 'yes' === $auction_secret ) {
						$price_html = esc_html__( 'This is a sealed auction.', 'yith-auctions-for-woocommerce' ) . '<br/>' . esc_html__( 'Current bid is hidden.', 'yith-auctions-for-woocommerce' );
					} else {
						if ( $product->is_start() ) {
							/* Translators: %s: Product price*/
							$price_html = sprintf( esc_html__( 'Current bid: %s', 'yith-auctions-for-woocommerce' ), $price );

							if ( $product->get_auction_type() && 'normal' !== $product->get_auction_type() ) {
								$price_html .= '<br/>' . esc_html__( 'This is a reverse auction.', 'yith-auctions-for-woocommerce' );
							}
						} else {
							$price_html = '';
						}
					}

					/**
					 * APPLY_FILTERS: yith_wcact_auction_price_html
					 *
					 * Filter the price HTML for the auction product.
					 *
					 * @param string     $price_html Price HTML
					 * @param WC_Product $product    Product object
					 * @param string     $price      Current bid price
					 *
					 * @return string
					 */
					$price = apply_filters( 'yith_wcact_auction_price_html', $price_html, $product, $price );
				}
			}

			return $price;
		}
	}
}
