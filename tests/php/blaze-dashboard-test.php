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

	/**
	 * Ensure non-WooCommerce menu uses "Blaze Ads" label (not "Advertising").
	 *
	 * @covers BlazeAds\Blaze_Dashboard::add_admin_menu
	 */
	public function test_non_woo_menu_uses_blaze_ads_label() {
		// Mock a non-WooCommerce environment.
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( false );

		$dashboard->add_admin_menu();

		// Verify the submenu page was registered under tools.php.
		$menu_url = menu_page_url( 'wp-blaze', false );
		$this->assertNotEmpty( $menu_url );

		// Check that the menu label is "Blaze Ads" by inspecting the global $submenu.
		global $submenu;
		$found_label = null;
		if ( isset( $submenu['tools.php'] ) ) {
			foreach ( $submenu['tools.php'] as $item ) {
				if ( 'wp-blaze' === $item[2] ) {
					$found_label = $item[0];
					break;
				}
			}
		}
		$this->assertNotNull( $found_label, 'wp-blaze submenu should exist under tools.php' );
		$this->assertEquals( 'Blaze Ads', $found_label );
	}

	/**
	 * Ensure the menu is promoted to top-level when active campaigns exist.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::add_admin_menu
	 */
	public function test_top_level_menu_when_active_campaigns() {
		// Mock a non-WooCommerce environment with active campaigns.
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( true );

		$dashboard->add_admin_menu();

		// A top-level page registers under $menu, not $submenu.
		global $menu;
		$found = false;
		if ( is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[2] ) && 'wp-blaze' === $item[2] ) {
					$found = true;
					$this->assertEquals( 'Blaze Ads', $item[0] );
					$this->assertEquals( 'dashicons-megaphone', $item[6] );
					break;
				}
			}
		}
		$this->assertTrue( $found, 'wp-blaze should be registered as a top-level menu page' );
	}

	/**
	 * Ensure that should_promote_to_top_level returns false for WooCommerce stores.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::should_promote_to_top_level
	 */
	public function test_should_not_promote_when_woo_active() {
		// The test bootstrap loads WooCommerce, so can_display_marketing_menu() returns true.
		$dashboard = new Blaze_Dashboard();
		$this->assertFalse( $dashboard->should_promote_to_top_level() );
	}

	/**
	 * Ensure has_active_campaigns returns false when there is no blog ID.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::has_active_campaigns
	 */
	public function test_has_active_campaigns_returns_false_without_blog_id() {
		// Clear any cached transient.
		delete_transient( Blaze_Dashboard::ACTIVE_CAMPAIGNS_TRANSIENT );

		$result = Blaze_Dashboard::has_active_campaigns();
		$this->assertFalse( $result );
	}

	/**
	 * Ensure has_active_campaigns uses cached transient value.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::has_active_campaigns
	 */
	public function test_has_active_campaigns_uses_transient_cache() {
		// Seed the transient with a "true" value.
		set_transient( Blaze_Dashboard::ACTIVE_CAMPAIGNS_TRANSIENT, 1, HOUR_IN_SECONDS );
		$this->assertTrue( Blaze_Dashboard::has_active_campaigns() );

		// Seed with a "false" value.
		set_transient( Blaze_Dashboard::ACTIVE_CAMPAIGNS_TRANSIENT, 0, HOUR_IN_SECONDS );
		$this->assertFalse( Blaze_Dashboard::has_active_campaigns() );

		// Clean up.
		delete_transient( Blaze_Dashboard::ACTIVE_CAMPAIGNS_TRANSIENT );
	}

	/**
	 * Ensure non-Woo connect URL uses admin.php when promoted to top-level.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_connect_url
	 */
	public function test_connect_url_uses_admin_php_when_top_level() {
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( true );

		$url = $dashboard->get_connect_url();
		$this->assertStringContainsString( 'admin.php?page=wp-blaze', $url );
		$this->assertStringNotContainsString( 'tools.php', $url );
	}

	/**
	 * Ensure non-Woo connect URL uses tools.php when not promoted.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_connect_url
	 */
	public function test_connect_url_uses_tools_php_when_submenu() {
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( false );

		$url = $dashboard->get_connect_url();
		$this->assertStringContainsString( 'tools.php?page=wp-blaze', $url );
	}

	/**
	 * Ensure get_admin_page_base returns correct values for each scenario.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_admin_page_base
	 */
	public function test_get_admin_page_base_woo() {
		// Test bootstrap loads WooCommerce, so this should be admin.php.
		$dashboard = new Blaze_Dashboard();
		$this->assertEquals( 'admin.php', $dashboard->get_admin_page_base() );
	}

	/**
	 * Ensure get_admin_page_base returns admin.php when promoted to top-level.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_admin_page_base
	 */
	public function test_get_admin_page_base_top_level() {
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( true );

		$this->assertEquals( 'admin.php', $dashboard->get_admin_page_base() );
	}

	/**
	 * Ensure get_admin_page_base returns tools.php for non-Woo submenu fallback.
	 *
	 * @covers BlazeAds\Blaze_Dashboard::get_admin_page_base
	 */
	public function test_get_admin_page_base_tools_submenu() {
		$dashboard = $this->getMockBuilder( Blaze_Dashboard::class )
			->onlyMethods( array( 'can_display_marketing_menu', 'should_promote_to_top_level' ) )
			->getMock();

		$dashboard->method( 'can_display_marketing_menu' )->willReturn( false );
		$dashboard->method( 'should_promote_to_top_level' )->willReturn( false );

		$this->assertEquals( 'tools.php', $dashboard->get_admin_page_base() );
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
