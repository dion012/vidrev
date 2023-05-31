<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Notify Class.
 *
 * @package YITH\Auctions\Includes
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Notify' ) ) {
	/**
	 *  Class Notify
	 *
	 * @class   YITH_WCACT_Notify
	 * @package Yithemes
	 * @since   Version 1.0.0
	 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
	 */
	class YITH_WCACT_Notify {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_WCACT_Notify
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * The unsubscribe page template.
		 *
		 * @var string
		 */
		public static $page_template = 'ywcact-unsubscribe-auction.php';

		/**
		 * Page templates.
		 *
		 * @var array
		 */
		public $post_page_templates = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCACT_Notify
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
			add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ) );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_core_template' ), 10, 3 );

			/* == Send emails on new bid for bidders and followers == */
			add_action( 'yith_wcact_successfully_bid', array( $this, 'notify_any_new_bid' ), 30, 3 );

			/* == Send emails closed by buy now for bidders and followers == */
			add_action( 'yith_wcact_after_set_closed_by_buy_now', array( $this, 'notify_closed_by_buy_now' ), 10, 3 );

			add_action( 'yith_wcact_email_footer', array( $this, 'email_footer_auctions' ) );

			/* == Unfollow / Unsubscribe email handler == */
			$this->post_page_templates = array(
				self::$page_template => __( 'YITH WCACT Unsubscribe template Page', 'yith-auctions-for-woocommerce' ),
			);

			add_action( 'init', array( $this, 'add_unsubscribe_page' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars_for_unsubscribe_auction_page' ) );
			add_action( 'template_redirect', array( $this, 'redirect_unsubscribe_template_missing_query_strings' ) );

			/* == Unsubscribe page template == */
			add_filter( 'template_include', array( $this, 'view_unsubscribe_template' ) );

			add_action( 'yith_wcact_unsubscribe_head', 'wp_head' );
			add_action( 'yith_wcact_unsubscribe_footer', 'wp_footer' );

			add_action( 'wp_enqueue_scripts', array( $this, 'unsubscribe_register_scripts' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'unsubscribe_register_styles' ), 11 );

			add_action( 'yith_wcact_unsubscribe_body', array( $this, 'show_unsubscribe_icon' ) );
			add_action( 'yith_wcact_unsubscribe_body', array( $this, 'show_unsubscribe_content' ), 20 );
		}

		/**
		 * Register email classes
		 *
		 * @param array $email_classes array with all auction email class.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return array
		 */
		public function register_email_classes( $email_classes ) {
			// User Emails.
			$email_classes['YITH_WCACT_Email_Better_Bid']                           = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-better-bid.php';
			$email_classes['YITH_WCACT_Email_End_Auction']                          = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-end-auction.php';
			$email_classes['YITH_WCACT_Email_Auction_Winner']                       = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-auction-winner.php';
			$email_classes['YITH_WCACT_Email_Successfully_Bid']                     = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-successfully-bid.php';
			$email_classes['YITH_WCACT_Email_Auction_No_Winner']                    = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-auction-no-winner.php';
			$email_classes['YITH_WCACT_Email_Delete_Bid']                           = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-delete-bid.php';
			$email_classes['YITH_WCACT_Email_Auction_Winner_Reminder']              = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-auction-winner-reminder.php';
			$email_classes['YITH_WCACT_Email_Not_Reached_Reserve_Price_Max_Bidder'] = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-not-reached-reserve-price-max-bid.php';

			// Admin Emails.
			$email_classes['YITH_WCACT_Email_Not_Reached_Reserve_Price'] = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-not-reached-reserve-price.php';
			$email_classes['YITH_WCACT_Email_Without_Bid']               = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-without-bid.php';
			$email_classes['YITH_WCACT_Email_Winner_Admin']              = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-winner-admin.php';
			$email_classes['YITH_WCACT_Email_Successfully_Bid_Admin']    = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-successfully-bid-admin.php';
			$email_classes['YITH_WCACT_Email_Delete_Bid_Admin']          = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-delete-bid-admin.php';
			$email_classes['YITH_WCACT_Email_Auction_Rescheduled_Admin'] = include YITH_WCACT_PATH . 'includes/emails/class.yith-wcact-auction-email-auction-rescheduled-admin.php';

			// Followers Emails.
			$email_classes['YITH_WCACT_Email_Successfully_Follow'] = include YITH_WCACT_PATH . 'includes/emails/followers/class-yith-wcact-email-successfully-follow.php';

			// Common Emails.
			$email_classes['YITH_WCACT_Email_New_Bid']        = include YITH_WCACT_PATH . 'includes/emails/common/class-yith-wcact-email-new-bid.php';
			$email_classes['YITH_WCACT_Email_Closed_Buy_Now'] = include YITH_WCACT_PATH . 'includes/emails/common/class-yith-wcact-email-closed-buy-now.php';

			return $email_classes;
		}

		/**
		 * Locate core template.
		 *
		 * @param string $core_file Template type.
		 * @param string $template Template name.
		 * @param string $template_base Template base.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return string
		 */
		public function locate_core_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				// HTML Email.
				'emails/better-bid.php',
				'emails/end-auction.php',
				'emails/not-reached-reserve-price.php',
				'emails/auction-winner.php',
				'emails/without-any-bids.php',
				'emails/auction-winner-admin.php',
				'emails/successfully-bid.php',
				'emails/successfully-bid-admin.php',
				'emails/auction-no-winner.php',
				'emails/auction-delete-bid.php',
				'emails/auction-delete-bid-admin.php',
				'emails/auction-winner-reminder.php',
				'emails/auction-rescheduled-admin.php',
				'emails/not-reached-reserve-price-max-bidder.php',

				// Followers.
				'emails/followers/successfully-follow.php',

				// Common.
				'emails/common/new-bid.php',
				'emails/common/closed-buy-now.php',

				// Plain Email.
				'emails/plain/better-bid.php',
				'emails/plain/end-auction.php',
				'emails/not-reached-reserve-price.php',

			);

			if ( in_array( $template, $custom_template, true ) ) {
				$core_file = YITH_WCACT_TEMPLATE_PATH . $template;
			}

			return $core_file;
		}

		/**
		 * Notify any new bid email for bidders and followers.
		 *
		 * @param  int        $bidder_user_id Bidder user id.
		 * @param  WC_Product $product Auction product.
		 * @param  array      $args Arguments.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return void
		 */
		public function notify_any_new_bid( $bidder_user_id, $product, $args ) {
			if ( 'auction' === $product->get_type() ) {
				$product_id = $product->get_id();

				// Check if user is added to the follower list or if a follower without make any bid before.
				$query            = YITH_Auctions()->bids;
				$bidder_user      = get_user_by( 'id', $bidder_user_id );
				$bidder_user_mail = ( isset( $bidder_user->data->user_email ) ) ? $bidder_user->data->user_email : '';

				$is_a_follower = $query->is_a_follower( $product_id, $bidder_user_mail, $bidder_user_id );

				if ( $is_a_follower ) {
					if ( empty( $is_a_follower->is_bidder ) ) {
						$query->update_follower_as_bidder( $product_id, $bidder_user_mail, 1 );
					}
				} else {
					// Register as a follower.
					$hash = wp_generate_password();
					$query->insert_follower( $product_id, $bidder_user_mail, $bidder_user_id, $hash, 1 );
				}

				// Auction new bid for bidders.
				if ( 'yes' === get_option( 'yith_wcact_email_bidders_new_bid', 'no' ) ) {
					$query = YITH_Auctions()->bids;
					$users = $query->get_users( $product_id );

					foreach ( $users as $id => $user_id ) {
						$registered_user_id = (int) $user_id->user_id;

						if ( $bidder_user_id === (int) $user_id->user_id ) {
							continue;
						}

						$user_hash = get_user_meta( $registered_user_id, '_yith_wcact_security_hash', true );

						if ( empty( $user_hash ) ) {
							$user_hash = wp_generate_password();

							update_user_meta( $registered_user_id, '_yith_wcact_security_hash', $user_hash );
						}

						// Create an object to pass the user hash.
						$object           = new stdClass();
						$object->hash     = $user_hash;
						$args['follower'] = $object;
						WC()->mailer();

						/**
						 * DO_ACTION: yith_wcact_email_new_bid
						 *
						 * Allow to trigger some action when the email to notify that a new bid has been placed is sent.
						 *
						 * @param int   $user_id    User ID
						 * @param int   $product_id Product ID
						 * @param array $args       Array of arguments
						 */
						do_action( 'yith_wcact_email_new_bid', (int) $user_id->user_id, $product_id, $args );
					}
				}

				// Auction new bid for followers.
				if ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_allow_subscribe', 'no' ) && 'yes' === get_option( 'yith_wcact_notify_followers_on_new_bids', 'no' ) ) {
					$product          = wc_get_product( $product_id );
					$users            = $product->get_followers_list();
					$bidder_user      = get_user_by( 'id', $bidder_user_id );
					$bidder_user_mail = ( isset( $bidder_user->data->user_email ) ) ? $bidder_user->data->user_email : '';

					if ( $users ) {
						foreach ( $users as $email => $user ) {
							if ( $bidder_user_mail === $email ) {
								continue;
							}

							$args['follower'] = $user;
							WC()->mailer();
							do_action( 'yith_wcact_email_new_bid', $email, $product_id, $args );
						}
					}
				}
			}
		}

		/**
		 * Notify closed by buy now for bidders and followers
		 *
		 * @param WC_Product    $product Auction product.
		 * @param WC_Order_Item $order_item Order item.
		 * @param WC_Order      $order Order.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return void
		 */
		public function notify_closed_by_buy_now( $product, $order_item, $order ) {
			if ( 'auction' === $product->get_type() ) {
				$product_id = $product->get_id();

				// Auction new bid for bidders.
				if ( 'yes' === get_option( 'yith_wcact_email_bidders_closed_by_buy_now', 'no' ) ) {
					$query = YITH_Auctions()->bids;
					$users = $query->get_users( $product_id );

					foreach ( $users as $id => $user_id ) {
						/**
						 * APPLY_FILTERS: yith_wcact_notify_user_prevent_email_closed_buy_now
						 *
						 * Filter whether to prevent the delivery of the email to notify that the auction has been closed by using the "Buy now" option.
						 *
						 * @param bool       $prevent_delivery Whether to prevent the delivery of the email or not
						 * @param int        $user_id          User ID
						 * @param WC_Product $product          Product object
						 * @param WC_Order   $order            Order object
						 *
						 * @return bool
						 */
						if ( apply_filters( 'yith_wcact_notify_user_prevent_email_closed_buy_now', false, (int) $user_id->user_id, $product, $order ) ) {
							continue;
						}

						WC()->mailer();

						/**
						 * DO_ACTION: yith_wcact_email_closed_buy_now
						 *
						 * Allow to trigger some action when the email to notify that a new bid has been placed is sent.
						 *
						 * @param int                   $user_id    User ID
						 * @param int                   $product_id Product ID
						 * @param WC_Order_Item_Product $order_item Irder item object
						 * @param WC_Order              $order      Order object
						 */
						do_action( 'yith_wcact_email_closed_buy_now', (int) $user_id->user_id, $product, $order_item, $order );
					}
				}

				// Auction new bid for followers.
				if ( 'yes' === get_option( 'yith_wcact_settings_tab_auction_allow_subscribe', 'no' ) && 'yes' === get_option( 'yith_wcact_notify_followers_auction_closed_by_buy_now', 'no' ) ) {
					$product = wc_get_product( $product_id );
					$users   = $product->get_followers_list();

					if ( $users ) {
						foreach ( $users as $key => $user ) {
							WC()->mailer();
							do_action( 'yith_wcact_email_closed_buy_now', $user, $product, $order_item, $order );
						}
					}
				}
			}
		}

		/**
		 * Email Footer template
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @param object $email $email object.
		 * @since  3.0
		 * @return void
		 */
		public function email_footer_auctions( $email ) {
			/**
			 * APPLY_FILTERS: yith_wcact_show_unsubscribe_link_on_email
			 *
			 * Filter whether to show the link to unsubscribe in the emails.
			 *
			 * @param bool     $show_link Whether to show the unsubscribe link in the emails or not
			 * @param WC_Email $email     Email object
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcact_show_unsubscribe_link_on_email', false, $email ) || 'yes' === get_option( 'yith_wcact_display_unsubscribe_link', 'yes' ) ) {
				$unsubscribe_page_id = get_option( 'yith_wcact_unsubscribe_page' );

				if ( $unsubscribe_page_id ) {
					$page = get_permalink( $unsubscribe_page_id );

					/**
					 * APPLY_FILTERS: yith_wcact_unsubscribe_link_params
					 *
					 * Filter the array with the arguments to build the unsubscribe URL.
					 *
					 * @param array  $args Array of arguments
					 * @param string $page Unsubscribe page URL
					 *
					 * @return array
					 */
					$unsubscribe_action_url = add_query_arg(
						apply_filters(
							'yith_wcact_unsubscribe_link_params',
							array(
								'yith-wcact-type'    => 'unsuscribe_auction',
								'yith-wcact-email'   => $email->object['user_email'],
								'yith-wcact-product' => $email->object['product']->get_id(),
								'security'           => $email->object['hash'],
							),
							$page
						),
						$page
					);

					$args = array(
						'email'                   => $email,
						'unsubscribe_action_url'  => $unsubscribe_action_url,
						'unsubscribe_action_text' => get_option( 'yith_wcact_unsubscribe_link_text', esc_html__( 'Unsubscribe', 'yith-auctions-for-woocommerce' ) ),
					);

					wc_get_template( 'auction-email-footer.php', $args, '', YITH_WCACT_PATH . 'templates/emails/' );
				}
			} else {
				// Call default WooCommerce footer.
				do_action( 'woocommerce_email_footer', $email );
			}
		}

		/**
		 * Add the unsubscribe product page
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return void
		 * @access public
		 */
		public function add_unsubscribe_page() {
			$option_name  = 'yith_wcact_unsubscribe_page';
			$option_value = get_option( $option_name );

			if ( $option_value && get_post( $option_value ) ) {
				// The page already exists.
				update_post_meta( $option_value, '_wp_page_template', self::$page_template );
				return;
			}

			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value = %s LIMIT 1;", self::$page_template ) );

			if ( $page_found ) {
				! $option_value && update_option( $option_name, $page_found );
			} else {
				$page_data = array(
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_author'    => 1,
					'post_name'      => 'unsubscribe-auctions',
					'post_title'     => esc_html__( 'Unsubscribe auctions', 'yith-auctions-for-woocommerce' ),
					'post_content'   => '<!-- wp:shortcode -->[yith_wcact_unsubscribe_auction]<!-- /wp:shortcode -->',
					'post_parent'    => 0,
					'comment_status' => 'closed',
				);

				$page_id = wp_insert_post( $page_data );
				add_post_meta( $page_id, '_wp_page_template', self::$page_template );
				update_option( $option_name, $page_id );
			}
		}

		/**
		 * Redirect to home page if the there is a missing parameter or not found follower list
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return void
		 */
		public function redirect_unsubscribe_template_missing_query_strings() {
			global $post;

			if ( $post && $post instanceof WP_Post ) {
				$post_page_template = get_post_meta( $post->ID, '_wp_page_template', true );

				if ( isset( $this->post_page_templates[ $post_page_template ] ) && ! current_user_can( 'manage_woocommerce' ) ) {
					$hash_found = false;

					// Only admin and shop managers can visit the page without any parameter.
					if ( ! empty( $_REQUEST['security'] ) && ! empty( $_REQUEST['yith-wcact-email'] ) && isset( $_REQUEST['yith-wcact-product'] ) && $_REQUEST['yith-wcact-product'] > 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$hash_found = true;
						$security   = sanitize_text_field( wp_unslash( $_REQUEST['security'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$email      = sanitize_email( wp_unslash( $_REQUEST['yith-wcact-email'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$auction_id = intval( wp_unslash( $_REQUEST['yith-wcact-product'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

						// Check if email is a valid format email.
						if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
							$query         = YITH_Auctions()->bids;
							$is_a_follower = $query->is_a_valid_follower( $auction_id, $email, $security );

							if ( $is_a_follower || is_array( $is_a_follower ) && ! empty( $is_a_follower ) ) {
								$hash_found = true;
							}
						}
					}

					if ( ! $hash_found ) {
						/**
						 * APPLY_FILTERS: yith_wcact_not_valid_request_redirect
						 *
						 * Filter the URL to redirect after unsubscribing from the auction if not found the user.
						 *
						 * @param string $url URL
						 *
						 * @return string
						 */
						$redirect_page = apply_filters( 'yith_wcact_not_valid_request_redirect', wc_get_page_permalink( 'shop' ) );
						wp_safe_redirect( $redirect_page );
						exit();
					}
				}
			}
		}

		/**
		 * Checks if the template is assigned to the page
		 *
		 * @param string $template The template.
		 *
		 * @return string
		 */
		public function view_unsubscribe_template( $template ) {
			global $post;

			if ( ! $post ) {
				return $template;
			}

			$file = '';

			if ( $post && $post instanceof WP_Post ) {
				// Return default template if we don't have a custom one defined.
				$post_page_template = get_post_meta( $post->ID, '_wp_page_template', true );

				if ( ! isset( $this->post_page_templates[ $post_page_template ] ) ) {
					return $template;
				}

				if ( ! empty( $_REQUEST['security'] ) && ! empty( $_REQUEST['yith-wcact-email'] ) && isset( $_REQUEST['yith-wcact-product'] ) && $_REQUEST['yith-wcact-product'] > 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$security = sanitize_text_field( wp_unslash( $_REQUEST['security'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					$email      = sanitize_email( wp_unslash( $_REQUEST['yith-wcact-email'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$auction_id = intval( wp_unslash( $_REQUEST['yith-wcact-product'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					// Check if email is a valid format email.
					if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$hash_found    = false;
						$query         = YITH_Auctions()->bids;
						$is_a_follower = $query->is_a_valid_follower( $auction_id, $email, $security );

						if ( $is_a_follower || is_array( $is_a_follower ) && ! empty( $is_a_follower ) ) {
							$hash_found = true;
						}

						if ( $hash_found ) {
							$file = get_stylesheet_directory() . '/' . get_post_meta( $post->ID, '_wp_page_template', true );

							if ( file_exists( $file ) ) {
								return $file;
							}

							$file = get_template_directory() . '/' . get_post_meta( $post->ID, '_wp_page_template', true );

							if ( file_exists( $file ) ) {
								return $file;
							}

							$file = YITH_WCACT_TEMPLATE_PATH . 'frontend/unsubscribe/' . get_post_meta( $post->ID, '_wp_page_template', true );
						}
					}
				}
			}

			return file_exists( $file ) ? $file : $template;
		}

		/**
		 * Register query vars for handle unsubscribe page
		 *
		 * @param array $vars Array of query vars.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 * @return array
		 */
		public function add_query_vars_for_unsubscribe_auction_page( $vars ) {
			$vars[] = 'yith-wcact-type';
			$vars[] = 'yith-wcact-email';
			$vars[] = 'yith-wcact-product';

			return $vars;
		}

		/**
		 * Adds noindex and noarchive to the robots meta tag for unsubscribe page.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function unsubscribe_sensitive_page() {
			add_filter( 'wp_robots', 'wp_robots_sensitive_page' );
		}

		/**
		 * Register scripts for unsubscribe auctions shortcode.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function unsubscribe_register_scripts() {
			global $post;

			if ( $post && $post instanceof WP_Post ) {
				wp_register_script(
					'yith_wcact_unsubscribe_auction_list',
					YITH_WCACT_ASSETS_URL . 'js/unsubscribe/unsubscribe-auction.js',
					array(
						'jquery',
						'jquery-ui-sortable',
					),
					YITH_WCACT_VERSION,
					true
				);

				wp_localize_script(
					'yith_wcact_unsubscribe_auction_list',
					'yith_wcact_unsubscribe',
					array(
						'ajaxurl'             => admin_url( 'admin-ajax.php' ),
						'no_auction_selected' => esc_html__( 'Please, select at least one auction', 'yith-auctions-for-woocommerce' ),
						'nonce'               => wp_create_nonce( 'ajax-unsubscribe-auctions' ),
					)
				);
			}
		}

		/**
		 * Register styles for unsubscribe auctions shortcode.
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function unsubscribe_register_styles() {
			global $post;

			if ( $post && $post instanceof WP_Post ) {
				wp_register_style( 'yith_wcact_unsubscribe_auction_list', YITH_WCACT_ASSETS_URL . 'css/unsubscribe/unsubscribe-auction.css', array(), YITH_WCACT_VERSION );
			}
		}

		/**
		 * Display unsubscribe icon on page
		 *
		 * @param WP_Post $post Post.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function show_unsubscribe_icon( $post ) {
			if ( $post && $post instanceof WP_Post ) {
				$page_logo = get_the_post_thumbnail( $post, 'thumbnail' );

				if ( $page_logo ) {
					/**
					 * APPLY_FILTERS: yith_wcact_unsubscribe_logo_args
					 *
					 * Filter the array with the arguments for the unsubscribe logo.
					 *
					 * @param array $args Array of arguments
					 *
					 * @return array
					 */
					$args = apply_filters(
						'yith_wcact_unsubscribe_logo_args',
						array(
							'logo' => $page_logo,
							'post' => $post,
						)
					);

					wc_get_template( 'unsubscribe-logo.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/unsubscribe/' );
				}
			}
		}

		/**
		 * Display user auctions where the user is follower or bidder
		 *
		 * @param WP_Post $post Post.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.0
		 */
		public function show_unsubscribe_content( $post ) {
			if ( $post && $post instanceof WP_Post ) {
				/**
				 * APPLY_FILTERS: yith_wcact_unsubscribe_content_args
				 *
				 * Filter the array with the arguments for the unsubscribe content.
				 *
				 * @param array $args Array of arguments
				 *
				 * @return array
				 */
				$args = apply_filters(
					'yith_wcact_unsubscribe_content_args',
					array(
						'post' => $post,
					)
				);

				wc_get_template( 'unsubscribe-content.php', $args, '', YITH_WCACT_TEMPLATE_PATH . 'frontend/unsubscribe/' );
			}
		}
	}
}

return YITH_WCACT_Notify::get_instance();
