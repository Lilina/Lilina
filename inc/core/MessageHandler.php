<?php
/**
 *
 * @package Lilina
 * @subpackage Admin
 */

class MessageHandler {
	private static $messages = array();
	private static $errors = array();

	/**
	 * Add a generic message
	 */
	public static function add($message = 'Unknown message', $uid = null) {
		if(is_string($message))
			$message = new Message($message);

		if($uid !== null)
			self::$messages[$uid] = $message;
		else
			self::$messages[] = $message;
	}

	/**
	 * Add an error message
	 *
	 * @param Error|string|object $message Either pass a string, or an Error object
	 */
	public static function add_error($message = 'Unknown error', $uid = null) {
		if(is_string($message))
			$message = new Error($message);

		if($uid !== null)
			self::$errors[$uid] = $message;
		else
			self::$errors[] = $message;
	}

	/**
	 * Get all messages
	 */
	public static function get() {
		return array_merge(self::$messages, self::$errors);
	}

	/**
	 * Get all error messages
	 */
	public static function get_errors() {
		return self::$errors;
	}

	/**
	 * Get all generic messages
	 */
	public static function get_messages() {
		return self::$messages;
	}
}