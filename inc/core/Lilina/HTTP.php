<?php
/**
 * HTTP class
 * @package Lilina
 * @subpackage HTTP
 */
if (!defined('LILINA_USERAGENT')) {
	define('LILINA_USERAGENT', 'Lilina/'. LILINA_CORE_VERSION . '; (' . get_option('baseurl') . '; http://getlilina.org/; Allow Like Gecko)');
}

/**
 * HTTP class
 *
 * Based on SimplePie_File, RequestCore and WordPress' WP_Http classes
 *
 * @todo Add non-blocking request support
 *
 * @package Lilina
 * @subpackage HTTP
 */
class Lilina_HTTP {
	const POST = 'POST';
	const GET = 'GET';
	const HEAD = 'HEAD';

	const VERSION = '1.4';

	protected static $transports = array();
	protected static $transport  = null;

	/**
	 * This is a static class, do not instantiate it
	 */
	private function __construct() {
	}

	/**
	 * Register a transport
	 *
	 * @param Lilina_HTTP_Transport $transport Transport to add, must support the Lilina_HTTP_Transport interface
	 */
	public static function add_transport(Lilina_HTTP_Transport $transport) {
		if (empty(Lilina_HTTP::$transports)) {
			Lilina_HTTP::$transports = array(
				'Lilina_HTTP_Transport_cURL',
				'Lilina_HTTP_Transport_fsockopen',
			);
		}
		Lilina_HTTP::$transports = array_merge(Lilina_HTTP::$transports, array($transport));
	}

	/**
	 * Get a working transport
	 *
	 * @return Lilina_HTTP_Transport
	 */
	protected static function get_transport() {
		if (!is_null(Lilina_HTTP::$transport)) {
			return new Lilina_HTTP::$transport();
		}
		if (empty(Lilina_HTTP::$transports)) {
			Lilina_HTTP::$transports = array(
				'Lilina_HTTP_Transport_cURL',
				'Lilina_HTTP_Transport_fsockopen',
			);
		}

		// Find us a working transport
		foreach(Lilina_HTTP::$transports as $class) {
			if (!class_exists($class))
				continue;

			$result = call_user_func(array($class, 'test'));
			if ($result) {
				Lilina_HTTP::$transport = $class;
				break;
			}
		}
		if (Lilina_HTTP::$transport === null) {
			throw new Lilina_HTTP_Exception(_r('No working transports found'), 'notransport', Lilina_HTTP::$transports);
		}

		return new Lilina_HTTP::$transport();
	}

	/**#@+
	 * Convienience function
	 *
	 * @param string $url
	 * @param array $headers
	 * @param array $data
	 * @param array $options
	 * @return Lilina_HTTP_Response
	 */
	public static function get($url, $headers = array(), $data = array(), $options = array()) {
		return Lilina_HTTP::request($url, $headers, $data, Lilina_HTTP::GET, $options);
	}
	public static function post($url, $headers = array(), $data = array(), $options = array()) {
		return Lilina_HTTP::request($url, $headers, $data, Lilina_HTTP::POST, $options);
	}
	public static function head($url, $headers = array(), $data = array(), $options = array()) {
		return Lilina_HTTP::request($url, $headers, $data, Lilina_HTTP::HEAD, $options);
	}
	/**#@-*/

	/**
	 * Main interface for HTTP requests
	 *
	 * @param string $url URL to request
	 * @param array $headers Extra headers to send with the request
	 * @param array $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
	 * @param string $type HTTP request type (use Lilina_HTTP constants)
	 * @return Lilina_HTTP_Response
	 */
	public static function request($url, $headers = array(), $data = array(), $type = Lilina_HTTP::GET, $options = array()) {
		if (!preg_match('/^http(s)?:\/\//i', $url)) {
			throw new Lilina_HTTP_Exception(_r('Only HTTP requests are handled.'), 'nonhttp', $url);
		}
		$defaults = array(
			'timeout' => 10,
			'useragent' => LILINA_USERAGENT,
			'redirected' => 0,
			'redirects' => 10,
			'blocking' => true,
			'type' => $type,
			'filename' => false
		);
		$options = array_merge($defaults, $options);
		$transport = Lilina_HTTP::get_transport();
		$response = $transport->request($url, $headers, $data, $options);
		return Lilina_HTTP::parse_response($response, $url, $headers, $data, $options);
	}

