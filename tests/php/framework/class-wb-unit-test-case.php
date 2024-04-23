<?php
/**
 * Class WB_Unit_Test_Case
 *
 * @package WooBlaze\Tests
 */

namespace WooBlaze\Tests\Framework;

use WP_UnitTestCase;

/**
 * WB Unit Test Case.
 *
 * Provides Woo Blaze-specific setup/tear down/assert methods, custom factories,
 * and helper functions.
 *
 */
class WB_Unit_Test_Case extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();

		wp_set_current_user(
			self::factory()->user->create(
				array( 'role' => 'administrator' )
			)
		);
	}
}
