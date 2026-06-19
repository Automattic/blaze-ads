<?php
/**
 * Class Blaze_Dashboard_Test
 *
 * @package BlazeAds\Tests
 */

namespace BlazeAds\Tests;

use BlazeAds\Tests\Framework\BA_Unit_Test_Case;
use BlazeAds\Blaze_Dashboard;

/**
 * Blaze Dashboard Test.
 *
 * Tests the Blaze_Dashboard class.
 */
class Blaze_Dashboard_Test extends BA_Unit_Test_Case {

	/**
	 * Ensure the correct action/filters are added on initialize.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::initialize
	 */
	public function test_initialize() {
		( new Blaze_Dashboard() )->initialize();

		// Jetpack Blaze should enqueue admin scripts in the initialization function.
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts' ) );
	}

	/**
	 * Ensure the menu slug filter is registered on initialize.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::initialize
	 */
	public function test_initialize_registers_menu_slug_filter() {
		$dashboard = new Blaze_Dashboard();
		$dashboard->initialize();

		$this->assertNotFalse( has_filter( 'jetpack_blaze_menu_slug', array( $dashboard, 'get_menu_slug' ) ) );
	}

	/**
	 * Ensure the CSS prefix filter is registered on initialize.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::initialize
	 */
	public function test_initialize_registers_css_prefix_filter() {
		$dashboard = new Blaze_Dashboard();
		$dashboard->initialize();

		$this->assertNotFalse( has_filter( 'jetpack_blaze_dashboard_css_prefix', array( $dashboard, 'get_css_prefix' ) ) );
	}

	/**
	 * Ensure get_menu_slug returns 'wp-blaze'.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_menu_slug
	 */
	public function test_get_menu_slug() {
		$dashboard = new Blaze_Dashboard();
		$this->assertEquals( 'wp-blaze', $dashboard->get_menu_slug() );
	}

	/**
	 * Ensure get_css_prefix returns 'woo-blaze'.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_css_prefix
	 */
	public function test_get_css_prefix() {
		$dashboard = new Blaze_Dashboard();
		$this->assertEquals( 'woo-blaze', $dashboard->get_css_prefix() );
	}

	/**
	 * Ensure get_admin_page_url_path returns the correct path.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_admin_page_url_path
	 */
	public function test_get_admin_page_url_path() {
		$dashboard = new Blaze_Dashboard();
		$this->assertEquals( 'admin.php?page=wp-blaze', $dashboard->get_admin_page_url_path() );
	}

	/**
	 * Ensure the correct config params are added for the Blaze Dashboard.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::blaze_ads_initial_config_data
	 */
	public function test_plugin_specific_config_state_is_added() {
		$data = ( new Blaze_Dashboard() )->blaze_ads_initial_config_data( array() );
		$this->assertNotEmpty( $data );
		$this->assertTrue( $data['is_blaze_plugin'] );
		$this->assertTrue( $data['is_woo_store'] );
		$this->assertNotEmpty( $data['need_setup'] );
	}

	/**
	 * Ensure connect URL uses admin.php with wp-blaze slug.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_connect_url
	 */
	public function test_connect_url_uses_admin_php() {
		$dashboard = new Blaze_Dashboard();
		$url       = $dashboard->get_connect_url();
		$this->assertStringContainsString( 'admin.php?page=wp-blaze', $url );
	}
}
