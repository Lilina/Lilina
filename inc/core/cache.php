<?php
/**
* Caching functions, taken from http://www.ilovejackdaniels.com/php/caching-output-in-php/
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
defined('LILINA') or die('Restricted access');
require_once(LILINA_INCPATH . '/core/conf.php');

function lilina_cache_check(){
	global $settings, $showtime;
	// Cache file to either load or create
	$cachefile = $settings['cachedir'] . md5('index-' . $showtime) . '.html';
	$cachefile_created = (@file_exists($cachefile)) ? @filemtime($cachefile) : 0;
	clearstatcache();
	// Show file from cache if still valid
	if (time() - $settings['cachetime'] < $cachefile_created) {
		//echo '<!--Retrieved from cache-->' . "\n";
		//ob_start('ob_gzhandler');
		readfile($cachefile);
		//ob_end_flush();
		exit();
	}
}
function lilina_cache_start(){
	global $settings;
	//echo '<!--Generated fresh-->' . "\n";
	ob_start();
}

function lilina_cache_end() {
	global $settings, $showtime;
	$cachefile = $settings['cachedir'] . md5('index-' . $showtime) . '.html'; // Cache file to either or create
	// Now the script has run, generate a new cache file
	$fp = fopen($cachefile, 'w');
	$pagecontent = ob_get_contents();
	// save the contents of output buffer to the file
	fwrite($fp, $pagecontent);
	fclose($fp);
	ob_end_flush();
}
?>