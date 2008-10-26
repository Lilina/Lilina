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
class ItemCache extends LilinaItems {
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
			$this->cached_items = unserialize($current);

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

		$sp = &$this->simplepie;
		$this->simplepie_items = $sp->get_items();

		$this->items = $this->cached_items;
		/** Run through each item at least once */
		while($this->has_items()) {
			$this->current_item();
			$this->check_item();
		}
		$this->reset_iterator();
		$this->save_cache();
	}

	/**
	 * Check the current item against the cached items
	 *
	 * Checks the item against the cached database. If the item does not
	 * exist, calls insert_item(). If the item is out-of-date, calls
	 * update_item().
	 *
	 * @since 1.0
	 */
	protected function check_item() {
		if(!isset( $this->cached_items[ $this->item->hash ] )) {
			$this->update_item();
			do_action('insert_item', $this->item);
			return;
		}

		$cached_item = $this->cached_items[ $this->get_id() ];
		if($cached_item->timestamp !== $this->item->timestamp || $cached_item->hash !== $this->item->hash) {
			$old_item = $this->cached_items[ $this->get_id() ];
			$this->update_item();
			do_action('update_item', $this->item, $old_item);
		}
	}

	/**
	 * Insert the current item into the cache database
	 *
	 * Inserts the item into the database with the information from the
	 * current item.
	 *
	 * @since 1.0
	 * @deprecated Use {@see update_item()} instead.
	 */
	protected function insert_item() {
		$this->update_item();
	}

	/**
	 * Update the cached version of the current item
	 *
	 * Updates the item into the database with the information from the
	 * current item.
	 *
	 * @since 1.0
	 */
	protected function update_item() {
		$this->cached_items[ $this->get_id() ] = $this->item;
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