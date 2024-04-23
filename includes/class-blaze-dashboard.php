<?php
/**
 * Class Blaze_Dashboard
 *
 * @package Automattic\WooBlaze
 */

namespace WooBlaze;

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
	public function initialize() {
		Jetpack_Blaze::init();

		// Configures the additional information we need in the state.
		add_filter( 'jetpack_blaze_dashboard_config_data', array( $this, 'woo_blaze_initial_config_data' ), 10, 1 );

		// Add initial actions.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 999 );
		add_action( 'admin_init', array( $this, 'jetpack_dashboard_redirection' ), 999 );

	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public function add_admin_menu(): void {
		$menu_slug = 'wc-blaze';

		$blaze_dashboard = new Jetpack_Blaze_Dashboard( 'admin.php', $menu_slug, 'woo-blaze' );
		// The is_woo_blaze_active method was removed when the new compatibility functions were added in jetpack Blaze.
		if ( method_exists( '\Automattic\Jetpack\Blaze', 'is_woo_blaze_active' ) ) {
			$blaze_dashboard = new Blaze_Compat_Dashboard();
		}

		$page_suffix = add_submenu_page(
			'woocommerce-marketing',
			esc_attr__( 'Blaze for WooCommerce', 'woo-blaze' ),
			__( 'Blaze for WooCommerce', 'woo-blaze' ),
			'manage_options',
			$menu_slug,
			array( $blaze_dashboard, 'render' )
		);
		add_action( 'load-' . $page_suffix, array( $blaze_dashboard, 'admin_init' ) );
	}

	/**
	 * Handles the redirection from the Jetpack Blaze dashboard URL to the new Woo Blaze dashboard
	 *
	 * @return void
	 */
	public function jetpack_dashboard_redirection() {
		global $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( 'tools.php' === $pagenow && isset( $_GET['page'] ) && 'advertising' === $_GET['page'] ) {
			wp_safe_redirect( admin_url( '/admin.php?page=wc-blaze', 'http' ), 302 );
			exit;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Sets the initial config data needed by the Woo Blaze dashboard.
	 *
	 * @param array $data Initial state for the Blaze Dashboard app.
	 *
	 * @return array
	 */
	public function woo_blaze_initial_config_data( array $data ): array {
		$connection = new Jetpack_Connection_Manager();
		$site_id    = Jetpack_Connection_Manager::get_site_id();

		$setup_reason = null;
		if ( ! $connection->is_connected() || ! $connection->is_user_connected() ) {
			$setup_reason = 'disconnected';
// phpcs:disable Squiz.PHP.CommentedOutCode.Found
			// } elseif ( is_plugin_active( 'jetpack/jetpack.php' ) && ! ( new Jetpack_Modules() )->is_active( 'blaze' ) ) {
			// $setup_reason = 'blaze_disabled';
// phpcs:enable Squiz.PHP.CommentedOutCode.Found
		} elseif ( is_numeric( $site_id ) && ! Jetpack_Blaze::site_supports_blaze( $site_id ) ) {
			if ( '-1' === get_option( 'blog_public' ) || (
					( function_exists( 'site_is_coming_soon' ) && \site_is_coming_soon() )
					|| (bool) get_option( 'wpcom_public_coming_soon' )
				)
			) {
				$setup_reason = 'site_private_or_coming_soon';
			} else {
				$setup_reason = 'site_ineligible';
			}
		}

		$data['is_woo_store'] = true; // Flag used to differentiate a WooCommerce installation.
		$data['need_setup']   = $setup_reason ?? false;

		return $data;
	}
}
