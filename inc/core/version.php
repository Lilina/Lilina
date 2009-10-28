<?php
/**
 * This holds the version number in a separate file so we can bump it without cluttering the SVN
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_CORE_VERSION', '1.0-bleeding');
define('LILINA_SETTINGS_VERSION', 480);
define('LILINA_FEEDSTORAGE_VERSION', 237);

$lilina = array(
	'core-sys'		=> array(
		'version'	=> LILINA_CORE_VERSION,
	),
	'feed-storage' => array(
		'version'	=> LILINA_FEEDSTORAGE_VERSION,
	),
	'settings-storage' => array(
		'version'	=> LILINA_SETTINGS_VERSION,
	),
);

define('LILINA_USERAGENT', 'Lilina/'. LILINA_CORE_VERSION . '; (' . get_option('baseurl') . '; http://getlilina.org/; Allow Like Gecko)');
?>