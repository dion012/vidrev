<?php // phpcs:ignore WordPress.NamingConventions
/**
 * YITH_WCACT_Multivendor_Compatibility Class.
 *
 * @package YITH\Auctions\Includes\Compatibility
 */

if ( ! defined( 'YITH_WCACT_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_WCACT_Multivendor_Compatibility' ) ) {
	/**
	 * Multi vendor class compatibility.
	 *
	 * @class   YITH_WCACT_Multivendor_Compatibility
	 * @package Yithemes
	 * @since   Version 1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WCACT_Multivendor_Compatibility {

		/**
		 * Construct
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_email_classes', array( $this, 'register_vendor_email_classes' ) );
		}

		/**
		 * Register Multi Vendor emails.
		 *
		 * @param array $email_classes Email classes.
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  1.0
		 * @return array
		 */
		public function register_vendor_email_classes( $email_classes ) {
			// Vendor Emails.
			$email_classes['YITH_WCACT_Vendor_Email_Not_Reached_Reserve_Price'] = include YITH_WCACT_PATH . 'includes/compatibility/class.yith-wcact-auction-vendor-email-not-reached-reserve-price.php';
			$email_classes['YITH_WCACT_Vendor_Email_Without_Bid']               = include YITH_WCACT_PATH . 'includes/compatibility/class.yith-wcact-auction-vendor-email-without-bid.php';
			$email_classes['YITH_WCACT_Vendor_Email_Winner']                    = include YITH_WCACT_PATH . 'includes/compatibility/class.yith-wcact-auction-vendor-email-winner.php';

			return $email_classes;
		}
	}
}

return new YITH_WCACT_Multivendor_Compatibility();
