<?php
/**
 * Administration page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Stop hacking attempts
 *
 * All included files (external libraries excluded) must check for presence of
 * this define (using defined() ) to avoid the files being accessed directly
 */
define('LILINA_PATH', dirname(dirname(__FILE__)));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
define('LILINA_ADMIN', 1) ;
define('LILINA_PAGE', 'admin');

global $settings;
require_once(LILINA_INCPATH . '/core/Lilina.php');
Lilina::check_installed();

Lilina_Plugins::init();
Localise::load_default_textdomain();
require_once(LILINA_INCPATH . '/core/update-functions.php');
require_once(LILINA_INCPATH . '/core/file-functions.php');
require_once(LILINA_INCPATH . '/core/version.php');
require_once(LILINA_INCPATH . '/core/feed-functions.php');
require_once(LILINA_INCPATH . '/contrib/parseopml.php');
require_once(LILINA_INCPATH . '/core/auth-functions.php');

do_action('admin_init');
do_action('init');

//Authentication Section
if(isset($_POST['user']) && isset($_POST['pass'])) {
	lilina_login_form($_POST['user'], $_POST['pass']);
}
else {
	lilina_login_form('', '');
}

/** This sanitises all input variables, so we don't have to worry about them later */
Lilina::level_playing_field();

require_once(LILINA_PATH . '/admin/includes/common.php');

if ( !empty($_REQUEST['page']) ) {
	do_action('admin_page_' . $_REQUEST['page']);
}