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
	 * Always returns false because blaze-ads registers its own menu in
	 * add_admin_menu(). This prevents the jetpack-blaze package from
	 * creating a duplicate entry.
	 *
	 * @return bool
	 */
	public function should_enable_jetpack_blaze_menu(): bool {
		return false;
	}

	/**
	 * Checks if the Jetpack top-level admin menu is registered.
	 *
	 * @return bool True if the 'jetpack' top-level menu exists.
	 */
	private static function is_jetpack_parent_available(): bool {
		global $menu;
		foreach ( (array) $menu as $item ) {
			if ( isset( $item[2] ) && 'jetpack' === $item[2] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public function add_admin_menu(): void {
		$menu_slug       = 'advertising';
		$parent_slug     = $this->get_menu_parent();
		$page_base       = 'tools.php' === $parent_slug ? 'tools.php' : 'admin.php';
		$blaze_dashboard = new Jetpack_Blaze_Dashboard( $page_base, $menu_slug, 'woo-blaze' );

		if ( 'tools.php' === $parent_slug ) {
			// Fallback: no Jetpack, no WooCommerce — register under Tools.
			$page_suffix = add_submenu_page(
				'tools.php',
				esc_attr__( 'Blaze Ads', 'blaze-ads' ),
				__( 'Blaze Ads', 'blaze-ads' ),
				'manage_options',
				$menu_slug,
				array( $blaze_dashboard, 'render' ),
				1
			);
		} else {
			// Register under Jetpack or WooCommerce Marketing — both resolve at admin.php.
			$page_suffix = add_submenu_page(
				$parent_slug,
				esc_attr__( 'Blaze Ads', 'blaze-ads' ),
				__( 'Blaze Ads', 'blaze-ads' ),
				'manage_options',
				$menu_slug,
				array( $blaze_dashboard, 'render' ),
				1
			);
		}
		add_action( 'load-' . $page_suffix, array( $blaze_dashboard, 'admin_init' ) );
	}

	/**
	 * Returns the parent menu slug for the Blaze Ads submenu.
	 *
	 * Priority: WooCommerce Marketing > Jetpack > Tools.
	 *
	 * @return string Parent menu slug.
	 */
	private function get_menu_parent(): string {
		if ( $this->can_display_marketing_menu() ) {
			return 'woocommerce-marketing';
		}

		if ( self::is_jetpack_parent_available() ) {
			return 'jetpack';
		}

		return 'tools.php';
	}

	/**
	 * Handles the redirection from the legacy wp-blaze slug to the current advertising slug.
	 *
	 * @return void
	 */
	public function jetpack_dashboard_redirection(): void {
		global $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'wp-blaze' === $_GET['page']
			&& in_array( $pagenow, array( 'tools.php', 'admin.php' ), true )
		) {
			wp_safe_redirect( admin_url( 'admin.php?page=advertising' ), 302 );
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
		$data['dashboard_path_prefix'] = '/advertising';
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
		$admin_page = 'admin.php?page=advertising';
		$url        = add_query_arg(
			array( 'blaze-ads-connect' => $blazeads_connect_from ),
			admin_url( $admin_page )
		);

		return html_entity_decode( wp_nonce_url( $url, 'blaze-ads-connect' ), ENT_COMPAT );
	}
}
