<?php
/**
* Caching functions, taken from http://www.ilovejackdaniels.com/php/caching-output-in-php/
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

defined('LILINA_PATH') or die('Restricted access');

/**
 * Load the configuration incase it hasn't been already
 */
require_once(LILINA_INCPATH . '/core/conf.php');

if(!function_exists('lilina_cache_check')) {
	/**
	 * Checks the cache.
	 *
	 * Checks the cache to find out whether to use the
	 * cached file or not.
	 */
	function lilina_cache_check(){
		global $showtime;
		// Cache file to either load or create
		$cachefile = get_option('cachedir') . md5('index-' . $showtime) . '.html';
		$cachefile_created = (@file_exists($cachefile)) ? @filemtime($cachefile) : 0;
		clearstatcache();
		/** Show file from cache if still valid */
		if (time() - get_option('cachetime') < $cachefile_created) {
			readfile($cachefile);
			die();
		}
	}
}

/**
 * Starts the output handler to capture output
 */
function lilina_cache_start(){
	lilina_cache_check();
	ob_start();
}

/**
 * Ends the output handler
 *
 * Saves output as a cached file and flushes the output cache to the display
 */
function lilina_cache_end() {
	$cachefile = apply_filters('cache_file', get_option('cachedir') . md5('index-' . get_offset()) . '.html'); // Cache file to either or create
	// Now the script has run, generate a new cache file
	$fp = fopen($cachefile, 'w');
	$pagecontent = apply_filters('lilina_cache_end', ob_get_contents());
	// save the contents of output buffer to the file
	fwrite($fp, $pagecontent);
	fclose($fp);
	ob_end_flush();
}
?>