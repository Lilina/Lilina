<?php
class Lilina_SimplePie_File extends SimplePie_File {
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
			$args = array(
				'timeout' => $this->timeout,
				'redirects' => $this->redirects,
				'useragent' => $this->useragent
			);

			try {
				$response = Lilina_HTTP::get($this->url, $this->headers, array(), $args);
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