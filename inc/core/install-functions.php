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
 * Installation tools
 *
 * Note: this class is outside of normal file structure, as it should only be
 * used in install.php
 *
 * @package Lilina
 */
class Installer {
	public function __construct() {
	}

	/**
	 * Lilina installer
	 *
	 * Installs Lilina after going through many complicated checks
	 *
	 * @param string $sitename Name of the site
	 * @param string $username Initial username of the admin user
	 * @param string $password Initial password of the admin user
	 * @return bool True if the installer succeeded, false otherwise
	 */
	public function install($sitename, $username, $password) {
		global $installer;
		require_once(LILINA_INCPATH . '/core/version.php');

		$settings = $this->generate_default_settings($sitename, $username, $password);
		if( !is_writable(LILINA_PATH . '/content/system/config/') || !($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+'))) {
			$this->file_error_notice(LILINA_PATH . '/content/system/config/settings.php', $sitename, $username, $password);
			return false;
		}
		fputs($settings_file, $settings);
		fclose($settings_file);

		if(file_exists(LILINA_PATH . '/content/system/config/feeds.data')) {
			echo "<p>Using existing feeds data</p>\n";
		}
		else {
			$feeds_file = @fopen(LILINA_PATH . '/content/system/config/feeds.data', 'w+');
			if(is_resource($feeds_file)) {
				$data['version'] = LILINA_FEEDSTORAGE_VERSION;
				$sdata	= base64_encode(serialize($data)) ;
				if(!$feeds_file) {
					$this->file_error_notice(LILINA_PATH . '/content/system/config/feeds.data', $sitename, $username, $password);
					return false;
				}
				fputs($feeds_file, $sdata);
				fclose($feeds_file);
			}
			else {
				echo "<p>Couldn't create <code>content/system/config/feeds.data</code>. Please ensure you create this yourself and make it writable by the server</p>\n";
			}
		}

		/** Make sure it's writable now */
		if(!$this->make_writable(LILINA_PATH . '/content/system/config/feeds.data')) {
			echo "<p>Couldn't make <code>content/system/config/feeds.data</code> writable. Please ensure you make it writable yourself</p>\n";
		}

		
		default_options();
		require_once(LILINA_INCPATH . '/core/class-datahandler.php');

		if(!save_options()) {
			$this->file_error_notice(LILINA_PATH . '/content/system/config/options.data', $sitename, $username, $password);
			return false;
		}
	?>
	<h1>Installation Complete!</h1>
	<p>Lilina has been installed and is now ready to go. Please note your username and password below, as it <strong>won't be shown again</strong>!</p>
	<dl>
		<dt>Your username is</dt>
		<dd id="username"><?php echo $username;?></dd>
		<dt>and your password is</dt>
		<dd id="password"><?php echo $password;?></dd>
	</dl>
	<p>We can <a href="admin/first-run.php">help you get started</a>, or if you know what you're doing, <a href="admin/">head straight for the admin panel</a>.</p>
	<?php
			return true;
	}
	
	/**
	 * Check that the system can run Lilina
	 *
	 * Checks Lilina's requirements against what the server actually has and
	 * complains if something goes wrong.
	 *
	 * @author SimplePie
	 * @return bool True if Lilina can run on this system without warnings, otherwise it does not return
	 */
	public function compatibility_test() {
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
	
	public function generate_default_settings($sitename, $username, $password) {
		$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
		$guessurl = preg_replace('|/install\.php.*|i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		if($guessurl[count($guessurl)-1] != '/') {
			$guessurl .= '/';
		}
		return "<?php
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
\$settings['settings_version'] = " . LILINA_SETTINGS_VERSION . ";
?>";
	}

	/**
	 * Attempt to make $filename writable by the server
	 *
	 * Check if $filename is writable. If not, attempt to change permissions
	 * before returning the result of is_writable()
	 *
	 * @param string $filename
	 * @return bool True if the file is writable, false if not
	 */
	public function make_writable($filename) {
		if(!is_writable($filename)) {
			chmod($filename, 0666);
		}

		clearstatcache();
		return is_writable($filename);
	}

	public function file_error_notice($filename, $sitename, $username, $password) {
		?>
		<h1>Uh oh!</h1>
		<p>Something happened and <code><?php echo $filename ?></code> couldn't be created. Check that the server has <a href="readme.html#permissions">permission</a> to create it.</p>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="sitename" value="<?php echo $sitename; ?>" />
		<input type="hidden" name="username" value="<?php echo $username; ?>" />
		<input type="hidden" name="password" value="<?php echo $password; ?>" />
		<input type="hidden" name="page" value="2">
		<input type="submit" value="Try again?" />
		<p>If this keeps happening and you can't work out why, check out the <a href="http://getlilina.org/docs/">documentation</a>. If you still can't work it out, try asking on <a href="http://getlilina.org/forums/">the forums</a>.</p>
		</form>
		<?php
	}
}

/**
 * Check if Lilina is installed and current
 *
 * Checks the PHP version and whether Lilina is installed and up-to-date
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
 * Detect whether Lilina is installed
 *
 * @return bool Whether Lilina is installed or not
 */
function lilina_is_installed() {
	if(file_exists(LILINA_PATH . '/content/system/config/settings.php')) {
		return true;
	}
	elseif(file_exists(LILINA_PATH . '/conf/settings.php')) {
		// Special case, for an old friend ;-)
		return true;
	}
	return false;
}

/**
 * Detects whether Lilina's settings need to be updated
 *
 * {{@internal Missing Long Description}}}
 * @global Get the current settings version
 */
function lilina_settings_current() {

	// Need to check this again, due to the above function
	if(!file_exists(LILINA_PATH . '/content/system/config/settings.php') && file_exists(LILINA_PATH . '/conf/settings.php')) {
		return false;
	}

	require_once(LILINA_PATH . '/inc/core/conf.php');

	global $lilina;
	require_once(LILINA_PATH . '/inc/core/version.php');

	require_once(LILINA_PATH . '/inc/core/feed-functions.php');

	load_feeds();

	global $data;
	global $settings;
	if( isset($settings['settings_version'])
	  && $settings['settings_version'] == LILINA_SETTINGS_VERSION
	  && isset($data['version'])
	  && $data['version'] == LILINA_FEEDSTORAGE_VERSION ) {
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
	$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$guessurl = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if(strpos('.', $_SERVER['REQUEST_URI']))
		$guessurl = dirname($guessurl);
	$guessurl = preg_replace('|/admin.*|i', '', $guessurl);
	$guessurl = str_replace('install.php', '', $guessurl);
	if($guessurl[count($guessurl)-1] != '/') {
		$guessurl .= '/';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title; ?> &mdash; Lilina News Aggregator</title>
		<style type="text/css">
			@import "<?php echo $guessurl ?>install.css";
		</style>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	</head>
	<body<?php if($class !== false) echo ' class="' . $class . '"'; ?>>
		<div id="container">
			<h1><?php echo $title; ?></h1>
			<?php echo $message; ?>

			<img id="logo" src="<?php echo $guessurl ?>admin/logo-small.png" alt="Lilina Logo" />
		</div>
	</body>
</html>
<?php
	die();
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
?>