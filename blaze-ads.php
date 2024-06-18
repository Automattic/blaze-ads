<?php
/**
 * Plugin Name: Blaze Ads
 * Plugin URI: https://github.com/automattic/blaze-ads
 * Description: One-click and you're set! Create ads for your products and store simpler than ever. Get started now and watch your business grow.
 * Version: 0.1.1
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Text Domain: blaze-ads
 * Domain Path: /languages
 * WC requires at least: 7.6
 * Requires at least: 6.3
 * Requires PHP: 7.4
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

define( 'WOOBLAZE_PLUGIN_FILE', __FILE__ );

define( 'WOOBLAZE_ABSPATH', __DIR__ . '/' );

require_once __DIR__ . '/vendor/autoload_packages.php';

// The JetPack autoloader might not catch up yet when activating the plugin. If so, we'll stop here to avoid JetPack connection failures.
$is_autoloading_ready = class_exists( Automattic\Jetpack\Connection\Rest_Authentication::class );
if ( ! $is_autoloading_ready ) {
	return;
}

/**
 * Initialize the Jetpack functionalities: connection, etc.
 */
function wooblaze_jetpack_init() {
	$jetpack_config = new Automattic\Jetpack\Config();
	$jetpack_config->ensure(
		'connection',
		array(
			'slug' => 'blaze-ads',
			'name' => 'Woo Blaze',
		)
	);

	$jetpack_config->ensure(
		'identity_crisis',
		array(
			'slug'          => 'blaze-ads',
			'customContent' => wooblaze_jetpack_idc_custom_content(),
			'logo'          => plugins_url( 'assets/images/woo-logo.svg', WOOBLAZE_PLUGIN_FILE ),
			'admin_page'    => '/wp-admin/admin.php?page=wc-blaze',
			'priority'      => 5,
		)
	);

	$jetpack_config->ensure( 'sync' );
}

/**
 * Get custom texts for Jetpack Identity Crisis (IDC) module.
 *
 * @return array
 */
