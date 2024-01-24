<?php
/**
 * Plugin Name: Woo Blaze
 * Plugin URI: https://github.com/automattic/woo-blaze
 * Description: Drive sales, and elevate your products to center stage, effortlessly. Witness your business flourishing in the blink of an eye.
 * Version: 0.0.1
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Text Domain: woo-blaze
 * Requires at least: 6.3
 * Requires PHP: 7.4
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

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
			'slug' => 'woo-blaze',
			'name' => 'Woo Blaze',
		)
	);
}

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
