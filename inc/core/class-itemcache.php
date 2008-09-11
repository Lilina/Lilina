<?php
/**
 * The ItemCache Class
 * @package Lilina
 * @subpackage Classes
 */

/**
 * The cache handler for persistant storage of items
 *
 * @package Lilina
 * @subpackage Classes
 */
class ItemCache {
	static $instance;

	function FeedItems($sp = null) {
		parent::LilinaItems($sp);
	}
	function get_instance($sp = null) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new FeedItems($sp);
		}
		return self::$instance;
	}

	/**
	 * Stop object cloning
	 *
	 * As this is a singleton, we don't want to be able to clone this
	 * @access private
	 */
	function __clone() {}
}