<?php

return apply_filters(
	'yith_wcmv_modules',
	array(

		'seller-vacation'          => array(
			'title'       => __( 'Vendors vacations', 'yith-woocommerce-product-vendors' ),
			'option_desc' => __( 'If you enable this option, vendors will be able to close their shops for vacation.', 'yith-woocommerce-product-vendors' ),
			'autoload'    => array(
				'yith-vendors-vacation-admin' => 'modules/vacation/class-yith-vendors-vacation-admin.php',
			),
			'includes'    => array(
				'common' => 'vacation/class-yith-vendors-vacation.php',
			),
		),

		'shipping'                 => array(
			'title'       => __( 'Vendors shipping', 'yith-woocommerce-product-vendors' ),
			'option_desc' => __( 'If you enable this option, vendors will be able to set their own costs for their shipping methods.', 'yith-woocommerce-product-vendors' ),
			'autoload'    => array(
				'yith-vendors-shipping-admin'    => 'modules/shipping/class-yith-vendors-shipping-admin.php',
				'yith-vendors-shipping-frontend' => 'modules/shipping/class-yith-vendors-shipping-frontend.php',
			),
			'includes'    => array(
				'common' => 'shipping/class-yith-vendors-shipping.php',
			),
		),

		'announcements'            => array(
			'title'          => __( 'Vendors announcements', 'yith-woocommerce-product-vendors' ),
			'option_desc'    => __( 'If you enable this option, you\'ll be able to create announcements to be shown on the vendors\' dashboards.', 'yith-woocommerce-product-vendors' ),
			'admin_sub_tabs' => array(
				'title' => _x( 'Announcements', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
			),
			'autoload'       => array(
				'yith-vendors-announcement'        => 'modules/announcements/class-yith-vendors-announcement.php',
				'yith-vendors-announcements-admin' => 'modules/announcements/class-yith-vendors-announcements-admin.php',
			),
			'includes'       => array(
				'common' => 'announcements/class-yith-vendors-announcements.php',
			),
		),

		'report-abuse'             => array(
			'title'          => __( 'Vendors report abuse', 'yith-woocommerce-product-vendors' ),
			'option_desc'    => __( 'If you enable this option, a "Report abuse" link will be shown on all of the product pages.', 'yith-woocommerce-product-vendors' ),
			'admin_sub_tabs' => array(
				''         => array(
					'title' => _x( 'Reported Abuse', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
				'settings' => array(
					'title' => _x( 'Report Abuse Settings', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
				),
			),
			'includes'       => array(
				'common' => 'class-yith-vendors-report-abuse.php',
			),
		),

		'staff'                    => array(
			'title'       => __( 'Vendor staff', 'yith-woocommerce-product-vendors' ),
			'option_desc' => __( 'If you enable this option, vendors will be able to add staff members to their stores.', 'yith-woocommerce-product-vendors' ),
			'includes'    => array(
				'admin'  => 'staff/class-yith-vendors-staff-admin.php',
				'common' => 'staff/class-yith-vendors-staff.php',
			),
		),

		'order-tracking'           => array(
			'title'             => 'Order Tracking',
			'name'              => 'YITH WooCommerce Order & Shipment Tracking',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-order-tracking/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to manage order tracking.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_YWOT_PREMIUM',
			'installed_version' => 'YITH_YWOT_VERSION',
			'min_version'       => '1.1.9',
			'compare'           => '>=',
		),

		'subscription'             => array(
			'title'             => 'Subscription',
			'name'              => 'YITH WooCommerce Subscription',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-subscription/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage subscription-based products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => array( 'ywsbs_subscription' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wcmv_subscription_caps', array( yith_wcmv_create_capabilities( 'ywsbs_sub' ) ), '4.0.0', 'yith_wcmv_subscription_module_capabilities' ),
			'premium'           => 'YITH_YWSBS_PREMIUM',
			'installed_version' => 'YITH_YWSBS_VERSION',
			'min_version'       => '1.0.0',
			'compare'           => '>=',
		),

		'name-your-price'          => array(
			'title'             => 'Name Your Price',
			'name'              => 'YITH WooCommerce Name Your Price',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-name-your-price/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage "name your price" products.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YWCNP_PREMIUM',
			'installed_version' => 'YWCNP_VERSION',
			'min_version'       => '1.0.0',
			'compare'           => '>=',
		),

		'size-charts'              => array(
			'title'             => 'Product Size Charts',
			'name'              => 'YITH Product Size Charts for WooCommerce',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-product-size-charts-for-woocommerce/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to add product size charts for their own products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => apply_filters_deprecated( 'yith_wcpsc_vendor_allowed_post_types', array( array( 'yith-wcpsc-wc-chart' ) ), '4.0.0', 'yith_wcmv_size_chart_module_post_types' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wcpsc_vendor_allowed_caps', array( yith_wcmv_create_capabilities( array( 'size_chart', 'size_charts' ) ) ), '4.0.0', 'yith_wcmv_size_chart_module_capabilities' ),
			'premium'           => 'YITH_WCPSC_PREMIUM',
			'installed_version' => 'YITH_WCPSC_VERSION',
			'min_version'       => '1.0.6',
			'compare'           => '>=',
		),

		'membership'               => array(
			'title'             => 'Membership',
			'name'              => 'YITH WooCommerce Membership',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-membership/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage memberships for their own customers.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => apply_filters_deprecated( 'yith_wcmbs_vendor_allowed_post_types', array( array( 'yith-wcmbs-plan' ) ), '4.0.0', 'yith_wcmv_membership_module_post_types' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wcmbs_vendor_allowed_caps', array( yith_wcmv_create_capabilities( array( 'plan', 'plans' ) ) ), '4.0.0', 'yith_wcmv_membership_module_capabilities' ),
			'premium'           => 'YITH_WCMBS_PREMIUM',
			'installed_version' => 'YITH_WCMBS_VERSION',
			'min_version'       => '1.0.4',
			'compare'           => '>=',
		),

		'live-chat'                => array(
			'title'             => 'Live Chat',
			'name'              => 'YITH Live Chat',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-live-chat/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to chat with their customers directly.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => array( 'ylc-macro' ),
			'capabilities'      => apply_filters_deprecated( 'yith_ylc_vendor_caps', array( yith_wcmv_create_capabilities( array( 'ylc-macro', 'ylc-macros' ) ) ), '4.0.0', 'yith_wcmv_live_chat_module_capabilities' ),
			'premium'           => 'YLC_PREMIUM',
			'installed_version' => 'YLC_VERSION',
			'min_version'       => '1.0.5',
			'compare'           => '>=',
		),

		'waiting-list'             => array(
			'title'             => 'Waiting List',
			'name'              => 'YITH WooCommerce Waiting List',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-waiting-list/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to manage their waiting lists and send emails to their customers.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_WCWTL_PREMIUM',
			'installed_version' => 'YITH_WCWTL_VERSION',
			'min_version'       => '1.0.6',
			'compare'           => '>=',
		),

		'surveys'                  => array(
			'title'             => 'Surveys',
			'name'              => 'YITH WooCommerce Surveys',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-surveys/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage surveys for their own customers.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => apply_filters_deprecated( 'yith_wc_surveys_vendor_allowed_post_types', array( array( 'yith_wc_surveys' ) ), '4.0.0', 'yith_wcmv_surveys_module_post_types' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wc_surveys_vendor_allowed_caps', array( yith_wcmv_create_capabilities( array( 'survey', 'surveys' ) ) ), '4.0.0', 'yith_wcmv_surveys_module_capabilities' ),
			'premium'           => 'YITH_WC_SURVEYS_PREMIUM',
			'installed_version' => 'YITH_WC_SURVEYS_VERSION',
			'min_version'       => '1.0.1',
			'compare'           => '>=',
		),

		'badge-management'         => array(
			'title'             => 'Badge Management',
			'name'              => 'YITH WooCommerce Badge Management',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-badge-management/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage badges for their own products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => apply_filters_deprecated( 'yith_wcbm_vendor_allowed_post_types', array( array( 'yith-wcbm-badge' ) ), '4.0.0', 'yith_wcmv_badge_management_module_post_types' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wcbm_vendor_allowed_caps', array( yith_wcmv_create_capabilities( array( 'badge', 'badges' ) ) ), '4.0.0', 'yith_wcmv_badge_management_module_capabilities' ),
			'premium'           => 'YITH_WCBM_PREMIUM',
			'installed_version' => 'YITH_WCBM_VERSION',
			'min_version'       => '1.2.3',
			'compare'           => '>=',
		),

		'review-discounts'         => array(
			'title'             => 'Review For Discounts',
			'name'              => 'YITH WooCommerce Review For Discounts',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-review-for-discounts/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage discounts for their own customers.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => array( 'ywrfd-discount' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wrfd_vendor_caps', array( yith_wcmv_create_capabilities( array( 'ywrfd-discount', 'ywrfd-discounts' ) ) ), '4.0.0', 'yith_wcmv_review_discounts_module_capabilities' ),
			'premium'           => 'YWRFD_PREMIUM',
			'installed_version' => 'YWRFD_VERSION',
			'min_version'       => '1.0.0',
			'compare'           => '>=',
		),

		'coupon-email-system'      => array(
			'title'             => 'Coupon Email System',
			'name'              => 'YITH WooCommerce Coupon Email System',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-coupon-email-system/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create custom coupons and send them by email to their own customers.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YWCES_PREMIUM',
			'installed_version' => 'YWCES_VERSION',
			'min_version'       => '1.0.5',
			'compare'           => '>=',
		),

		'pdf-invoice'              => array(
			'title'             => 'PDF Invoice',
			'name'              => 'YITH WooCommerce PDF Invoice',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create invoices for their orders. This feature requires vendors to be able to manage their orders individually.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_YWPI_PREMIUM',
			'installed_version' => 'YITH_YWPI_VERSION',
			'min_version'       => '1.3.0',
			'compare'           => '>=',
			'option_name'       => 'yith_wpv_vendors_enable_pdf_invoice',
		),

		'request-quote'            => array(
			'title'             => 'Request a Quote',
			'name'              => 'YITH WooCommerce Request a quote',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to receive and manage their own quote requests. This feature requires vendors to be able to manage their orders individually.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_YWRAQ_PREMIUM',
			'installed_version' => 'YITH_YWRAQ_VERSION',
			'min_version'       => '1.4.0',
			'compare'           => '>=',
			'option_name'       => 'yith_wpv_vendors_enable_request_quote',
			'includes'          => array(
				'common' => 'class-yith-vendors-request-quote.php',
			),
		),

		'catalog-mode'             => array(
			'title'             => 'Catalog Mode',
			'name'              => 'YITH WooCommerce Catalog Mode',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to enable the catalog mode for their own products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => array( 'ywctm-button-label' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wctm_vendor_caps', array( yith_wcmv_create_capabilities( array( 'ywctm-button-label', 'ywctm-button-labels' ) ) ), '4.0.0', 'yith_wcmv_catalog_mode_module_capabilities' ),
			'premium'           => 'YWCTM_PREMIUM',
			'installed_version' => 'YWCTM_VERSION',
			'min_version'       => '1.3.0',
			'compare'           => '>=',
			'option_name'       => 'yith_wpv_vendors_enable_catalog_mode',
		),

		'role-based-prices'        => array(
			'title'             => 'Role Based Prices',
			'name'              => 'YITH WooCommerce Role Based Prices',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-role-based-prices/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create custom price rules for their own products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => array( 'yith_price_rule' ),
			'capabilities'      => apply_filters_deprecated( 'yith_wrbp_vendor_caps', array( yith_wcmv_create_capabilities( array( 'price_rule', 'price_rules' ) ) ), '4.0.0', 'yith_wcmv_role_based_prices_module_capabilities' ),
			'premium'           => 'YWCRBP_PREMIUM',
			'installed_version' => 'YWCRBP_VERSION',
			'min_version'       => '1.0.0',
			'compare'           => '>=',
		),

		'advanced-product-options' => array(
			'title'             => 'Product Add-ons',
			'name'              => 'YITH WooCommerce Product Add-ons',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create advanced product options for their products.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_WAPO_PREMIUM',
			'installed_version' => 'YITH_WAPO_VERSION',
			'min_version'       => '1.0.0',
			'compare'           => '>=',
		),

		'sms-notifications'        => array(
			'title'             => 'SMS Notifications',
			'name'              => 'YITH WooCommerce SMS Notifications',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-sms-notifications/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to receive SMS notifications about their orders.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YWSN_PREMIUM',
			'installed_version' => 'YWSN_VERSION',
			'min_version'       => '1.0.3',
			'compare'           => '>=',
			'option_name'       => 'yith_wpv_vendors_enable_sms',
		),

		'bulk-product-editing'     => array(
			'title'             => 'Bulk Product Editing',
			'name'              => 'YITH WooCommerce Bulk Product Editing',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-bulk-product-editing/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to bulk edit their products.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_WCBEP_PREMIUM',
			'installed_version' => 'YITH_WCBEP_VERSION',
			'min_version'       => '1.1.23',
			'compare'           => '>=',
			'option_name'       => 'yith_wpv_vendors_option_bulk_product_editing_options_management',
		),

		'product-bundles'          => array(
			'title'             => 'Product Bundles',
			'name'              => 'YITH WooCommerce Product Bundles',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-product-bundles/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create bundled products.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_WCPB_PREMIUM',
			'installed_version' => 'YITH_WCPB_VERSION',
			'min_version'       => '1.1.3',
			'compare'           => '>=',
		),

		'eu-energy-label'          => array(
			'title'             => 'EU Energy Label',
			'name'              => 'YITH WooCommerce EU Energy Label',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-eu-energy-label/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to add labels to their products with their energy classes.', 'yith-woocommerce-product-vendors' ),
			'premium'           => 'YITH_WCEUE_PREMIUM',
			'installed_version' => 'YITH_WCEUE_VERSION',
			'min_version'       => '1.0.5',
			'compare'           => '>=',
		),

		'booking'                  => array(
			'title'             => 'Booking',
			'name'              => 'YITH Booking and Appointment for WooCommerce Premium',
			'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-booking/',
			'option_desc'       => __( 'If you enable this option, vendors will be able to create bookable products.', 'yith-woocommerce-product-vendors' ),
			'post_types'        => apply_filters_deprecated( 'yith_wcbk_vendor_allowed_post_types', array( array( 'yith_booking' ) ), '4.0.0', 'yith_wcmv_booking_module_post_types' ),
			'premium'           => 'YITH_WCBK_PREMIUM',
			'installed_version' => 'YITH_WCBK_VERSION',
			'min_version'       => '1.0.7',
			'compare'           => '>=',
		),
	)
);
