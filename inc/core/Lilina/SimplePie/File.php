<?php
class Lilina_SimplePie_File extends SimplePie_File {

	function Lilina_SimplePie_File($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		$this->__construct($url, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
	}
	function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		$this->url = $url;
		$this->timeout = $timeout;
		$this->redirects = $redirects;
		$this->headers = $headers;

		$this->useragent = $useragent;
		if ( SIMPLEPIE_USERAGENT === $this->useragent ) {
			// Use LILINA_USERAGENT instead
			$this->useragent = null;
		}

		$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE;

		if ( preg_match('/^http(s)?:\/\//i', $url) ) {
			$args = array( 'timeout' => $this->timeout, 'redirection' => $this->redirects);

			$request = new HTTPRequest('', $this->timeout, $this->useragent);
			try {
				$response = $request->get($this->url, $this->headers);
			}
			catch (Exception $e) {
				$this->error = 'HTTP Error: ' . $e->getMessage();
				$this->success = false;
				return;
			}

			$this->headers = $response->headers;
			$this->body = $response->body;
			$this->status_code = $response->status_code;
			$this->success = true;
		} else {
			if ( ! $this->body = file_get_contents($url) ) {
				$this->error = 'file_get_contents could not read the file';
				$this->success = false;
			}
		}
	}
}