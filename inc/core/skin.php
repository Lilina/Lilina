<?php
/**
 * Templating functions
 * @todo Document this file
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');


/**
 * @todo Document
 */
 //Define all the functions for our skins
function template_sitename($return='echo'){
	global $settings;
	if($return == 'echo') {
		echo $settings['sitename'];
		return true;
	}
	elseif($return == 'var') {
		return $settings['sitename'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

/**
 * @todo Document
 */
function template_siteurl($return=false){
	global $settings;
	if($return == false) {
		echo $settings['baseurl'];
		return true;
	}
	return $settings['baseurl'];
}

/**
 * @todo Document
 */
function template_synd_header($return='echo'){
	global $settings;
	if($settings['output']['rss']){
		$header = '<link rel="alternate" type="application/rss+xml" title="' . _r('RSS Feed') . '" href="rss.php" />';
	}
	if($settings['output']['atom']){
		$header = '<link rel="alternate" type="application/rss+xml" title="' . _r('Atom Feed') . '" href="atom.php" />';
	}
	echo apply_filters('template_synd_header', $header);
}
add_action('template_header', 'template_synd_header');

/**
 * @todo Document
 */
function template_synd_links(){
	global $settings;
	if($settings['output']['rss']){
		$rss = _r('RSS Feed');
		echo _r('RSS'), ': <a href="rss.php"><img src="', template_file_load('feed.png'), '" alt="', $rss, '" title="', $rss, '" /></a> ';
	}
	if($settings['output']['atom']){
		$atom = _r('Atom Feed');
		echo _r('Atom'), ': <a href="atom.php"><img src="', template_file_load('feed.png'), '" alt="', $atom, '" title="', $atom, '" /></a>';
	}
	return true;
}

/**
 * @todo Document
 */
function template_header(){
	global $settings;
	do_action('template_header');
	return true;
}

/**
 * @todo Document
 */
function template_end_errors($return='echo'){
	global $end_errors;
	if($return == 'echo') {
		echo $end_errors;
		return true;
	}
	elseif($return == 'var') {
		return $end_errors;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}


/**
 * @todo Document
 */
function template_footer(){
	do_action('template_footer');
	return true;
}

/**
 * @todo Document
 */
function template_times(){
	global $settings;
	foreach($settings['interface']['times'] as $current_time){
		if(is_int($current_time)){
			echo '<li><a href="index.php?hours='.$current_time.'">'.$current_time . _r('h') . '</a></li>' . "\n";
		}
		else {
			switch($current_time) {
				case 'week':
					echo '<li><a href="index.php?hours=168">' . _r('week') . '</a></li>' . "\n";
					break;
			}
		}
	}
	echo '<li class="last"><a href="index.php?hours=-1"><span>' . _r('all') . '</span></a></li>' . "\n";
}

/**
 * @todo Document
 * @todo Implement items_per_page, feed_include, feed_exclude
 */
function query_setup($override = true) {
	global $data, $item, $list, $item_number, $settings, $showtime, $total_items;
	$defaults = array(
		'show_since' => -1,
		'items_per_page' => 5,
		'max_items' => -1,

		'feed_include' => -1,
		'feed_exclude' => -1,

		'page_num' => -1,
		'offset' => -1,
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	/** Default setup */
	if($show_since < 0) {
		if(!isset($showtime)) {
			if(isset($_REQUEST['hours']) && !empty($_REQUEST['hours'])) {
				if( -1 == $_REQUEST['hours'])
					$showtime = 0;
				else
					$showtime = ((int) $_REQUEST['hours'] * 60 * 60);
			}
			else
				$showtime = ((int) $settings['interface']['times'][0] * 60 * 60);

			$showtime = apply_filters('showtime', $showtime);
		}
		$show_since = $showtime;
	}
	$showtime = time() - $show_since;
	
	if($items_per_page < 0)
		// Do nothing for now

	if($max_items < 0)
		$max_items = $list->get_item_quantity();

	$total_items = $max_items;

	if($feed_include < 0)
		// Do nothing for now

	if($feed_exclude < 0)
		// Do nothing for now
		
	if($page_num > 0)
		$offset += ($items_per_page * $page_num);

	if($offset >= 0)
		$item_number = ($offset - 1);
		
	if(empty($data))
		$data = lilina_load_feeds($settings['files']['feeds']);

	if(!isset($data['feeds']) || count($data['feeds']) === 0)
		return false;

	if(empty($list))
		$list	= lilina_return_items($data);
}

/**
 * Initializes SimplePie and loads the feeds into the global <tt>$list</tt> array.
 *
 * Loads feeds from conf/feeds.data into the global <tt>$list</tt> array if not already done.
 * Then calls <tt>lilina_return_items()</tt> and stores the SimplePie object returned in the
 * global <tt>$list</tt> array if not already done.
 
 * Increments the <tt>$item_number</tt> if the <tt>$increment</tt> parameter is true, or
 * initializes the <tt>$item_number</tt> if not already done. Then sets the <tt>$showtime</tt>
 * variable if not already done.
 *
 * Checks if the current item's date is less than the <tt>$showtime</tt> variable and if so,
 * returns false to stop processing items. If not, checks if the <tt>$item_number</tt> is
 * less than the total number of items and if so, returns true. Otherwise, returns false.
 * @global <tt>$data</tt> contains feed information
 * @global <tt>$list</tt> contains a SimplePie object
 * @global <tt>$item_number</tt> contains the current item's position in the item list
 * @global <tt>$settings</tt> contains filename information and default time to display
 * @global <tt>$showtime</tt> contains the
 *
 * @return boolean Are items available?
 */
function has_items($increment = true) {
	global $data, $item, $list, $item_number, $settings, $showtime, $total_items;
	if(empty($data))
		$data = lilina_load_feeds($settings['files']['feeds']);

	if(!isset($data['feeds']) || count($data['feeds']) === 0)
		return false;

	if(empty($list))
		$list	= lilina_return_items($data);

	if(!isset($item_number) && $increment)
		$item_number = 0;
	elseif ($increment)
		++$item_number;
	
	if(!isset($showtime)) {
		$showtime = get_offset();
		if($showtime != 0)
			$showtime = time() - $showtime;
	}

	if(!isset($total_items))
		$total_items = $list->get_item_quantity();
	
	if($total_items <= 0)
		return apply_filters('has_items', false, $showtime, $total_items);
	
	if(!isset($item)) {
		if($list->get_item(0)->get_date( $format ) < $showtime)
			return apply_filters('has_items', false, $showtime, $total_items);
	}
	else {
		if(get_the_date('U') < $showtime)
			return apply_filters('has_items', false, $showtime, $total_items);
	}

	if($item_number < $total_items)
		return apply_filters('has_items', true, $showtime, $total_items);

	return apply_filters('has_items', false, $showtime, $total_items);
}

/**
* Gets all items from all feeds and returns as an array
*
* @return array List of items and associated data
*/
function get_items() {
	/*global $data, $list, $items, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	if(empty($items)) {
		$items	= lilina_return_output($list);
	}
	return apply_filters('get_items', $items);*/
}

/**
 * Gets the offset seconds from which the items are shown
 *
 * @global array Holds defaults
 * @global string Holds offset time
 */
function get_offset($format = 'U') {
	global $settings, $offset_time;

	if(!isset($offset_time)) {
		if(isset($_REQUEST['hours']) && !empty($_REQUEST['hours'])) {
			if( -1 == $_REQUEST['hours'])
				$offset_time = 0;
			else
				$offset_time = (int) $_REQUEST['hours'] * 60 * 60;
		}
		else
			$offset_time = (int) $settings['interface']['times'][0] * 60 * 60;
		$offset_time = apply_filters('showtime', $offset_time);
	}
	return date($format, $offset_time);
}

/**
 * @todo Document
 */
function the_item() {
	global $list, $item_number, $item;
	$item = apply_filters('the_item', $list->get_item( $item_number ));
}

/**
 * @todo Document
 */
function the_title() {
	global $item;
	echo apply_filters('the_title', $item->get_title());
}

/**
 * @todo Document
 */
function the_summary() {
	global $item;
	echo apply_filters('the_summary', $item->get_description());
}

/**
 * @todo Document
 */
function the_content() {
	global $item;
	echo apply_filters('the_content', $item->get_content());
}

/**
 * @todo Document
 */
function the_link() {
	global $item;
	echo apply_filters( 'the_link', $item->get_link() );
}

/**
 * @todo Document
 */
function get_the_date($format='U') {
	global $item;
	return apply_filters( 'get_the_date', $item->get_date( $format ) );
}

/**
 * @todo Document
 */
function the_date($args='') {
	$defaults = array(
		'format' => 'H:i:s, l d F, Y'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	echo get_the_date($format);
}

/**
 * SimplePie only gives us the URL as an ID, we want a MD5
 * @todo Document
 */
function get_the_id($id = -1) {
	global $list, $item;
	if($id >= 0)
		$current_item = $list->get_item( $id );
	else
		$current_item = $item;
	return apply_filters( 'get_the_id', $current_item->get_id(true), $current_item, $id );
}

/**
 * @todo Document
 */
function the_id($id = -1) {
	echo get_the_id($id);
}

/**
 * @todo Document
 */
function the_feed_name() {
	global $item;
	echo apply_filters( 'the_feed_name', $item->get_feed()->get_title() );
}

/**
 * @todo Document
 */
function the_feed_url() {
	global $item;
	echo apply_filters( 'the_feed_url', $item->get_feed()->get_link() );
}

/**
 * @todo Document
 */
function the_feed_favicon() {
	global $item;
	echo apply_filters( 'the_feed_favicon', $item->get_feed()->get_favicon() );
}

/**
 * @todo Document
 */
function get_the_feed_id($id = -1) {
	global $list, $item;
	if($id >= 0)
		$current_feed = $list->get_item( $id )->get_feed();
	else
		$current_feed = $item->get_feed();
	return apply_filters( 'get_the_feed_id', md5($current_feed->get_link() . $current_feed->get_title()) );
}

/**
 * @todo Document
 */
function the_feed_id($id = -1) {
	echo get_the_feed_id($id);
}

/**
 * @todo Document
 */
function has_enclosure() {
	global $item, $enclosure;
	$enclosure = apply_filters( 'has_enclosure', $item->get_enclosure() );
	$enclosure_link = $enclosure->get_link();
	return !empty($enclosure_link);
}

if(!function_exists('the_enclosure')) {
	/**
	 * @todo Document
	 */
	function the_enclosure() {
		global $item, $enclosure;
		if(empty($enclosure)) {
			if(!has_enclosure()) {
				return false;
			}
		}

		echo apply_filters( 'the_enclosure', '<a href="' . $enclosure->get_link() . '">' . _r('View podcast') . '</a>' . "\n" );
	}
}

/**
 * @todo Document
 */
function atom_enclosure() {
	global $item, $enclosure;
	if(!$enclosure)
		$enclosure = apply_filters( 'has_enclosure', $item->get_enclosure() );

	if(!has_enclosure())
		return false;

	echo apply_filters('atom_enclosure', '<link href="' . $enclosure->get_link() . '" rel="enclosure" length="' . $enclosure->get_length() . '" type="' . $enclosure->length() . '" />' . "\n");
}

/**
 * @todo Document
 */
function date_equals($args='') {
	global $item, $item_number, $list, $total_items;
	$defaults = array(
		'equalto' => 'previous'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	if( 'previous' == $equalto )
			$equalto = $item_number - 1;
	elseif( 'next' == $equalto )
			$equalto = $item_number + 1 ;

	if( !is_int( $equalto ) || $equalto >= $total_items || $equalto < 0 )
		return false;
	$equals = $item->get_date( 'l d F, Y' ) == $list->get_item( $equalto )->get_date( 'l d F, Y' );
	return apply_filters('date_equals', $equals, $equalto);
}

/**
 * @todo Document
 */
function feed_equals($args='') {
	global $item, $item_number, $list, $total_items;
	$defaults = array(
		'equalto' => 'previous'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	if( 'previous' == $equalto)
		$equalto = $item_number - 1;
	elseif( 'next' == $equalto)
		$equalto = $item_number + 1;

	if( !is_int( $equalto ) || $equalto >= $total_items || $equalto < 0 )
		return false;
	$equals = get_the_feed_id() == get_the_feed_id($equalto);
	return apply_filters('feed_equals', $equals, $equalto);
}

/**
* Feeds available for parsing with {@link get_feeds}
*
* @return boolean Are feeds available?
*/
function has_feeds() {
	global $data, $list, $settings;
	if(empty($data))
		$data = lilina_load_feeds($settings['files']['feeds']);

	if(!isset($data['feeds']) || count($data['feeds']) === 0)
		return false;

	return true;
	//if(empty($list)) {
	//	$list	= lilina_return_items($data);
	//}
	//var_dump($list);
	//return apply_filters('has_feeds', ((is_array($list[0]) && count($list[0]) > 0) ? true : false));
}

/**
* Gets all feeds and returns as an array
*
* @return array List of feeds and associated data
*/
function get_feeds() {
	return array();
	global $data, $list, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	return apply_filters('get_feeds', $list[0]);
}

/**
 * Replacable functions from here on
 */

if(!function_exists('template_load')) {
	global $templates;
	$templates['default']	= LILINA_INCPATH . '/templates/' . $settings['template'] . '/index.php';
	$templates['rss']		= LILINA_INCPATH . '/templates/' . $settings['template'] . '/rss.php';
	$templates['mobile']	= LILINA_INCPATH . '/templates/' . $settings['template'] . '/mobile.php';
	/**
	* Load the current template
	*
	* @param string $type Type of template; rss, default, mobile
	*/
	function template_load($type='default') {
		global $settings, $templates;
		if(file_exists($templates[$type])) {
			require_once($templates[$type]);
		}
		else {
			if($type == 'default') {
				require_once(LILINA_INCPATH . '/templates/' . get_option('template') . '/index.php');
			}
			else {
				require_once(LILINA_INCPATH . '/templates/' . get_option('template') . '/' . $type . '.php');
			}
		}
	}
}
if(!function_exists('template_file_load')) {
	/**
	 * Returns the URL for a specified file
	 * @deprecated Deprecated in favour of {@see{template_directory()}
	 */
	function template_file_load($file) {
		global $settings;
		return apply_filters('template_file_load', $settings['baseurl'] . 'inc/templates/' . $settings['template'] . '/' . $file, $file);
	}
}
if(!function_exists('template_directory')) {
	function template_directory() {
		global $settings;
		echo get_option('baseurl') . 'inc/templates/' . get_option('template');
	}
}
?>
