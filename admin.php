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
define('LILINA',1);
define('LILINA_PATH',dirname(__FILE__));
define('LILINA_INCPATH',dirname(__FILE__) . '/inc');
//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
if(!lilina_check_installed()) {
	echo 'Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a>';
	die();
}
//Protect from register_globals
$settings	= 0;
global $settings;
$authed		= 0;
$page		= (isset($_GET['page'])? $_GET['page'] : '');
$page		= htmlentities($page);
$action		= (isset($_GET['action'])? $_GET['action'] : '');
$action		= htmlentities($action);
$product	= (isset($_GET['product'])? $_GET['product'] : '');
$product	= htmlentities($product);
$name		= (isset($_GET['name'])? $_GET['name'] : '');
$name		= htmlentities($name);
$url		= (isset($_GET['url'])? $_GET['url'] : '');
$url		= htmlentities(urlencode($url));
//Require our settings, must be before $data
require_once(LILINA_INCPATH . '/core/conf.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

$data		= file_get_contents($settings['files']['feeds']) ;
$data		= unserialize( base64_decode($data) ) ;
//Old functions, not yet migrated
require_once(LILINA_INCPATH . '/core/lib.php');
//Our current version
require_once(LILINA_INCPATH . '/core/version.php');

//Authentication Section
//Start the session
session_start();
//Check if we are logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
	//Not logged in, lets load the authentication script
	require_once(LILINA_INCPATH . '/core/auth-functions.php');
	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$authed = lilina_admin_auth($_POST['user'], $_POST['pass']);
	}
	else {
		$authed = lilina_admin_auth('', '');
	}
}
if(isset($_GET['logout']) && $_GET['logout'] == 'logout') {
	//We already know we are logged in,
	//so lets unset the variable then reload the page
    unset($_SESSION['is_logged_in']);
	header('Location: ' . $_SERVER['PHP_SELF']);
	die();
}

//Misc. Functions
function get_feeds() {
	global $data;
	return $data['feeds'];
}
function import_opml($opml_file) {
	require_once(LILINA_INCPATH . '/contrib/parseopml.php');
	return parse_opml($opml_file);
}

//Navigation
switch($page) {
	case 'feeds': 
		$out_page = 'admin-feeds.php';
	break;
	case 'settings':
		$out_page = 'admin-settings.php';
	break;
	default:
		$out_page = 'admin-home.php';
	break;
}

//Actions:	flush (cache),
//			add (feed)
//			remove (feed)
//			change (feed)
//			import (OPML)
switch($action){
	case 'flush':
		//Must delete Magpie and Lilina caches at the same time
		//Lilina cache clear from
		//http://www.ilovejackdaniels.com/php/caching-output-in-php/
		$cachedir = $settings['cachedir'];
		if ($handle = @opendir($cachedir)) {
			while (false !== ($file = @readdir($handle))) {
				if ($file != '.' and $file != '..') {
					//$result .= $file . ' deleted.<br />';
					@unlink($cachedir . '/' . $file);
				}
			}
			@closedir($handle);
		}
		else {
			$result		.= _r('Error deleting files in ') . $settings['cachedir'] . ' - ' . _r('Make sure the directory is writable and PHP/Apache has the correct permissions to modify it.') . '<br />';
		}
		if($times_file = @fopen($settings['files']['times'], 'w')) fclose($times_file);
		else {
			$result		.= _r('Error clearing times from ') . $settings['files']['times'] . ' - ' . _r('Make sure the file is writable and PHP/Apache has the correct permissions to modify it.') . '<br />';
		}
		$result			.= _r('Successfully cleared cache!') . '<br />';
	break;
	case 'add':
		/*$data = array(
						'feeds' => array(
										array(
											'feed'	=> 'http://liberta-project.net/rss.xml',
											'name'	=> 'Liberta Project',
											'cat'	=> 'default'),
										array(
											'feed'	=> 'http://cubegames.net/wordpress/feed/',
											'name'	=> 'Cube Games Blog',
											'cat'	=> 'default'),
										array(
											'feed'	=> 'http://lilina.cubegames.net/feed/',
											'name'	=> 'Lilina News Aggregator Blog',
											'cat'	=> 'default')
									)
					);*/
		if(!(str_pos($url, '.rss') || str_pos($url, '.atom') || str_pos($url, '.xml'))) {
			lilina_get_rss($url);
		}
		if(empty($category)) {
			$category	= 'default';
		}
		if(empty($name)) {
			//We don't care, we'll get it from the feed
		}
		if(empty($url)) {
			//Now this we do care about
			
		}
		$feed_num	= count($data['feeds']);
		$data['feeds'][$feed_num]['feed']	= $url;
		$data['feeds'][$feed_num]['name']	= $name;
		$data['feeds'][$feed_num]['cat']	= $category;
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		fputs($fp,$sdata) ;
		fclose($fp) ;
		$result	.= _r('Added feed ') . $name . _r(' with URL as ') . htmlentities($url) . '<br />';
	break;
	case 'remove':
		//$data['feeds'][
	break;
	case 'import':
		import_opml($url);
	break;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Admin Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="inc/templates/default/admin.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>js/engine.js"></script>
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>js/fat.js"></script>
</head>
<body onload="javascript:adminLoader('<?php echo $page; ?>');">
<div id="wrap">
<div id="pagetitle">
	<h1><?php echo $settings['sitename']; ?> - Admin Panel</h1>
</div>
<div id="navigation">
	<ul class="links">
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>"<?php
			if($page=='home'){
			echo ' class="current"';
			}?>><?php _e('Home'); ?></a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=feeds"<?php
			if($page=='feeds'){
			echo ' class="current"';
			}?>><?php _e('Feeds'); ?></a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings"<?php
			if($page=='settings'){
			echo ' class="current"';
			}?>><?php _e('Settings');?></a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?logout=logout">
			<?php _e('Logout'); ?></a>
	</ul>
</div>
<div id="main">
<?php
if($out_page){
	require_once(LILINA_INCPATH . '/pages/'.$out_page);
}
else {
	echo 'No page selected';
}
?>
</div>
</div>
</body>
</html>
