<?php
/**
 * The Lilina FeedItems class
 * @package Lilina
 * @subpackage Classes
 */

/**
 * A singleton implementation of LilinaItems
 *
 * @uses LilinaItems
 * @package Lilina
 * @subpackage Classes
 */
class FeedItems extends LilinaItems {
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