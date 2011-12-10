<?php
/**
 * The Lilina items class
 * @package Lilina
 * @subpackage Classes
 */

/** 
 * Item manager
 *
 */
class Lilina_Items implements Countable, IteratorAggregate {
	protected $data = array();
	protected $iterator;

	/**
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 */
	protected function __construct() {
		$handler = new DataHandler();
		$current = $handler->load('items.data');
		if ($current !== null) {
			$this->data = json_decode($current);
			if ($this->data === $current || $this->data === null) {
				$this->data = unserialize($current);
			}

			$this->data = (array) $this->data;
		}

		foreach ($this->data as $key => $value) {
			$this->data[$key] = Lilina_Item::from_obj($value);
		}
		$this->sort_all();
	}

	/**
	 * Get the current item
	 *
	 * Convienience method, if you're trying to loop through the
	 * items, ensure you use the Iterator
	 * @return Lilina_Item
	 */
	public function &current() {
		return $this->getIterator()->current();
	}

	/**
	 * Get the previous item
	 *
	 * @since 1.0
	 *
	 * @return bool|Lilina_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function &previous() {
		$previous = $this->getIterator()->previous();
		$this->getIterator()->next();

		return $previous;
	}

	/**
	 * Get the items iterator
	 *
	 * @return Lilina_Items_Iterator
	 */
	public function getIterator() {
		if (!empty($this->iterator)) {
			return $this->iterator;
		}

		$this->iterator = new Lilina_Items_Iterator($this->data);
		return $this->iterator;
	}

	/**
	 * Get the number of items
	 *
	 * @return int
	 */
	public function count() {
		return count($this->data);
	}
	
	/**
	 * Get an item
	 *
	 * Get an item by ID
	 *
	 * @param int $hash Item index to retrieve
	 * @return bool|Lilina_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function get($id) {
		if (!isset($this->items[$id])) {
			return false;
		}

		return $this->items[$id];
	}

	public function get_items() {
		return $this->data;
	}

	/**
	 * Check the current item against the cached items
	 *
	 * Checks the item against the cached database. If the item does not
	 * exist, calls insert_item(). If the item is out-of-date, calls
	 * update_item().
	 *
	 * @param Lilina_Item $item Item to check
	 * @return bool
	 */
	public function check($item) {
		if (!isset($this->data[$item->hash])) {
			$this->update($item);
			do_action('insert_item', $item);
			return true;
		}

		$existing = $this->data[$item->hash];
		if ($existing->timestamp !== $item->timestamp) {
			$this->update($item);
			do_action('update_item', $item, $existing);
			return true;
		}

		return false;
	}

	/**
	 * Update the cached version of the current item
	 *
	 * Updates the item into the database with the information from the
	 * current item.
	 *
	 * @param Lilina_Item $item Item to update
	 */
	protected function insert($item) {
		$this->data[$item->hash] = $item;
	}

	/**
	 * Update the cached version of the current item
	 *
	 * Updates the item into the database with the information from the
	 * current item.
	 *
	 * @param Lilina_Item $item Item to update
	 */
	protected function update($item) {
		$this->data[$item->hash] = $item;
	}

	/**
	 * Save items
	 *
	 * Stores current items back into cache.
	 *
	 * @since 1.0
	 */
	public function save() {
		$this->data = apply_filters('save_items', $this->data, $this);
		$handler = new DataHandler();
		$handler->save('items.data', json_encode($this->data));
	}

	/**
	 * usort callback for items
	 *
	 * @param stdObject $a First item
	 * @param stdObject $b Second item
	 * @param bool
	 */
	public static function sort_items($a, $b) {
		return $b->timestamp - $a->timestamp;
	}
}