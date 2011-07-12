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
	 * Sorting of items
	 *
	 * Decides which item sorting to use. Defaults to 'time' (sorting reverse
	 * chronologically)
	 * @var string
	 */
	protected $sort = 'time';
	
	/**
	 * Grouping of items
	 *
	 * Decides what to group items by. For example, 'feed' will group by feed
	 * ID.
	 *
	 * Note: Regardless of grouping, Items::$items is always a single-level
	 * associative array of items. Grouping simply orders the items by the
	 * group first, then sorts within each group as per Items::$sort.
	 * @var string
	 */
	protected $group = '';

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
		return $b->timestamp - $a->timestamp;
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
		if(is_array($this->items)) {
			switch($this->sort) {
				case 'time':
				default:
					uasort($this->items, array('Items', 'sort_items'));
					break;
					
			}
			switch($this->group) {
				case 'feed':
					$this->group_by_feed();
					break;
				default:
					// No grouping by default
					break;
			}
		}
	}

	protected function group_by_feed(){
		// Group by feed_id
		foreach($this->items as $key => $value) {
			$grouped[$value->feed_id][$key] = $value;
		}
		// Flattern
		foreach($grouped as $group_items) {
			foreach($group_items as $key => $value) {
				$items[$key] = $value;
			}
		}
		$this->items = $items;
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
		return $this->items;
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
		if (isset($this->conditions['after'])) {
			$ids = array_keys($this->items);
			$pos = array_search($this->conditions['after'], $keys);
			if ($pos !== false)
				$this->items = array_slice($array, $pos + 1);
		}
		if (isset($this->conditions['until'])) {
			$ids = array_keys($this->items);
			$pos = array_search($this->conditions['until'], $keys);
			if ($pos !== false)
				$this->items = array_slice($array, 0, $pos);
		}

		$this->items = array_filter($this->items, array($this, 'filter_callback'));

		if (isset($this->conditions['start']) || isset($this->conditions['limit'])) {
			if (!empty($this->conditions['start'])) {
				$start = $this->conditions['start'];
			}
			else {
				$start = 0;
			}

			if (!empty($this->conditions['limit'])) {
				$limit = $this->conditions['limit'];
			}
			else {
				$limit = null;
			}

			$this->items = array_slice($this->items, $start, $limit);
		}
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
		if($cached_item->timestamp !== $item->timestamp) {
			$this->update_item($item);
			do_action('update_item', $item, $cached_item);
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