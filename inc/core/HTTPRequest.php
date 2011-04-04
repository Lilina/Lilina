<?php
/**
 * HTTP Request class
 */

if(!defined('LILINA_USERAGENT')){
	define('LILINA_USERAGENT', 'Lilina/'. LILINA_CORE_VERSION . '; (' . get_option('baseurl') . '; http://getlilina.org/; Allow Like Gecko)');
}

/**
 * HTTP Request class
 *
 * Based on SimplePie_File, RequestCore and WordPress' WP_Http classes
 *
 * @todo Add chunked encoding support (RFC 2616, section 3.6.1)
 * @todo Add support for content encoding (gzip, compress, deflate) (RFC 2616, section 3.5)
 * @todo Add non-blocking request support
 *
 * @author Ryan McCue
 * @package Lilina
 * @subpackage HTTP
 */
class HTTPRequest {
	const POST = 'POST';
	const GET = 'GET';
	const HEAD = 'HEAD';

	protected $transports = array();
	protected $transport  = 'fsockopen';
	protected $timeout    = 10;
	protected $useragent  = null;
	protected $redirects  = 0;

	public function __construct($transport = 'fsockopen', $timeout = 10, $useragent = null, $transports = array()) {
		$this->transports['curl']      = 'HTTPRequest_cURL';
		$this->transports['fsockopen'] = 'HTTPRequest_fsockopen';
		$this->transports = array_merge($this->transports, $transports);

		if(empty($transport) || !isset($this->transports[$transport])) {
			foreach($this->transports as $t => $class) {
				$result = call_user_func(array($class, 'test'));
				if($result) {
					$transport = $t;
					break;
				}
			}
		}

		$this->transport = $this->transports[$transport];
		
		$this->timeout = $timeout;
		if ($useragent === null) {
			$useragent = LILINA_USERAGENT;
		}
		$this->useragent = $useragent;
	}

	/**#@+
	 * Convienience function
	 *
	 * @param string $url
	 * @param array $headers
	 * @param array $data
	 * @return stdClass See {@link parse_response}
	 */
	public function get($url, $headers = array(), $data = array()) {
		return $this->request($url, $headers, $data, HTTPRequest::GET);
	}
	public function post($url, $headers = array(), $data = array()) {
		return $this->request($url, $headers, $data, HTTPRequest::POST);
	}
	public function head($url, $headers = array(), $data = array()) {
		return $this->request($url, $headers, $data, HTTPRequest::HEAD);
	}
	/**#@-*/

	/**
	 * Main interface for HTTP requests
	 *
	 * @param string $url URL to request
	 * @param array $headers Extra headers to send with the request
	 * @param array $data Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
	 * @param string $type HTTP request type (use HTTPRequest constants)
	 * @return stdClass See {@link parse_response}
	 */
	public function request($url, $headers = array(), $data = array(), $type = HTTPRequest::GET) {
		if (!preg_match('/^http(s)?:\/\//i', $url)) {
			throw new Exception(_r('Only HTTP requests are handled.'));
		}
		$this->redirects = 0;
		$transport = new $this->transport();
		$response = $transport->request($url, $this->timeout, $headers, $data, $this->useragent, $type);
		return $this->parse_response($response, $url, $headers, $data, $type);
	}

