<?php
/**
 * Class Blaze_Ads_Dashboard_Test
 *
 * @package BlazeAds\Tests
 */

namespace BlazeAds\Tests;

use BlazeAds\Tests\Framework\BA_Unit_Test_Case;
use BlazeAds\Blaze_Conversions;
use BlazeAds\Tests\Helpers\BA_Helper_Order;

/**
 * Blaze Conversions Test.
 *
 * Tests the Blaze_Conversions class.
 */
class Blaze_Conversions_Test extends BA_Unit_Test_Case {

	/**
	 * Ensure the hooks are added correctly
	 *
	 * @covers BlazeAds\Blaze_Conversions::init_hooks
	 */
	public function test_initialize() {
		( new Blaze_Conversions() )->initialize();
		$this->assertNotFalse( has_action( 'woocommerce_thankyou' ) );
	}

	/**
	 * Ensure the script can be enqueued in admin.
	 *
	 * @covers BlazeAds\Blaze_Conversions::add_conversion_tracking_pixel
	 */
	public function test_add_conversion_tracking_pixel() {
		$order     = BA_Helper_Order::create_order();
		$entry_url = sprintf( 'https://example.com/product/belt?wpb_id=1_MsEwNsswXXXX&wpb_advertiser=56f2bb5c3513d5802f3972b2a34ca531' );
		update_post_meta( $order->get_id(), '_wc_order_attribution_session_entry', $entry_url );

		( new Blaze_Conversions() )->add_conversion_tracking_pixel( $order->get_id() );

		$this->expectOutputRegex( '/<img src="https:\/\/public-api.wordpress.com\/wpcom\/v2\/wordads\/dsp\/api\/v1\/conversion\/pixel.gif.*?wpb_id.*?wpb_advertiser.*?" crossorigin="anonymous".*>/i' );
	}

}
