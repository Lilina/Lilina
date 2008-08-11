<?php
/**
 * Functions related to installation
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//Stop hacking attempts
defined('LILINA_PATH') or die('Restricted access');

/**
 * lilina_check_installed() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_check_installed() {
	if(version_compare('5.2', phpversion(), '>'))
		lilina_nice_die('<p>Your server is running PHP version ' . phpversion() . ' but Lilina needs PHP 5.2 or newer</p>');

	if(!lilina_is_installed()) {
		lilina_nice_die("<p>Whoops! It doesn't look like you've installed Lilina yet. Don't panic, you can <a href='install.php'>install it now</a></p>", 'Not Installed');
	}
	if(!lilina_settings_current()) {
		lilina_nice_die("<p>Looks like Lilina is out of date! No worries, just <a href='install.php?action=upgrade'>go ahead and update</a></p>", 'Out of Date');
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

	require_once(LILINA_PATH . '/inc/core/file-functions.php');

	global $data;
	$data = lilina_load_feeds($settings['files']['feeds']);

	if( isset($settings['settings_version'])
	  && $settings['settings_version'] == $lilina['settings-storage']['version']
	  && isset($data['version'])
	  && $data['version'] == $lilina['feed-storage']['version'] ) {
		return true;
	}
	return false;
}

/**
 * lilina_nice_die() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_nice_die($message, $title = 'Whoops!', $class = false) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title; ?> &mdash; Lilina News Aggregator</title>
		<style type="text/css">
			@import "install.css";
		</style>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	</head>
	<body<?php if($class !== false) echo ' class="' . $class . '"'; ?>>
		<div id="container">
			<h1><?php echo $title; ?></h1>
			<?php echo $message; ?>

			<img id="logo" src="inc/templates/default/logo-small.png" alt="Lilina Logo" />
		</div>
	</body>
</html>
<?php
	die();
}
?>