<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		admin.php
Purpose:	Administration page
Notes:		Need to move all crud to plugins
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
//Stop hacking attempts
define('LILINA',1) ;

function lilina_check_installed() {
	if(@file_exists('./conf/settings.php') {
		return true;
	}
	return false;
}

function lilina_install_err($error = 0) {
	switch($error) {
		case 0:
			lilina_install_page(1);
			break;
		case 1:
		default:
			break;
	}
}

function lilina_install_page($page = 0) {
	switch($page) {
		case 0:
			require_once('./inc/pages/install-not.php');
			break;
		case 1:
		default:
			trigger_error('Page not defined');
	}
}
?>