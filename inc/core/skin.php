<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		parseopml.php
Purpose:	OPML Parser
Notes:		Adapted from
	http://www.sencer.de/code/showOPML.phps
			CAUTION: HERE BE DRAGONS!
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

function template_header($return='echo'){
//MUSTFIX: NOT YET IMPLEMENTED
}
?>
