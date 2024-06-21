<?php
/**
 * Class Woo_Blaze
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

use WooBlaze\Blaze_Marketing_Channel;
use WooBlaze\Blaze_Dashboard;
use WooBlaze\Blaze_Conversions;
use WooBlaze\Blaze_Translations_Loader;
use WooBlaze\Blaze_Dependency_Service;

/**
 * Main class for the Woo Blaze extension. Its responsibility is to initialize the extension.
 */
class Woo_Blaze {

	/**
	 * Cache for plugin headers to avoid multiple calls to get_file_data
	 *
	 * @var array
	 */
	private static $plugin_headers = null;

	/**
	 * Static-only class.
	 */
	private function __construct() {
	}

	/**
	 * Entry point to the initialization logic.
	 */
	public static function init(): void {
		define( 'WOOBLAZE_VERSION_NUMBER', self::get_plugin_headers()['Version'] );
		define( 'WOOBLAZE_WC_VERSION', defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0' );

		// Initialize the dependency service. so that admins get notices even if dependencies are not met.
		$dependency_service = new Blaze_Dependency_Service();
		$dependency_service->initialize();

		// Stop if dependencies are not met, or we shouldn't initialize.
		if ( false === $dependency_service->has_valid_dependencies() || ! self::should_initialize() ) {
			return;
		}

		// Initialize services.
		( new Blaze_Dashboard() )->initialize();
		( new Blaze_Marketing_Channel() )->initialize();
		( new Blaze_Conversions() )->initialize();
		( new Blaze_Translations_Loader() )->initialize();
	}


	/**
	 * Determines if criteria is met to enable Woo Blaze dashboard.
	 *
	 * @return bool
	 */
	public static function should_initialize(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Prints the given message in an "admin notice" wrapper with "error" class.
	 *
	 * @param string $message Message to print. Can contain HTML.
	 */
	public static function display_admin_error( string $message ) {
		self::display_admin_notice( $message, 'notice-error' );
	}


	/**
	 * Prints the given message in an "admin notice" wrapper with provided classes.
	 *
	 * @param string $message Message to print. Can contain HTML.
	 * @param string $classes Space separated list of classes to be applied to notice element.
	 */
	public static function display_admin_notice( string $message, string $classes ) {
		?>
		<div class="notice wcpay-notice <?php echo esc_attr( $classes ); ?>">
			<p><b>Blaze for WooCommerce</b></p>
			<p><?php echo wp_kses( $message ); ?></p>
		</div>
		<?php
	}


	/**
	 * Get plugin headers and cache the result to avoid reopening the file.
	 * First call should execute get_file_data and fetch headers from plugin details comment.
	 * Subsequent calls return the value stored in the variable $plugin_headers.
	 *
	 * @return array Array with plugin headers
	 */
	public static function get_plugin_headers(): ?array {
		if ( null === self::$plugin_headers ) {
			self::$plugin_headers = get_file_data(
				WOOBLAZE_PLUGIN_FILE,
				array(
					'Version'    => 'Version',
					'WCRequires' => 'WC requires at least',
					'RequiresWP' => 'Requires at least',
				)
			);
		}

		return self::$plugin_headers;
	}
}
