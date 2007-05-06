<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		cache.php
Purpose:	Dynamic page caching
Notes:		Believed to be Public Domain. Taken from
http://www.ilovejackdaniels.com/php/caching-output-in-php/
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
require_once('./inc/core/conf.php');

function lilina_cache_check(){
	global $settings;
	// Cache file to either load or create
	$cachefile = $settings['cachedir'] . md5('index') . '.html';
	$cachefile_created = (@file_exists($cachefile)) ? @filemtime($cachefile) : 0;
	clearstatcache();
	// Show file from cache if still valid
	if (time() - $settings['cachetime'] < $cachefile_created) {
		echo '<!--Retreived from cache-->' . "\n";
		//ob_start('ob_gzhandler');
		readfile($cachefile);
		//ob_end_flush();
		exit();
	}
}
function lilina_cache_start(){
	global $settings;
	echo '<!--Generated fresh-->' . "\n";
	ob_start();
}

function lilina_cache_end() {
	global $settings;
	$cachefile = $settings['cachedir'] . md5('index') . '.html'; // Cache file to either or create
	// Now the script has run, generate a new cache file
	$fp = fopen($cachefile, 'w');
	$pagecontent = ob_get_contents();
	// save the contents of output buffer to the file
	fwrite($fp, $pagecontent);
	fclose($fp);
	ob_end_flush();
}
?>