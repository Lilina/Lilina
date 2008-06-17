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
	 * @var int
	 */
	var $offset = 0;

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
	 *
	 */
	function init() {
		if(is_null($this->simplepie))
			$this->load();

		$sp = &$this->simplepie;
		$this->simplepie_items = $sp->get_items();
	}

	/**
	 * load() - {@internal Short Description Missing}}
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
	 * get_item() - {@internal Short Description Missing}}
	 * {@internal Long Description Missing}}
	 */
	function get_item() {
		if( !isset($this->simplepie_items[ $this->offset ]) )
			return false;
		$item = $this->simplepie_items[$this->offset];
		$this->offset++;
		return $item;
	}

	/**
	 * reset_iterator() - {@internal Short Description Missing}}
	 * {@internal Long Description Missing}}
	 */
	function reset_iterator() {
		$this->offset = 0;
	}
	
	function has_items() {
		return isset($this->simplepie_items[ $this->offset ]);
	}
}