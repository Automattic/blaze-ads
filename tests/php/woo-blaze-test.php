<?php
/**
 * Class Woo_Blaze_Test
 *
 * @package WooBlaze\Tests
 */

namespace WooBlaze\Tests;

use WooBlaze\Tests\Framework\WB_Unit_Test_Case;
use \Woo_Blaze;

/**
 * Woo Blaze Test.
 *
 * Tests the Woo_Blaze class.
 */
class Woo_Blaze_Test extends WB_Unit_Test_Case {

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
	 * @covers WooBlaze::should_initialize
	 */
	public function test_editor_not_eligible_to_blaze() {
		// The default user is admin (check WB_Unit_Test_Case set_up method.
		$this->assertTrue( Woo_Blaze::should_initialize() );

		wp_set_current_user( $this->editor_id );
		$this->assertFalse( Woo_Blaze::should_initialize() );
	}

	/**
	 * Ensures the correct version is defined in the WOOBLAZE_VERSION_NUMBER variable.
	 */
	public function test_sets_correct_plugin_defined_version() {
		$this->assertNotEmpty( WOOBLAZE_VERSION_NUMBER );
		$this->assertMatchesRegularExpression( '/\d+.\d+.\d+/', WOOBLAZE_VERSION_NUMBER );
	}

	/**
	 * Ensures the correct version is defined in the WOOBLAZE_VERSION_NUMBER variable.
	 */
	public function test_correct_plugin_headers_are_collected() {
		$headers = Woo_Blaze::get_plugin_headers();
		$this->assertNotEmpty( $headers );
		$this->assertNotEmpty( $headers['Version'] );
		$this->assertMatchesRegularExpression( '/\d+.\d+.\d+/', $headers['Version'] );

	}
}
