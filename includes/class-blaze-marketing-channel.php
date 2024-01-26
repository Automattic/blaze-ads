<?php


namespace WooBlaze;

use Automattic\WooCommerce\Admin\Marketing\MarketingChannels;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaign;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaignType;
use Automattic\WooCommerce\Admin\Marketing\MarketingChannelInterface;
use Automattic\WooCommerce\Admin\Marketing\Price;

// use Exception;

defined( 'ABSPATH' ) || exit;



class Woo_Blaze_Marketing_Channel implements MarketingChannelInterface {


	protected $campaign_types;
	protected $merchant_statuses;
	protected $ads_campaign;

	public function can_register_marketing_channel(): bool {

		// Check if the Multichannel Marketing plugin is active.
		if ( ! defined( 'WC_MCM_EXISTS' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * MarketingChannelRegistrar constructor.
	 */
	public function __construct() {
		$this->campaign_types = $this->generate_campaign_types();
		$wc_container         = $GLOBALS['wc_container'];
		$marketing_channels   = $wc_container->get( MarketingChannels::class );
		$marketing_channels->register( $this );

	}


	public function initialize(): void {

		if ( ! $this->can_register_marketing_channel() ) {
			return;
		}

	}

	public function get_slug(): string {
		return 'woo-blaze';
	}

	public function get_name(): string {
		return __( 'Woo Blaze', 'woo-blaze' );
	}

	public function get_description(): string {
		return __( 'Drive sales, and elevate your products to center stage, effortlessly. Witness your business flourishing in the blink of an eye.', 'woo-blaze' );
	}

	public function get_icon_url(): string {
		return 'https://s1.wp.com/wp-content/themes/h4/assets/blaze/blaze-flame.svg';
	}

	public function get_setup_url(): string {
		return admin_url( 'admin.php?page=wc-admin&path=/google/settings' );
	}

	public function get_supported_campaign_types(): array {
		return $this->campaign_types;
	}

	public function is_setup_completed(): bool {
		return true;
	}

	public function get_product_listings_status(): string {
		return self::PRODUCT_LISTINGS_NOT_APPLICABLE;
	}

	public function get_errors_count(): int {
		return 0;
	}

	public function get_campaigns(): array {

		$blaze_campaigns = array();

		$campaign_data = array(
			'id'   => '1234',
			'name' => 'Woo Blaze test',
		);

		// $marketing_campaign = new MarketingCampaign( '1234', $test_campaign_type_1, 'Ad #1234', 'https://example.com/manage-campaigns', new Price( '1000', 'USD' ) );

		// add the campaign to the list of campaigns
		$blaze_campaigns[] = new MarketingCampaign(
			'1234',
			$this->campaign_types['woo-blaze'],
			'Campaign no 1',
			admin_url( 'admin.php?page=wc-admin&path=/google/dashboard&subpath=/campaigns/edit&programId=' . $campaign_data['id'] ),
			new Price( '1000', 'USD' ),
		);

		// add the campaign to the list of campaigns
		$blaze_campaigns[] = new MarketingCampaign(
			'5678',
			$this->campaign_types['woo-blaze'],
			'Campaign no 2',
			admin_url( 'admin.php?page=wc-admin&path=/google/dashboard&subpath=/campaigns/edit&programId=' . $campaign_data['id'] ),
			new Price( '35', 'USD' ),
		);

		return $blaze_campaigns;

	}

	protected function generate_campaign_types(): array {
		return array(
			'woo-blaze' => new MarketingCampaignType(
				'woo-blaze',
				$this,
				'Woo Blaze',
				'Drive sales, and elevate your products to center stage, effortlessly. Witness your business flourishing in the blink of an eye.',
				admin_url( 'admin.php?page=wc-admin&path=/google/dashboard&subpath=/campaigns/create' ),
				$this->get_icon_url()
			),
		);
	}


}




