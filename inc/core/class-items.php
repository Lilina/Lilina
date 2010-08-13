<?php
/**
 * The Lilina items class
 * @package Lilina
 * @subpackage Classes
 */
class Items {
	/**
	 * @var array|string
	 */
	protected $feeds;

	/**
	 * @var stdObject
	 */
	protected $current_item;

	/**
	 * @var string
	 */
	protected $current_feed;

	/**
	 * Stores item data in an stdClass object
	 * @var array
	 */
	public $item = array();

	/**
	 * Stores previous item data in an stdClass object
	 * @var array
	 */
	public $previous_item = array();

	/**
	 * Authoritative item database
	 *
	 * Any changes to this should be saved back to a file
	 * @var array
	 */
	protected $cached_items = array();

	/**
	 * Local copy of item database to work with
	 * @var array
	 */
	public $items = array();

	/**
	 * Conditions used when filtering items
	 * @var array
	 */
	protected $conditions = array();

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
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 */
	protected function __construct() {
		$this->data = new DataHandler();
		$current = $this->data->load('items.data');
		if($current !== null) {
			// Workaround for old, serialized PHP database
			if(($this->items = json_decode($current)) === $current) {
				$this->items = unserialize($current);
			}
			$this->items = (array) $this->items;
			$this->cached_items = $this->items;
		}
	}

	public function get_instance() {
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
		$this->conditions = array_merge($this->conditions, $conditions);
	}



	/**
	 * Load items and resort
	 */
	public function init() {
		$this->items = $this->cached_items;
		$this->sort_all();
	}

	public function reset() {
		$this->items = $this->cached_items;
	}

	/**
	 * usort callback for items
	 *
	 * @param stdObject $a First item
	 * @param stdObject $b Second item
	 * @param bool
	 */
	public function sort_items($a, $b) {
		return $a->timestamp <= $b->timestamp;
	}

	/**
	 * Sort all items
	 *
	 * This bypasses SimplePie's sorting (and lack thereof for items without
	 * timestamps).
	 */
	public function sort_all() {
		if(is_array($this->cached_items))
			uasort($this->cached_items, array('Items', 'sort_items'));
		if(is_array($this->items))
			uasort($this->items, array('Items', 'sort_items'));
	}

	/**
	 * Retreive the items without fetching new ones
	 *
	 * Depending on whether {@link init()} is called or not, this may include
	 * new items.
	 * @return array List of items
	 */
	public function retrieve() {
		return $this->items;
	}

	/**
	 * Normalise a SimplePie_Item into a stdClass
	 *
	 * Converts a SimplePie_Item into a new-style stdClass
	 */
	public function normalise($item, $feed_id = '') {
		if($enclosure = $item->get_enclosure()) {
			$enclosure = $enclosure->get_link();
		}
		else {
			// SimplePie_Item::get_enclosure() returns null, so we need to change this to false
			$enclosure = false;
		}
		if($author = $item->get_author()) {
			$author = array(
				'name' => $item->get_author()->get_name(),
				'url' => $item->get_author()->get_link()
			);
		}
		else {
			$author = array(
				'name' => false,
				'url' => false
			);
		}
		$new_item = (object) array(
			'hash'      => $item->get_id(true),
			'timestamp' => $item->get_date('U'),
			'title'     => $item->get_title(),
			'content'   => $item->get_content(),
			'summary'   => $item->get_description(),
			'permalink' => $item->get_permalink(),
			'metadata'  => (object) array(
				'enclosure' => $enclosure
			),
			'author'    => (object) $author,
			'feed'      => $item->get_feed()->get_link()
		);
		if(!empty($feed_id))
			$new_item->feed_id = $feed_id;
		return apply_filters('item_data', $new_item);
	}
	
	/**
	 * Return all items
	 *
	 * @since 1.0
	 *
	 * @return array All items from the feed
	 */
	public function get_items() {
		return $this->items;
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
		if( !isset($this->items[ $hash ]) )
			return false;

		$item = $this->items[$hash];
		return $item;
	}

	/**
	 * Returns the current item
	 *
	 * @since 1.0
	 *
	 * @return bool|stdClass False if item doesn't exist, otherwise returns the specified item
	 */
	public function current_item() {
		$this->previous_item = $this->current_item;
		$this->current_item = '';

		$item = each($this->items);
		$item = $item['value'];
		if(!$item)
			return false;

		$this->current_item = $item;
		$this->current_feed = $item->feed;

		return $item;
	}

	/**
	 * Return the previous item
	 *
	 * @since 1.0
	 *
	 * @return bool|stdClass False if item doesn't exist, otherwise returns the specified item
	 */
	public function previous_item() {
		if(empty($this->previous_item))
			return false;

		return $this->previous_item;
	}

	/**
	 * Reset the item index iterator
	 *
	 * Resets LilinaItems::$offset to 0
	 *
	 * @since 1.0
	 */
	public function reset_iterator() {
		reset($this->items);
	}

	/**
	 * reset_iterator() - {@internal Short Description Missing}}
	 *
	 * {@internal Long Description Missing}}
	 */
	public function has_items() {
		return !!current($this->items);
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
		return !!$this->current_item->metadata->enclosure;
	}
	
	/**
	 * Return the enclosure for the current item
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the enclosure
	 */
	public function get_enclosure() {
		return $this->current_item->metadata->enclosure;
	}

	/**
	 * Return the ID for the current item
	 *
	 * @since 1.0
	 *
	 * @return string MD5 hash
	 */
	public function get_id() {
		return $this->current_item->hash;
	}

	/**
	 *
	 */
	public function filter() {
		$this->items = array_filter($this->items, array($this, 'filter_callback'));
	}

	protected function filter_callback($item) {
		foreach($this->conditions as $key => $condition) {
			switch($key) {
				case 'time':
					if($item->timestamp < $condition) {
						return false;
					}
					break;
				case 'feed':
					if(empty($item->feed_id) || $item->feed_id != $condition) {
						return false;
					}
					break;
			}
		}

		return true;
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
	public function check_item($item) {
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
		if(isset($this->cached_items[ $item->hash ]))
			do_action('itemcache-update', $item);
		else
			do_action('itemcache-insert', $item);
		
		$this->items[ $item->hash ] = $item;
		$this->cached_items[ $item->hash ] = $item;
	}

	/**
	 * Cache items
	 *
	 * Stores current items back into cache.
	 *
	 * @since 1.0
	 */
	public function save_cache() {
		$this->cached_items = apply_filters('save_items', $this->cached_items, $this);
		$this->data->save('items.data', json_encode($this->cached_items));
	}
}