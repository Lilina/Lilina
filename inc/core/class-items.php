<?php
/**
 * The Lilina items class
 * @package Lilina
 * @subpackage Classes
 */
class Items {
	/**
	 * SimplePie object
	 * @var SimplePie
	 */
	protected $simplepie;

	/**
	 * @var array|string
	 */
	protected $feeds;

	/**
	 * Items array, obtained from $simplepie->get_items()
	 * @var array
	 */
	protected $simplepie_items;

	/**
	 * @var SimplePie_Item
	 */
	protected $current_item;

	/**
	 * @var string
	 */
	protected $current_feed;

	/**
	 * Stores item data in an stdClass object, independant of SimplePie
	 * @var array
	 */
	public $item = array();

	/**
	 * Stores previous item data in an stdClass object, independant of SimplePie
	 * @var array
	 */
	public $previous_item = array();

	/**
	 * List of all items
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Object constructor
	 *
	 * Sets our used properties with user input
	 * @param SimplePie
	 */
	public function __construct($sp = null) {
		if($sp !== null) {
			$this->simplepie = $sp;
			/** Free up memory just in case */
			unset($sp);
			$this->init();
		}
	}

	/**
	 * Initialize our class and load the items in
	 *
	 * {@internal Long Description Missing}}
	 */
	public function init() {
		if(is_null($this->simplepie))
			$this->load();

		$this->simplepie_items = &$this->simplepie->get_items();

		/** Run through each item at least once */
		foreach($this->simplepie_items as $item) {
			$new_item = $this->normalise($item);
			$this->items[ $new_item->hash ] = $new_item;
		}

		uasort($this->items, array($this, 'sort_items'));

		$this->simplepie->__destruct();
		unset($this->simplepie);
		unset($this->simplepie_items);

		return $this->items;
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
	 * Set the feeds property for Items::load()
	 *
	 * @param array|string $feeds Single-level array of feed URLs or single URL as a string
	 */
	public function set_feeds($feeds) {
		$this->feeds = $feeds;
	}

	/**
	 * Normalise a SimplePie_Item into a stdClass
	 *
	 * Converts a SimplePie_Item into a new-style stdClass
	 */
	protected function normalise($item) {
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
		return apply_filters('item_data', $new_item);
	}

	/**
	 * Create a new SimplePie object
	 *
	 * Creates an instance of SimplePie with Items::$feeds
	 *
	 * @since 1.0
	 */
	public function load() {
		global $lilina;

		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');

		$feed = new SimplePie();
		$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; ('.get_option('baseurl').'; http://getlilina.org/; Allow Like Gecko) SimplePie/' . SIMPLEPIE_BUILD);
		$feed->set_stupidly_fast(true);
		$feed->set_cache_location(get_option('cachedir'));
		$feed->set_favicon_handler(get_option('baseurl') . '/lilina-favicon.php');
		$feed = apply_filters('simplepie-config', $feed);

		$feed->set_feed_url($this->feeds);
		$feed->init();

		/** We need this so we have something to work with. */
		$feed->get_items();

		if(!isset($feed->data['ordered_items'])) {
			$feed->data['ordered_items'] = $feed->data['items'];
		}

		/** Let's force sorting */
		usort($feed->data['ordered_items'], array(&$feed, 'sort_items'));
		usort($feed->data['items'], array(&$feed, 'sort_items'));

		$this->simplepie = $feed;

		/** Free up memory just in case */
		unset($feed);
	}
	
	/**
	 * Return all items
	 *
	 * @since 1.0
	 *
	 * @return array All items from the feed
	 */
	public function get_items() {
		return $this->simplepie_items;
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
		if( !isset($this->items[ $offset ]) )
			return false;

		$item = $this->items[$offset];
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
}