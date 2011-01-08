<?php
/**
 * Templating functions
 * @todo Document this file
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');


/**
 * @todo Document
 * @deprecated Use get_option('sitename') instead
 */
 //Define all the functions for our skins
function template_sitename(){
	echo get_option('sitename');
}

/**
 * @todo Document
 * @deprecated Use get_option('baseurl') instead
 */
function template_siteurl(){
	echo get_option('baseurl');
}

/**
 * @todo Document
 */
function template_synd_header(){
	$header = '<link rel="alternate" type="application/rss+xml" title="' . _r('RSS Feed') . '" href="?method=feed&type=rss2" />';
	$header = '<link rel="alternate" type="application/atom+xml" title="' . _r('Atom Feed') . '" href="?method=feed&type=atom" />';
	echo apply_filters('template_synd_header', $header);
}

/**
 * @todo Document
 */
function template_synd_links(){
	echo '<a href="?method=feed&type=rss2">' . _r('RSS Feed') . '</a> ';
	echo '<a href="?method=feed&type=atom">' . _r('Atom Feed') . '</a>';
}

/**
 * @todo Document
 */
function template_header(){
	do_action('template_header');
}


/**
 * @todo Document
 */
function template_footer(){
	do_action('template_footer');
}

/**
 * @todo Document
 * @return boolean Are items available?
 */
function has_items() {
	global $lilina_items;

	if(count(Feeds::get_instance()->getAll()) === 0)
		return false;

	if(empty($lilina_items)) {
		foreach(Feeds::get_instance()->getAll() as $the_feed)
			$feed_list[] = $the_feed['feed'];

		$lilina_items = Items::get_instance();
		$lilina_items->init();
		$conditions = apply_filters('return_items-conditions', array('time' => (time() - get_offset())));
		$lilina_items->set_conditions($conditions);
		$lilina_items->filter();
	}

	return $lilina_items->has_items();
}

/**
 * Gets the offset seconds from which the items are shown
 *
 * @global array Holds defaults
 * @global string Holds offset time
 */
function get_offset($as_hours = false) {
	global $offset_time;

	if(!isset($offset_time)) {
		if(isset($_REQUEST['hours']) && !empty($_REQUEST['hours'])) {
			if( -1 == $_REQUEST['hours'])
				$offset_time = 0;
			else
				$offset_time = (int) $_REQUEST['hours'] * 60 * 60;
		}
		else
			$offset_time = (int) 24 * 60 * 60;
		$offset_time = apply_filters('showtime', $offset_time);
	}
	if($as_hours == true)
		return $offset_time / 60 / 60;
	return $offset_time;
}

/**
 * @todo Document
 */
function the_item() {
	global $lilina_items, $item;

	$item = apply_filters('the_item', $lilina_items->current_item());
}

/**
 * @todo Document
 */
function get_the_title() {
	global $item;
	return apply_filters( 'the_title', $item->title );
}

/**
 * @todo Document
 */
function the_title() {
	echo get_the_title();
}

/**
 * @todo Document
 */
function get_the_summary($chars = 150) {
	global $item;
	return apply_filters('the_summary', shorten($item->summary, $chars) );
}

/**
 * @todo Document
 */
function the_summary($chars = 150) {
	echo get_the_summary($chars);
}

/**
 * @todo Document
 */
function get_the_content() {
	global $item;
	return apply_filters('the_content', $item->content);
}

/**
 * @todo Document
 */
function the_content() {
	echo get_the_content();
}

/**
 * @todo Document
 */
function get_the_link() {
	global $item;
	return apply_filters( 'the_link', $item->permalink );
}

/**
 * @todo Document
 */
function the_link() {
	echo get_the_link();
}

/**
 * Display the link to the item's author
 *
 * @param string $args Passed through to the_author_name()
 */
