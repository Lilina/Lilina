<?php
/**
 * Database exception
 *
 * @package Lilina
 * @subpackage Database
 */

/**
 * Database exception
 *
 * @package Lilina
 * @subpackage Database
 */
class Lilina_DB_Exception extends Exception {
	public function __construct($message, $code) {
		if (is_string($code)) {
			$code = Errors::get_code($code);
		}
		parent::__construct($message, $code);
	}
}