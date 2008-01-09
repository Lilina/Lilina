<?php
// $Id$
/**
 * Initialization page
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
/**
 * Stop hacking attempts
 */
define('LILINA',1) ;
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
$settings	= 0;



if(!@file_exists(LILINA_PATH . '/conf/settings.php')) {
	echo 'Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a>';
	die();
}
require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');
$timer_start = lilina_timer_start();

//Current Version
require_once(LILINA_INCPATH . '/core/version.php');

//Custom error handler
//require_once(LILINA_INCPATH . '/core/errors.php');

//Caching to reduce loading times
require_once(LILINA_INCPATH . '/core/cache.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

// Do not update cache unless called with parameter force_update=1
if (isset($_GET['force_update']) && $_GET['force_update'] == 1) {
	define('MAGPIE_CACHE_AGE', 1);
}
else {
	lilina_cache_check();
}
//Require our standard stuff
require_once(LILINA_INCPATH . '/core/lib.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/file-functions.php');

//Templating functions
require_once(LILINA_INCPATH . '/core/skin.php');

// load times*/
$time_table	= lilina_load_times();

lilina_save_times($time_table);
global $settings;
ob_start();
template_load();

//Cache the output
global $showtime;
$cachefile = $settings['cachedir'] . md5('index-' . $showtime) . '.html'; // Cache file to either or create
// Now the script has run, generate a new cache file
$fp = fopen($cachefile, 'w');
$pagecontent = ob_get_contents();
// save the contents of output buffer to the file
fwrite($fp, $pagecontent);
fclose($fp);
ob_end_flush();
?>