function the_author_link($args = '') {
	global $item;
	if(empty($item->author->url))
		return the_author_name($args);

	$defaults = array(
		'before' => '',
		'after' => '',
		'echo' => true
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	if($item->author->url) {
		$before .= '<a href="' . $item->author->url . '">';
		$after = '</a>' . $after;
	}
	echo the_author_name('echo=0&before=' . $before . '&after=' . $after);
}

/**
 * Display or Retrieve the item's author
 *
 * @param string $args
 * @return string|null
 */
function the_author_name($args = '') {
	global $item;
	if(empty($item->author->name))
		return;

	$defaults = array(
		'before' => '',
		'after' => '',
		'echo' => true
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	if((bool) $echo)
		echo $before . $item->author->name . $after;
	else
		return $before . $item->author->name . $after;
}

/**
 * Retrieve the date of the item
 *
 * Will only output the date if the current post's date is different from the
 * previous one output.
 *
 * @param string $args
 */
function get_the_date($args = '') {
	global $lilina_items, $item;
	$defaults = array(
		'format' => 'H:i:s, l d F, Y',
		'before' => '',
		'after' => '',
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	$previous = false;
	if($lilina_items->previous_item()) {
		$previous = get_the_time($format, $lilina_items->previous_item()->timestamp);
	}
	$current = get_the_time($format, $item->timestamp);
	if ( $previous == $current ) {
		return;
	}

	return apply_filters('the_date', $before . $current . $after, $format, $before, $after);
}

/**
 * Display the date of the item
 *
 * Will only output the date if the current post's date is different from the
 * previous one output.
 *
 * @see get_the_date()
 *
 * @param string $args
 */
function the_date($args='') {
	$date = get_the_date($args);
	if(!empty($date)) {
		echo $date;
	}
}

/**
 * Retrieve the time of the item
 *
 * @param string $format PHP date format
 * @param int $timestamp Optional extra timestamp to pass through relevant filters
 * @return string
 */
function get_the_time($format='U', $timestamp = null) {
	global $item;
	if(null === $timestamp) {
		$timestamp = $item->timestamp;
	}
	$timestamp = apply_filters('timestamp', $timestamp);
	return apply_filters( 'get_the_time', date($format, $timestamp), $timestamp, $format );
}

/**
 * @todo Document
 */
function the_time($args='') {
	$defaults = array(
		'format' => 'H:i:s, l d F, Y'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);
	echo get_the_time($format);
}

/**
 * @todo Document
 */
function get_the_id($id = null) {
	global $lilina_items, $item;
	if($id !== null)
		$current_item = $lilina_items->get_item( $id );
	else
		$current_item = $item;
	return apply_filters( 'get_the_id', $current_item->hash, $current_item, $id );
}

/**
 * @todo Document
 */
function the_id($id = null) {
	echo get_the_id($id);
}

/**
 * @todo Document
 */
function get_the_feed_name() {
	global $item;
	$feed = Feeds::get_instance()->get($item->feed_id);
	$name = $feed['name'];
	return apply_filters( 'the_feed_name', $name, $feed, $item->feed_id );
}

function the_feed_name() {
	echo get_the_feed_name();
}

/**
 * @todo Document
 */
function get_the_feed_url() {
	global $item;
	$feed = Feeds::get_instance()->get($item->feed_id);
	$url = $feed['url'];
	return apply_filters( 'the_feed_url', $url, $feed, $item->feed_id );
}

function the_feed_url() {
	echo get_the_feed_url();
}

/**
 * @todo Document
 */
function get_the_feed_favicon($feed = null) {
	global $item;
	if ($feed === null) {
		$feed = Feeds::get_instance()->get($item->feed_id);
	}
	$icon = $feed['icon'];
	// New favicons
	if ($icon === true) {
		$icon = get_option('baseurl') . 'lilina-favicon.php?feed=' . $feed['id'];
	}
	if(!$icon)
		$icon = get_option('baseurl') . 'lilina-favicon.php?i=default';
	return apply_filters( 'the_feed_favicon', $icon, $feed, $feed['id'] );
	
}

/**
 * @todo Document
 */
function the_feed_favicon() {
	echo get_the_feed_favicon();
}

/**
 * @todo Document
 */
function get_the_feed_id() {
	global $item;
	return apply_filters( 'get_the_feed_id', $item->feed_id );
}

/**
 * @todo Document
 */
function the_feed_id() {
	echo get_the_feed_id();
}

/**
 * @todo Document
 */
function has_enclosure() {
	global $item;
	$enclosure = apply_filters( 'has_enclosure', $item->metadata->enclosure );
	return !empty($enclosure);
}

if(!function_exists('the_enclosure')) {
	/**
	 * @todo Document
	 */
	function the_enclosure() {
		global $item;
		if(!has_enclosure()) {
			return false;
		}
		$metadata = enclosure_metadata();
		$type = '';
		if(!empty($metadata->type))
			$type = ' (' . $metadata->type . ')';

		echo apply_filters( 'the_enclosure', '<a href="' . $item->metadata->enclosure . '" rel="enclosure">' . _r('View media') . $type .'</a>' . "\n" );
	}
}

/**
 * @todo Document
 */
function atom_enclosure() {
	global $item;
	if(!has_enclosure())
		return false;

	//echo apply_filters('atom_enclosure', '<link href="' . $enclosure . '" rel="enclosure" length="' . $enclosure->get_length() . '" type="' . $enclosure->get_type() . '" />' . "\n");
	echo apply_filters('atom_enclosure', '<link href="' . $item->metadata->enclosure . '" rel="enclosure" />' . "\n");
}

function enclosure_metadata() {
	global $item;
	if(!isset($item->metadata->enclosure_data))
		return false;
	return $item->metadata->enclosure_data;
}

/**
* Feeds available for parsing with {@link get_feeds}
*
* @return boolean Are feeds available?
*/
function has_feeds() {
	$feeds = Feeds::get_instance()->getAll();
	return apply_filters('has_feeds', count($feeds === 0));
}

/**
* Gets all feeds and returns as an array
*
* @return array List of feeds and associated data
*/
function get_feeds() {
	return apply_filters('get_feeds', Feeds::get_instance()->getAll());
}

/**
 *
 */
function list_feeds($args = '') {
	$defaults = array(
		'format' => '<a href="%1$s">%3$s</a> [<a href="%4$s">' . _r('Feed') . '</a>]',
		'title_length' => 0
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	if(has_feeds()) {
		$feeds = get_feeds();
		usort($feeds, '_sort_feeds');
		foreach($feeds as $feed) {
			$icon = get_the_feed_favicon($feed);
			$title = ($title_length > 0) ? shorten($feed['name'], $title_length) : $feed['name'];
			printf($format, $feed['url'], $icon, $title, $feed['feed']);
		}
	}
}

/**
 * Sort feeds by name (internal)
 *
 * @param array $a First feed array
 * @param array $b Second feed array
 * @return See strnatcasecmp()
 */
function _sort_feeds($a, $b) {
    return strnatcasecmp($a['name'], $b['name']);
}

/**
 * Output all available "actions" for the current item
 *
 */
function action_bar($args = '') {
	global $item;
	$defaults = array(
		'header' => '<ul>',
		'footer' => '</ul>',
		'before' => '<li class="action">',
		'after' => '</li>'
	);
	$args = lilina_parse_args($args, $defaults);
	/** Make sure we don't overwrite any current variables */
	extract($args, EXTR_SKIP);

	$services = Services::get_for_item($item);

	if(!empty($services)) {
		echo $header;

		foreach ($services as $id => $service) {
			echo $before . '<a href="' . $service['action'] . '" class="service service-'. $service['type'] . ' service-' . $id . '">' . $service['label'] .'</a>' . $after;
		}

		echo $footer;
	}

	do_action('river_entry');
}

/**
 * Output the URL to a specified template file
 *
 * Converts filename to URL after finding location and then outputs it
 *
 * @since 1.0
 * @see get_template_file
 *
 * @param string $file Filename to find, relative to template directory
 */
function template_file($file) {
	echo get_template_file($file);
}

/**
 * Return the URL to a specified template file
 *
 * Converts filename to URL after finding location and then returns it
 *
 * @since 1.0
 *
 * @param string $file Filename to find, relative to template directory
 * @return string Absolute URL to the file
 */
function get_template_file($file) {
	return Templates::path_to_url(Templates::get_file($file));
}

/**
 * Replacable functions from here on
 */

if(!function_exists('template_file_load')) {
	/**
	 * @deprecated Deprecated in favour of {@see{Templates::get_template_dir_url()}}
	 */
	function template_file_load($file) {
		return apply_filters('template_file_load', Templates::get_template_dir_url() . '/' . $file, $file);
	}
}
if(!function_exists('template_directory')) {
	/**
	 * @deprecated Deprecated in favour of {@see{Templates::get_template_dir_url()}}
	 */
	function template_directory() {
		echo get_template_directory();
	}
}
if(!function_exists('get_template_directory')) {
	/**
	 * @deprecated Deprecated in favour of {@see{Templates::get_template_dir_url()}}
	 */
	function get_template_directory() {
		return Templates::get_template_dir_url();
	}
}
?>
