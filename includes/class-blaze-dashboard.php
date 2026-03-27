<?php
/**
 * Class Blaze_Dashboard
 *
 * @package Automattic\BlazeAds
 */

namespace BlazeAds;

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Blaze as Jetpack_Blaze;
use Automattic\Jetpack\Modules as Jetpack_Modules;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection_Manager;

/**
 * Its responsibility is to render the customized version of the Blaze Dashboard.
 */
class Blaze_Dashboard {

	/**
	 * Initializes/configures the Jetpack Blaze module.
	 */
	public function initialize(): void {
		// Configures the additional information we need in the state.
		add_filter( 'jetpack_blaze_dashboard_config_data', array( $this, 'blaze_ads_initial_config_data' ), 10, 1 );

		// Customize jetpack-blaze menu registration via filters (slug, CSS prefix).
		add_filter( 'jetpack_blaze_menu_slug', array( $this, 'get_menu_slug' ) );
		add_filter( 'jetpack_blaze_dashboard_css_prefix', array( $this, 'get_css_prefix' ) );

		// Redirect legacy ?page=advertising URLs to ?page=wp-blaze.
		add_action( 'admin_menu', array( $this, 'jetpack_dashboard_redirection' ), 999 );
		add_action(
			'admin_init',
			array( $this, 'jetpack_connect_onboarding' ),
			1000
		); // Run this after dashboard redirect.

		// Invalidate jetpack-blaze campaign cache on dashboard page load.
		add_action( 'admin_init', array( $this, 'maybe_invalidate_campaigns_cache' ) );

		// We initialize the module only if we are running standalone, or if Jetpack Blaze is enabled inside Jetpack plugin.
		// We don't want to override the user's decision to disable Blaze. We have a specific page that shows how to re-enable it.
		if ( $this->is_blaze_module_active() ) {
			Jetpack_Blaze::init();
		}
	}

	/**
	 * Returns the menu slug used by Blaze Ads.
	 *
	 * Hooked to `jetpack_blaze_menu_slug` to override the default 'advertising'.
	 *
	 * @return string
	 */
	public function get_menu_slug(): string {
		return 'wp-blaze';
	}

	/**
	 * Returns the CSS prefix for the Blaze Ads dashboard.
	 *
	 * Hooked to `jetpack_blaze_dashboard_css_prefix` to override the default 'jp-blaze'.
	 *
	 * @return string
	 */
	public function get_css_prefix(): string {
		return 'woo-blaze';
	}

	/**
	 * Returns the full admin URL path for the Blaze Ads dashboard page.
	 *
	 * @return string E.g. 'admin.php?page=wp-blaze'.
	 */
	public function get_admin_page_url_path(): string {
		return 'admin.php?page=wp-blaze';
	}

