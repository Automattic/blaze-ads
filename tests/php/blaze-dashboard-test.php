<?php
/**
 * Class Woo_Blaze_Dashboard_Test
 *
 * @package WooBlaze\Tests
 */

namespace WooBlaze\Tests;

use WooBlaze\Tests\Framework\WB_Unit_Test_Case;
use WooBlaze\Blaze_Dashboard;

/**
 * Blaze Dashboard Test.
 *
 * Tests the Woo_Blaze_Dashboard class.
 */
class Blaze_Dashboard_Test extends WB_Unit_Test_Case {
	/**
	 * Test has root dom.
	 *
	 * @covers WooBlaze\Blaze_Dashboard::render
	 */
	public function test_render() {
		$this->expectOutputRegex( '/<div id="wpcom" class="woo-blaze-dashboard".*>/i' );
		( new Blaze_Dashboard() )->render();
	}

	/**
	 * Ensure the script can be enqueued in admin.
	 *
	 * @covers WooBlaze\Blaze_Dashboard::admin_init
	 */
	public function test_admin_init() {
		( new Blaze_Dashboard() )->admin_init();
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts' ) );
	}

	/**
	 * Ensure the script and style are enqueued.
	 *
	 * @covers WooBlaze\Blaze_Dashboard::load_admin_scripts
	 */
	public function test_load_admin_scripts() {
		$script_handle = Blaze_Dashboard::SCRIPT_HANDLE;
		$style_handle  = $script_handle . '-style';

		// Scripts and style should not be enqueued on the main dashboard.
		( new Blaze_Dashboard() )->load_admin_scripts( 'index.php' );
		$this->assertFalse( wp_script_is( $script_handle, 'enqueued' ) );
		$this->assertFalse( wp_style_is( $style_handle, 'enqueued' ) );

		// Simulates response from plugins build_meta.json file.
		$this->mock_wp_remote_get(
			array(
				'response' => array( 'code' => 204 ),
				'body'     => wp_json_encode(
					array(
						'cache_buster' => 'f8a42e76',
					)
				),
			)
		);

		// They should, however, be enqueued on the Woo Blaze page.
		( new Blaze_Dashboard() )->load_admin_scripts( 'marketing_page_wc-blaze' );
		$this->assertTrue( wp_script_is( $script_handle, 'enqueued' ) );
		$this->assertTrue( wp_style_is( $style_handle, 'enqueued' ) );
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
