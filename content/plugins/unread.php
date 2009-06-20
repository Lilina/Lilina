<?php
/*
Plugin Name: Unread Items
Plugin URI: http://codex.getlilina.org/Plugins/Included/Unread
Description: A
Author: Ryan McCue
Version: 0.0
Min Version: 1.0
Author URI: http://cubegames.net
License: GPL
*/

class UnreadItems {
	function __construct() {
	
	}
	
	function addprops($item) {
		$item->status = 'unread';
		return $item;
	}
}


$unread = new UnreadItems();
add_filter('item_data_precache', array($unread, 'addprops'));