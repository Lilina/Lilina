<?php
/**
 * cURL HTTP transport
 *
 * @package Lilina
 * @subpackage HTTP
 */

/**
 * cURL HTTP transport
 *
 * @package Lilina
 * @subpackage HTTP
 */
class Lilina_HTTP_Transport_cURL implements Lilina_HTTP_Transport {
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

	public function request($url, $headers = array(), $data = array(), $options = array()) {
		$headers = Lilina_HTTP::flattern($headers);

		switch ($options['type']) {
			case Lilina_HTTP::POST:
				curl_setopt($this->fp, CURLOPT_POST, true);
				curl_setopt($this->fp, CURLOPT_POSTFIELDS, $data);
				break;
			case Lilina_HTTP::HEAD:
				curl_setopt($this->fp, CURLOPT_NOBODY, true);
				break;
		}

		curl_setopt($this->fp, CURLOPT_URL, $url);
		curl_setopt($this->fp, CURLOPT_TIMEOUT, $options['timeout']);
		curl_setopt($this->fp, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
		curl_setopt($this->fp, CURLOPT_REFERER, $url);
		curl_setopt($this->fp, CURLOPT_USERAGENT, $options['useragent']);
		curl_setopt($this->fp, CURLOPT_HTTPHEADER, $headers);

		if (true === $options['blocking']) {
			curl_setopt($this->fp, CURLOPT_HEADER, true);
		}
		else {
			curl_setopt($this->fp, CURLOPT_HEADER, false);
		}

		$this->headers = curl_exec($this->fp);
		if ($options['blocking'] === false) {
			curl_close($this->fp);
			return false;
		}
		if (curl_errno($this->fp) === 23 || curl_errno($this->fp) === 61) {
			curl_setopt($this->fp, CURLOPT_ENCODING, 'none');
			$this->headers = curl_exec($this->fp);
		}
		if (curl_errno($this->fp)) {
			throw new Lilina_HTTP_Exception('cURL error ' . curl_errno($this->fp) . ': ' . curl_error($this->fp), 'curlerror', $this->fp);
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