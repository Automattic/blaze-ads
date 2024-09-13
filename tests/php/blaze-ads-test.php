<?php
/**
 * Class Blaze_Ads_Test
 *
 * @package BlazeAds\Tests
 */

namespace BlazeAds\Tests;

use BlazeAds\Tests\Framework\BA_Unit_Test_Case;
use \Blaze_Ads;

/**
 * Blaze Ads Test.
 *
 * Tests the Blaze_Ads class.
 */
class Blaze_Ads_Test extends BA_Unit_Test_Case {

	/**
	 * Editor user ID
	 * @var int
	 */
	protected int $editor_id;

	public function set_up() {
		parent::set_up();

		$this->editor_id = self::factory()->user->create(
			array( 'role' => 'editor' )
		);
	}

	/**
	 * Ensures the plugins initialize only for admins
	 *
	 * @covers BlazeAds::should_initialize_dashboard
	 */
	public function test_editor_not_eligible_to_blaze() {
		// The default user is admin (check BA_Unit_Test_Case set_up method).
		$this->assertTrue( Blaze_Ads::should_initialize_dashboard() );

		wp_set_current_user( $this->editor_id );
		$this->assertFalse( Blaze_Ads::should_initialize_dashboard() );
	}

	/**
	 * Ensures the correct version is defined in the BLAZEADS_VERSION_NUMBER variable.
	 */
	public function test_sets_correct_plugin_defined_version() {
		$this->assertNotEmpty( BLAZEADS_VERSION_NUMBER );
		$this->assertMatchesRegularExpression( '/\d+.\d+.\d+/', BLAZEADS_VERSION_NUMBER );
	}

	/**
	 * Ensures the correct version is defined in the BLAZEADS_VERSION_NUMBER variable.
	 */
	public function test_correct_plugin_headers_are_collected() {
		$headers = Blaze_Ads::get_plugin_headers();
		$this->assertNotEmpty( $headers );
		$this->assertNotEmpty( $headers['Version'] );
		$this->assertMatchesRegularExpression( '/\d+.\d+.\d+/', $headers['Version'] );

	}
}
