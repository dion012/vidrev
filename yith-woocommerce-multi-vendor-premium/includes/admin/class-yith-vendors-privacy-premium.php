<?php
/**
 * YITH Vendors Privacy Premium Class
 *
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Privacy_Premium' ) ) {
	/**
	 * Class YITH_Vendors_Privacy_Premium
	 *
	 * @since  2.6.0
	 * @author Francesco Licandro
	 * @author Andrea Grillo
	 */
	class YITH_Vendors_Privacy_Premium extends YITH_Vendors_Privacy {

		/**
		 * Class constructor.
		 *
		 * @since 2.6.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @return void
		 */
		public function __construct() {
			add_filter( 'yith_wcmv_get_vendor_personal_data_fields', array( $this, 'get_vendor_personal_data_fields_premium' ) );
			add_filter( 'yith_wcmv_get_vendor_personal_data_fields_type', array( $this, 'get_vendor_personal_data_fields_type_premium' ) );

			parent::__construct();
		}

		/**
		 * Get premium vendor personal data field to export/erase.
		 *
		 * @since 2.6.0
		 * @author Andrea Grillo
		 * @param array $fields Current personal data fields to export.
		 * @return array Vendor Personal data fields.
		 */
		public function get_vendor_personal_data_fields_premium( $fields ) {
			$premium_fields = array(
				'location'              => __( 'Store Location', 'yith-woocommerce-product-vendors' ),
				'store_email'           => __( 'Store Email', 'yith-woocommerce-product-vendors' ),
				'telephone'             => __( 'Vendor Phone', 'yith-woocommerce-product-vendors' ),
				'vat'                   => __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ),
				'bank_account'          => __( 'Vendor Bank Account', 'yith-woocommerce-product-vendors' ),
				'commission'            => __( 'Commission Rate (%)', 'yith-woocommerce-product-vendors' ),
				'registration_date'     => __( 'Registration Date', 'yith-woocommerce-product-vendors' ),
				'registration_date_gmt' => __( 'Registration Date GMT', 'yith-woocommerce-product-vendors' ),
				'socials'               => __( 'Vendor socials URLs', 'yith-woocommerce-product-vendors' ),
			);

			return array_merge( $fields, $premium_fields );
		}

		/**
		 * Get premium vendor personal data field type to export/erase.
		 *
		 * @since 2.6.0
		 * @author Andrea Grillo
		 * @param array $fields Current personal data fields type to export.
		 * @return array Vendor Personal data fields
		 */
		public function get_vendor_personal_data_fields_type_premium( $fields ) {
			$premium_fields = array(
				'location'     => 'text',
				'store_email'  => 'email',
				'telephone'    => 'text',
				'vat'          => 'text',
				'bank_account' => 'text',
				'socials'      => 'url',
				'legal_notes'  => 'text',
				'header_image' => 'yith_wcmv_profile_media',
				'avatar'       => 'yith_wcmv_profile_media',
			);

			return array_merge( $fields, $premium_fields );
		}

		/**
		 * Filters anonymize data.
		 *
		 * @since 2.6.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param string $anonymous Anonymized data.
		 * @param string $type      Type of the data.
		 * @param string $data      Original data.
		 */
		public function privacy_anonymize_data_filter( $anonymous, $type, $data ) {

			parent::privacy_anonymize_data_filter( $anonymous, $type, $data );

			if ( 'yith_wcmv_profile_media' === $type ) {

				$to_delete = get_option( 'yith_wpv_vendor_data_to_delete', array() );

				if ( in_array( 'media', $to_delete, true ) ) {
					wp_delete_attachment( $data, true );
				}
				$anonymous = 0;
			}

			return $anonymous;
		}

		/**
		 * Gets the message of the privacy to display.
		 * To be overloaded by the implementor.
		 *
		 * @since 2.6.0
		 * @author Francesco Licandro
		 * @author Andrea Grillo
		 * @param string $section The message section.
		 * @return string
		 */
		public function get_privacy_message( $section ) {

			$message = '';
			switch ( $section ) {
				case 'collect_and_store':
					$message = '<p>' . __( 'We collect information about you during the registration and checkout processes on our store.', 'yith-woocommerce-product-vendors' ) . '</p>' .
								'<p>' . __( 'While you visit our site, we’ll track:', 'yith-woocommerce-product-vendors' ) . '</p>' .
								'<ul>' .
								'<li>' . __( 'Vendor information: we will use this data to create a vendor profile that allows each vendor to sell products on this website in exchange for a commission fee on each sale.', 'yith-woocommerce-product-vendors' ) . '</li>' .
								'<li>' . __( 'The information required to start a vendor shop is the following: name and store description, header image, shop logo, address, email, phone number, VAT/SSN, legal notes, links to social profiles (Facebook, Twitter, LinkedIn, YouTube, Vimeo, Instagram, Pinterest, Flickr, Behance, TripAdvisor), payment information (IBAN and/or PayPal email), and information related to commissions and issued payments.', 'yith-woocommerce-product-vendors' ) . '</li>' .
								'</ul>';
					break;

				case 'has_access':
					$message = '<p>' . __( 'Members of our team have access to the information you provide to us. For example, both Administrators and Shop Managers can access:', 'yith-woocommerce-product-vendors' ) . '</p>' .
							   '<ul>' .
							   '<li>' . __( 'Vendor information', 'yith-woocommerce-product-vendors' ) . '</li>' .
							   '<li>' . __( 'Data concerning commissions earned by the vendor', 'yith-woocommerce-product-vendors' ) . '</li>' .
							   '<li>' . __( 'Data about payments', 'yith-woocommerce-product-vendors' ) . '</li>' .
							   '</ul>' .
							   '<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

				case 'payments':
					$message = '<p>' . __( 'We send payments to vendors through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.', 'yith-woocommerce-product-vendors' ) . '</p>' .
							   '<p>' . __( 'Please see the <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full">PayPal Privacy Policy</a> for more details.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

				case 'share':
					$message = '<p>' . __( 'We share information with third parties who help us provide commissions payments to you.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

			}

			return $message;
		}
	}
}
