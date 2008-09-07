<?php
/**
 *
 * @package Lilina
 * @subpackage Admin
 */

class MessageHandler {
	private static $messages = array();
	
	/**
	 * Add a message
	 */
	public static function add($message = 'Unknown error') {
		if(is_string($message))
			$message = new Message($message);
		MessageHandler::$messages[] = $message;
	}
	
	/**
	 * Get all messages
	 */
	public static function get() {
		return MessageHandler::$messages;
	}
}