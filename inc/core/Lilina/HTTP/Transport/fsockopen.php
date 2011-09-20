<?php
/**
 * fsockopen HTTP transport
 *
 * @package Lilina
 * @subpackage HTTP
 */

/**
 * fsockopen HTTP transport
 *
 * @package Lilina
 * @subpackage HTTP
 */
class Lilina_HTTP_Transport_fsockopen implements Lilina_HTTP_Transport {
	public $headers = array();
	public $info;

	public function request($url, $headers = array(), $data = array(), $options = array()) {
		$url_parts = parse_url($url);
		if (isset($url_parts['scheme']) && strtolower($url_parts['scheme']) === 'https') {
			$url_parts['host'] = "ssl://$url_parts[host]";
			$url_parts['port'] = 443;
		}
		if (!isset($url_parts['port'])) {
			$url_parts['port'] = 80;
		}
		$fp = @fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, $options['timeout']);
		if (!$fp) {
			throw new Lilina_HTTP_Exception($errstr, 'fsockopenerror');
			return;
		}
		stream_set_timeout($fp, $options['timeout']);

		$request_body = '';
		$out = '';
		switch ($options['type']) {
			case Lilina_HTTP::POST:
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
			case Lilina_HTTP::HEAD:
				$head = Lilina_HTTP_Transport_fsockopen::format_get($url_parts, $data);
				$out = "HEAD $head HTTP/1.0\r\n";
				break;
			default:
				$get = Lilina_HTTP_Transport_fsockopen::format_get($url_parts, $data);
				$out = "GET $get HTTP/1.0\r\n";
				break;
		}
		$out .= "Host: {$url_parts['host']}\r\n";
		$out .= "User-Agent: {$options['useragent']}\r\n";
		$accept_encoding = $this->accept_encoding();
		if (!empty($accept_encoding)) {
			$out .= "Accept-Encoding: $accept_encoding\r\n";
		}

		if (isset($url_parts['user']) && isset($url_parts['pass'])) {
			$out .= "Authorization: Basic " . base64_encode("$url_parts[user]:$url_parts[pass]") . "\r\n";
		}

		$headers = Lilina_HTTP::flattern($headers);
		$out .= implode($headers, "\r\n");
		$out .= "\r\nConnection: Close\r\n\r\n" . $request_body;
		fwrite($fp, $out);
		if (!$options['blocking']) {
			fclose($fp);
			return '';
		}

		$this->info = stream_get_meta_data($fp);

		$this->headers = '';
		$this->info = stream_get_meta_data($fp);
		if (!$options['filename']) {
			while (!feof($fp)) {
				$this->headers .= fread($fp, 1160);
			}
		}
		else {
			$download = fopen($options['filename'], 'wb');
			$doingbody = false;
			$response = '';
			while (!feof($fp)) {
				$block = fread($fp, 1160);
				if ($doingbody) {
					fwrite($download, $block);
				}
				else {
					$response .= $block;
					if (strpos($response, "\r\n\r\n")) {
						list($this->headers, ) = explode("\r\n\r\n", $response, 2);
						$doingbody = true;
					}
				}
			}
			fclose($download);
		}
		if ($this->info['timed_out']) {
			throw new Lilina_HTTP_Exception('fsocket timed out', 'timeout');
		}
		fclose($fp);

		return $this->headers;
	}

	protected static function accept_encoding() {
		$type = array();
		if (function_exists('gzinflate')) {
			$type[] = 'deflate;q=1.0';
		}

		if (function_exists('gzuncompress')) {
			$type[] = 'compress;q=0.5';
		}

		if (function_exists('gzdecode')) {
			$type[] = 'gzip;q=0.5';
		}

		return implode(', ', $type);
	}

	protected static function format_get($url_parts, $data) {
		if (!empty($data)) {
			if (empty($url_parts['query']))
				$url_parts['query'] = '';

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