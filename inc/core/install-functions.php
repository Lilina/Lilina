<?php
/**
 * Functions related to installation
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//Stop hacking attempts
defined('LILINA') or die('Restricted access');

/**
 * lilina_check_installed() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_check_installed() {
	if(!lilina_is_installed()) {
		lilina_nice_die('<p>Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a></p>');
	}
	if(!lilina_settings_current()) {
		lilina_nice_die('<p>Your installation of Lilina is out of date. Please <a href="install.php?action=upgrade">upgrade your settings</a> first</p>');
	}
}

/**
 * lilina_is_installed() - Detects whether Lilina is installed or not and returns a boolean
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_is_installed() {
	if(file_exists(LILINA_PATH . '/conf/settings.php')) {
		return true;
	}
	return false;
}

/**
 * lilina_settings_current() - Detects whether Lilina's settings need to be updated
 *
 * {{@internal Missing Long Description}}}
 * @global Get the current settings version
 */
function lilina_settings_current() {
	global $settings;
	require_once(LILINA_PATH . '/inc/core/conf.php');
	global $lilina;
	require_once(LILINA_PATH . '/inc/core/version.php');
	if( isset($settings['settings_version']) && $settings['settings_version'] == $lilina['settings-storage']['version'] ) {
		return true;
	}
	return false;
}

/**
 * lilina_nice_die() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_nice_die($message, $title = 'Error') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title; ?> - Lilina News Aggregator</title>
		<style type="text/css">
			@import "install.css";
		</style>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	</head>
	<body>
		<div id="container">
			<div id="header">
				<img src="inc/templates/default/logo-small.png" alt="Lilina Logo" />
				<h1>Lilina News Aggregator</h1>
				<h2><?php echo $title; ?></h2>
			</div>
			<div id="menu">
				<ul>
					<li><a href="http://getlilina.org/">Lilina Website</a></li>
					<li><a href="http://getlilina.org/forums/">Forums</a></li>
					<li><a href="http://getlilina.org/docs/">Documentation</a></li>
				</ul>
			</div>
			<div id="content">
				<?php echo $message; ?>
			</div>
		</div>
	</body>
</html>
<?php
	die();
}
?>