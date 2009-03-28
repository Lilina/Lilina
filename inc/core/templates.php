<?php
/**
 * Templating functions
 *
 * Will supercede skin.php. Maps standard functions to FeedItems calls
 * @package Lilina
 * @subpackage Classes
 */

/**
 * {@internal Missing Description}
 * @todo Document
 */
function get_item() {
	$items = FeedItems::get_instance();
	return $items->get_item();
}