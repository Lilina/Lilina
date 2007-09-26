<?php
/**
 * Administration page
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
//Stop hacking attempts
define('LILINA',1) ;
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
if(!lilina_check_installed()) {
	echo 'Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a>';
	die();
}
//Protect from register_globals
$settings	= 0;
global $settings;
$authed		= false;
$result		= '';
$page		= (isset($_GET['page'])? $_GET['page'] : '');
$page		= htmlentities($page);
$action		= (isset($_GET['action'])? $_GET['action'] : '');
$action		= htmlentities($action);
$product	= (isset($_GET['product'])? $_GET['product'] : '');
$product	= htmlentities($product);

//Add variables
$add_name	= (isset($_GET['add_name'])? $_GET['add_name'] : '');
$add_name	= htmlentities($add_name);
$add_url	= (isset($_GET['add_url'])? $_GET['add_url'] : '');
$add_url	= htmlentities($add_url);

//Change variables
$change_name	= (isset($_GET['change_name']))? $_GET['change_name'] : '';
$change_name	= htmlentities($change_name);
$change_url	= (isset($_GET['change_url']))? $_GET['change_url'] : '';
$change_url	= htmlentities($change_url);
$change_id	= (isset($_GET['change_id']))? $_GET['change_id'] : '';
$change_id	= htmlentities($change_id);

//Remove variables
$remove_id	= (isset($_GET['remove']))? $_GET['remove'] : '';
$remove_id	= htmlentities($remove_id);

//Require our settings, must be before $data
require_once(LILINA_INCPATH . '/core/conf.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

$data		= file_get_contents($settings['files']['feeds']) ;
$data		= unserialize( base64_decode($data) ) ;
//Old functions, not yet migrated
//require_once(LILINA_INCPATH . '/core/lib.php');
//Our current version
require_once(LILINA_INCPATH . '/core/version.php');

//For the RSS auto discovery
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Parse OPML files
require_once(LILINA_INCPATH . '/contrib/parseopml.php');

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
function get_feed_list() {
	global $data;
	return $data['feeds'];
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
			$result		.= sprintf(_r('Error deleting files in %s - Make sure the directory is writable and PHP/Apache has the correct permissions to modify it.'), $settings['cachedir']) . '<br />';
		}
		if($times_file = @fopen($settings['files']['times'], 'w')) fclose($times_file);
		else {
			$result		.= sprintf(_r('Error clearing times from %s - Make sure the file is writable and PHP/Apache has the correct permissions to modify it.'), $settings['files']['times']) . '<br />';
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
		if(strpos($add_url, 'feed://') === 0) {
			$add_url	= str_replace('feed://', 'http://', $add_url);
		}
		elseif(strpos($add_url, 'feed:') === 0) {
			$add_url	= str_replace('feed:', '', $add_url);
		}
		else {
			$file	= fopen($add_url, 'r');
			if(!$file) {
				$result	.= _r('Could not retrieve feed. Check that this server can connect to the remote server');
				break;
			}
			else {
				$meta	= stream_get_meta_data($file);
				foreach($meta['wrapper_data'] as $the_meta) {
					$content_type	= eregi('^Content-Type: [^;];', $the_meta);
					if($content_type) {
						//Insert RSS or Atom types here
						switch($content_type) {
							case 'text/xml':
							case 'application/xml':
							case 'application/rss+xml':
							case 'application/atom+xml':
								break;
							default:
								$add_url = lilina_get_rss($add_url);
								break;
						}
					}
				}
			}
			fclose($file);
		}
		if(empty($add_category)) {
			$category	= 'default';
		}
		if(empty($add_name)) {
			//We don't care, we'll get it from the feed
		}
		if(empty($add_url)) {
			//Now this we do care about
			$result .= _r('Couldn\'t add feed: No feed URL supplied');
			break;
		}
		$feed_num	= count($data['feeds']);
		$data['feeds'][$feed_num]['feed']	= $add_url;
		$data['feeds'][$feed_num]['name']	= $add_name;
		$data['feeds'][$feed_num]['cat']	= 'default'; //$add_category;
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		$result	.= sprintf(_r('Added feed %s with URL as %s'), $add_name, htmlentities($add_url)) . '<br />';
	break;
	case 'remove':
		array_splice($data['feeds'], $remove_id, 1);
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		$result	.= _r('Removed feed') . '<br />';
	break;
	case 'change':
		$data['feeds'][$change_id]['feed'] = $change_url;
		$data['feeds'][$change_id]['name'] = $change_name;
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		$result	.= sprintf(_r('Changed feed #%d with URL as %s'), $change_id, htmlentities($change_url)) . '<br />';
	break;
	case 'import':
		$imported_feeds = parse_opml($add_url);
		foreach($imported_feeds as $imported_feed) {
			
		}
	break;
	case 'reset':
		unlink(LILINA_PATH . '/conf/settings.php');
		printf(_r('settings.php successfully removed. <a href="%s">Reinstall</a>'), $_SERVER['PHP_SELF']);
	break;
}
header('Content-Type: text/html; charset=utf-8');
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
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/fat.js"></script>
</head>
<body onload="javascript:adminLoader('<?php echo $page; ?>');">
<?php
if(isset($result) && !empty($result)) {
	echo '<div id="alert">' . $result . '</div>';
}
?>
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
if($action == 'diagnostic') {
	echo 'Now starting diagnostic test...';
	echo '<pre>';
	echo 'PHP Version: '.phpversion();
	echo "\nDisplay Errors: ".(ini_get('display_errors') == '1' ? 'On' : 'Off');
	$error_reporting_level = (ini_get('error_reporting') == '2047' ? 'E_ALL' : 'Not E_ALL');
	echo "\nError Level: $error_reporting_level";
	if($error_reporting_level == 'Not E_ALL') {
		echo "\nSetting error reporting level to E_ALL";
		
	}
	echo '
Register Globals: '.(ini_get('register_globals') == '' ? 'Off' : 'On');
	flush();
	if(!isset($settings['auth']) || !is_array($settings['auth']) ||
		!isset($settings['auth']['user']) || !isset($settings['auth']['pass'])) {
		echo '
Error with authentication settings';
		flush();
	}
	echo '
Current path to Lilina: ', LILINA_PATH;
	echo '
Current path to includes folder: ', LILINA_INCPATH;
	echo '
Current URL: ', $settings['baseurl'];
	flush();
	echo '
Now attempting to include all files: ';
	flush();
	require_once(LILINA_INCPATH . '/core/auth-functions.php');
	require_once(LILINA_INCPATH . '/core/cache.php');
	require_once(LILINA_INCPATH . '/core/conf.php');
	//require_once(LILINA_INCPATH . '/core/errors.php');
	require_once(LILINA_INCPATH . '/core/feed-functions.php');
	require_once(LILINA_INCPATH . '/core/file-functions.php');
	require_once(LILINA_INCPATH . '/core/install-functions.php');
	require_once(LILINA_INCPATH . '/core/l10n.php');
	require_once(LILINA_INCPATH . '/core/lib.php');
	require_once(LILINA_INCPATH . '/core/misc-functions.php');
	require_once(LILINA_INCPATH . '/core/plugin-functions.php');
	require_once(LILINA_INCPATH . '/core/skin.php');
	require_once(LILINA_INCPATH . '/core/version.php');
	require_once(LILINA_INCPATH . '/contrib/feedcreator.class.php');
	require_once(LILINA_INCPATH . '/contrib/gettext.php');
	require_once(LILINA_INCPATH . '/contrib/magpie.php');
	require_once(LILINA_INCPATH . '/contrib/parseopml.php');
	require_once(LILINA_INCPATH . '/contrib/streams.php');
	flush();
	echo '
All files successfully included';
	echo '
Settings dump:';
	flush();
	var_dump($settings);
	flush();
	echo '
Diagnostic finished</pre>'; 
	flush();
}
elseif($out_page){
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