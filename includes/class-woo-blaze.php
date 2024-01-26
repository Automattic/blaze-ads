<?php
/**
 * Class Woo_Blaze
 *
 * @package WooBlaze
 */

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Blaze\Dashboard_REST_Controller;
use Automattic\Jetpack\Connection\Client;
use Automattic\Jetpack\Connection\Manager as Jetpack_Connection;
use WooBlaze\Blaze_Dashboard;
use WooBlaze\Woo_Blaze_Marketing_Channel;

/**
 * Main class for the Woo Blaze extension. Its responsibility is to initialize the extension.
 */
class Woo_Blaze {

	/**
	 * Static-only class.
	 */
	private function __construct() {
	}

	public static function fetch( $url ) {
		// Call the endpoint. TODO: should be a better way to fetch
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_exec( $ch );
		curl_close( $ch );
	}

	public static function generate_wpbid() {
		// Todo: we should just grab the WPBID from the url
		// Example of wpbid. Should be same characters which is 60: EzEzNjgwNDE6ZTIwNDdhNWUtNTY4YS00YjkxLTliNTItNDZmN2YyMjcxYjRs
		$generated_wpbid  = '';
		$characters       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		for ( $i = 0; $i < 60; $i++ ) {
			$generated_wpbid .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return $generated_wpbid;
	}

	public static function register_conversion_like( $comment_id, $user_id, $like, $optin ) {
		$generated_code = self::generate_wpbid();
		$url            = 'https://public-api.wordpress.com/wpcom/v2/wordads/dsp/api/v1/conversion?wpbid=31368041_' . $generated_code . '&advertiser=f3bad0c7c8d8adf998da735293722d23&product_id=comment_like';
		self::fetch( $url );
		echo $url;
	}

	public static function register_conversion_comment( $comment_ID ) {
		$generated_code = self::generate_wpbid();
		$url            = 'https://public-api.wordpress.com/wpcom/v2/wordads/dsp/api/v1/conversion?wpbid=31368041_' . $generated_code . '&advertiser=f3bad0c7c8d8adf998da735293722d23&product_id=comment';
		self::fetch( $url );
	}

	public static function register_conversion_comment_reply( $comment_ID ) {
		$generated_code = self::generate_wpbid();
		$url            = 'https://public-api.wordpress.com/wpcom/v2/wordads/dsp/api/v1/conversion?wpbid=31368041_' . $generated_code . '&advertiser=f3bad0c7c8d8adf998da735293722d23&product_id=comment_reply';
		self::fetch( $url );
	}

	public static function register_conversion_comment_like( $comment_ID ) {
		$generated_code = self::generate_wpbid();
		$url            = 'https://public-api.wordpress.com/wpcom/v2/wordads/dsp/api/v1/conversion?wpbid=31368041_' . $generated_code . '&advertiser=f3bad0c7c8d8adf998da735293722d23&product_id=comment_like';
		self::fetch( $url );
		echo $url;
	}

	/**
	 * Entry point to the initialization logic.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ), 999 );
		add_action( 'comment_post', array( __CLASS__, 'register_conversion_comment' ), 999 );
		add_action( 'comment_like_record', array( __CLASS__, 'register_conversion_like' ), 999 );
		add_action( 'replyto-comment', array( __CLASS__, 'register_conversion_comment_reply' ), 999 );
		add_action( 'like-comment', array( __CLASS__, 'register_conversion_comment_like' ), 999 );

		// Define marketing channel
		new Woo_Blaze_Marketing_Channel();
	}

	/**
	 * Determines if criteria is met to enable Blaze features.
	 * Keep in mind that this makes remote requests, so we want to avoid calling it when unnecessary, like in the frontend.
	 *
	 * @return bool
	 */
	public static function should_initialize(): bool {
		$should_initialize = true;
		$site_id           = Jetpack_Connection::get_site_id();

		// Only admins should be able to Blaze posts on a site.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if the site supports Blaze.
		if ( is_numeric( $site_id ) && ! \Automattic\Jetpack\Blaze::site_supports_blaze( $site_id ) ) {
			$should_initialize = false;
		}

		/**
		 * Filter to disable all Blaze functionality.
		 *
		 * @param bool $should_initialize Whether Blaze should be enabled. Default to true.
		 *
		 * @since 0.3.0
		 */
		return apply_filters( 'woo_blaze_enabled', $should_initialize );
	}

	/**
	 * Adds Blaze entry point to the menu under the Marketing section.
	 */
	public static function add_admin_menu(): void {
		if ( ! self::should_initialize() ) {
			return;
		}

		$blaze_dashboard = new Blaze_Dashboard();

		$page_suffix = add_submenu_page(
			'woocommerce-marketing',
			esc_attr__( 'Blaze for WooCommerce', 'woo-blaze' ),
			__( 'Blaze for WooCommerce', 'woo-blaze' ),
			'manage_options',
			'wc-blaze',
			array( $blaze_dashboard, 'render' )
		);
		add_action( 'load-' . $page_suffix, array( $blaze_dashboard, 'admin_init' ) );

		// Make the API request.
		$blog_id = Jetpack_Options::get_option( 'id' );

		// https://public-api.wordpress.com/wpcom/v2/sites/208691235/wordads/dsp/api/v1/search/campaigns/site/208691235?order=asc&order_by=post_date&page=1&_envelope=1
		// https://javiolmo89wooblaze.jurassic.tube/wp-json/jetpack/v4/blaze-app/sites/227939112/wordads/dsp/api/v1/search/campaigns/site/227939112?order=asc&order_by=post_date&page=1
		// $url      = sprintf( '/sites/%d/wordads/dsp/api/v1/campaigns/site/%d?order=asc&order_by=post_date&page=1', $blog_id );
		/*
		$url      = '/sites/227939112/wordads/dsp/api/v1/campaigns/site/227939112?order=asc&order_by=post_date&page=1';
		*/

		// $blaze = new Dashboard_REST_Controller();
		$path   = 'v1/search/campaigns/site/227939112';
		$params = '?order=asc&order_by=post_date&page=1';

		// $url = sprintf( '/sites/%d/wordads/dsp/api/%s/%s', $blog_id, $path, $params );
		$url      = sprintf( '/sites/%d/blaze/posts', $blog_id );
		$response = Client::wpcom_json_api_request_as_blog(
			$url,
			'2',
			array( 'method' => 'GET' ),
			null,
			'wpcom'
		);

		$response_code         = wp_remote_retrieve_response_code( $response );
		$response_body_content = wp_remote_retrieve_body( $response );
		$response_body         = json_decode( $response_body_content, true );

		// var_dump($response_body);

		// $req contains $req->get_params()
		// $test = $blaze->get_dsp_generic( 'v1/campaigns', $req );
		// var_dump($blaze);

		// var_dump($response);
	}

}
