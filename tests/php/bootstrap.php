<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WooBlaze\Tests
 */

namespace WooBlaze\Tests;

/**
 * Class Woo_Blaze_Unit_Tests_Bootstrap
 */
class Woo_Blaze_Unit_Tests_Bootstrap {

	/** @var Woo_Blaze_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	/** @var string plugins directory storing dependency plugins */
	public $plugins_dir;

	/**
	 * Setup the unit testing environment.
	 */
	public function __construct() {
		ini_set( 'display_errors', 'on' ); // phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted
		error_reporting( E_ALL & ~E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( dirname( $this->tests_dir ) );
		$this->plugins_dir  = sys_get_temp_dir() . '/wordpress/wp-content/plugins';
		$this->wp_tests_dir = sys_get_temp_dir() . '/wordpress-tests-lib';

		// load test function so tests_add_filter() is available.
		require_once $this->wp_tests_dir . '/includes/functions.php';

		// load WC.
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_plugins' ) );

		/*
		* Load PHPUnit Polyfills for the WP testing suite.
		* @see https://github.com/WordPress/wordpress-develop/pull/1563/
		*/
		define(
			'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
			__DIR__ . '/../../vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php'
		);

		// load the WP testing environment.
		require_once $this->wp_tests_dir . '/includes/bootstrap.php';

		// load testing framework.
		$this->includes();
	}

	/**
	 * Load Plugin
	 */
	public function load_plugins() {
		require_once $this->plugins_dir . '/jetpack/jetpack.php';

		require_once $this->plugins_dir . '/woocommerce/woocommerce.php';

		require_once $this->plugin_dir . '/woo-blaze.php';
	}

	/**
	 * Load Woo Blaze-specific test cases and factories.
	 */
	public function includes() {
		require_once $this->tests_dir . '/framework/class-wb-unit-test-case.php';
	}

	/**
	 * Get the single class instance.
	 *
	 * @return Woo_Blaze_Unit_Tests_Bootstrap
	 */
	public static function instance(): Woo_Blaze_Unit_Tests_Bootstrap {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Woo_Blaze_Unit_Tests_Bootstrap::instance();


