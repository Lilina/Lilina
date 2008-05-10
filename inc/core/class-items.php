<?php
/**
 * The Lilina items class
 * @package Lilina
 * @subpackage Classes
 */
class Lilina_Items {
	/**
	 * Our SimplePie object to work with
	 * @var SimplePie
	 */
	var $simplepie;

	/**
	 * Our items array, obtained from $simplepie->get_items()
	 * @var array
	 */
	var $simplepie_items;

	/**
	 *
	 */
	var $offset;

	/**
	 * Lilina_Items() - Initialiser for the class
	 *
	 * Sets our used properties with user input
	 * @param SimplePie
	 */
	function Lilina_Items($sp = null) {
		if(!$sp)
			$sp = lilina_load_feeds(get_option('files', 'feeds'));
		$this->simplepie = $sp;
		$this->simplepie_items = apply_filters('simplepie_items', $sp->get_items());
		$this->offset = 0;
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

		foreach($input['feeds'] as $the_feed)
			$feed_list[] = $the_feed['feed'];

		$feed->set_feed_url($feed_list);
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
}