	/**
	 * Invalidates the jetpack-blaze active campaigns transient when the user
	 * loads the Blaze Ads dashboard page. This ensures the menu position
	 * updates on the next admin page load after a campaign is created.
	 */
	public function maybe_invalidate_campaigns_cache(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'wp-blaze' === $_GET['page'] ) {
			$site_id = Jetpack_Connection_Manager::get_site_id();
			if ( is_numeric( $site_id ) ) {
				delete_transient( 'jetpack_blaze_has_active_campaigns_' . $site_id );
			}
		}
	}

	/**
	 * Handles the redirection from the Jetpack Blaze dashboard URL to the new Blaze Ads dashboard
	 *
	 * @return void
	 */
	public function jetpack_dashboard_redirection(): void {
		global $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'advertising' === $_GET['page']
			&& in_array( $pagenow, array( 'tools.php', 'admin.php' ), true )
		) {
			wp_safe_redirect( admin_url( '/' . $this->get_admin_page_url_path(), 'http' ), 302 );
			exit;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Runs the onboarding logic for Jetpack connect.
	 *
	 * @return void
	 */
	public function jetpack_connect_onboarding(): void {
		$connect_handler = new Jetpack_Connect_Handler();
		$connect_handler->maybe_handle_onboarding();
	}

	/**
	 * Sets the initial config data needed by the Blaze Ads dashboard.
	 *
	 * @param array $data Initial state for the Blaze Dashboard app.
	 *
	 * @return array
	 */
	public function blaze_ads_initial_config_data( array $data ): array {
		$setup_reason = $this->check_setup_plugin_status();

		$data['is_blaze_plugin']       = true;
		$data['dashboard_path_prefix'] = '/wp-blaze';
		$data['is_woo_store']          = Blaze_Dependency_Service::is_woo_core_active();
		$data['need_setup']            = $setup_reason ?? false;

		if ( 'disconnected' === $setup_reason ) {
			$data['connect_url'] = $this->get_connect_url();

			$jetpack_error_message = get_transient( Jetpack_Connect_Handler::ERROR_MESSAGE_TRANSIENT );
			delete_transient( Jetpack_Connect_Handler::ERROR_MESSAGE_TRANSIENT );
			$data['jetpack_error_message'] = $jetpack_error_message;
		}

		// Add additional options to the site's information.
		if ( ! empty( $data['initial_state'] ) && ! empty( $data['initial_state']['sites'] ) && ! empty( $data['initial_state']['sites']['items'] ) ) {
			foreach ( $data['initial_state']['sites']['items'] as $key => $site ) {
				$options = $site['options'] ?? array();

				$options['blaze_ads_version'] = BLAZE_ADS_VERSION_NUMBER;

				$data['initial_state']['sites']['items'][ $key ]['options'] = $options;
			}
		}

		return $data;
	}

	/**
	 * Checks the status of the plugin setup
	 *
	 * @return string Setup reason. NULL if no setup is required.
	 */
	public function check_setup_plugin_status(): ?string {
		$connection = new Jetpack_Connection_Manager();
		$site_id    = Jetpack_Connection_Manager::get_site_id();

		$setup_reason = null;

		if ( ! $connection->is_connected() || ! $connection->is_user_connected() ) {
			$setup_reason = 'disconnected';
		} elseif ( ! $this->is_blaze_module_active() ) {
			$setup_reason = 'blaze_disabled';
		} elseif ( '-1' === get_option( 'blog_public' ) || (
				( function_exists( 'site_is_coming_soon' ) && \site_is_coming_soon() )
				|| get_option( 'wpcom_public_coming_soon' )
			)
		) {
			$setup_reason = 'site_private_or_coming_soon';
		} elseif ( is_numeric( $site_id ) && ! Jetpack_Blaze::site_supports_blaze( $site_id ) ) {
			$setup_reason = 'site_ineligible';
		}

		return $setup_reason;
	}

	/**
	 * Returns if the Jetpack Blaze module is active in the site.
	 *
	 * @return bool Jetpack Blaze module status
	 */
	public function is_blaze_module_active(): bool {
		return ! class_exists( 'Jetpack' ) || ( new Jetpack_Modules() )->is_active( 'blaze' );
	}


	/**
	 * Returns the Jetpack connect URL.
	 * In reality this is just to trigger a page reload that re-reruns the onboarding logic and this could have been a window.reload on client
	 * this method simply makes sure the server controls the url that handles the connect redirect for easy change without needing to update the client.
	 *
	 * @param string $blazeads_connect_from Optional. A page ID representing where the user should be returned to after connecting. Default is '1' - redirects back to the overview page.
	 *
	 * @return string Jetpack connect url.
	 */
	public function get_connect_url( string $blazeads_connect_from = '1' ): string {
		$admin_page = $this->get_admin_page_url_path();
		$url        = add_query_arg(
			array( 'blaze-ads-connect' => $blazeads_connect_from ),
			admin_url( $admin_page )
		);

		return html_entity_decode( wp_nonce_url( $url, 'blaze-ads-connect' ), ENT_COMPAT );
	}
}
