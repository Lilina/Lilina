<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		skin.php
Purpose:	Templating functions
Notes:		CAUTION: HERE BE DRAGONS!
			$return_type can be either
			echo (default) or var for an array
Functions:	template_sitename( $return_type );
			template_siteurl( $return_type );
			template_synd_header( $return_type );
			template_synd_links( $return_type );
			template_header( $return_type );
			template_opml( $return_type );
			template_output( $return_type );
			template_source_list( $return_type );
			template_end_errors( $return_type );
			template_footer( $return_type );
			template_path( $return_type );
			template_times( $return_type );
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');

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

function template_siteurl($return=false){
	global $settings;
	if($return == false) {
		echo $settings['baseurl'];
		return true;
	}
	return $settings['baseurl'];
}

function template_synd_header($return='echo'){
	global $settings;
	if($settings['output']['rss']){
		echo '<link rel="alternate" type="application/rss+xml" title="' . _r('RSS Feed') . '" href="rss.php" />';
	}
	if($settings['output']['atom']){
		echo '<link rel="alternate" type="application/rss+xml" title="' . _r('Atom Feed') . '" href="rss.php?output=atom" />';
	}
	return true;
}

function template_synd_links(){
	global $settings;
	if($settings['output']['rss']){
		echo _r('RSS'), ': <a href="rss.php"><img src="', template_file_load('feed.png'), '" alt="', _r('RSS Feed') . '" title="', _r('RSS Feed'), '" /></a> ';
	}
	if($settings['output']['atom']){
		echo _r('Atom'), ': <a href="rss.php?output=atom"><img src="', template_file_load('feed.png'), '" alt="', _r('Atom Feed'), '" title="', _r('Atom Feed'), '" /></a>';
	}
	return true;
}

function template_header($return='echo'){
	global $settings;
	return true;
}

/*function template_opml($return='echo'){
	global $settings;
	if($settings['output']['opml']===true) {
		if($return == 'echo') {
			echo '<a href="cache/opml.xml">OPML</a>';
			return true;
		}
		elseif($return == 'var') {
			return 'cache/opml.xml';
		}
		else {
			echo 'Error: return type '.$return.' is not valid';
			return false;
		}
	}
	else {
		return false;
	}
}*/

// function template_output($return='echo', $feeds){
	// if($return == 'echo') {
		// echo lilina_make_output($feeds);
		// return true;
	// }
	// elseif($return == 'var') {
		// return lilina_make_output($feeds);
	// }
	// else {
		// echo 'Error: return type '.$return.' is not valid';
		// return false;
	// }
// }

// function template_source_list($return='echo', $input){
	// if($return == 'echo') {
		// $list = lilina_make_items($input);
		// echo $list[0];
		// return $list[1];
	// }
	// elseif($return == 'var') {
		// $list = lilina_make_items($input);
		// return $list;
	// }
	// else {
		// echo 'Error: return type '.$return.' is not valid';
		// return false;
	// }
// }

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


function template_footer(){
	global $timer_start;
	global $lilina;
	echo '<p>', sprintf(_r('Powered by <a href="http://lilina.cubegames.net/">Lilina News Aggregator</a> %s'), $lilina['core-sys']['version']),
	'<br />', sprintf(_r('This page was last generated on %s and took %f seconds'), date('Y-m-d \a\t g:i a'), lilina_timer_end($timer_start));
	return true;
}

function template_path(){
	global $settings;
	return $settings['template_path'];
}

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
	return (count($items) > 0) ? true : false;
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
	return $items;
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
	return (count($list[0]) > 0) ? true : false;
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
	return $list[0];
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
		return $settings['template_path'] . '/' . $file;
	}
}
?>