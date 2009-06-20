<?php
/**
 * Installation of Lilina
 *
 * Installation functions including
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
define('LILINA_CONTENT_DIR', LILINA_PATH . '/content');
header('Content-Type: text/html; charset=UTF-8');

require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/install-functions.php');
require_once(LILINA_INCPATH . '/core/file-functions.php');
lilina_level_playing_field();

if(version_compare('5.2', phpversion(), '>'))
	lilina_nice_die('<p>Your server is running PHP version ' . phpversion() . ' but Lilina needs PHP 5.2 or newer</p>');

//Make sure Lilina's not installed
if(lilina_is_installed()) {
	if( !lilina_settings_current() ) {
		if(isset($_GET['action']) && $_GET['action'] == 'upgrade') {
			upgrade();
		}
		else {
			lilina_nice_die('<p>Your installation of Lilina is out of date. Please <a href="install.php?action=upgrade">upgrade your settings</a> first</p>');
		}
	}
	else {
		lilina_nice_die('<p>Lilina is already installed. <a href="index.php">Head back to the main page</a></p>');
	}
}

global $installer;
$installer = new Installer();

/**
 * upgrade() - Run upgrade processes on supplied data
 *
 * {{@internal Missing Long Description}}}
 */
function upgrade() {
	global $lilina;
	//require_once(LILINA_INCPATH . '/core/plugin-functions.php');
	require_once(LILINA_INCPATH . '/core/feed-functions.php');
	require_once(LILINA_INCPATH . '/core/version.php');
	require_once(LILINA_INCPATH . '/core/misc-functions.php');

	/** Rename possible old files */
	if(@file_exists(LILINA_PATH . '/.myfeeds.data'))
		rename(LILINA_PATH . '/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.myfeeds.data'))
		rename(LILINA_PATH . '/conf/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.feeds.data'))
		rename(LILINA_PATH . '/conf/.feeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/feeds.data'))
		rename(LILINA_PATH . '/conf/feeds.data', LILINA_PATH . '/content/system/config/feeds.data');

	if(@file_exists(LILINA_PATH . '/conf/settings.php'))
		rename(LILINA_PATH . '/conf/settings.php', LILINA_PATH . '/content/system/config/settings.php');

	require_once(LILINA_PATH . '/inc/core/conf.php');

	if(@file_exists(LILINA_PATH . '/content/system/config/feeds.data')) {
		$feeds = file_get_contents(LILINA_PATH . '/content/system/config/feeds.data');
		$feeds = unserialize( base64_decode($feeds) );

		/** Are we pre-versioned? */
		if(!isset($feeds['version'])){

			/** Is this 0.7? */
			if(!is_array($feeds['feeds'][0])) {
				/** 1 dimensional array, each value is a feed URL string */
				foreach($feeds['feeds'] as $new_feed) {
					add_feed($new_feed);
				}
			}

			/** We must be in between 0.7 and r147, when we started versioning */
			elseif(!isset($feeds['feeds'][0]['url'])) {
				foreach($feeds['feeds'] as $new_feed) {
					add_feed($new_feed['feed'], $new_feed['name']);
				}
			}

			/** The feeds are up to date, but we don't have a version */
			else {
			}

		}
		elseif($feeds['version'] != $lilina['feed-storage']['version']) {
			/** Note the lack of breaks here, this means the cases cascade */
			switch(true) {
				case $feeds['version'] < 147:
					/** We had a b0rked upgrader, so we need to make sure everything is okay */
					foreach($feeds['feeds'] as $this_feed) {
						
					}
				case $feeds['version'] < 237:
					/** We moved stuff around this version, but we've handled that above. */
			}
		}
		else {
		}
		global $data;
		$data = $feeds;
		$data['version'] = $lilina['feed-storage']['version'];
		save_feeds();
	} //end file_exists()


	/** Just in case... */
	unset($BASEURL);
	require(LILINA_PATH . '/content/system/config/settings.php');

	if(isset($BASEURL) && !empty($BASEURL)) {
		// 0.7 or below
		$raw_php		= "<?php
// What you want to call your Lilina installation
\$settings['sitename'] = '$SITETITLE';\n
// The URL to your server
\$settings['baseurl'] = '$BASEURL';\n
// Username and password to log into the administration panel\n// 'pass' is MD5ed
\$settings['auth'] = array(
							'user' => '$USERNAME',
							'pass' => '" . md5($PASSWORD) . "'
							);\n
// All the enabled plugins, stored in a serialized string
\$settings['enabled_plugins'] = '';\n
// Version of these settings; don't change this
\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";\n?>";

		if(!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving content/system/config/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif(!isset($settings['settings_version'])) {
		// Between 0.7 and r147
		// Fine to just use existing settings
		$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
		$raw_php		= str_replace('?>', "// Version of these settings; don't change this\n" .
							"\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";\n?>", $raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+')) || !is_resource($settings_file)) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving content/system/config/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif($settings['settings_version'] != $lilina['settings-storage']['version']) {
		/** Note the lack of breaks here, this means the cases cascade */
		switch(true) {
			case $settings['settings_version'] < 237:
				/** We moved stuff around this version, but we've handled that above. */
			case $settings['settings_version'] < 297:
				new_options_297();
			case $settings['settings_version'] < 302:
				new_options_302();
			case $settings['settings_version'] < 339:
				new_options_339();
			case $settings['settings_version'] < 368:
				new_options_368();
		}

		$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
		$raw_php		= str_replace(
			"\$settings['settings_version'] = " . $settings['settings_version'] . ";",
			"\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";",
			$raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving content/system/config/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);

		require_once(LILINA_INCPATH . '/core/class-datahandler.php');
		if(!save_options()) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving content/system/config/options.data failed</p>', 'Upgrade failed');
		}
	}

	$string = '';
	if(count(MessageHandler::get()) === 0) {
		lilina_nice_die('<p>Your installation has been upgraded successfully. Now, <a href="index.php">get back to reading!</a></p>', 'Upgrade Successful');
		return;
	}
	else
		$string .= '<p>Your installation has <strong>not</strong> been upgraded successfully. Here\'s the error:</p><ul><li>';

	lilina_nice_die($string . implode('</li><li>', MessageHandler::get()) . '</li></ul>', 'Upgrade failed');
}

function default_options() {
	new_options_297();
	new_options_302();
	new_options_339();
	new_options_368();
}
function new_options_297() {
	global $options;
	$options['offset']					= 0;
	$options['encoding']				= 'utf-8';
	if(empty($options['template']))
		$options['template'] = 'default';
	if(empty($options['locale']))
		$options['locale'] = 'en';
}
function new_options_302() {
	global $options;
	$options['timezone'] = 'UTC';
}
/**
 * It appears we missed this at some point
 */
function new_options_339() {
	global $options;
	if(empty($options['encoding']))
		$options['encoding'] = 'utf-8';
}
function new_options_368() {
	global $options, $settings;
	if(empty($options['sitename'])) {
		if(!empty($settings['sitename']))
			$options['sitename'] = $settings['sitename'];
		else
			$options['sitename'] = 'Lilina News Aggregator';
	}
}

function create_settings_file() {
	
}

//Initialize variables
if(!empty($_POST['page'])) {
	$page				= htmlspecialchars($_POST['page']);
}
elseif(!empty($_GET['page'])) {
	$page				= htmlspecialchars($_GET['page']);
}
else {
	$page				= false;
}
$from					= (isset($_POST['from'])) ? htmlspecialchars($_POST['from']) : false;
$sitename				= isset($_POST['sitename']) ? $_POST['sitename'] : false;
$username				= isset($_POST['username']) ? $_POST['username'] : false;
$password				= isset($_POST['password']) ? $_POST['password'] : false;
$error					= ((!$sitename || !$username || !$password) && $page && $page != 1) ? true : false;

if($page === "1" && !isset($_REQUEST['skip']))
	$installer->compatibility_test();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Installation - Lilina News Aggregator</title>
		<style type="text/css">
			@import "install.css";
		</style>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	</head>
	<body>
		<div id="container">
<?php
switch($page) {
	case 1:
?>
<h1>Setting Up</h1>
<p>To install, we're going to need some quick details for your site. This includes the title and setting up your administrative user.</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<fieldset id="general">
		<legend>General Settings</legend>
		<div class="row<?php if(!$sitename) echo ' highlight';?>">
			<label for="sitename">Name of site</label>
			<input type="text" value="<?php echo (!$sitename) ? 'Lilina News Aggregator' : $sitename;?>" name="sitename" id="sitename" class="input" size="40" />
		</div>
	</fieldset>
	<fieldset id="security">
		<legend>Security Settings</legend>
		<div class="row<?php if(!$username) echo ' highlight';?>">
			<label for="username">Admin Username</label>
			<input type="text" value="<?php echo (!$username) ? 'admin' : $username;?>" name="username" id="username" class="input" size="40" />
		</div>
		<div class="row<?php if(!$password) echo ' highlight';?>">
			<label for="password">Admin Password</label>
			<input type="text" value="<?php echo (!$password) ? generate_password() : $password;?>" name="password" id="password" class="input" size="40" />
		</div>
	</fieldset>
	<input type="hidden" value="2" name="page" id="page" />
	<input type="submit" value="Next" class="submit" />
</form>
<?php
		break;
	case 2:
		$installer->install($sitename, $username, $password);
		break;
	default:
?>
<h1>Installation</h1>
<p>Welcome to Lilina installation. We're now going to start installing. Make sure that the <code>content/</code> directory is <a href="readme.html#permissions">writable</a>.</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="Install" class="submit" />
</form>
<?php
		break;
}
?>
		</div>
	</body>
</html>