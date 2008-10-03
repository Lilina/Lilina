<?php
/**
 * Backwards compatibility redirect
 *
 * Redirects to the new admin/ directory
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @author WordPress Team
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
require_once(LILINA_INCPATH . '/core/conf.php');

header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $settings['baseurl'] . 'admin/');
header('Connection: close');
die();