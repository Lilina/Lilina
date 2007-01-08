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
//Stop hacking attempts
defined('LILINA') or die('Restricted access');

//Define all the functions for our skins
function template_sitename($return='echo'){
	if($return == 'echo') {
		echo $settings['sitename'];
	}
	elseif($return == 'var') {
		return $settings['sitename'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_siteurl($return='echo'){
	if($return == 'echo') {
		echo $settings['baseurl'];
	}
	elseif($return == 'var') {
		return $settings['baseurl'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_synd_header($return='echo'){
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
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_synd_links($return='echo'){
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
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_header($return='echo'){
//MUSTFIX: NOT YET IMPLEMENTED
}

function template_opml($return='echo'){
	if($return == 'echo') {
		echo '<a href="cache/opml.xml">OPML</a>';
	}
	elseif($return == 'var') {
		return 'cache/opml.xml';
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_output($return='echo'){
	if($return == 'echo') {
		echo $out;
	}
	elseif($return == 'var') {
		return $out;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_source_list($return='echo'){
	if($return == 'echo') {
		echo $channel_list;
	}
	elseif($return == 'var') {
		return $channel_list;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_end_errors($return='echo'){
	if($return == 'echo') {
		echo $end_errors;
	}
	elseif($return == 'var') {
		return $end_errors;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}


function template_footer($return='echo'){
	if($return == 'echo') {
		echo '<p>powered by <a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="lilina news aggregator" title="lilina news aggregator" /></a> v
	'.$LILINAVERSION.'<br />
	This page was last generated on '. date('Y-m-d \a\t g:i a').'<br />.
	This page was generated in '.$totaltime.' seconds
	?></div>';
	}
	elseif($return == 'var') {
		$return_me = array('<a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="Lilina News Aggregator" title="Lilina News Aggregator" /></a>', $LILINAVERSION, date('Y-m-d \a\t g:i a'), $totaltime);
		return $return_me;
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_path($return='echo'){
	if($return == 'echo') {
		echo $settings['templates']['path'];
	}
	elseif($return == 'var') {
		return $settings['templates']['path'];
	}
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

function template_times($return='echo'){
	$return_me	= array();
	for($q=0;$q<count($settings['interface']['times']);$q++){
		$current_time	= $settings['interface']['times'][$q];
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
	else {
		echo 'Error: return type '.$return.' is not valid';
	}
}

?>
