<?php
/**
 * Class Blaze_Dashboard
 *
 * @package Automattic\BlazeAds
 */

namespace BlazeAds;

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Blaze as Jetpack_Blaze;
use Automattic\Jetpack\Blaze\Dashboard as Jetpack_Blaze_Dashboard;
use Automattic\Jetpack\Modules as Jetpack_Modules;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection_Manager;
use BlazeAds\Exceptions\Base_Exception;
use Jetpack_Options;

/**
 * Its responsibility is to render the customized version of the Blaze Dashboard.
 */
class Blaze_Dashboard {

	/**
	 * Transient key for caching the active campaigns check.
	 */
	const ACTIVE_CAMPAIGNS_TRANSIENT = 'blazeads_has_active_campaigns';

	/**
	 * TTL for the active campaigns transient, in seconds (1 hour).
	 */
	const ACTIVE_CAMPAIGNS_TRANSIENT_TTL = HOUR_IN_SECONDS;

	/**
	 * Initializes/configures the Jetpack Blaze module.
	 */
	public function initialize(): void {
		// Configures the additional information we need in the state.
		add_filter( 'jetpack_blaze_dashboard_config_data', array( $this, 'blaze_ads_initial_config_data' ), 10, 1 );
		// Allow disabling of the Jetpack Blaze menu for non-Woo sites, to avoid showing 2 advertising sub menus in the Tools menu.
		add_filter( 'jetpack_blaze_enabled', array( $this, 'should_enable_jetpack_blaze_menu' ), 10, 1 );

		// Add initial actions.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 999 );
		add_action( 'admin_menu', array( $this, 'jetpack_dashboard_redirection' ), 999 );
		add_action(
			'admin_init',
			array( $this, 'jetpack_connect_onboarding' ),
			1000
		); // Run this after dashboard redirect.

		// We initialize the module ony if we are running standalone, or if Jetpack Blaze is enabled inside Jetpack plugin.
		// We don't want to override the user's decision to disable Blaze. We have a specific page that shows how to re-enable it.
		if ( $this->is_blaze_module_active() ) {
			Jetpack_Blaze::init();
		}
	}

	/**
	 * Checks if the Marketing Blaze submenu can be displayed on the site.
	 *
	 * @return bool
	 */
	public function can_display_marketing_menu(): bool {
		return Blaze_Dependency_Service::is_woo_core_active();
	}

	/**
	 * Checks if the Jetpack Blaze menu should be enabled.
	 *
	 * @return bool
	 */
	public function should_enable_jetpack_blaze_menu(): bool {
		return false;
	}

	/**
	 * Determines whether the menu should be promoted to a top-level menu page.
	 *
	 * The menu is promoted when the site has active Blaze campaigns, regardless
	 * of whether WooCommerce is active. This makes the Blaze Ads entry more
	 * visible in the admin sidebar.
	 *
	 * @return bool True if the menu should be a top-level page.
	 */
	public function should_promote_to_top_level(): bool {
		return self::has_active_campaigns();
	}

	/**
	 * Checks if the site has any active Blaze campaigns by calling the DSP API.
	 *
	 * Results are cached with a transient for 1 hour. This method is independent of
	 * the WooCommerce MarketingCampaign infrastructure and returns a simple boolean.
	 *
	 * @return bool True if the site has at least one active campaign.
	 */
	public static function has_active_campaigns(): bool {
		$cached = get_transient( self::ACTIVE_CAMPAIGNS_TRANSIENT );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$has_campaigns = false;

		try {
			$blog_id = Jetpack_Options::get_option( 'id' );
			if ( empty( $blog_id ) ) {
				set_transient( self::ACTIVE_CAMPAIGNS_TRANSIENT, 0, self::ACTIVE_CAMPAIGNS_TRANSIENT_TTL );
				return false;
			}

			$path     = sprintf( 'v1/campaigns/site/%s/stats', $blog_id );
			$response = Blaze_Ads_Utils::call_dsp_server( $blog_id, $path, 'GET' );

			if ( 200 === $response['status'] && isset( $response['body']['total'] ) && ! empty( $response['body']['total'] ) ) {
				$has_campaigns = true;
			}
		} catch ( Base_Exception $e ) {
			// On failure, cache as false so we don't keep retrying on every page load.
			$has_campaigns = false;
		}

		set_transient( self::ACTIVE_CAMPAIGNS_TRANSIENT, $has_campaigns ? 1 : 0, self::ACTIVE_CAMPAIGNS_TRANSIENT_TTL );

		return $has_campaigns;
	}

	/**
	 * Returns the admin page base for the Blaze Ads dashboard.
	 *
	 * When promoted to top-level (active campaigns in any context) or displayed
	 * under the WooCommerce Marketing submenu, the base is admin.php. Otherwise,
	 * for non-Woo sites without active campaigns, it falls back to tools.php.
	 *
	 * @return string The page base, e.g. 'admin.php' or 'tools.php'.
	 */
	public function get_admin_page_base(): string {
		if ( $this->should_promote_to_top_level() ) {
			return 'admin.php';
		}

		if ( $this->can_display_marketing_menu() ) {
			return 'admin.php';
		}

		return 'tools.php';
	}

	/**
	 * Returns the full admin URL path for the Blaze Ads dashboard page.
	 *
	 * @return string E.g. 'admin.php?page=wp-blaze' or 'tools.php?page=wp-blaze'.
	 */
	public function get_admin_page_url_path(): string {
		return $this->get_admin_page_base() . '?page=wp-blaze';
	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public function add_admin_menu(): void {
		$menu_slug            = 'wp-blaze';
		$promote_to_top_level = $this->should_promote_to_top_level();

		$blaze_dashboard = new Jetpack_Blaze_Dashboard( $this->get_admin_page_base(), $menu_slug, 'woo-blaze' );

		if ( $promote_to_top_level ) {
			$page_suffix = add_menu_page(
				esc_attr__( 'Blaze Ads', 'blaze-ads' ),
				__( 'Blaze Ads', 'blaze-ads' ),
				'manage_options',
				$menu_slug,
				array( $blaze_dashboard, 'render' ),
				'dashicons-megaphone',
				30
			);
		} elseif ( $this->can_display_marketing_menu() ) {
			$page_suffix = add_submenu_page(
				'woocommerce-marketing',
				esc_attr__( 'Blaze Ads', 'blaze-ads' ),
				__( 'Blaze Ads', 'blaze-ads' ),
				'manage_options',
				$menu_slug,
				array( $blaze_dashboard, 'render' )
			);
		} else {
			$page_suffix = add_submenu_page(
				'tools.php',
				esc_attr__( 'Blaze Ads', 'blaze-ads' ),
				__( 'Blaze Ads', 'blaze-ads' ),
				'manage_options',
				$menu_slug,
				array( $blaze_dashboard, 'render' ),
				1
			);
		}
		add_action( 'load-' . $page_suffix, array( $blaze_dashboard, 'admin_init' ) );
		add_action( 'load-' . $page_suffix, array( $this, 'invalidate_campaigns_cache' ) );
	}

	/**
	 * Invalidates the active campaigns transient when the user loads the
	 * Blaze Ads dashboard page. This ensures the menu position updates on
	 * the next admin page load after a campaign is created or finishes.
	 */
	public function invalidate_campaigns_cache(): void {
		delete_transient( self::ACTIVE_CAMPAIGNS_TRANSIENT );
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
