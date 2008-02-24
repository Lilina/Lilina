<?php
/**
* OPML feeds export, different to the items export on rss.php
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
//Stop hacking attempts
/**
 * @todo Document
 */
define('LILINA',1) ;
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
$settings	= 0;
$out		= '';
/** Current Version */
require_once(LILINA_INCPATH . '/core/version.php');
require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');

//Make sure we are actually installed...
require_once(LILINA_INCPATH . '/core/install-functions.php');
if(!lilina_check_installed()) {
	echo 'Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a>';
	die();
}

//Plugins and misc stuff
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
require_once(LILINA_INCPATH . '/core/l10n.php');

//Caching to reduce loading times
//require_once(LILINA_INCPATH . '/core/cache.php');

// Do not update cache unless called with parameter force_update=1
//if (isset($_GET['force_update']) && $_GET['force_update'] == 1) {
//	define('MAGPIE_CACHE_AGE', 1);
//}
//else {
//	lilina_cache_check();
//}
//Require our standard stuff
require_once(LILINA_INCPATH . '/core/lib.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/file-functions.php');

//Templating functions
require_once(LILINA_INCPATH . '/core/skin.php');

/*$data = lilina_load_feeds($settings['files']['feeds']);

// load times*/
header('Content-Type: application/xml; charset=utf-8');
$time_table	= lilina_load_times();
lilina_save_times($time_table); //Thu, 22 Feb 2007 03:09:43 GMT
echo '<opml version="1.1">
	<head>
<title>' . $settings['sitename'] . '</title>
<dateModified>' . date('D, j M Y G:i:s O') . '</dateModified>
</head>
	<body>';
if(has_feeds()) {
	foreach(get_feeds() as $feed) { 
	echo '
	<outline text="' .  htmlspecialchars($feed['name']) .
	'" title="' .   htmlspecialchars($feed['name']) .
	'" type="rss" xmlUrl="' . htmlspecialchars($feed['feed']) .
	'" htmlUrl="' . htmlspecialchars($feed['url']) . '" />';
	}
}
else {
	//Already handled above; if there are no feeds, then there should be no items...
}
echo '</body></opml>';
?>