<?php
// $Id$
/**
 * Initialization page
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
$settings	= array();
error_reporting(E_ALL);


require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
$timer_start = lilina_timer_start();

/** Current Version */
require_once(LILINA_INCPATH . '/core/version.php');

//Caching to reduce loading times
require_once(LILINA_INCPATH . '/core/cache.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/file-functions.php');

//Templating functions
require_once(LILINA_INCPATH . '/core/skin.php');

spl_autoload_register('__autoload');

do_action('init');
Templates::load();
?>