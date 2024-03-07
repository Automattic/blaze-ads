<?php
/**
 * Class Blaze_Translations_Loader
 *
 * @package Automattic\WooBlaze
 */

namespace WooBlaze;

defined( 'ABSPATH' ) || exit;

use DateTime;

/**
 * A class for managing WordPress translations loading for WooBlaze.
 *
 * Unlike for other WordPress.org plugins, WooBlaze translations are loaded from https://translate.wordpress.com.
 *
 * @codeCoverageIgnore -- The most of the code interacts with global WordPress methods to wrangle translations. Not that much to test here, so excluding it from test coverage.
 */
class Blaze_Translations_Loader {

	/**
	 * Load translations for the plugin.
	 */
	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'load_woo_blaze_translations' ), 10, 1 );
	}

	/**
	 * Hooks into auto-update process to load plugin translations from translate.wordpress.com.
	 *
	 * Runs in a cron thread, or in a visitor thread if triggered
	 * by _maybe_update_plugins(), or in an auto-update thread.
	 *
	 * @param object $transient The update_plugins transient object.
	 *
	 * @return object The same or a modified version of the transient.
	 */
	public function load_woo_blaze_translations( $transient ): object {

		// Load translations from translate.wordpress.com.
		try {
			if ( is_object( $transient ) ) {
				$translations            = $this->get_translations_update_data();
				$merged_translations     = array_merge( isset( $transient->translations ) ? $transient->translations : array(), $translations );
				$transient->translations = $merged_translations;
			}
		} catch ( \Exception $ex ) {
			// TODO maybe add some kind of logging here.
			// 'Error with loading WooBlaze translations from WordPress.com. Reason: ' . $ex->getMessage().
			return $transient;
		}
		return $transient;

	}


	/**
	 * Get translations updates information.
	 *
	 * @return array Update data {product_id => data}
	 * @throws \Exception If something goes wrong with fetching info about translation packages from WordPress.com.
	 */
	public function get_translations_update_data(): array {
		$installed_translations = wp_get_installed_translations( 'plugins' );
		$locales                = array_values( get_available_languages() );

		if ( empty( $locales ) ) {
			return array();
		}

		// Use the same timeout values as Woo Core https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/includes/admin/helper/class-wc-helper-updater.php#L257.
		$timeout = wp_doing_cron() ? 30 : 3;

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( WOOBLAZE_PLUGIN_FILE );

		/**
		 * Note: TextDomain could differ from the plugin slug, but WordPress uses TextDomain to load translations.
		 */
		$plugin_name = $plugin_data['TextDomain'];

		$request_body = array(
			'locales' => $locales,
			'plugins' => array(),
		);

		$request_body['plugins'][ $plugin_name ] = array(
			'version' => WOOBLAZE_VERSION_NUMBER,
		);

		$raw_response = wp_remote_post(
			'https://translate.wordpress.com/api/translations-updates/woocommerce',
			array(
				'body'    => wp_json_encode( $request_body ),
				'headers' => array( 'Content-Type: application/json' ),
				'timeout' => $timeout,
			)
		);

		$response_code = wp_remote_retrieve_response_code( $raw_response );
		if ( 200 !== $response_code ) {
			// TODO maybe add some kind of logging here.
			// sprintf( 'Raw response: %s', var_export( $raw_response, true ) ).
			throw new \Exception(
				sprintf( 'Request failed. HTTP response code: %s', $response_code )
			);
		}

		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		if ( array_key_exists( 'success', $response ) && false === $response['success'] ) {
			// The shape of response is not known, so more specific error message can't be provided in exception. Logging the response body for debuggin purposes.
			// TODO maybe add some kind of logging here.
			// sprintf( 'Unexpected response body: %s', var_export( $response, true ) ).
			throw new \Exception( 'Unexpected response body.' );
		}

		$language_packs = $response['data'][ $plugin_name ];

		$translations = array();

		foreach ( $language_packs as $language_pack ) {
			// Maybe we have this language pack already installed so lets check revision date.
			if ( array_key_exists( $plugin_name, $installed_translations ) && array_key_exists( $language_pack['wp_locale'], $installed_translations[ $plugin_name ] ) ) {
				$installed_translation_revision_time = new DateTime( $installed_translations[ $plugin_name ][ $language_pack['wp_locale'] ]['PO-Revision-Date'] );
				$new_translation_revision_time       = new DateTime( $language_pack['last_modified'] );
				// Skip if translation language pack is not newer than what is installed already.
				if ( $new_translation_revision_time <= $installed_translation_revision_time ) {
					continue;
				}
			}
			$translations[] = array(
				'type'       => 'plugin',
				'slug'       => $plugin_name,
				'language'   => $language_pack['wp_locale'],
				'version'    => $language_pack['version'],
				'updated'    => $language_pack['last_modified'],
				'package'    => $language_pack['package'],
				'autoupdate' => true,
			);
		}

		return $translations;
	}
}
