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
class ItemCache extends Items {
	/**
	 * Singleton instance of self
	 *
	 * Holds a singleton instance of self, for use by get_instance()
	 *
	 * @access protected
	 */
	protected static $instance;

	/**
	 * DataHandler instance
	 *
	 * Saves time, money and memory if we keep the same DataHandler
	 *
	 * @access protected
	 */
	protected $data;

	/**
	 * Items loaded from the cache
	 *
	 * @access protected
	 */
	protected $cached_items = array();

	public function __construct($sp = null) {
		$this->data = new DataHandler();
		$current = $this->data->load('items.data');
		if($current !== null)
			$this->cached_items = $this->items = unserialize($current);

		parent::__construct($sp);
	}

	public function get_instance($sp = null) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new ItemCache($sp);
		}
		return self::$instance;
	}

	/**
	 * Stop object cloning
	 *
	 * As this is a singleton, we don't want to be able to clone this
	 * @access private
	 */
	private function __clone() {}

	/**
	 * Initialize needed variables
	 *
	 * {@internal Long Description Missing}}
	 */
	public function init() {
		if(is_null($this->simplepie))
			$this->load();

		$this->simplepie_items = &$this->simplepie->get_items();

		$updated = false;

		foreach($this->simplepie_items as $item) {
			$new_item = $this->normalise($item);
			$this->items[ $new_item->hash ] = $new_item;
			//$updated = $updated || $this->check_item($new_item);
			if($this->check_item($new_item)) {
				$updated = true;
				echo '<!-- updated item! -->';
			}
		}

		uasort($this->cached_items, array($this, 'sort_items'));
		uasort($this->items, array($this, 'sort_items'));

		if($updated)
			$this->save_cache();

		$this->simplepie->__destruct();
		unset($this->simplepie);
		unset($this->simplepie_items);
		unset($this->cached_items);

		return $this->items;
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
	protected function check_item($item) {
		if(!isset( $this->cached_items[ $item->hash ] )) {
			$this->update_item($item);
			do_action('insert_item', $item);
			return true;
		}

		$cached_item = $this->cached_items[ $item->hash ];
		if($cached_item->timestamp !== $item->timestamp || $cached_item->hash !== $item->hash) {
			$old_item = $this->cached_items[ $item->hash ];
			$this->update_item($item);
			do_action('update_item', $item, $old_item);
			return true;
		}

		return false;
	}

	/**
	 * Insert the current item into the cache database
	 *
	 * Inserts the item into the database with the information from the
	 * current item.
	 *
	 * @since 1.0
	 * @deprecated Use {@see update_item()} instead.
	 *
	 * @param stdClass $item Item to insert into database
	 */
	protected function insert_item($item) {
		$this->update_item($item);
	}

	/**
	 * Update the cached version of the current item
	 *
	 * Updates the item into the database with the information from the
	 * current item.
	 *
	 * @since 1.0
	 *
	 * @param stdClass $item Item to update
	 */
	protected function update_item($item) {
		$this->cached_items[ $item->hash ] = $item;
	}

	/**
	 * Cache items
	 *
	 * Stores current items back into cache.
	 *
	 * @since 1.0
	 */
	protected function save_cache() {
		$this->data->save('items.data', serialize($this->cached_items));
	}
}