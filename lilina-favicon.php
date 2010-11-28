<?php
/**
 * lilina-favicon.php - Displays SimplePie favicons
 *
 * Generates a Atom feed from the available items.
 * Thanks to Feed on Feeds' favicon.php for inspiration
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @author Feed on Feeds Team
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

define('LILINA_PAGE', 'favicon');

// Hide errors
ini_set('display_errors', false);

require_once(LILINA_INCPATH . '/contrib/simplepie.class.php');

if(!isset($_GET['i']))
	die();

require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');

function faux_hash($input) { return $input; }

if($_GET['i'] != 'default' && file_exists(LILINA_CACHE_DIR . $_GET['i'] . '.spi')) {
	SimplePie_Misc::display_cached_file($_GET['i'], LILINA_CONTENT_DIR . '/system/cache', 'spi', 'SimplePie_Cache', 'faux_hash');
}
else {
	require_once(LILINA_INCPATH . '/core/class-templates.php');
	Locale::load_default_textdomain();

	header('Content-Type: image/png');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days

	echo file_get_contents(Templates::get_file('feed.png'));
	die();
}

?>