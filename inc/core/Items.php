<?php
/**
 * The Lilina items class
 *
 * @deprecated
 * @package Lilina
 * @subpackage Classes
 */
class Items {
	/**
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 */
	protected function __construct() {
		Lilina_Items::get_instance();
	}

	public static function &get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Items();
		}
		return self::$instance;
	}

	/**
	 * Stop object cloning
	 *
	 * As this is a singleton, we don't want to be able to clone this
	 * @access private
	 */
	protected function __clone() {}

	/**
	 * Set the conditions to use when filtering
	 *
	 * @param array $conditions
	 */
	public function set_conditions($conditions) {
	}

	/**
	 * Load items and resort
	 */
	public function init() {
	}

	public function reset() {
	}

	/**
	 * Sort all items
	 *
	 * This bypasses SimplePie's sorting (and lack thereof for items without
	 * timestamps).
	 */
	public function sort_all() {
	}

	/**
	 * Retreive the items without fetching new ones
	 *
	 * Depending on whether {@link init()} is called or not, this may include
	 * new items.
	 * @deprecated Use get_items() instead
	 * @return array List of items
	 */
	public function retrieve() {
		return Lilina_Items::get_instance()->get_items();
	}
	
	/**
	 * Return all items
	 *
	 * @since 1.0
	 *
	 * @return array All items from the feed
	 */
	public function get_items() {
		return Lilina_Items::get_instance()->get_items();
	}
	
	/**
	 * Return a specific item
	 *
	 * Retrieves a specific item and returns it, if it exists. If it does not
	 * exist, returns false.
	 *
	 * @since 1.0
	 *
	 * @param int $hash Item index to retrieve
	 * @return bool|stdClass False if item doesn't exist, otherwise returns the specified item
	 */
	public function get_item($hash) {
		return Lilina_Items::get_instance()->get($hash);
	}

	/**
	 * Returns the current item
	 *
	 * @since 1.0
	 *
	 * @return bool|stdClass False if item doesn't exist, otherwise returns the specified item
	 */
	public function current_item() {
		return Lilina_Items::get_instance()->current();
	}

	/**
	 * Return the previous item
	 *
	 * @since 1.0
	 *
	 * @return bool|stdClass False if item doesn't exist, otherwise returns the specified item
	 */
	public function previous_item() {
		return Lilina_Items::get_instance()->previous();
	}

	/**
	 * Reset the item index iterator
	 *
	 * Resets LilinaItems::$offset to 0
	 *
	 * @since 1.0
	 */
	public function reset_iterator() {
	}

	/**
	 * reset_iterator() - {@internal Short Description Missing}}
	 *
	 * {@internal Long Description Missing}}
	 */
	public function has_items() {
		return Lilina_Items::get_instance()->getIterator()->valid();
	}
	
	/**
	 * Check whether the current item has an enclosure or not
	 *
	 * Checks to make sure an item has an enclosure and that that enclosure
	 * has a link to use.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function has_enclosure() {
		return !!Lilina_Items::get_instance()->current()->has_enclosure();
	}
	
	/**
	 * Return the enclosure for the current item
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the enclosure
	 */
	public function get_enclosure() {
		return Lilina_Items::get_instance()->current()->enclosure;
	}

	/**
	 * Return the ID for the current item
	 *
	 * @since 1.0
	 *
	 * @return string MD5 hash
	 */
	public function get_id() {
		return Lilina_Items::get_instance()->current()->hash;
	}

	/**
	 *
	 */
	public function filter() {
	}

	/**
	 * Check the current item against the cached items
	 *
	 * Checks the item against the cached database. If the item does not
	 * exist, calls insert_item(). If the item is out-of-date, calls
	 * update_item().
	 *
	 * @since 1.0
	 *
	 * @param stdClass $item Item to check
	 */
	public function check($item) {
		return Lilina_Items::get_instance()->check($item);
	}

	/**
	 * Cache items
	 *
	 * Stores current items back into cache.
	 *
	 * @since 1.0
	 */
	public function save_cache() {
	}
}