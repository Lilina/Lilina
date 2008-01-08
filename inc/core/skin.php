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
	global $timer_start;
	global $lilina;
	$footer = '<p>' . sprintf(_r('Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a> %s'), $lilina['core-sys']['version']);
	$footer .= '<br />', sprintf(_r('This page was last generated on %s and took %f seconds'), date('Y-m-d \a\t g:i a'), lilina_timer_end($timer_start)));
	echo apply_filters('template_footer', $footer, 
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
* Items available for parsing with {@link get_items}
*
* @return boolean Are items available?
*/
function has_items() {
	global $data, $list, $items, $item_number, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($data) || !is_array($data) || count($data) === 0) {
		return false;
	}
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	return apply_filters('has_items', ( $item_number < $list->get_item_quantity() ) );
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
 * @todo Document
 */
function the_item() {
	global $data, $list, $items, $item_number, $item;
	if(!isset($item_number))
		$item_number = 0;
	$item = $list->get_item($item_number);
	++$item_number;
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
	echo apply_filters( 'the_id', $item->get_link() );
}

/**
 * @todo Document
 */
function get_the_date($args='') {
	global $item;
	$defaults = array(
		'format' => 'H:i:s, l d F, Y'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	echo apply_filters( 'get_the_date', $item->get_date( $format ) );
}

/**
 * @todo Document
 */
function the_date($args='') {
	echo get_the_date($args);
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
	printf(_r('Post from %s'), apply_filters( 'the_feed_name', $item->get_feed()->get_title() ) );
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
	return $enclosure;
}

/**
 * @todo Document
 */
function the_enclosure() {
	global $item, $enclosure;
	if(!$enclosure)
		$enclosure = apply_filters( 'has_enclosure', $item->get_enclosure() );
	echo apply_filters( 'the_enclosure', $enclosure->embed() );
}

/**
 * @todo Document
 */
function date_equals($args='') {
	global $item, $item_number, $list;
	$defaults = array(
		'equalto' => 'previous'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	switch ( $equalto ) {
		case 'previous':
			if($item_number - 1 >= $list->get_item_quantity())
				return false;
			$equals = $item->get_date( 'l d F, Y' ) == $list->get_item( $item_number - 1 )->get_date( 'l d F, Y' );
			break;
		case 'next':
			if($item_number + 1 >= $list->get_item_quantity())
				return false;
			$equals = $item->get_date( 'l d F, Y' ) == $list->get_item( $item_number + 1 )->get_date( 'l d F, Y' );
			break;
		default:
			//Idiot proofing^H^H^H^H^H^H^H^H^H^H^H^H^HPoka-Yoke
			if( isint( $equalto ) || $equalto >= $list->get_item_quantity() )
				return false;
			$equals = $item->get_date( 'l d F, Y' ) == $list->get_item( $equalto )->get_date( 'l d F, Y' );
			break;
	}
	return apply_filters('date_equals', $equals, $equalto);
}

/**
 * @todo Document
 */
function feed_equals($args='') {
	global $item, $item_number, $list;
	$defaults = array(
		'equalto' => 'previous'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	switch ( $equalto ) {
		case 'previous':
			if($item_number - 1 >= $list->get_item_quantity())
				return false;
			$equals = get_the_feed_id() == get_the_feed_id($item_number - 1);
			break;
		case 'next':
			if($item_number + 1 >= $list->get_item_quantity())
				return false;
			$equals = get_the_feed_id() == get_the_feed_id($item_number + 1);
			break;
		default:
			//Idiot proofing^H^H^H^H^H^H^H^H^H^H^H^H^HPoka-Yoke
			if((int) $equalto >= $list->get_item_quantity() || (int) $equalto < 0 )
				return false;
			$equals = get_the_feed_id() == get_the_feed_id($equalto);
			break;
	}
	return apply_filters('feed_equals', $equals, $equalto);
}

/**
* Feeds available for parsing with {@link get_feeds}
*
* @return boolean Are feeds available?
*/
function has_feeds() {
	global $data, $list, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($data) || !is_array($data) || count($data) === 0) {
		return false;
	}
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
				require_once(LILINA_INCPATH . '/templates/default/index.php');
			}
			else {
				require_once(LILINA_INCPATH . '/templates/default/' . $type . '.php');
			}
		}
	}
}
if(!function_exists('template_file_load')) {
	/**
	 * Returns the URL for a specified file
	 */
	function template_file_load($file) {
		global $settings;
		return apply_filters('template_file_load', $settings['baseurl'] . 'inc/templates/' . $settings['template'] . '/' . $file, $file);
	}
}
?>