	/**
	 * Simple HTTP response parser
	 *
	 * @param string $headers Full response text including headers and body
	 * @param string $url Original request URL
	 * @param array $req_headers Original $headers array passed to {@link request()}, in case we need to follow redirects
	 * @param array $req_data Original $data array passed to {@link request()}, in case we need to follow redirects
	 * @param array $req_type Original $type constant passed to {@link request()}, in case we need to follow redirects
	 * @return stdClass Contains "body" string, "headers" array, "status code" integer, "success" boolean, "redirects" integer as properties
	 */
	protected function parse_response($headers, $url, $req_headers, $req_data, $req_type) {
		$redirects = 10;
		$headers = explode("\r\n\r\n", $headers, 2);
		$return = new stdClass;
		$return->body = array_pop($headers);
		$headers = $headers[0];
		// Pretend CRLF = LF for compatibility (RFC 2616, section 19.3)
		$headers = str_replace("\r\n", "\n", $headers);
		// Unfold headers (replace [CRLF] 1*( SP | HT ) with SP) as per RFC 2616 (section 2.2)
		$headers = preg_replace('/\n[ \t]/', ' ', $headers);
		$headers = explode("\n", $headers);
		preg_match('#^HTTP/1\.\d[ \t]+(\d+)#i', array_shift($headers), $matches);
		if(empty($matches)) {
			throw new Exception(_r('Response could not be parsed'));
		}
		$return->status_code = (int) $matches[1];
		$return->success = false;
		if($return->status_code >= 200 && $return->status_code < 300)
			$return->success = true;

		$return->headers = array();
		foreach($headers as $header) {
			list($key, $value) = explode(':', $header, 2);
			$value = trim($value);
			preg_replace('#(\s+)#i', ' ', $value);
			$key = strtolower($key);
			if(isset($return->headers[$key])) {
				if(!is_array($return->headers[$key])) {
					$return->headers[$key] = array($return->headers[$key]);
				}
				$return->headers[$key][] = $value;
			}
			else {
				$return->headers[$key] = $value;
			}
		}
		if (isset($return->headers['transfer-encoding'])) {
			$return->body = HTTPRequest::decode_chunked($return->body);
		}
		if (isset($return->headers['content-encoding']) && $this->transport !== 'HTTPRequest_cURL') {
			switch ($return->headers['content-encoding']) {
				case 'gzip':
					$decoder = new SimplePie_gzdecode($return->body);
					if ($result = $decoder->parse()) {
						$return->body = $decoder->data;
					}
					else {
						throw new Exception(_r('Unable to decode HTTP "gzip" stream'));
					}
					break;
				case 'deflate':
					if (($body = gzuncompress($return->body)) === false)
					{
						if (($body = gzinflate($return->body)) === false)
						{
							throw new Exception(_r('Unable to decode HTTP "deflate" stream'));
						}
					}
					$return->body = $body;
					break;
			}
		}

		//fsockopen and cURL compatibility
		if(isset($return->headers['connection']))
			unset($return->headers['connection']);

		if ((in_array($return->status_code, array(300, 301, 302, 303, 307)) || $return->status_code > 307 && $return->status_code < 400) && isset($return->headers['location']) && $this->redirects < $redirects) {
			$this->redirects++;
			$location = SimplePie_Misc::absolutize_url($return->headers['location'], $url);
			return $this->request($location, $req_headers, $req_data, $req_type);
		}

		$return->redirects = $this->redirects;
		return $return;
	}

	protected static function decode_chunked($data) {
		if ( ! preg_match( '/^[0-9a-f]+(\s|\r|\n)+/mi', trim($data) ) )
			return $data;

		$decoded = '';
		$body = $data;

		while (true) {
			$is_chunked = (bool) preg_match( '/^([0-9a-f]+)(\s|\r|\n)+/mi', $body, $matches );
			if ( !$is_chunked ) {
				// Looks like it's not chunked after all
				//throw new Exception('Not chunked after all: ' . $body);
				return $body;
			}

			$length = hexdec( $matches[1] );
			$chunk_length = strlen( $matches[0] );
			$decoded .= $part = substr($body, $chunk_length, $length);
			$body = ltrim(str_replace(array($matches[0], $part), '', $body), "\r\n");

			if (trim($body) === '0') {
				// We'll just ignore the footer headers
				return $decoded;
			}
		}
	}

	protected function parse_headers($headers) {
		$return = new stdClass;

		return $return;
	}

	public static function flattern($array) {
		$return = array();
		foreach($array as $key => $value) {
			$return[] = "$key: $value";
		}
		return $return;
	}
}

class HTTPRequest_cURL {
	public $headers = array();
	public $info;

