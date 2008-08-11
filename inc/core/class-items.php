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
	var $simplepie;
	
	/**
	 * @var array|string
	 */
	var $feeds;
	
	/**
	 * Our items array, obtained from $simplepie->get_items()
	 * @var array
	 */
	var $simplepie_items;
	
	/**
	 * @access protected
	 * @var int
	 */
	var $offset = 0;
	
	/**
	 * @var SimplePie_Item
	 */
	var $current_item;
	
	/**
	 * @var SimplePie
	 *
	 */
	var $current_feed;
	
	/**
	 * Store metadata for the current item
	 *
	 * Erased to a blank array on get_item()
	 * @var array
	 */
	var $current_metadata = array();
	
	/**
	 * Store metadata for all items
	 *
	 * Only contains metadata from items which have already been processed
	 * through the loop
	 * @var array
	 */
	var $all_metadata = array();
	
	/**
	 * LilinaItems() - Initialiser for the class
	 *
	 * Sets our used properties with user input
	 * @param SimplePie
	 */
	function LilinaItems($sp = null) {
		if($sp !== null) {
			$this->simplepie = $sp;
			/** Free up memory just in case */
			unset($sp);
			$this->init();
		}
	}
	
	/**
	 * init() - Initialize our class and load the items in
	 *
	 * {@internal Long Description Missing}}
	 */
	function init() {
		if(is_null($this->simplepie))
			$this->load();

		$sp = &$this->simplepie;
		$this->simplepie_items = $sp->get_items();
	}
	
	/**
	 * load() - Load $this->feeds into a new SimplePie object
	 *
	 * {@internal Long Description Missing}}
	 * @todo Document
	 */
	function load() {
		global $lilina;

		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');

		$feed = new SimplePie();
		$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; '.get_option('baseurl'));
		$feed->set_stupidly_fast(true);
		$feed->set_cache_location(LILINA_PATH . '/cache');
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
	 * get_items() - {@internal Short Description Missing}}
	 *
	 * {@internal Long Description Missing}}
	 * @todo Document
	 */
	function get_items() {
		return $this->simplepie_items;
	}
	
	/**
	 * get_item() - Get the current item
	 *
	 * Return the current item
	 */
	function get_item() {
		$this->all_metadata[$this->offset] = $this->current_metadata;
		$this->current_metadata = array();

		if( !isset($this->simplepie_items[ $this->offset ]) )
			return false;

		$item =$this->current_item = $this->simplepie_items[$this->offset];
		$this->current_feed = $item->get_feed();
		$this->offset++;
		return $this->current_item;
	}
	
	/**
	 * reset_iterator() - {@internal Short Description Missing}}
	 *
	 * {@internal Long Description Missing}}
	 */
	function reset_iterator() {
		$this->offset = 0;
	}
	
	function has_items() {
		return isset($this->simplepie_items[ $this->offset ]);
	}
	
	/**
	 * has_enclosure() - Whether an item has an enclosure or not
	 *
	 * Checks to make sure an item has an enclosure and that that enclosure
	 * has a link to use. Caches in $this->current_metadata
	 * @return bool
	 */
	function has_enclosure() {
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
	 * get_enclosure() - Get the enclosure for the current item
	 */
	function get_enclosure() {
		has_enclosure();
		
		return $this->current_metadata['enclosure_link'];
	}
	
	/**
	 * get_favicon() - Get the favicon for the current item's feed
	 */
	function get_favicon() {
		$feed = $this->current_feed;

		if(!$return = $feed->get_favicon())
			$return = get_option('baseurl') . 'lilina-favicon.php?i=default';

		$this->current_metadata['favicon'] = $return;

		return apply_filters( 'the_feed_favicon', $return );
		
	}
	
	/**
	 * get_feed_id() - Get the ID for the current item's feed
	 */
	function get_feed_id($id = -1) {
		if($id >= 0) {
			$item = $this->get_item( $id );
			$current_feed = $item->get_feed();
		}
		else
			$current_feed = $this->current_feed;
		return apply_filters( 'get_feed_id', md5($current_feed->get_link() . $current_feed->get_title()) );
	}
}