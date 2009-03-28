<?php
/**
* OPML feeds export, different to the items export on rss.php
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
 * @todo Document
 */
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

/** Current Version */
require_once(LILINA_INCPATH . '/core/version.php');
require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');

//Plugins and misc stuff
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
//require_once(LILINA_INCPATH . '/core/l10n.php');
Locale::load_default_textdomain();

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/file-functions.php');

//Templating functions
require_once(LILINA_INCPATH . '/core/skin.php');

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="', get_option('encoding'), '"?'.'>'; ?>

<opml version="1.1">
	<head>
		<title><?php echo get_option('sitename'); ?></title>
		<dateModified><?php /** This is unclean */ echo date('D, j M Y G:i:s O'); ?></dateModified>
	</head>
	<body>
<?php
if(has_feeds()) {
	foreach(get_feeds() as $feed) {
		$feed = array_map('htmlspecialchars', $feed)
		?>
		<outline text="<?php echo $feed['name']; ?>" title="<?php echo $feed['name']; ?>" type="rss" xmlUrl="<?php echo $feed['feed']; ?>" htmlUrl="<?php echo $feed['url']; ?>" />
		<?php
	}
}
?>
	</body>
</opml>