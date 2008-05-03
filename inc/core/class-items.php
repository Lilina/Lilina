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