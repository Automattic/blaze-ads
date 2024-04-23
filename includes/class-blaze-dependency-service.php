<?php
/**
 * Class Blaze_Dependency_Service
 *
 * @package Automattic\WooBlaze
 */

namespace WooBlaze;

defined( 'ABSPATH' ) || exit;

/**
 * Validates dependencies (core, plugins, versions) for Blaze
 * Used in the plugin main class for validation.
 */
class Blaze_Dependency_Service {

	const WOOCORE_NOT_FOUND    = 'woocore_disabled';
	const WOOCORE_INCOMPATIBLE = 'woocore_outdated';
	const WP_INCOMPATIBLE      = 'wp_outdated';


	/**
	 * Initializes this class's WP hooks.
	 *
	 * @return void
	 */
	public function initialize() {
		add_filter( 'admin_notices', array( $this, 'display_admin_notices' ) );
	}

	/**
	 * Checks if all the dependencies needed to run Woo Blaze are present
	 *
	 * @return bool True if all required dependencies are met.
	 */
	public function has_valid_dependencies(): bool {
		return empty( $this->get_invalid_dependencies() );
	}


	/**
	 * Render admin notices for unmet dependencies. Called on the admin_notices hook.
	 */
	public function display_admin_notices() {

		// Do not show alerts while installing plugins.
		if ( self::is_at_plugin_install_page() ) {
			return;
		}

		$invalid_dependencies = $this->get_invalid_dependencies();

		if ( ! empty( $invalid_dependencies ) ) {
			\Woo_Blaze::display_admin_error( $this->get_notice_for_invalid_dependency( $invalid_dependencies[0] ) );
		}
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_woo_core_active(): bool {
		return class_exists( 'WooCommerce' );
	}


	/**
	 * Returns an array of invalid dependencies
	 *
	 * @return array of invalid dependencies as string constants.
	 */
	public function get_invalid_dependencies(): array {
		$invalid_dependencies = array();

		if ( ! $this->is_woo_core_active() ) {
			$invalid_dependencies[] = self::WOOCORE_NOT_FOUND;
		}

		if ( ! $this->is_woo_core_version_compatible() ) {
			$invalid_dependencies[] = self::WOOCORE_INCOMPATIBLE;
		}

		if ( ! $this->is_wp_version_compatible() ) {
			$invalid_dependencies[] = self::WP_INCOMPATIBLE;
		}

		return $invalid_dependencies;
	}

	/**
	 * Checks if current page is plugin installation process page.
	 *
	 * @return bool True when installing plugin.
	 */
	private static function is_at_plugin_install_page(): bool {
		$cur_screen = get_current_screen();

		return $cur_screen && 'update' === $cur_screen->id && 'plugins' === $cur_screen->parent_base;
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_woo_core_version_compatible(): bool {
		$plugin_headers = \Woo_Blaze::get_plugin_headers();
		$wc_version     = $plugin_headers['WCRequires'];

		// Check if the version of WooCommerce is compatible with Woo Blaze.
		return ( defined( 'WOOBLAZE_WC_VERSION' ) && version_compare( WOOBLAZE_WC_VERSION, $wc_version, '>=' ) );
	}

	/**
	 * Checks if the version of WordPress is compatible with Woo Blaze.
	 *
	 * @return bool True if WordPress version is greater than or equal the minimum accepted
	 */
	public function is_wp_version_compatible(): bool {
		$plugin_headers = \Woo_Blaze::get_plugin_headers();
		$wp_version     = $plugin_headers['RequiresWP'];

		return version_compare( get_bloginfo( 'version' ), $wp_version, '>=' );
	}


	/**
	 * Get the error constant of an invalid dependency, and transforms it into HTML to be used in an Admin Notice.
	 *
	 * @param string $code - invalid dependency constant.
	 *
	 * @return string HTML to render admin notice for the unmet dependency.
	 */
	private function get_notice_for_invalid_dependency( string $code ): string {
		$plugin_headers = \Woo_Blaze::get_plugin_headers();
		$wp_version     = $plugin_headers['RequiresWP'];
		$wc_version     = $plugin_headers['WCRequires'];

		$error_message = '';

		switch ( $code ) {
			case self::WOOCORE_NOT_FOUND:
				$error_message = Woo_Blaze_Utils::esc_interpolated_html(
					sprintf(
					/* translators: %1$s: Woo Blaze, %2$s: WooCommerce */
						__( '%1$s requires <a>%2$s</a> to be installed and active.', 'woo-blaze' ),
						'Woo Blaze',
						'WooCommerce'
					),
					array( 'a' => '<a href="https://wordpress.org/plugins/woocommerce">' )
				);

				if ( current_user_can( 'install_plugins' ) ) {
					if ( is_wp_error( validate_plugin( 'woocommerce/woocommerce.php' ) ) ) {
						// WooCommerce is not installed.
						$activate_url  = wp_nonce_url(
							admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ),
							'install-plugin_woocommerce'
						);
						$activate_text = __( 'Install WooCommerce', 'woo-blaze' );
					} else {
						// WooCommerce is installed, so it just needs to be enabled.
						$activate_url  = wp_nonce_url(
							admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php' ),
							'activate-plugin_woocommerce/woocommerce.php'
						);
						$activate_text = __( 'Activate WooCommerce', 'woo-blaze' );
					}
					$error_message .= ' <a href="' . $activate_url . '">' . $activate_text . '</a>';
				}

				break;
			case self::WOOCORE_INCOMPATIBLE:
				$error_message = Woo_Blaze_Utils::esc_interpolated_html(
					sprintf(
					/* translators: %1: Woo Blaze, %2: current Woo Blaze version, %3: WooCommerce, %4: required WC version number, %5: currently installed WC version number */
						__(
							'%1$s %2$s requires <strong>%3$s %4$s</strong> or greater to be installed (you are using %5$s). ',
							'woo-blaze'
						),
						'Woo Blaze',
						WOOBLAZE_VERSION_NUMBER,
						'WooCommerce',
						$wc_version,
						WOOBLAZE_WC_VERSION
					),
					array( 'strong' => '<strong>' )
				);

				break;
			case self::WP_INCOMPATIBLE:
				$error_message = Woo_Blaze_Utils::esc_interpolated_html(
					sprintf(
					/* translators: %1: Woo Blaze, %2: required WP version number, %3: currently installed WP version number */
						__(
							'%1$s requires <strong>WordPress %2$s</strong> or greater (you are using %3$s).',
							'woo-blaze'
						),
						'Woo Blaze',
						$wp_version,
						get_bloginfo( 'version' )
					),
					array( 'strong' => '<strong>' )
				);
				if ( current_user_can( 'update_core' ) ) {
					$error_message .= ' <a href="' . admin_url( 'update-core.php' ) . '">' . __(
						'Update WordPress',
						'woo-blaze'
					) . '</a>';
				}
				break;

		}

		return $error_message;
	}
}

