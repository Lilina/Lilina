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
	protected $adapter;
	protected $data = array();
	protected $iterator;

	/**
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 */
	public function __construct() {
		$this->adapter = Lilina_DB::get_adapter(get_option('dboptions', LILINA_PATH . '/content/system/data'), get_option('dbdriver', 'Lilina_DB_Adapter_File'));
	}

	public static function get_instance() {
		static $instance = false;
		if ($instance === false) {
			$instance = new Lilina_Items();
		}

		return $instance;
	}

	public function query($options = array()) {
		$defaults = array(
			'table' => 'items',
			'orderby' => array(
				'key' => 'timestamp',
				'direction' => 'desc',
				'compare' => 'int'
			),
			'limit' => 10,
			'fetchas' => 'Lilina_Item',
			'reindex' => 'hash'
		);
		$options = array_merge($defaults, $options);

		$this->data = $this->adapter->retrieve($options);
		unset($this->iterator);
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

	public function each($callback) {
		$this->getIterator()->each($callback);
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
	 * Get the total number of items
	 *
	 * @return int
	 */
	public function total_count() {
		return $this->adapter->count(array(
			'table' => 'items'
		));
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
		if (!isset($this->data[$id])) {
			$items = $this->adapter->retrieve(array(
				'table' => 'items',
				'limit' => 1,
				'where' => array(
					array('hash', '==', $id)
				),
				'fetchas' => 'Lilina_Item',
			));
			if (!empty($items)) {
				return array_shift($items);
			}

			return false;
		}

		return $this->data[$id];
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
		$items = $this->adapter->retrieve(array(
			'table' => 'items',
			'limit' => 1,
			'where' => array(
				array('hash', '==', $item->hash)
			),
			'fetchas' => 'Lilina_Item',
		));

		if (count($items) !== 1) {
			$this->insert($item);
			do_action('insert_item', $item);
			return true;
		}

		$existing = array_shift($items);
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
		$this->adapter->insert($item, array(
			'table' => 'items',
			'primary' => 'hash'
		));
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
		$this->adapter->update($item, array(
			'table' => 'items',
			'where' => array(
				array(
					'hash', '==', $item->hash
				)
			)
		));
	}
}