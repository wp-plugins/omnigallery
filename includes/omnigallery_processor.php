<?php
/**
 * Gallery processor class to be extended by individual processors. This class has an abstract method called <code>get_gallery_images</code>
 * that has to be defined by each inheriting processor.
 *
 * This is also where the OAuth support is implemented. The URLs are defined using abstract functions, while a handful of utility functions are defined.
 * Most utility functions have been adapted from the OAuth PHP package distributed here: http://code.google.com/p/oauth-php/.
 *
 * @package Photonic
 * @subpackage Extensions
 */

abstract class OmniGallery_Processor {
	public $library, $thumb_size, $full_size, $api_key, $api_secret, $provider, $nonce, $oauth_timestamp, $signature_parameters, $oauth_version, $oauth_done;

	function __construct() {
		global $photonic_slideshow_library;
		$this->library = $photonic_slideshow_library;
		$this->nonce = OmniGallery_Processor::nonce();
		$this->oauth_timestamp = time();
		$this->oauth_version = '1.0';
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @abstract
	 * @param array $attr
	 */
	abstract protected function get_gallery_images($attr = array());

	public function oauth_signature_method() {
		return 'HMAC-SHA1';
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public abstract function parse_token($response);

	/**
	 * Generates a nonce for use in signing calls.
	 *
	 * @static
	 * @return string
	 */
	public static function nonce() {
		$mt = microtime();
		$rand = mt_rand();
		return md5($mt . $rand);
	}

	/**
	 * Encodes the URL as per RFC3986 specs. This replaces some strings in addition to the ones done by a rawurlencode.
	 * This has been adapted from the OAuth for PHP project.
	 *
	 * @static
	 * @param $input
	 * @return array|mixed|string
	 */
	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array('OmniGallery_Processor', 'urlencode_rfc3986'), $input);
		}
		else if (is_scalar($input)) {
			return str_replace(
				'+',
				' ',
				str_replace('%7E', '~', rawurlencode($input))
			);
		}
		else {
			return '';
		}
	}

	/**
	 * Takes an array of parameters, then parses it and generates a query string. Prior to generating the query string the parameters are sorted in their natural order.
	 * Without sorting the signatures between this application and the provider might differ.
	 *
	 * @static
	 * @param $params
	 * @return string
	 */
	public static function build_query($params) {
		if (!$params) {
			return '';
		}
		$keys = array_map(array('OmniGallery_Processor', 'urlencode_rfc3986'), array_keys($params));
		$values = array_map(array('OmniGallery_Processor', 'urlencode_rfc3986'), array_values($params));
		$params = array_combine($keys, $values);

		// Sort by keys (natsort)
		uksort($params, 'strnatcmp');
		$pairs = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				natsort($value);
				foreach ($value as $v2) {
					$pairs[] = ($v2 == '') ? "$key=0" : "$key=$v2";
				}
			}
			else {
				$pairs[] = ($value == '') ? "$key=0" : "$key=$value";
			}
		}

		$string = implode('&', $pairs);
		return $string;
	}

	/**
	 * Takes a string of parameters in an HTML encoded string, then returns an array of name-value pairs, with the parameter
	 * name and the associated value.
	 *
	 * @static
	 * @param $input
	 * @return array
	 */
	public static function parse_parameters($input) {
		if (!isset($input) || !$input) return array();

		$pairs = explode('&', $input);

		$parsed_parameters = array();
		foreach ($pairs as $pair) {
			$split = explode('=', $pair, 2);
			$parameter = urldecode($split[0]);
			$value = isset($split[1]) ? urldecode($split[1]) : '';

			if (isset($parsed_parameters[$parameter])) {
				// We have already recieved parameter(s) with this name, so add to the list
				// of parameters with this name
				if (is_scalar($parsed_parameters[$parameter])) {
					// This is the first duplicate, so transform scalar (string) into an array
					// so we can add the duplicates
					$parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
				}

				$parsed_parameters[$parameter][] = $value;
			}
			else {
				$parsed_parameters[$parameter] = $value;
			}
		}
		return $parsed_parameters;
	}

	/**
	 * Returns the URL of a request sans the query parameters.
	 * This has been adapted from the OAuth PHP package.
	 *
	 * @static
	 * @param $url
	 * @return string
	 */
	public static function get_normalized_http_url($url) {
		$parts = parse_url($url);

		$port = @$parts['port'];
		$scheme = $parts['scheme'];
		$host = $parts['host'];
		$path = @$parts['path'];

		$port or $port = ($scheme == 'https') ? '443' : '80';

		if (($scheme == 'https' && $port != '443')
				|| ($scheme == 'http' && $port != '80')) {
			$host = "$host:$port";
		}
		return "$scheme://$host$path";
	}

	/**
	 * If authentication is enabled for this processor and the user has not authenticated this site to access his profile,
	 * this shows a login box.
	 *
	 * @param $post_id
	 * @return string
	 */
	public function get_login_box($post_id = '') {
		$login_box_option = 'photonic_'.$this->provider.'_login_box';
		$login_button_option = 'photonic_'.$this->provider.'_login_button';
		global $$login_box_option, $$login_button_option;
		$login_box = $$login_box_option;
		$login_button = $$login_button_option;
		$ret = '<div id="photonic-login-box-'.$this->provider.'" class="photonic-login-box photonic-login-box-'.$this->provider.'">';
		$ret .= wp_specialchars_decode($login_box, ENT_QUOTES);
		if (trim($login_button) == '') {
			$login_button = 'Login';
		}
		else {
			$login_button = wp_specialchars_decode($login_button, ENT_QUOTES);
		}
		$url = '#';
		$target = '';
		if ($this->provider == 'picasa') {
			$url = $this->get_authorization_url();
			$target = 'target="_blank"';
		}

		if (!empty($post_id)) {
			$rel = "rel='auth-button-single-$post_id'";
		}
		else {
			$rel = '';
		}
		$ret .= "<p class='photonic-auth-button'><a href='$url' $target class='auth-button auth-button-{$this->provider}' $rel>".$login_button."</a></p>";
		$ret .= '</div>';
		return $ret;
	}
}