	/**
	 * HTTP response parser
	 *
	 * @param string $headers Full response text including headers and body
	 * @param string $url Original request URL
	 * @param array $req_headers Original $headers array passed to {@link request()}, in case we need to follow redirects
	 * @param array $req_data Original $data array passed to {@link request()}, in case we need to follow redirects
	 * @param array $options Original $options array passed to {@link request()}, in case we need to follow redirects
	 * @return Lilina_HTTP_Response
	 */
	protected static function parse_response($headers, $url, $req_headers, $req_data, $options) {
		$return = new Lilina_HTTP_Response();
		if (!$options['blocking']) {
			return $return;
		}

		if (!$options['filename']) {
			$headers = explode("\r\n\r\n", $headers, 2);
			$return->body = array_pop($headers);
			$headers = $headers[0];
		}
		else {
			$return->body = '';
		}
		// Pretend CRLF = LF for compatibility (RFC 2616, section 19.3)
		$headers = str_replace("\r\n", "\n", $headers);
		// Unfold headers (replace [CRLF] 1*( SP | HT ) with SP) as per RFC 2616 (section 2.2)
		$headers = preg_replace('/\n[ \t]/', ' ', $headers);
		$headers = explode("\n", $headers);
		preg_match('#^HTTP/1\.\d[ \t]+(\d+)#i', array_shift($headers), $matches);
		if (empty($matches)) {
			throw new Lilina_HTTP_Exception(_r('Response could not be parsed'), 'noversion', $headers);
		}
		$return->status_code = (int) $matches[1];
		if ($return->status_code >= 200 && $return->status_code < 300) {
			$return->success = true;
		}

		foreach ($headers as $header) {
			list($key, $value) = explode(':', $header, 2);
			$value = trim($value);
			preg_replace('#(\s+)#i', ' ', $value);
			$key = strtolower($key);
			if (isset($return->headers[$key])) {
				if (!is_array($return->headers[$key])) {
					$return->headers[$key] = array($return->headers[$key]);
				}
				$return->headers[$key][] = $value;
			}
			else {
				$return->headers[$key] = $value;
			}
		}
		if (isset($return->headers['transfer-encoding'])) {
			$return->body = Lilina_HTTP::decode_chunked($return->body);
			unset($return->headers['transfer-encoding']);
		}
		if (isset($return->headers['content-encoding'])) {
			$return->body = self::decompress($return->body);
		}

		//fsockopen and cURL compatibility
		if (isset($return->headers['connection'])) {
			unset($return->headers['connection']);
		}

		if (in_array($return->status_code, array(300, 301, 302, 303, 307)) || $return->status_code > 307 && $return->status_code < 400) {
			if (isset($return->headers['location']) && $options['redirected'] < $options['redirects']) {
				$options['redirected']++;
				$location = $return->headers['location'];
				return Lilina_HTTP::request($location, $req_headers, $req_data, false, $options);
			}
			elseif ($options['redirected'] >= $options['redirects']) {
				throw new Lilina_HTTP_Exception(_r('Too many redirects'), 'toomanyredirects', $return);
			}
		}

		$return->redirects = $options['redirected'];
		return $return;
	}

	protected static function decode_chunked($data) {
		if (!preg_match('/^[0-9a-f]+(\s|\r|\n)+/mi', trim($data))) {
			return $data;
		}

		$decoded = '';
		$body = $data;

		while (true) {
			$is_chunked = (bool) preg_match( '/^([0-9a-f]+)(\s|\r|\n)+/mi', $body, $matches );
			if (!$is_chunked) {
				// Looks like it's not chunked after all
				//throw new Exception('Not chunked after all: ' . $body);
				return $body;
			}

			$length = hexdec($matches[1]);
			$chunk_length = strlen($matches[0]);
			$decoded .= $part = substr($body, $chunk_length, $length);
			$encoded = ltrim(substr($encoded, $chunk_length + $length), "\r\n");

			if (trim($body) === '0') {
				// We'll just ignore the footer headers
				return $decoded;
			}
		}
	}

	public static function flattern($array) {
		$return = array();
		foreach ($array as $key => $value) {
			$return[] = "$key: $value";
		}
		return $return;
	}

	protected static function decompress($data) {
		if (function_exists('gzdecode') && ($decoded = gzdecode($data)) !== false) {
			$return->body = $decoded;
		}
		elseif (function_exists('gzinflate') && ($decoded = @gzinflate($data)) !== false) {
			$return->body = $decoded;
		}
		elseif (($decoded = self::compatible_gzinflate($data)) !== false) {
			return $decoded;
		}
		elseif (function_exists('gzuncompress') && ($decoded = @gzuncompress($data)) !== false) {
			return $decoded;
		}
		
		return $data;
	}

	/**
	 * Decompress deflated string while staying compatible with the majority of servers.
	 *
	 * Certain servers will return deflated data with headers which PHP's gziniflate()
	 * function cannot handle out of the box. The following function lifted from
	 * http://au2.php.net/manual/en/function.gzinflate.php#77336 will attempt to deflate
	 * the various return forms used.
	 *
	 * @link http://au2.php.net/manual/en/function.gzinflate.php#77336
	 *
	 * @param string $gzData String to decompress.
	 * @return string|bool False on failure.
	 */
	protected static function compatible_gzinflate($gzData) {
		if ( substr($gzData, 0, 3) == "\x1f\x8b\x08" ) {
			$i = 10;
			$flg = ord( substr($gzData, 3, 1) );
			if ( $flg > 0 ) {
				if ( $flg & 4 ) {
					list($xlen) = unpack('v', substr($gzData, $i, 2) );
					$i = $i + 2 + $xlen;
				}
				if ( $flg & 8 )
					$i = strpos($gzData, "\0", $i) + 1;
				if ( $flg & 16 )
					$i = strpos($gzData, "\0", $i) + 1;
				if ( $flg & 2 )
					$i = $i + 2;
			}
			return gzinflate( substr($gzData, $i, -8) );
		} else {
			return false;
		}
	}
}