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

if(file_exists(LILINA_PATH . '/cache/' . md5($_GET['i']) . '.spi')) {
    SimplePie_Misc::display_cached_file($_GET['i'], LILINA_PATH . '/cache', 'spi');
}
else {
    header('Location: inc/templates/' . $settings['template'] . '/feed-icon.png');
}

?>