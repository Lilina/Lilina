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

define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');

if(!isset($_GET['i']))
	die();

require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');

if($_GET['i'] != 'default' && file_exists(LILINA_PATH . '/cache/' . $_GET['i'] . '.spi')) {
    SimplePie_Misc::display_cached_file($_GET['i'], LILINA_PATH . '/cache', 'spi');
}
else {
	require_once(LILINA_INCPATH . '/core/class-templates.php');
	require_once(LILINA_INCPATH . '/core/l10n.php');
    header(sprintf('Location: %s', Templates::path_to_url( Templates::get_file('feed.png') )));
}

?>