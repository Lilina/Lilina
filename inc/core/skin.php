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
	echo apply_filters('template_footer', '<p>', sprintf(_r('Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a> %s'), $lilina['core-sys']['version']),
	'<br />', sprintf(_r('This page was last generated on %s and took %f seconds'), date('Y-m-d \a\t g:i a'), lilina_timer_end($timer_start)));
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
	global $data, $list, $items, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($data) || !is_array($data) || count($data) === 0) {
		return false;
	}
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	if(empty($items)) {
		$items	= lilina_return_output($list[1]);
	}
	return apply_filters('has_items', ((count($items) > 0) ? true : false));
}

/**
* Gets all items from all feeds and returns as an array
*
* @return array List of items and associated data
*/
function get_items() {
	global $data, $list, $items, $settings;
	if(empty($data)) {
		$data = lilina_load_feeds($settings['files']['feeds']);
	}
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	if(empty($items)) {
		$items	= lilina_return_output($list[1]);
	}
	return apply_filters('get_items', $items);
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
	if(empty($list)) {
		$list	= lilina_return_items($data);
	}
	return apply_filters('has_feeds', ((is_array($list[0]) && count($list[0]) > 0) ? true : false));
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