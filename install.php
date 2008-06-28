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
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
header('Content-Type: text/html; charset=UTF-8');

require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/install-functions.php');

if(version_compare('4.3', phpversion(), '>'))
	lilina_nice_die('<p>Your server is running PHP version ' . phpversion() . ' but Lilina needs PHP 4.3 or newer</p>');

//Make sure Lilina's not installed
if(lilina_is_installed()) {
	require_once(LILINA_PATH . '/inc/core/conf.php');
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

/**
 * Generates a random password for the user
 *
 * Thanks goes to Jon Haworth for this function
 * @author Jon Haworth <jon@laughing-buddha.net>
 * @param int $length Length of generated password
 * @return string
 */
function generate_password ($length = 8) {
	// start with a blank password
	$password = '';
	// define possible characters
	$possible = '0123456789bcdfghjkmnpqrstvwxyz';
	// set up a counter
	$i = 0;
	// add random characters to $password until $length is reached
	while ($i < $length) { 
		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
		// we don't want this character if it's already in the password
		if (!strstr($password, $char)) { 
			$password .= $char;
			++$i;
		}
	}
	// done!
	return $password;
}

/**
 * compatibility_test() - Check that the system can run Lilina
 *
 * {@internal Missing Long Description}}
 * @author SimplePie
 */
function compatibility_test() {
	$errors = array();
	$warnings = array();
	$output = "<p>The following errors were found with your installation:</p>";
	$xml_ok = extension_loaded('xml');
	$pcre_ok = extension_loaded('pcre');
	$zlib_ok = extension_loaded('zlib');
	$mbstring_ok = extension_loaded('mbstring');
	$iconv_ok = extension_loaded('iconv');
	if($xml_ok && $pcre_ok && $mbstring_ok && $iconv_ok && $zlib_ok)
		return;
	if(!$xml_ok)
		$errors[] = "<strong>XML:</strong> Your PHP installation doesn't support XML parsing.";
	if(!$pcre_ok)
		$errors[] = "<strong>PCRE:</strong> Your PHP installation doesn't support Perl-Compatible Regular Expressions.";
	if(!$iconv_ok && !$mbstring_ok)
		$errors[] = "<strong>mbstring and iconv</strong>: You do not have either of these extensions installed. Lilina requires at least one of these in order to function properly.";
	elseif(!$iconv_ok)
		$warnings[] = "<strong>iconv:</strong> <code>mbstring</code> is installed, but <code>iconv</code> is not. This means that not all character encodings or translations will be supported.";
	elseif(!$mbstring_ok)
		$warnings[] = "<strong>mbstring:</strong> <code>iconv</code> is installed, but <code>mbstring</code> is not. This means that not all character encodings or translations will be supported.";
	if(!$zlib_ok)
		$warnings[] = "<strong>Zlib:</strong> The <code>Zlib</code> extension is not available. You will not be able to use feeds with GZIP compression.";

	if(!empty($errors)) {
		$output .= "\n<h2>Errors</h2>\n<ul>\n<li>";
		$output .= implode(" <em>Looks like Lilina won't run.</em></li>\n<li>", $errors);
		$output .= "</li>\n</ul>\n";
	}

	if(!empty($warnings)) {
		$output .= "\n<h2>Warnings</h2>\n<ul>\n<li>";
		$output .= implode(" <em>This might cause some problems with some feeds.</em></li>\n<li>", $warnings);
		$output .= "</li>\n</ul>\n";
	}

	if(empty($errors)) {
		$output .= "<p>These warnings might cause some feeds not to be read properly, however <em>you will be able to run Lilina.</em></p>\n";
		$output .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		$output .= '<input type="hidden" name="page" value="1" /><input type="hidden" name="skip" value="1" />';
		$output .= '<input class="submit" type="submit" value="Continue" /></form>';
		$output .= "<p id='footnote-quote'>Danger, Will Robinson! &mdash; <em>Lost in Space</em></p>";
		lilina_nice_die($output, 'Whoops!');
	}

	else {
		$output .= '<p>These errors mean that <em>you will not be able to run Lilina.</em></p>';
		$output .= "<p id='footnote-quote'>Kosa moja haliachi mke &mdash; <em>Swahili proverb ('One mistake isn't reason enough to leave your wife')</em></p>";
		lilina_nice_die($output, 'Uh oh!');
	}
}

function install($sitename, $username, $password) {
	require_once(LILINA_INCPATH . '/core/version.php');

	$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$guessurl = preg_replace('|/install\.php.*|i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	if($guessurl[count($guessurl)-1] != '/') {
		$guessurl .= '/';
	}
	?>
<?php
		flush();
		$raw_php		= "<?php
// What you want to call your Lilina installation
\$settings['sitename'] = '" . addslashes($sitename) . "';

// The URL to your server
\$settings['baseurl'] = '$guessurl';

// Username and password to log into the administration panel
// 'pass' is MD5ed
\$settings['auth'] = array(
							'user' => '$username',
							'pass' => '" . md5($password) . "'
							);

// All the enabled plugins, stored in a serialized string
\$settings['enabled_plugins'] = '';

// Version of these settings; don't change this
\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";
?>";
		if( !is_writable(LILINA_PATH . '/conf/')
			|| !($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+'))
			|| !is_resource($settings_file) ) {
			?>
			<h1>Uh oh!</h1>
			<p>Something happened and <code><?php echo LILINA_PATH; ?>/conf/settings.php</code> couldn't be created. Check that the server has <a href="readme.html#permissions">permission</a> to create it.</p>
			<form action="<?php /** @todo This is unsafe. Convert */ echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="sitename" value="<?php echo $sitename; ?>" />
			<input type="hidden" name="username" value="<?php echo $username; ?>" />
			<input type="hidden" name="password" value="<?php echo $password; ?>" />
			<input type="hidden" name="page" value="2">
			<input class="submit" type="submit" value="Try again?" />
			<p>If this keeps happening and you can't work out why, check out the <a href="http://getlilina.org/docs/">documentation</a>. If you still can't work it out, try asking on <a href="http://getlilina.org/forums/">the forums</a>.</p>
			</form>
			<?php
			return false;
		}
		if(file_exists(LILINA_PATH . '/conf/feeds.data')) {
			echo "<p>Using existing feeds data</p>\n";
		}
		else {
			$feeds_file = @fopen(LILINA_PATH . '/conf/feeds.data', 'w+');
			if(is_resource($feeds_file)) {
				$data['version'] = $lilina['feed-storage']['version'];
				$sdata	= base64_encode(serialize($data)) ;
				if(!$feeds_file) {
					?>
					<h1>Uh oh!</h1>
					<p>Something happened and <code><?php echo LILINA_PATH; ?>/conf/feeds.data</code> couldn't be created. Check that the server has <a href="readme.html#permissions">permission</a> to create it.</p>
					<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<input type="hidden" name="sitename" value="<?php echo $sitename; ?>" />
					<input type="hidden" name="username" value="<?php echo $username; ?>" />
					<input type="hidden" name="password" value="<?php echo $password; ?>" />
					<input type="hidden" name="page" value="2">
					<input type="submit" value="Try again?" />
					<p>If this keeps happening and you can't work out why, check out the <a href="http://getlilina.org/docs/">documentation</a>. If you still can't work it out, try asking on <a href="http://getlilina.org/forums/">the forums</a>.</p>
					</form>
					<?php
					return false;
				}
				fputs($feeds_file, $sdata);
				fclose($feeds_file);
			}
			else {
				echo "<p>Couldn't create <code>conf/feeds.data</code>. Please ensure you create this yourself and make it writable by the server</p>\n";
			}
		}

		/** Make sure it's writable now */
		if(!is_writable(LILINA_PATH . '/conf/feeds.data')) {
			/** We'll try this first */
			chmod(LILINA_PATH . '/conf/feeds.data', 0644);
			if(!is_writable(LILINA_PATH . '/conf/feeds.data')) {
				/** Nope, let's give group permissions too */
				chmod(LILINA_PATH . '/conf/feeds.data', 0664);
				if(!is_writable(LILINA_PATH . '/conf/feeds.data')) {
					/** Still no dice, give write permissions to all */
					chmod(LILINA_PATH . '/conf/feeds.data', 0666);
					if(!is_writable(LILINA_PATH . '/conf/feeds.data')) {
						/** OK, we can't make it writable ourselves. Tell the user this */
						echo "<p>Couldn't make <code>conf/feeds.data</code> writable. Please ensure you make it writable yourself</p>\n";
					}
				}
			}
		}

		fputs($settings_file, $raw_php);
		fclose($settings_file);
?>
<h1>Installation Complete!</h1>
<p>Lilina has been installed and is now ready to go. Please note your username and password below, as it won't be shown again!</p>
<dl>
	<dt>Your username is</dt>
	<dd id="username"><?php echo $username;?></dd>
	<dt>and your password is</dt>
	<dd id="password"><?php echo $password;?></dd>
</dl>
<p>We can <a href="admin.php?page=first-run">help you get started</a>, or if you know what you're doing, <a href="admin.php">head straight for the admin panel</a>.</p>
<?php
		return true;
}


/**
 * upgrade() - Run upgrade processes on supplied data
 *
 * {{@internal Missing Long Description}}}
 */
function upgrade() {
	global $lilina;
	require_once(LILINA_INCPATH . '/core/plugin-functions.php');
	require_once(LILINA_INCPATH . '/core/feed-functions.php');
	require_once(LILINA_INCPATH . '/core/misc-functions.php');

	/** Rename possible old files */
	if(@file_exists(LILINA_PATH . '/.myfeeds.data'))
		rename(LILINA_PATH . '/.myfeeds.data', LILINA_PATH . '/conf/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.myfeeds.data'))
		rename(LILINA_PATH . '/conf/.myfeeds.data', LILINA_PATH . '/conf/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.feeds.data'))
		rename(LILINA_PATH . '/conf/.feeds.data', LILINA_PATH . '/conf/feeds.data');


	if(@file_exists(LILINA_PATH . '/conf/feeds.data')) {
		$feeds = file_get_contents(LILINA_PATH . '/conf/feeds.data');
		$feeds = unserialize( base64_decode($feeds) );

		/** Are we pre-versioned? */
		if(!isset($feeds['version'])){

			/** Is this 0.7? */
			if(!is_array($feeds['feeds'][0])) {
				/** 1 dimensional array, each value is a feed URL string */
				foreach($feeds['feeds'] as $new_feed) {
					add_feed($new_feed);
				}
				global $data;
				$data = $feeds;
				$data['version'] = $lilina['feed-storage']['version'];
				save_feeds();
			}

			/** We must be in between 0.7 and r147, when we started versioning */
			elseif(!isset($feeds['feeds'][0]['url'])) {
				foreach($feeds['feeds'] as $new_feed) {
					add_feed($new_feed['feed'], $new_feed['name']);
				}
				global $data;
				$data = $feeds;
				$data['version'] = $lilina['feed-storage']['version'];
				save_feeds();
			}

			/** The feeds are up to date, but we don't have a version */
			else {
				global $data;
				$data = $feeds;
				$data['version'] = $lilina['feed-storage']['version'];
				save_feeds();
			}

		}
		elseif($feeds['version'] != $lilina['feed-storage']['version']) {
			switch($feeds['version']):
				case 147:
					/** We had a b0rked upgrader, so we need to make sure everything is okay */
					foreach($feeds['feeds'] as $this_feed) {
						
					}
			endswitch;
			global $data;
			$data = $feeds;
			$data['version'] = $lilina['feed-storage']['version'];
			save_feeds();
		}
		else {
		}
	} //end file_exists()


	/** Just in case... */
	unset($BASEURL);
	require(LILINA_PATH . '/conf/settings.php');

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

		if(!($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+')) || !is_resource($settings_file)) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving conf/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif(!isset($settings['settings_version'])) {
		// Between 0.7 and r147
		// Fine to just use existing settings
		$raw_php		= file_get_contents(LILINA_PATH . '/conf/settings.php');
		$raw_php		= str_replace('?>', "// Version of these settings; don't change this\n" .
							"\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";\n?>", $raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+')) || !is_resource($settings_file)) {
			lilina_nice_die('<p>Failed to upgrade settings: Saving conf/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif($settings['settings_version'] != $lilina['settings-storage']['version']) {
		
	}
	lilina_nice_die('<p>Your installation has been upgraded successfully. Now, <a href="index.php">get back to reading!</a></p>', 'Upgrade Successful');
}

require_once(LILINA_INCPATH . '/core/misc-functions.php');
lilina_level_playing_field();

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
	compatibility_test();
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
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
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
	<input type="submit" value="Next &raquo;" class="submit" />
</form>
<?php
		break;
	case 2:
		install($sitename, $username, $password);
		break;
	case 0:
	case false:
	default:
?>
<h1>Installation</h1>
<p>Welcome to Lilina installation. We're now going to start installing. Make sure that both the <code>conf/</code> and <code>cache/</code> directories exist and are <a href="readme.html#permissions">writable</a>.</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="Install &raquo;" class="submit" />
</form>
<?php
		break;
}
?>
			<img id="logo" src="inc/templates/default/logo-small.png" alt="Lilina Logo" />
		</div>
	</body>
</html>