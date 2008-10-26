<?php
/**
 * The Lilina items class
 * @package Lilina
 * @subpackage Classes
 */
class LilinaItems {
	/**
	 * Our SimplePie object to work with
	 * @var SimplePie
	 */
	protected $simplepie;

	/**
	 * @var array|string
	 */
	public $feeds;

	/**
	 * Our items array, obtained from $simplepie->get_items()
	 * @var array
	 */
	protected $simplepie_items;

	/**
	 * @access protected
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @var SimplePie_Item
	 */
	protected $current_item;

	/**
	 * @var SimplePie
	 *
	 */
	protected $current_feed;

	/**
	 * Store data outside of SimplePie_Item
	 *
	 * Stores item data in an stdClass object, independant of SimplePie
	 * @var array
	 */
	public $item = array();

	/**
	 * List of all items
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Store metadata for the current item
	 *
	 * Erased to a blank array on get_item()
	 * @var array
	 */
	public $current_metadata = array();

	/**
	 * Store metadata for all items
	 *
	 * Only contains metadata from items which have already been processed
	 * through the loop
	 * @var array
	 */
	public $all_metadata = array();

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

		$sp = &$this->simplepie;
		$this->simplepie_items = $sp->get_items();
		
		/** Run through each item at least once */
		while($this->has_items()) {
			$this->current_item();
		}
		$this->reset_iterator();
	}
	
	/**
	 * Create a new SimplePie object
	 *
	 * Creates an instance of SimplePie with LilinaItems::$feeds
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
	 * @param int $offset Item index to retrieve
	 * @return bool|SimplePie_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function get_item($offset) {
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
	 * @return bool|SimplePie_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function current_item() {
		$this->all_metadata[$this->offset] = $this->current_metadata;
		$this->current_metadata = array();
		$this->item = '';

		$item = $this->get_item($this->offset);
		if(!$item)
			return false;

		$this->current_item = $this->items[$this->offset];
		$this->current_feed = $this->current_item->get_feed();

		/** Initialise metadata */
		$this->has_enclosure();
		$this->get_favicon();

		$this->item = (object) array(
			'hash'      => $this->current_item->get_id(true),
			'timestamp' => $this->current_item->get_date('U'),
			'title'     => $this->current_item->get_title(),
			'content'   => $this->current_item->get_content(),
			'summary'   => $this->current_item->get_description(),
			'permalink' => $this->current_item->get_permalink(),
			'metadata'  => (object) array(
				'enclosure' => $this->get_enclosure()
			),
			'author'    => (object) array(
				'name' => $this->current_item->get_author()->get_name(),
				'url' => $this->current_item->get_author()->get_link()
			),
			'feed'      => $this->get_feed_id()
		);
		$this->item = apply_filters('item_data', $this->item);
		$this->items[ $this->current_item->get_id(true) ] = &$this->item;

		$this->offset++;
		return $item;
	}

	/**
	 * Reset the item index iterator
	 *
	 * Resets LilinaItems::$offset to 0
	 *
	 * @since 1.0
	 */
	public function reset_iterator() {
		$this->offset = 0;
	}

	/**
	 * reset_iterator() - {@internal Short Description Missing}}
	 *
	 * {@internal Long Description Missing}}
	 */
	public function has_items() {
		return isset($this->simplepie_items[ $this->offset ]);
	}
	
	/**
	 * Check whether the current item has an enclosure or not
	 *
	 * Checks to make sure an item has an enclosure and that that enclosure
	 * has a link to use. Caches in $this->current_metadata
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function has_enclosure() {
		if(isset($this->item->metadata->enclosure))
			return $this->item->metadata->enclosure;

		if(isset($this->current_metadata['has_enclosure']))
			return $this->current_metadata['has_enclosure'];

		$current = $this->current_item;
		$enclosure = $this->current_metadata['enclosure'] = $current->get_enclosure();

		if(!$enclosure) {
			$this->current_metadata['has_enclosure'] = false;
			return false;
		}

		$this->current_metadata['enclosure_link'] = $enclosure->get_link();
		return $this->current_metadata['has_enclosure'] = !empty($this->current_metadata['enclosure_link']);		
	}
	
	/**
	 * Return the enclosure for the current item
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the enclosure
	 */
	public function get_enclosure() {
		if(isset($this->item->metadata->enclosure))
			return $this->item->metadata->enclosure;

		if(!$this->has_enclosure())
			return false;

		return $this->current_metadata['enclosure_link'];
	}
	
	/**
	 * Return the favicon for the current feed
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the favicon
	 */
	public function get_favicon() {
		if(!$return = $this->current_feed->get_favicon())
			$return = get_option('baseurl') . 'lilina-favicon.php?i=default';

		return $this->current_metadata['favicon'] = $return;
	}

	/**
	 * Return the ID for the current item
	 *
	 * @since 1.0
	 *
	 * @return string MD5 hash
	 */
	public function get_id() {
		if(isset($this->item->hash))
			return $this->item->hash;
		return $this->current_item->get_id(true);
	}
	
	/**
	 * Return the ID for the current feed
	 *
	 * @since 1.0
	 *
	 * @return string MD5 hash
	 */
	public function get_feed_id() {
		return md5($this->current_feed->get_link() . $this->current_feed->get_title());
	}
}