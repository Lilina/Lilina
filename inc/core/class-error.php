<?php
/**
 *
 * @package Lilina
 * @subpackage Admin
 */

class Error extends Message {
	public $message = '';
	public $type = 'error';
	/**
	 * Add a message
	 */
	public function __construct($message = 'Unknown error') {
		parent::__construct($message);
	}
	
	public function __toString() {
		return $this->message;
	}
}