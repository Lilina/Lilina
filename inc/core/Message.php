<?php
/**
 *
 * @package Lilina
 * @subpackage Admin
 */

class Message {
	public $message = '';
	public $type = 'message';
	/**
	 * Add a message
	 */
	public function __construct($message = 'Unknown error') {
		$this->message = $message;
	}
	
	public function __toString() {
		return $this->message;
	}
}