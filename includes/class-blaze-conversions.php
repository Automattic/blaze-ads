<?php
/**
 * Class Blaze_Conversions
 *
 * @package Automattic\BlazeAds
 */

namespace BlazeAds;

defined( 'ABSPATH' ) || exit;

/**
 * Its responsibility is to initiate conversion pixels that the plugin uses.
 */
class Blaze_Conversions {

	/**
	 * Blaze DSP conversion URL
	 *
	 * @var string
	 */
	const CONVERSION_PIXEL_URL = 'https://public-api.wordpress.com/wpcom/v2/wordads/dsp/api/v1/conversion/pixel.gif?%s';

	/**
	 * Initialize hooks for Blaze conversions
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'woocommerce_thankyou', array( $this, 'add_conversion_tracking_pixel' ), 10, 1 );
	}

	/**
	 * Adds the conversion pixel image to the thankyou checkout page
	 *
	 * @param int $order_id Order id.
	 */
	public function add_conversion_tracking_pixel( int $order_id ): void {
		$order              = wc_get_order( $order_id );
		$meta_session_entry = $order->get_meta( '_wc_order_attribution_session_entry', true );

		parse_str( wp_parse_url( $meta_session_entry, PHP_URL_QUERY ), $queries );
		if ( isset( $queries['wpb_id'] ) && isset( $queries['wpb_advertiser'] ) ) {
			$conversion_parameters = http_build_query(
				array(
					'wpb_id'         => $queries['wpb_id'],
					'wpb_advertiser' => $queries['wpb_advertiser'],
					'wpb_type'       => 'purchase',
					'wpb_currency'   => $order->get_currency(),
					'wpb_amount'     => $order->get_total(),
				)
			);

			$url = sprintf( self::CONVERSION_PIXEL_URL, $conversion_parameters );
			echo '<img src="' . esc_url( $url ) . '" crossorigin="anonymous" style="display:none" width="1px" height="1px" />';
		}
	}

}
