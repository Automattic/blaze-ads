<?php
/**
 * Class Blaze_Marketing_Channel
 *
 * @package Automattic\WooBlaze
 */

namespace WooBlaze;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Marketing\MarketingChannels;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaign;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaignType;
use Automattic\WooCommerce\Admin\Marketing\MarketingChannelInterface;
use Automattic\WooCommerce\Admin\Marketing\Price;
use Automattic\Jetpack\Modules as Jetpack_Modules;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection_Manager;
use Jetpack_Options;

/**
 * Marketing Channel implementation for Blaze Campaigns.
 * This class is responsible for registering Blaze as a marketing channel and displaying Blaze campaigns
 *
 * Class Woo_Blaze_Marketing_Channel
 */
class Blaze_Marketing_Channel implements MarketingChannelInterface {

	/**
	 * The campaign types supported by Blaze.
	 *
	 * @var array
	 */
	protected array $campaign_types;

	/**
	 * MarketingChannelRegistrar constructor.
	 */
	public function __construct() {
	}

	/**
	 * Initialize the marketing channel.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( ! $this->can_register_marketing_channel() ) {
			return;
		}

		$this->campaign_types = $this->generate_campaign_types();
		$wc_container         = $GLOBALS['wc_container'];
		$marketing_channels   = $wc_container->get( MarketingChannels::class );
		$marketing_channels->register( $this );
	}


	/**
	 * Check if the Multichannel Marketing plugin is active.
	 *
	 * @return bool
	 */
	public function can_register_marketing_channel(): bool {

		// Check if the Multichannel Marketing plugin is active.
		if ( ! defined( 'WC_MCM_EXISTS' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the unique identifier string for the marketing channel extension, also known as the plugin slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'woo-blaze';
	}

	/**
	 * Returns the name of the marketing channel.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return __( 'Woo Blaze', 'woo-blaze' );
	}

	/**
	 * Returns the description of the marketing channel.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __(
			'Drive sales, and elevate your products to center stage, effortlessly. Witness your business flourishing in the blink of an eye.',
			'woo-blaze'
		);
	}

	/**
	 * Returns the path to the channel icon.
	 *
	 * @return string
	 */
	public function get_icon_url(): string {
		return 'https://widgets.wp.com/blaze-dashboard/common/blaze-flame-woo.svg';
	}

	/**
	 * Returns an array of marketing campaign types that the channel supports.
	 *
	 * @return MarketingCampaignType[] Array of marketing campaign type objects.
	 */
	public function get_supported_campaign_types(): array {
		return $this->campaign_types;
	}

	/**
	 * Returns the setup status of the marketing channel.
	 *
	 * @return bool
	 */
	public function is_setup_completed(): bool {
		$connection = new Jetpack_Connection_Manager();
		if ( ! $connection->is_connected() || ! $connection->is_user_connected() ) {
			return false;
		}
		if ( is_plugin_active( 'jetpack/jetpack.php' ) && ! ( new Jetpack_Modules() )->is_active( 'blaze' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the status of the marketing channel's product listings.
	 *
	 * @return string
	 */
	public function get_product_listings_status(): string {
		return self::PRODUCT_LISTINGS_NOT_APPLICABLE;
	}

	/**
	 * Returns the number of channel issues/errors (e.g. account-related errors, product synchronization issues, etc.).
	 *
	 * @return int The number of issues to resolve, or 0 if there are no issues with the channel.
	 */
	public function get_errors_count(): int {
		return 0;
	}

	/**
	 * Returns the URL to the settings page, or the link to complete the setup/onboarding if the channel has not been set up yet.
	 *
	 * @return string
	 */
	public function get_setup_url(): string {
		return admin_url( sprintf( 'admin.php?page=wc-blaze#!/wc-blaze/setup/%s', $this->get_site_hostname() ) );
	}

	/**
	 * Returns the campaign price for the given Blaze campaign.
	 *
	 * @param array $campaign Blaze campaign object.
	 *
	 * @return Price
	 */
	public function get_campaign_price( $campaign ): Price {
		$price_amount = isset( $campaign['campaign_stats']['total_budget'] ) && is_numeric( $campaign['campaign_stats']['total_budget'] )
			? $campaign['campaign_stats']['total_budget']
			: 0;
		$price        = new Price( $price_amount, 'USD' );

		return $price;
	}

	/**
	 * Returns the campaign URL for the given Blaze campaign.
	 *
	 * @param array  $campaign Blaze campaign object.
	 * @param string $site_url Site URL.
	 *
	 * @return string
	 */
	public function get_campaign_url( $campaign, $site_url ): string {
		return admin_url(
			sprintf(
				'admin.php?page=wc-blaze#!/wc-blaze/campaigns/%s/%s',
				$campaign['campaign_id'],
				$site_url
			)
		);
	}

	/**
	 *  Returns the site hostname.
	 *
	 * @return string
	 */
	public function get_site_hostname(): string {
		return parse_url( get_site_url(), PHP_URL_HOST );
	}

	/**
	 * Returns the marketing campaigns from the Blaze campaigns information.
	 *
	 * @param array $campaigns List of Blaze campaigns.
	 *
	 * @return MarketingCampaign[] Array of marketing campaign objects.
	 */
	public function get_marketing_campaigns( $campaigns ): array {
		$marketing_campaigns = array();
		$site_url            = parse_url( get_site_url(), PHP_URL_HOST );

		foreach ( $campaigns as $campaign ) {
			$marketing_campaigns[] = new MarketingCampaign(
				$campaign['campaign_id'],
				$this->campaign_types['woo-blaze'],
				$campaign['name'],
				$this->get_campaign_url( $campaign, $this->get_site_hostname() ),
				$this->get_campaign_price( $campaign )
			);
		}

		return $marketing_campaigns;
	}

	/**
	 * Returns an array of the channel's marketing campaigns.
	 *
	 * @return MarketingCampaign[]
	 */
	public function get_campaigns(): array {
		$query_params = array(
			'order'    => 'asc',
			'order_by' => 'post_date',
			'status'   => 'active',
		);

		$blog_id  = Jetpack_Options::get_option( 'id' );
		$path     = sprintf( 'v1/search/campaigns/site/%s', $blog_id );
		$response = Woo_Blaze_Utils::call_dsp_server( $blog_id, $path, 'GET', $query_params );

		if ( ! isset( $response['campaigns'] ) ) {
			return array();
		}

		$campaigns = array_map(
			function ( $campaign ) {
				return array(
					'campaign_id'    => $campaign['campaign_id'],
					'start_date'     => $campaign['start_date'],
					'end_date'       => $campaign['end_date'],
					'name'           => $campaign['name'],
					'campaign_stats' => $campaign['campaign_stats'],
					'audience_list'  => $campaign['audience_list'],
				);
			},
			$response['campaigns']
		);

		return $this->get_marketing_campaigns( $campaigns );
	}

	/**
	 * Returns the URL for creating a new marketing campaign.
	 *
	 * @return mixed
	 */
	public function get_campaign_create_url(): string {
		return admin_url(
			sprintf(
				'admin.php?page=wc-blaze#!/wc-blaze/posts/promote/post-0/%s',
				$this->get_site_hostname()
			)
		);
	}

	/**
	 * Generate an array of supported marketing campaign types.
	 *
	 * @return MarketingCampaignType[]
	 */
	protected function generate_campaign_types(): array {
		return array(
			'woo-blaze' => new MarketingCampaignType(
				'woo-blaze',
				$this,
				'Woo Blaze',
				$this->get_description(),
				$this->get_campaign_create_url(),
				$this->get_icon_url()
			),
		);
	}
}



