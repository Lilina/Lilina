<?php
/**
 * Functions related to installation
 * @todo Move to actual files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//Stop hacking attempts
defined('LILINA') or die('Restricted access');

/**
 * @todo Document
 */
function lilina_check_installed() {
	if(@file_exists('./conf/settings.php')) {
		return true;
	}
	return false;
}
?>