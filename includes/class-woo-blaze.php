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
use WooBlaze\Blaze_Conversions;

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
		// Stop if WooCommerce or Jetpack is not installed or is disabled.
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'Automattic\Jetpack\Blaze' ) ) {
			return;
		}

		// Add initial actions.
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ), 999 );

		// Initialize services.
		( new Woo_Blaze_Marketing_Channel() )->initialize();
		( new Blaze_Conversions() )->initialize();
	}

	/**
	 * Determines if criteria is met to enable Blaze features.
	 * Keep in mind that this makes remote requests, so we want to avoid calling it when unnecessary, like in the frontend.
	 *
	 * @return bool
	 */
	public static function should_initialize(): bool {
		$site_id = Jetpack_Connection::get_site_id();

		// Only admins should be able to Blaze posts on a site.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if the site supports Blaze.
		if ( is_numeric( $site_id ) && ! \Automattic\Jetpack\Blaze::site_supports_blaze( $site_id ) ) {
			return false;
		}

		return true;
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
	 * @param array  $query_params The query parameters to send.
	 *
	 * @return mixed
	 */
	public static function call_dsp_server( $blog_id, $route, $method = 'GET', $query_params = array() ) {

		// Make the API request.
		$url = sprintf( '/sites/%d/wordads/dsp/api/%s', $blog_id, $route );
		$url = add_query_arg( $query_params, $url );

		$response = Client::wpcom_json_api_request_as_user(
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
