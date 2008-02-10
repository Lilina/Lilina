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

/**
 * Stops hacking later on
 */
define('LILINA', 1);
header('Content-Type: text/html; charset=UTF-8');

//Make sure Lilina's not installed
if(@file_exists('./conf/settings.php')) {
	define('LILINA_PATH', dirname(__FILE__));
	require_once(LILINA_PATH . '/inc/core/conf.php');
	if(( !isset($settings['settings_version']) || $settings['settings_version'] > $lilina['settings-storage']['version'] )
		&& (isset($_GET['action']) && $_GET['action'] == 'upgrade')) {
		die('<p>Your installation of Lilina is out of date. Please <a href="install.php?action=upgrade">upgrade your settings</a> first</p>');
	}
	elseif(!isset($_GET['action']) || $_GET['action'] != 'upgrade') {
		die('<p>Lilina is already installed. <a href="index.php">Head back to the main page</a></p>');
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

function install($sitename, $username, $password) {
	require_once('./inc/core/version.php');

	$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$guessurl = preg_replace('|/install\.php.*|i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	if($guessurl[count($guessurl)-1] != '/') {
		$guessurl .= '/';
	}
	?>
<p>Now saving settings to conf/settings.php - Stand by...</p>
<?php
		flush();
		$raw_php		= "<?php
// What you want to call your Lilina installation
\$settings['sitename'] = '$sitename';

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
		/** Make sure it's writable now */
		if(!is_writable('./conf/feeds.data')) {
			/** We'll try this first */
			chmod('./conf/', 0644);
			if(!is_writable('./conf/')) {
				/** Nope, let's give group permissions too */
				chmod('./conf/', 0664);
				if(!is_writable('./conf/')) {
					/** Still no dice, give write permissions to all */
					chmod('./conf/', 0666);
					if(!is_writable('./conf/')) {
						/** OK, we can't make it writable ourselves. Tell the user this */
						echo "<p>Couldn't create <code>conf/feeds.data</code>. Please ensure you create this yourself and make it writable by the server</p>\n";
					}
				}
			}
		}
		if( !is_writable('./conf/')
			|| !($settings_file = @fopen('./conf/settings.php', 'w+'))
			|| !is_resource($settings_file) ) {
			?>
			<p>Please make sure that the conf directory is writable and that I can create <code>conf/settings.php</code></p>
			<p>You can also try saving the following as <code>conf/settings.php</code></p>
<pre>
<?php highlight_string($raw_php); ?>
</pre>
			<form action="<?php /** @todo This is unsafe. Convert */ echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="sitename" value="<?php echo $sitename; ?>" />
			<input type="hidden" name="username" value="<?php echo $username; ?>" />
			<input type="hidden" name="password" value="<?php echo $password; ?>" />
			<input type="hidden" name="page" value="2">
			<input type="submit" value="Try again?" />
			</form>
			<?php
			return false;
		}
		if(file_exists('./conf/feeds.data')) {
			echo "<p>Using existing feeds</p>\n";
		}
		else {
			$feeds_file = @fopen('./conf/feeds.data', 'w+');
			if(is_resource($feeds_file)) {
				fclose($feeds_file) ;
			}
			else {
				echo "<p>Couldn't create <code>conf/feeds.data</code>. Please ensure you create this yourself and make it writable by the server</p>\n";
			}
		}
		/** Make sure it's writable now */
		if(!is_writable('./conf/feeds.data')) {
			/** We'll try this first */
			chmod('./conf/feeds.data', 0644);
			if(!is_writable('./conf/feeds.data')) {
				/** Nope, let's give group permissions too */
				chmod('./conf/feeds.data', 0664);
				if(!is_writable('./conf/feeds.data')) {
					/** Still no dice, give write permissions to all */
					chmod('./conf/feeds.data', 0666);
					if(!is_writable('./conf/feeds.data')) {
						/** OK, we can't make it writable ourselves. Tell the user this */
						echo "<p>Couldn't create <code>conf/feeds.data</code>. Please ensure you create this yourself and make it writable by the server</p>\n";
					}
				}
			}
		}
		// I don't think we need this any more
		/*if(!@file_exists('./conf/time.data')) {
			$times_file = @fopen('./conf/time.data', 'w+');
			if(is_resource($times_file)) {
				fclose($times_file);
			}
		}*/
		fputs($settings_file, $raw_php);
		fclose($settings_file);
?>
<p>Lilina has been set up on your server and is ready to run. Open <a href="index.php">your home page</a> and get reading!</p>
<dl>
	<dt>Username</dt>
	<dd><?php echo $username;?></dd>
	<dt>Password</dt>
	<dd><?php echo $password;?></dd>
</dl>
<p>Were you expecting more steps? Sorry to disappoint. All done! :)</p>
<?php
		return true;
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
if(!get_magic_quotes_gpc()) {
	$sitename				= (isset($_POST['sitename'])) ? addslashes(htmlspecialchars($_POST['sitename'])) : false;
	$username				= (isset($_POST['username'])) ? addslashes(htmlspecialchars($_POST['username'])) : false;
	$password				= (isset($_POST['password'])) ? addslashes(htmlspecialchars($_POST['password'])) : false;
}
else {
	$sitename				= (isset($_POST['sitename'])) ? htmlspecialchars($_POST['sitename']) : false;
	$username				= (isset($_POST['username'])) ? htmlspecialchars($_POST['username']) : false;
	$password				= (isset($_POST['password'])) ? htmlspecialchars($_POST['password']) : false;
}
$error					= ((!$sitename || !$username || !$password) && $page && $page != 1) ? true : false;
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
			<div id="header">
				<img src="inc/templates/default/logo-small.png" alt="Lilina Logo" />
				<h1>Lilina News Aggregator</h1>
				<h2>Installation Step <?php echo $page;?></h2>
			</div>
			<div id="menu">
				<ul>
					<li><a href="http://getlilina.org/">Lilina Website</a></li>
					<li><a href="http://getlilina.org/forums/">Forums</a></li>
					<li><a href="http://getlilina.org/docs/">Documentation</a></li>
				</ul>
			</div>
			<div id="content">
<?php
if($error) {
	$page--;
}
switch($page) {
	case 1:
		if($error) {
		}
?>
<p>Let's get started on the installation!</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table style="width:100%">
		<tr><td colspan="2"><h2>General Settings</h2></td></tr>
		<tr<?php if(!$sitename) echo ' class="highlight"';?>>
			<td class="label"><label for="sitename"><span class="label">Name of site</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$sitename) ? 'Lilina News Aggregator' : $sitename;?>" name="sitename" id="sitename" size="40" /></td>
		</tr>
		<tr><td colspan="2"><h2>Security Settings</h2></td></tr>
		<tr<?php if(!$username) echo ' class="highlight"';?>>
			<td class="label"><label for="username"><span class="label">Admin Username</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$username) ? 'admin' : $username;?>" name="username" id="username" size="40" /></td>
		</tr>
		<tr<?php if(!$password) echo ' class="highlight"';?>>
			<td class="label"><label for="password"><span class="label">Admin Password</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$password) ? generate_password() : $password;?>" name="password" id="password" size="40" /></td>
		</tr>	
		<tr>
			<td colspan="2"><span class="formw">
				<input type="hidden" value="2" name="page" id="page" />
				<input type="submit" value="Next &raquo;" style="width:50%;font-size:2em;" />
			</span>
			</td>
		</tr>
	</table>
	<div style="clear:both;">&nbsp;</div>
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
<p>Welcome to Lilina installation. We're now going to start installing. Make sure that both the <code>conf/</code> and <code>cache/</code> directories exist and are <a href="readme.html#permissions">writable</a>.</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="Install &raquo;" style="width: 50%; font-size: 2em;" />
</form>
<?php
		break;
}
?>
			</div>
		</div>
		<div id="footer">
			<p>Powered by Lilina 1.0 | Copyright Lilina Development Team</p>
		</div>
	</body>
</html>