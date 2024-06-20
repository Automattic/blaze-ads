<?php
/**
 * Class Jetpack_Connect_Handler
 *
 * @package Automattic\WooBlaze
 */

namespace WooBlaze;

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Connection\Manager;
use WooBlaze\Exceptions\API_Exception;

/**
 * Class representing Jetpack_Connect_Handler
 */
class Jetpack_Connect_Handler {

	const ERROR_MESSAGE_TRANSIENT = 'wcblaze_error_message';

	/**
	 * Jetpack connection handler.
	 *
	 * @var Automattic\Jetpack\Connection\Manager
	 */
	private $connection_manager;



	/**
	 * Constructor
	 * Setup the connection manager.
	 */
	public function __construct() {
		$this->connection_manager = new Manager( 'blaze-ads' );
	}



	/**
	 * Handle onboarding flow.
	 */
	public function maybe_handle_onboarding() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['blaze-ads-connect'] ) && check_admin_referer( 'blaze-ads-connect' ) ) {
				$is_connected = false;
			if ( ! $is_connected ) {
				$this->redirect_to_onboarding_flow_page();
			}
		}
	}

	/**
	 * Redirects to the onboarding flow page.
	 * Also checks if the server is connected and try to connect it otherwise.
	 *
	 * @param string $source The source of the redirect.
	 *
	 * @return void
	 */
	private function redirect_to_onboarding_flow_page( string $source = 'blaze-ads-connect-page' ) {
		$site_url = parse_url( get_site_url(), PHP_URL_HOST );

		$redirect_url = add_query_arg(
			array( 'source' => $source ),
			admin_url( 'admin.php?page=wc-blaze' )
		);

		if ( ! $this->is_connected() ) {
			try {
				$this->start_connection( $redirect_url );
			} catch ( API_Exception $e ) {
				// If we can't connect to the server, return, the error will be shown on the relevant page.
				$this->redirect_to_connect_home_page( $e->getMessage() );
				return;
			}
		}
	}

	/**
	 * Utility function to immediately redirect to the dashboard connect page.
	 * Note that this function immediately ends the execution.
	 *
	 * @param string $error_message Optional error message to show in a notice.
	 */
	public function redirect_to_connect_home_page( $error_message = null ) {
		if ( isset( $error_message ) ) {
			set_transient( self::ERROR_MESSAGE_TRANSIENT, $error_message, 30 );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wc-blaze' ) );
		exit();
	}


	/**
	 * Checks if Jetpack is connected.
	 *
	 * Checks if connection is authenticated in the same way as Jetpack_Client or Jetpack Connection Client does.
	 *
	 * @return bool true if Jetpack connection has access token.
	 */
	public function is_connected() {
		return $this->connection_manager->is_connected() && $this->connection_manager->has_connected_owner();
	}

	/**
	 * Starts the Jetpack connection process. Note that running this function will immediately redirect
	 * to the Jetpack flow, so any PHP code after it will never be executed.
	 *
	 * @param string $redirect - URL to redirect to after the connection process is over.
	 *
	 * @throws API_Exception - Exception thrown on failure.
	 */
	public function start_connection( $redirect ) {
		// Register the site to wp.com.
		if ( ! $this->connection_manager->is_connected() ) {
			$result = $this->connection_manager->try_registration();
			if ( is_wp_error( $result ) ) {
				throw new API_Exception( $result->get_error_message(), 'wcblaze_jetpack_register_site_failed', 500 );
			}
		}

		// Redirect the user to the Jetpack user connection flow.
		add_filter( 'jetpack_use_iframe_authorization_flow', '__return_false' );
		$calypso_env           = defined( 'WOOCOMMERCE_CALYPSO_ENVIRONMENT' ) && in_array( WOOCOMMERCE_CALYPSO_ENVIRONMENT, array( 'development', 'wpcalypso', 'horizon', 'stage' ), true ) ? WOOCOMMERCE_CALYPSO_ENVIRONMENT : 'production';
		$connect_authorize_url = add_query_arg(
			array(
				'from'        => 'blaze-ads',
				'calypso_env' => $calypso_env,
			),
			$this->connection_manager->get_authorization_url( null, $redirect )
		);
		// Using wp_redirect intentionally because we're redirecting outside.
		wp_redirect( $connect_authorize_url ); // phpcs:ignore WordPress.Security.SafeRedirect
		exit;
	}
}
