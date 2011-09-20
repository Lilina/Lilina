<?php
/**
 * HTTP response class
 *
 * Contains a response from Lilina_HTTP::request()
 * @package Lilina
 * @subpackage HTTP
 */

/**
 * HTTP response class
 *
 * Contains a response from Lilina_HTTP::request()
 * @package Lilina
 * @subpackage HTTP
 */
class Lilina_HTTP_Response {
	/**
	 * Response body
	 * @var string
	 */
	public $body = '';

	/**
	 * Headers, as an associative array
	 * @var array
	 */
	public $headers = array();

	/**
	 * Status code, false if non-blocking
	 * @var integer|boolean
	 */
	public $status_code = false;

	/**
	 * Whether the request succeeded or not
	 * @var boolean
	 */
	public $success = false;

	/**
	 * Number of redirects the request used
	 * @var integer
	 */
	public $redirects = 0;

	/**
	 * URL requested
	 * @var string
	 */
	public $url = '';
}