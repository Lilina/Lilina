<?php
/**
 * Feed management class
 *
 * @package Lilina
 * @subpackage Administration
 */

class Lilina_Feeds extends Lilina_Iterable implements Countable {
	protected $data = array();
	protected $adapter;

	/**
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 */
	public function __construct() {
		$this->adapter = apply_filters('lilina_feeds_db', false);
		if ($this->adapter === false) {
			$this->adapter = Lilina_DB::get_adapter(
				array(
					get_option('dboptions', LILINA_PATH . '/content/system/config'),
					'json'
				),
				get_option('dbdriver', 'Lilina_DB_Adapter_File')
			);
		}
	}

	public static function &get_instance() {
		static $instance = false;
		if ($instance === false) {
			$instance = new Lilina_Feeds();
		}

		return $instance;
	}

	public function query($options = array()) {
		$defaults = array(
			'table' => 'feeds',
			'orderby' => array(
				'key' => 'name',
				'direction' => 'asc',
				'compare' => 'strcase'
			),
			'fetchas' => 'Lilina_Feed',
			'reindex' => 'id',
			// intentionally no limit set
		);
		$options = array_merge($defaults, $options);

		$this->data = $this->adapter->retrieve($options);
		unset($this->iterator);
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
			'table' => 'feeds'
		));
	}
	
	/**
	 * Get an item
	 *
	 * Get an item by ID
	 *
	 * @param int $id Feed index to retrieve
	 * @return bool|Lilina_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function get($id) {
		if (!isset($this->data[$id])) {
			$feeds = $this->adapter->retrieve(array(
				'table' => 'feeds',
				'limit' => 1,
				'where' => array(
					array('id', '==', $id)
				),
				'fetchas' => 'Lilina_Feed',
			));
			if (!empty($feeds)) {
				return array_shift($feeds);
			}

			return null;
		}

		return $this->data[$id];
	}

	public function get_items() {
		return $this->data;
	}

	/**
	 * Update the cached version of the current item
	 *
	 * Updates the item into the database with the information from the
	 * current item.
	 *
	 * @param Lilina_Feed $feed Feed to update
	 */
	public function insert($feed) {
		$this->adapter->insert($feed, array(
			'table' => 'feeds',
			'primary' => 'id'
		));
		$feed->update();
	}

	/**
	 * Update the cached version of the current feed
	 *
	 * Updates the feed into the database with the information from the
	 * current item.
	 *
	 * @param Lilina_Feed $feed Feed to update
	 */
	public function update($feed) {
		$this->adapter->update($feed, array(
			'table' => 'feeds',
			'where' => array(
				array(
					'id', '==', $feed->id
				)
			)
		));
	}

	/**
	 * Delete a feed from the database
	 *
	 * @param Lilina_Feed $feed Feed to delete
	 */
	public function delete($feed) {
		$this->adapter->delete(array(
			'table' => 'feeds',
			'where' => array(
				array(
					'id', '==', $feed->id
				)
			)
		));

		Lilina_Items::get_instance()->delete_bulk(array(
			'where' => array(
				array('feed_id', '==', $feed->id)
			)
		));

		// This should be considered legacy support, as this should
		// be replaced with a database entry instead
		$cache = new DataHandler(get_option('cachedir'));
		if($cache->load($feed->id . '.ico') !== null) {
			$cache->delete($feed->id . '.ico');
		}
	}
}