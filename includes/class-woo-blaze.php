<?php
/**
 * Class Woo_Blaze
 *
 * @package WooCommerce\Blaze
 */

defined( 'ABSPATH' ) || exit;

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
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public static function add_admin_menu(): void {
		add_submenu_page(
			'woocommerce-marketing',
			'Blaze for WooCommerce',
			'Blaze for WooCommerce',
			'manage_woocommerce',
			'woo-blaze',
			array( __CLASS__, 'overview_page_html' )
		);
	}

	/**
	 * Renders the Blaze Overview page.
	 */
	public static function overview_page_html(): void {
		?>
		<div class="wrap">
			<h1>Blaze for WooCommerce</h1>
		</div>
		<?php
	}
}
