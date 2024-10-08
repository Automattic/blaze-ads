<?php
/**
 * Class Blaze_Ads_Utils
 *
 * @package Automattic\BlazeAds
 */

namespace BlazeAds;

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Connection\Client;
use BlazeAds\Exceptions\Base_Exception;

/**
 * Blaze Ads Utils class
 */
class Blaze_Ads_Utils {

	/**
	 * Calls the Blaze (aka DSP) server
	 *
	 * @param int    $blog_id The blog ID.
	 * @param string $route The route to call.
	 * @param string $method The HTTP method to use.
	 * @param array  $query_params The query parameters to send.
	 *
	 * @return array with { code: int and body: mixed }.
	 * @throws Base_Exception If the request to the DSP ends in error.
	 */
	public static function call_dsp_server(
		int $blog_id,
		string $route,
		string $method = 'GET',
		array $query_params = array()
	): array {
		// Make the API request.
		$url = sprintf( '/sites/%d/wordads/dsp/api/%s', $blog_id, $route );
		$url = add_query_arg( $query_params, $url );

		$response = Client::wpcom_json_api_request_as_user(
			$url,
			'v2',
			array( 'method' => $method ),
			null,
			'wpcom'
		);

		if ( is_wp_error( $response ) ) {
			throw new Base_Exception( esc_html( $response->get_error_message() ), 'blazeads_dsp_request_failed' );
		}

		$response_code         = wp_remote_retrieve_response_code( $response );
		$response_body_content = wp_remote_retrieve_body( $response );
		$response_body         = json_decode( $response_body_content, true );

		return array(
			'status' => $response_code,
			'body'   => $response_body,
		);
	}

	/**
	 * Mirrors JS's createInterpolateElement functionality.
	 * Returns a string where angle brackets expressions are replaced with unescaped html while the rest is escaped.
	 *
	 * @param string $string string to process.
	 * @param array  $element_map map of elements to not escape.
	 *
	 * @return string String where all the html was escaped, except for the tags specified in element map.
	 */
	public static function esc_interpolated_html( string $string, array $element_map ): string {
		// Regex to match string expressions wrapped in angle brackets.
		$tokenizer    = '/<(\/)?(\w+)\s*(\/)?>/';
		$string_queue = array();
		$token_queue  = array();
		$last_mapped  = true;
		// Start with a copy of the string.
		$processed = $string;

		// Match every angle bracket expression.
		while ( preg_match( $tokenizer, $processed, $matches ) ) {
			$matched = $matches[0];
			$token   = $matches[2];
			// Determine if the expression is closing (</a>) or self-closed (<img />).
			$is_closing     = ! empty( $matches[1] );
			$is_self_closed = ! empty( $matches[3] );

			// Split the string on the current matched token.
			$split = explode( $matched, $processed, 2 );
			if ( $last_mapped ) {
				// If the previous token was present in the element map, or we're at the start, put the string between it and the current token in the queue.
				$string_queue[] = $split[0];
			} else {
				// If the previous token was not found in the elements map, append it together with the string before the current token to the last item in the queue.
				$string_queue[ count( $string_queue ) - 1 ] .= $split[0];
			}
			// String is now the bit after the current token.
			$processed = $split[1];

			// Check if the current token is in the map.
			if ( isset( $element_map[ $token ] ) ) {
				preg_match( '/^<(\w+)(\s.+?)?\/?>$/', $element_map[ $token ], $map_matches );
				if ( ! $map_matches ) {
					// Should not happen with the properly formatted html as map value. Return the whole string escaped.
					return esc_html( $string );
				}
				// Add the matched token and its attributes into the token queue. It will not be escaped when constructing the final string.
				$tag   = $map_matches[1];
				$attrs = $map_matches[2] ?? '';
				if ( $is_closing ) {
					$token_queue[] = '</' . $tag . '>';
				} elseif ( $is_self_closed ) {
					$token_queue[] = '<' . $tag . $attrs . '/>';
				} else {
					$token_queue[] = '<' . $tag . $attrs . '>';
				}

				// Mark the current token as found in the map.
				$last_mapped = true;
			} else {
				// Append the current token into the string queue. It will be escaped.
				$string_queue[ count( $string_queue ) - 1 ] .= $matched;
				// Mark the current token as not found in the map.
				$last_mapped = false;
			}
		}

		// No mapped tokens were found in the string, or token and string queues are not of equal length.
		// The latter should not happen - token queue and string queue should be the same length.
		if ( empty( $token_queue ) || count( $token_queue ) !== count( $string_queue ) ) {
			return esc_html( $string );
		}

		// Construct the final string by escaping the string queue values and not escaping the token queue.
		$result = '';
		while ( ! empty( $token_queue ) ) {
			$result .= esc_html( array_shift( $string_queue ) ) . array_shift( $token_queue );
		}
		$result .= esc_html( $processed );

		return $result;
	}
}