function wooblaze_jetpack_idc_custom_content(): array {
	$custom_content = array(
		'headerText'                => __( 'Safe Mode', 'blaze-ads' ),
		'mainTitle'                 => __( 'Safe Mode activated', 'blaze-ads' ),
		'mainBodyText'              => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__(
				'We’ve detected that you have duplicate sites connected to %s. When Safe Mode is active, some features like campaign creation may not be available until you’ve resolved this issue below. Safe Mode is most frequently activated when you’re transferring your site from one domain to another, or creating a staging site for testing. <safeModeLink>Learn more</safeModeLink>',
				'blaze-ads'
			),
			'Blaze for WooCommerce'
		),
		'migratedTitle'             => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__( '%s connection successfully transferred', 'blaze-ads' ),
			'Blaze for WooCommerce'
		),
		'migratedBodyText'          => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__( 'Safe Mode has been deactivated and %s is fully functional.', 'blaze-ads' ),
			'Blaze for WooCommerce'
		),
		'migrateCardTitle'          => __( 'Transfer connection', 'blaze-ads' ),
		'migrateButtonLabel'        => __( 'Transfer your connection', 'blaze-ads' ),
		'startFreshCardTitle'       => __( 'Create a new connection', 'blaze-ads' ),
		'startFreshButtonLabel'     => __( 'Create a new connection', 'blaze-ads' ),
		'nonAdminTitle'             => __( 'Safe Mode activated', 'blaze-ads' ),
		'nonAdminBodyText'          => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__(
				'We’ve detected that you have duplicate sites connected to %s. When Safe Mode is active, some features like campaign creation may not be available until you’ve resolved this issue below. Safe Mode is most frequently activated when you’re transferring your site from one domain to another, or creating a staging site for testing. A site adminstrator can resolve this issue. <safeModeLink>Learn more</safeModeLink>',
				'blaze-ads'
			),
			'Blaze for WooCommerce'
		),
		// When doc is ready, set support URL similar to 'https://woocommerce.com/document/woopayments/testing-and-troubleshooting/safe-mode/'.
		'supportURL'                => 'https://jetpack.com/redirect/?source=jetpack-support-safe-mode',
		'adminBarSafeModeLabel'     => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__( '%s Safe Mode', 'blaze-ads' ),
			'Blaze for WooCommerce'
		),
		'dynamicSiteUrlText'        => sprintf(
		/* translators: %s: Blaze for WooCommerce. */
			__(
				"<strong>Notice:</strong> It appears that your 'wp-config.php' file might be using dynamic site URL values. Dynamic site URLs could cause %s to enter Safe Mode. <dynamicSiteUrlSupportLink>Learn how to set a static site URL.</dynamicSiteUrlSupportLink>",
				'blaze-ads'
			),
			'Blaze for WooCommerce'
		),
		// When doc is ready, set support URL similar to 'https://woocommerce.com/document/woopayments/testing-and-troubleshooting/safe-mode/#dynamic-site-urls'.
		'dynamicSiteUrlSupportLink' => 'https://jetpack.com/redirect/?source=jetpack-idcscreen-dynamic-site-urls',
	);

	$urls = Automattic\Jetpack\Identity_Crisis::get_mismatched_urls();
	if ( false !== $urls ) {
		$current_url = untrailingslashit( $urls['current_url'] );
		/**
		 * Undo the reverse the Jetpack IDC library is doing since we want to display the URL.
		 *
		 * @see https://github.com/Automattic/jetpack-identity-crisis/blob/trunk/src/class-identity-crisis.php#L471
		 */
		$idc_sync_error = Automattic\Jetpack\Identity_Crisis::check_identity_crisis();
		if ( is_array( $idc_sync_error ) && ! empty( $idc_sync_error['reversed_url'] ) ) {
			$urls['wpcom_url'] = strrev( $urls['wpcom_url'] );
		}
		$wpcom_url = untrailingslashit( $urls['wpcom_url'] );

		$custom_content['migrateCardBodyText'] = sprintf(
		/* translators: %1$s: The current site domain name. %2$s: The original site domain name. Please keep hostname tags in your translation so that they can be formatted properly. %3$s: Blaze for WooCommerce. */
			__(
				'Transfer your %3$s connection from <hostname>%2$s</hostname> to this site <hostname>%1$s</hostname>. <hostname>%2$s</hostname> will be disconnected from %3$s.',
				'blaze-ads'
			),
			$current_url,
			$wpcom_url,
			'Blaze for WooCommerce'
		);

		$custom_content['startFreshCardBodyText'] = sprintf(
		/* translators: %1$s: The current site domain name. %2$s: The original site domain name. Please keep hostname tags in your translation so that they can be formatted properly. %3$s: Blaze for WooCommerce. */
			__(
				'Create a new connection to %3$s for <hostname>%1$s</hostname>. Your <hostname>%2$s</hostname> connection will remain as is.',
				'blaze-ads'
			),
			$current_url,
			$wpcom_url,
			'Blaze for WooCommerce'
		);
	}

	return $custom_content;
}


// Jetpack's Rest_Authentication needs to be initialized even before plugins_loaded.
Automattic\Jetpack\Connection\Rest_Authentication::init();

// Jetpack-config will initialize the modules on "plugins_loaded" with priority 2, so this code needs to be run before that.
add_action( 'plugins_loaded', 'wooblaze_jetpack_init', 1 );


/**
 * Initialize the extension. Note that this gets called on the "plugins_loaded" filter,
 * so WooCommerce classes are guaranteed to exist at this point (if WooCommerce is enabled).
 */
function wooblaze_init() {
	require_once WOOBLAZE_ABSPATH . '/includes/class-woo-blaze.php';
	Woo_Blaze::init();
}

// Make sure this is run *after* WooCommerce has a chance to initialize its packages (wc-admin, etc). That is run with priority 10.
add_action( 'plugins_loaded', 'wooblaze_init', 11 );
