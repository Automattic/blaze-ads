<?php
/**
 * Class Woo_Blaze
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Blaze\Dashboard_REST_Controller;
use Automattic\Jetpack\Connection\Client;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection;
use WooBlaze\Blaze_Dashboard;
use WooBlaze\Woo_Blaze_Marketing_Channel;

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
		new Woo_Blaze_Marketing_Channel();
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

	/**
	 * Calls the DSP server
	 *
	 * @param int    $blog_id The blog ID.
	 * @param string $route The route to call.
	 * @param string $method The HTTP method to use.
	 *
	 * @return mixed
	 */
	public static function call_dsp_server( $blog_id, $route, $method = 'GET' ) {

		// Make the API request.
		$params                = '?order=asc&order_by=post_date&page=1';
		$url                   = sprintf( '/sites/%d/wordads/dsp/api/%s/%s', $blog_id, $route, $params );
		$response              = Client::wpcom_json_api_request_as_user(
			$url,
			'v2',
			array( 'method' => $method ),
			null,
			'wpcom'
		);
		$response_code         = wp_remote_retrieve_response_code( $response );
		$response_body_content = wp_remote_retrieve_body( $response );
		$response_body         = json_decode( $response_body_content, true );
		return $response_body;
	}
}