	public function __construct() {
		$curl = curl_version();
		$this->version = $curl['version'];
		$this->fp = curl_init();
		curl_setopt($this->fp, CURLOPT_HEADER, 1);
		curl_setopt($this->fp, CURLOPT_RETURNTRANSFER, 1);
		if (version_compare($this->version, '7.10.5', '>=')) {
			curl_setopt($this->fp, CURLOPT_ENCODING, '');
		}
		curl_setopt ($this->fp, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($this->fp, CURLOPT_SSL_VERIFYPEER, 0); 
	}

	public function request($url, $timeout = 10, $headers = array(), $data = array(), $useragent = null, $type = HTTPRequest::GET) {
		$r = array('blocking' => true);
		$headers = HTTPRequest::flattern($headers);

		switch($type) {
			case HTTPRequest::POST:
				curl_setopt($this->fp, CURLOPT_POST, true);
				curl_setopt($this->fp, CURLOPT_POSTFIELDS, $data);
				break;
			case HTTPRequest::HEAD:
				curl_setopt($this->fp, CURLOPT_NOBODY, true);
				break;
		}

		curl_setopt($this->fp, CURLOPT_URL, $url);
		curl_setopt($this->fp, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->fp, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($this->fp, CURLOPT_REFERER, $url);
		curl_setopt($this->fp, CURLOPT_USERAGENT, $useragent);
		curl_setopt($this->fp, CURLOPT_HTTPHEADER, $headers);

		if ( true === $r['blocking'] )
			curl_setopt($this->fp, CURLOPT_HEADER, true);
		else
			curl_setopt($this->fp, CURLOPT_HEADER, false);

		$this->headers = curl_exec($this->fp);
		if ( !$r['blocking'] ) {
			curl_close($this->fp);
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}
		if (curl_errno($this->fp) === 23 || curl_errno($this->fp) === 61) {
			curl_setopt($this->fp, CURLOPT_ENCODING, 'none');
			$this->headers = curl_exec($this->fp);
		}
		if (curl_errno($this->fp)) {
			throw new Exception('cURL error ' . curl_errno($this->fp) . ': ' . curl_error($this->fp));
			return;
		}
		$this->info = curl_getinfo($this->fp);
		curl_close($this->fp);

		return $this->headers;
	}

	/**
	 * Whether this transport is valid
	 *
	 * @return boolean True if the transport is valid, false otherwise.
	 */
	public static function test() {
		return (function_exists('curl_init') && function_exists('curl_exec'));
	}
}

class HTTPRequest_fsockopen {
	public $headers = array();
	public $info;

	public function request($url, $timeout = 10, $headers = array(), $data = array(), $useragent = null, $type = HTTPRequest::GET) {
		$url_parts = parse_url($url);
		if (isset($url_parts['scheme']) && strtolower($url_parts['scheme']) === 'https') {
			$url_parts['host'] = "ssl://$url_parts[host]";
			$url_parts['port'] = 443;
		}
		if (!isset($url_parts['port'])) {
			$url_parts['port'] = 80;
		}
		$fp = @fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, $timeout);
		if (!$fp) {
			throw new Exception('fsockopen error: ' . $errstr);
			return;
		}
		stream_set_timeout($fp, $timeout);

		$request_body = '';
		$out = '';
		switch($type) {
			case HTTPRequest::POST:
				if (isset($url_parts['path'])) {
					$path = $url_parts['path'];
					if (isset($url_parts['query'])) {
						$path .= '?' . $url_parts['query'];
					}
				}
				else {
					$path = '/';
				}
				$out = "POST $path HTTP/1.0\r\n";
				if (is_array($data)) {
					$request_body = http_build_query($data, null, '&');
				}
				else {
					$request_body = $data;
				}
				$headers['Content-Length'] = strlen($request_body);
				$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
				break;
			case HTTPRequest::HEAD:
				$head = HTTPRequest_fsockopen::format_get($url_parts, $data);
				$out = "HEAD $head HTTP/1.0\r\n";
				break;
			default:
				$get = HTTPRequest_fsockopen::format_get($url_parts, $data);
				$out = "GET $get HTTP/1.0\r\n";
				break;
		}
		$out .= "Host: {$url_parts['host']}\r\n";
		$out .= "User-Agent: $useragent\r\n";
		$accept_encoding = $this->accept_encoding();
		if (!empty($accept_encoding)) {
			$out .= "Accept-Encoding: $accept_encoding\r\n";
		}

		if (isset($url_parts['user']) && isset($url_parts['pass'])) {
			$out .= "Authorization: Basic " . base64_encode("$url_parts[user]:$url_parts[pass]") . "\r\n";
		}

		$headers = HTTPRequest::flattern($headers);
		$out .= implode($headers, "\r\n");
		$out .= "\r\nConnection: Close\r\n\r\n" . $request_body;
		fwrite($fp, $out);

		$this->info = stream_get_meta_data($fp);

		$this->headers = '';
		while (!feof($fp)) {
			$this->headers .= fread($fp, 1160);
			$this->info = stream_get_meta_data($fp);
		}
		if ($this->info['timed_out']) {
			throw new Exception('fsocket timed out');
		}
		fclose($fp);

		return $this->headers;
	}

	protected static function accept_encoding() {
		$type = array();
		if ( function_exists( 'gzinflate' ) )
			$type[] = 'deflate;q=1.0';

		if ( function_exists( 'gzuncompress' ) )
			$type[] = 'compress;q=0.5';

		$type[] = 'gzip;q=0.5';

		return implode(', ', $type);
	}

	protected static function format_get($url_parts, $data) {
		if(!empty($data)) {
			$url_parts['query'] .= '&' . http_build_query($data, null, '&');
			$url_parts['query'] = trim($url_parts['query'], '&');
		}
		if (isset($url_parts['path'])) {
			if (isset($url_parts['query'])) {
				$get = "$url_parts[path]?$url_parts[query]";
			}
			else {
				$get = $url_parts['path'];
			}
		}
		else {
			$get = '/';
		}
		return $get;
	}

	/**
	 * Whether this transport is valid
	 *
	 * @return boolean True if the transport is valid, false otherwise.
	 */
	public static function test() {
		return function_exists('fsockopen');
	}
}