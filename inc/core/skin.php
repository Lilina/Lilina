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

function template_siteurl($return='echo'){
	global $settings;
	if($return == 'echo') {
		echo $settings['baseurl'];
		return true;
	}
	elseif($return == 'var') {
		return $settings['baseurl'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_synd_header($return='echo'){
	global $settings;
	if($return == 'echo') {
		if($settings['output']['rss']){
			echo '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="rss.php" />';
		}
		if($settings['output']['opml']){
			echo '<link rel="alternate" type="application/rss+xml" title="OPML Feed" href="rss.php?output=opml" />';
		}
		if($settings['output']['atom']){
			echo '<link rel="alternate" type="application/rss+xml" title="Atom Feed" href="rss.php?output=atom" />';
		}
		return true;
	}
	elseif($return == 'var') {
		$return_me = array(0 => false, 1 => false, 2 => false);
		if($settings['output']['rss']){
			$return_me[0] = true;
		}
		if($settings['output']['opml']){
			$return_me[1] = true;
		}
		if($settings['output']['atom']){
			$return_me[2] = true;
		}
		return $return_me;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_synd_links($return='echo'){
	global $settings;
	if($return == 'echo') {
		if($settings['output']['rss']){
			echo '<a title="RSS Feed" href="rss.php"><img src="i/feed.png" alt="RSS feed" title="RSS feed" />';
		}
		if($settings['output']['opml']){
			echo '<a title="OPML Feed" href="rss.php?output=opml"><img src="i/feed.png" alt="OPML feed" title="OPML feed" />';
		}
		if($settings['output']['atom']){
			echo '<a title="Atom Feed" href="rss.php?output=atom"><img src="i/feed.png" alt="Atom feed" title="Atom feed" />';
		}
		return true;
	}
	elseif($return == 'var') {
		$return_me = array(0 => false, 1 => false, 2 => false);
		if($settings['output']['rss']){
			$return_me[0] = true;
		}
		if($settings['output']['opml']){
			$return_me[1] = true;
		}
		if($settings['output']['atom']){
			$return_me[2] = true;
		}
		return $return_me;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_header($return='echo'){
	global $settings;
	//call_hooked('template_header');
	return true;
}

function template_opml($return='echo'){
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
}

function template_output($return='echo', $feeds){
	if($return == 'echo') {
		echo lilina_make_output($feeds)
		return true;
	}
	elseif($return == 'var') {
		return lilina_make_output($feeds);
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_source_list($return='echo', $input){
	if($return == 'echo') {
		$list = lilina_make_items($input);
		echo $list[0];
		return $list[1];
	}
	elseif($return == 'var') {
		$list = lilina_make_items($input);
		return $list;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_end_errors($return='echo'){
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


function template_footer($return='echo'){
	global $timer_start;
	global $lilina;
	if($return == 'echo') {
		echo '<p>Powered by <a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="lilina news aggregator" title="lilina news aggregator" /></a> v'
		. $lilina['core-sys']['version']
		. '<br />This page was last generated on '
		. date('Y-m-d \a\t g:i a')
		. ' and took '
		. lilina_timer_end($timer_start)
		. ' seconds</div>';
		return true;
	}
	elseif($return == 'var') {
		$return_me = array(
							'<a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="Lilina News Aggregator" title="Lilina News Aggregator" /></a>',
							$lilina['core-sys']['version'],
							date('Y-m-d \a\t g:i a'),
							lilina_timer_end($timer_start)
							);
		return $return_me;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_path($return='echo'){
	global $settings;
	if($return == 'echo') {
		echo $settings['template_path'];
		return true;
	}
	elseif($return == 'var') {
		return $settings['template_path'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
		return false;
	}
}

function template_times($return='echo'){
	global $settings;
	$return_me	= array();
	if($return == ('echo'||'var')){
		foreach($settings['interface']['times'] as $current_time){
			if(is_int($current_time)){
				if($return == 'echo') {
					echo '<li><a href="index.php?hours='.$current_time.'"><span>'.$current_time.'h</span></a></li>';
				}
				elseif($return == 'var') {
					$return_me[] = array(
										'time'	=> $current_time,
										'label'	=> $current_time
										);
				}
			}
			else {
				switch($current_time) {
					case 'week':
						if($return == 'echo') {
							echo '<li><a href="index.php?hours=168"><span>week</span></a></li>';
						}
						elseif($return == 'var') {
							$return_me[] = array(
												'time'	=> '168',
												'label'	=> 'week'
												);
						}
					break;
					case 'all':
						if($return == 'echo') {
							echo '<li><a href="index.php?hours=-1"><span>all</span></a></li>';
						}
						elseif($return == 'var') {
							$return_me[] = array(
												'time'	=> '-1',
												'label'	=> 'all'
												);
						}
					break;
				}
			}
		}
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

?>
