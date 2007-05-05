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
//Check installed
require_once('./inc/core/install-functions.php');
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
require_once('./inc/core/conf.php');
$data		= file_get_contents($settings['files']['feeds']) ;
$data		= unserialize( base64_decode($data) ) ;
//Old functions, not yet migrated
require_once('./inc/core/lib.php');
//Our current version
require_once('./inc/core/version.php');

//Authentication Section
//Start the session
session_start();
//Check if we are logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
	//Not logged in, lets load the authentication script
	require_once('./inc/core/auth-functions.php');
	$authed = lilina_admin_auth($_POST['user'], $_POST['pass']);
}
if($_GET['logout'] == 'logout') {
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
	require_once('./inc/contrib/parseopml.php');
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
//			import (OPML)
switch($action){
	case 'flush':
		//Would have a switch here, but it's unnecessary
		//and I don't know about having two switches, might
		//screw up :)
		if($product == 'magpie'){
			define('MAGPIE_CACHE_AGE',1) ;			
		}
		elseif($product == 'lilina'){
			//Once again, from
			//http://www.ilovejackdaniels.com/php/caching-output-in-php/
			$cachedir = $settings['cachedir'];
			if ($handle = @opendir($cachedir)) {
				while (false !== ($file = @readdir($handle))) {
					if ($file != '.' and $file != '..') {
						$result = $file . ' deleted.<br />';
						@unlink($cachedir . '/' . $file);
					}
				}
				@closedir($handle);
			}
		}
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
		$data['feeds'][count($data['feeds'])]['feed']	= $url;
		$data['feeds'][count($data['feeds'])]['name']	= $name;
		$data['feeds'][count($data['feeds'])]['cat']	= $category;
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		fputs($fp,$sdata) ;
		fclose($fp) ;
		$result	.= 'Added feed ' . $name . ' with URL as ' . htmlentities($url);
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
<link rel="stylesheet" type="text/css" href="templates/default/admin.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>
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
			}?>>Home</a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=feeds"<?php
			if($page=='feeds'){
			echo ' class="current"';
			}?>>Feeds</a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings"<?php
			if($page=='settings'){
			echo ' class="current"';
			}?>>Settings</a>
		</li>
		<li>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?logout=logout">
			Logout</a>
	</ul>
</div>
<div id="main">
<?php
if($out_page){
	require_once('./inc/pages/'.$out_page);
}
else {
	echo 'No page selected';
}
?>
</div>
</div>
</body>
</html>
