<?php
/**
 * Class Blaze_Marketing_Channel_Test
 *
 * @package BlazeAds\Tests
 */

namespace BlazeAds\Tests;

use BlazeAds\Tests\Framework\BA_Unit_Test_Case;
use BlazeAds\Blaze_Marketing_Channel;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaignType;

/**
 * Blaze Marketing Channel Test.
 *
 * Tests the Blaze_Marketing_Channel class.
 */
class Blaze_Marketing_Channel_Test extends BA_Unit_Test_Case {
	/** @var Blaze_Marketing_Channel $channel */
	protected static Blaze_Marketing_Channel $channel;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$channel = new Blaze_Marketing_Channel();
		self::$channel->setup_marketing_channel();
	}

	/**
	 * Ensure the get_slug method returns information
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_slug
	 */
	public function test_get_slug_is_not_empty() {
		$this->assertNotEmpty( self::$channel->get_slug() );
	}

	/**
	 * Ensure the get_name method returns information
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_name
	 */
	public function test_get_name_is_not_empty() {
		$this->assertNotEmpty( self::$channel->get_name() );
	}

	/**
	 * Ensure the get_description method returns information
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_description
	 */
	public function test_get_description_is_not_empty() {
		$this->assertNotEmpty( self::$channel->get_description() );
	}

	/**
	 * Ensure the get_description method returns information
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_product_listings_status
	 */
	public function test_get_product_listings_status_is_not_empty() {
		$this->assertNotEmpty( self::$channel->get_product_listings_status() );
	}

	/**
	 * Ensure the get_description method returns information
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_product_listings_status
	 */
	public function test_get_errors_count_is_valid() {
		$this->assertEquals( 0, self::$channel->get_errors_count() );
	}

	/**
	 * Ensure we return a correct list of supported campaigns
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_supported_campaign_types
	 */
	public function test_get_supported_campaign_types_returns_ads_campaign() {
		$this->assertCount( 1, self::$channel->get_supported_campaign_types() );
		$this->assertContainsOnlyInstancesOf(
			MarketingCampaignType::class,
			self::$channel->get_supported_campaign_types()
		);
		$this->assertArrayHasKey( 'woo-blaze', self::$channel->get_supported_campaign_types() );
		$this->assertEquals( 'woo-blaze', self::$channel->get_supported_campaign_types()['woo-blaze']->get_id() );
	}

	/**
	 * Ensure we return a valid icon url
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_icon_url
	 */
	public function test_get_icon_url_is_valid() {
		$this->assertNotEmpty( self::$channel->get_icon_url() );
		$this->assertNotFalse( filter_var( self::$channel->get_icon_url(), FILTER_VALIDATE_URL ) );
	}

	/**
	 * Ensure we return a valid setup url
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_setup_url
	 */
	public function test_get_setup_url_is_valid() {
		$this->assertNotEmpty( self::$channel->get_setup_url() );
		$this->assertNotFalse( filter_var( self::$channel->get_setup_url(), FILTER_VALIDATE_URL ) );
	}

	/**
	 * Ensure we return a valid campaign list
	 * TODO: We need to refactor the code to be able to correctly mock DSP calls. Currently, its only testing that we don't get an error
	 *
	 * @covers BlazeAds\Blaze_Marketing_Channel::get_setup_url
	 */
	public function test_get_campaigns_returns_array() {
		$campaigns = self::$channel->get_campaigns();
		$this->assertIsArray( $campaigns );
	}
}
