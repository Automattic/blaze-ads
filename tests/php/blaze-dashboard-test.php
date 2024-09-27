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
	 * Ensure the new admin menu is added in the correct section.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::add_admin_menu
	 */
	public function test_it_adds_admin_menu_correctly() {
		( new Blaze_Dashboard() )->add_admin_menu();

		$menu_url = menu_page_url( 'wp-blaze' );
		$this->assertNotEmpty( $menu_url );
		$this->assertMatchesRegularExpression( '/woocommerce-marketing/', $menu_url );
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

	private function mock_wp_remote_get( $response ) {
		add_filter(
			'pre_http_request',
			function () use ( $response ) {
				return $response;
			}
		);
	}
}
