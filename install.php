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

require_once(LILINA_INCPATH . '/core/Lilina.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/install-functions.php');
require_once(LILINA_INCPATH . '/core/file-functions.php');
require_once(LILINA_INCPATH . '/core/version.php');

/**#@+
 * Dummy function, for use before Lilina is installed.
 */
if (!function_exists('apply_filters')) {
	function apply_filters($name, $value) {
		return $value;
	}
}
/**#@-*/

Lilina::level_playing_field();

if(version_compare('5.2', phpversion(), '>'))
	Lilina::nice_die('<p>Your server is running PHP version ' . phpversion() . ' but Lilina needs PHP 5.2 or newer</p>');

//Make sure Lilina's not installed
if (Lilina::is_installed()) {
	if (Lilina::settings_current()) {
		Lilina::nice_die('<p>Lilina is already installed. <a href="index.php">Head back to the main page</a></p>');
	}

	if (!isset($_GET['action']) || $_GET['action'] !== 'upgrade') {
		Lilina::nice_die('<p>Your installation of Lilina is out of date. Please <a href="install.php?action=upgrade">upgrade your settings</a>.</p>');
	}

	Lilina_Upgrader::run();

	// We should have already died by now, but if not...
	die();
}

global $installer;
$installer = new Installer();

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

switch($page) {
	case 1:
		Installer::header();
?>
<h1 id="title">Setting Up</h1>
<p>To install, we're going to need some quick details for your site. This includes the title and setting up your administrative user.</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<fieldset id="general">
		<h2>General Settings</h2>
		<div class="row">
			<label for="sitename">Name of site</label>
			<input type="text" value="<?php echo (!$sitename) ? 'Lilina' : $sitename;?>" name="sitename" id="sitename" class="input" size="40" />
			<p class="sidenote">Give your site something to identify it by. This can be changed later.</p>
		</div>
	</fieldset>
	<fieldset id="security">
		<h2>Security Settings</h2>
		<div class="row">
			<label for="username">Admin Username</label>
			<input type="text" value="<?php echo (!$username) ? 'admin' : $username;?>" name="username" id="username" class="input" size="40" />
			<p class="sidenote">&ldquo;admin&rdquo; probably isn&apos;t the best choice, but it&apos;ll do.</p>
		</div>
		<div class="row">
			<label for="password">Admin Password</label>
			<input type="text" value="<?php echo (!$password) ? generate_password() : $password;?>" name="password" id="password" class="input" size="40" />
			<p class="sidenote">Pick something strong and memorable. If you forget this, you might have to reinstall!</p>
		</div>
	</fieldset>
	<input type="hidden" value="2" name="page" id="page" />
	<input type="submit" value="Next" class="submit" />
</form>
<?php
		Installer::footer();
		break;
	case 2:
		$installer->install($sitename, $username, $password);
		break;
	default:
		Installer::header();
?>
<h1 id="title">Installation</h1>
<p>Welcome to Lilina installation. We're now going to start installing. Make sure that the <code>content/system/</code> directory and all subdirectories are <a href="readme.html#permissions">writable</a>.</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="Install" class="submit" />
</form>
<?php
		Installer::footer();
		break;
}