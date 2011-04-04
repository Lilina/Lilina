<?php
/**
 * Exception for HTTP requests
 *
 * @package Lilina
 * @subpackage HTTP
 */

/**
 * Exception for HTTP requests
 *
 * @package Lilina
 * @subpackage HTTP
 */
class Lilina_Updater_Exception extends Exception {
	protected $type;
	protected $data;

	public function __construct($message, $type, $data = null, $previous = null) {
		parent::__construct($message, 0, $previous);

		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * Like getCode(), but a string code.
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Gives any relevant data
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}
}