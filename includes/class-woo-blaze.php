<?php
/**
 * Class Woo_Blaze
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Connection\Manager as Jetpack_Connection;
use WooBlaze\Blaze_Dashboard;

/**
 * Main class for the Woo Blaze extension. Its responsibility is to initialize the extension.
 */
class Woo_Blaze {

	/**
	 * Static-only class.
	 */
	private function __construct() {
	}

	/**
	 * Entry point to the initialization logic.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ), 999 );
	}

	/**
	 * Determines if criteria is met to enable Blaze features.
	 * Keep in mind that this makes remote requests, so we want to avoid calling it when unnecessary, like in the frontend.
	 *
	 * @return bool
	 */
	public static function should_initialize(): bool {
		$should_initialize = true;
		$site_id           = Jetpack_Connection::get_site_id();

		// Only admins should be able to Blaze posts on a site.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if the site supports Blaze.
		if ( is_numeric( $site_id ) && ! \Automattic\Jetpack\Blaze::site_supports_blaze( $site_id ) ) {
			$should_initialize = false;
		}

		/**
		 * Filter to disable all Blaze functionality.
		 *
		 * @param bool $should_initialize Whether Blaze should be enabled. Default to true.
		 *
		 * @since 0.3.0
		 */
		return apply_filters( 'woo_blaze_enabled', $should_initialize );
	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public static function add_admin_menu(): void {
		if ( ! self::should_initialize() ) {
			return;
		}

		$blaze_dashboard = new Blaze_Dashboard();

		$page_suffix = add_submenu_page(
			'woocommerce-marketing',
			esc_attr__( 'Blaze for WooCommerce', 'woo-blaze' ),
			__( 'Blaze for WooCommerce', 'woo-blaze' ),
			'manage_options',
			'wc-blaze',
			array( $blaze_dashboard, 'render' )
		);
		add_action( 'load-' . $page_suffix, array( $blaze_dashboard, 'admin_init' ) );
	}

}
