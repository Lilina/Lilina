<?php
/**
 * Administration page
 * @todo Major cleanup of everything contained within and move to own folder
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//
/**
 * Stop hacking attempts
 *
 * All included files (external libraries excluded) must check for presence of
 * this define (using defined() ) to avoid the files being accessed directly
 */
define('LILINA',1) ;
define('LILINA_ADMIN', 1) ;
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
$page		= htmlspecialchars($page);
$action		= (isset($_GET['action'])? $_GET['action'] : '');
$action		= htmlspecialchars($action);

//Add variables
$add_name	= (isset($_GET['add_name'])? $_GET['add_name'] : '');
$add_name	= htmlspecialchars($add_name);
$add_url	= (isset($_GET['add_url'])? $_GET['add_url'] : '');

//Change variables
$change_name	= (isset($_GET['change_name']))? $_GET['change_name'] : '';
$change_name	= htmlspecialchars($change_name);
$change_url	= (isset($_GET['change_url']))? $_GET['change_url'] : '';
$change_id	= (isset($_GET['change_id']))? $_GET['change_id'] : '';
$change_id	= htmlspecialchars($change_id);

//Remove variables
$remove_id	= (isset($_GET['remove']))? $_GET['remove'] : '';
$remove_id	= htmlspecialchars($remove_id);

//Import variable
$import_url	= (isset($_GET['import_url']))? $_GET['import_url'] : '';
$import_url	= htmlspecialchars($import_url);

//Require our settings, must be before $data
require_once(LILINA_INCPATH . '/core/conf.php');

require_once(LILINA_INCPATH . '/core/plugin-functions.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/update-functions.php');

do_action('init');

/**
 * Contains all feed names, URLs and (eventually) categories
 * @global array $data
 */
$data		= file_get_contents($settings['files']['feeds']) ;
$data		= unserialize( base64_decode($data) ) ;
//Old functions, not yet migrated
require_once(LILINA_INCPATH . '/core/lib.php');
//Our current version
require_once(LILINA_INCPATH . '/core/version.php');

//For the RSS auto discovery
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Parse OPML files
require_once(LILINA_INCPATH . '/contrib/parseopml.php');
require_once(LILINA_INCPATH . '/core/auth-functions.php');

//Authentication Section
if(isset($_POST['user']) && isset($_POST['pass'])) {
	lilina_login_form($_POST['user'], $_POST['pass']);
}
else {
	lilina_login_form('', '');
}

if(isset($_GET['logout']) && $_GET['logout'] == 'logout') {
	lilina_logout();
	die();
}



//Misc. Functions
/**
 * 
 * @global array
 */
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
					@unlink($cachedir . '/' . $file);
				}
			}
			@closedir($handle);
		}
		else {
			add_notice(sprintf(_r('Error deleting files in %s'), $settings['cachedir']));
			add_tech_notice(_r('Make sure the directory is writable and PHP/Apache has the correct permissions to modify it.'));
		}
		if($times_file = @fopen($settings['files']['times'], 'w')) fclose($times_file);
		else {
			add_notice(sprintf(_r('Error clearing times from %s'), $settings['files']['times']));
			add_tech_notice(_r('Make sure the file is writable and PHP/Apache has the correct permissions to modify it.'));
		}
		add_notice(_r('Successfully cleared cache!'));
	break;
	case 'add':
		add_feed($add_url, $add_name);
	break;
	case 'remove':
		$removed = $data['feeds'][$remove_id];
		unset($data['feeds'][$remove_id]);
		$data['feeds'] = array_values($data['feeds']);
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(sprintf(_r('Removed feed &mdash; <a href="%s">Undo</a>?'), htmlspecialchars($_SERVER['PHP_SELF']) . '?page=feeds&amp;action=add&amp;add_name=' . urlencode($removed['name']) . '&amp;add_url=' . urlencode($removed['feed'])));
	break;
	case 'change':
		$data['feeds'][$change_id]['feed'] = $change_url;
		if(!empty($change_name)) {
			$data['feeds'][$change_id]['name'] = $change_name;
		}
		else {
			//Need to have a similar function to add_feed()
		}
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(sprintf(_r('Changed "%s" (#%d)'), $change_name, $change_id));
	break;
	case 'import':
		import_opml($import_url);
	break;
	case 'reset':
		unlink(LILINA_PATH . '/conf/settings.php');
		printf(_r('settings.php successfully removed. <a href="%s">Reinstall</a>'), $_SERVER['PHP_SELF']);
		die();
	break;
}

do_action('admin_header');
do_action("admin_header-$out_page");
do_action('send_headers');

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
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/fat.js"></script>
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/admin.js"></script>
</head>
<body id="admin-<?php echo $out_page; ?>" class="admin-page">
<div id="header">
	<h1 id="sitetitle"><a href="<?php echo $settings['baseurl']; ?>"><?php echo $settings['sitename']; ?></a></h1>
	<div id="navigation">
	    <h2>Navigation</h2>
		<ul id="mainnavigation">
<?php
$navigation = array(
	array(_r('Home'), 'admin-home.php', ''),
	array(_r('Feeds'), 'admin-feeds.php', 'feeds'),
	array(_r('Settings'), 'admin-settings.php', 'settings'),
);
$subnavigation = apply_filters('navigation', $navigation);
foreach($navigation as $nav_item) {
	if($out_page == $nav_item[1]) {
		/** Hack */
		if(!isset($current_page))
			$current_page = $nav_item[2];
		$nav_items[] = "<li class='current'><a href='admin.php?page={$nav_item[2]}'>{$nav_item[0]}</a>";
	}
	else
		$nav_items[] = "<li><a href='admin.php?page={$nav_item[2]}'>{$nav_item[0]}</a>";
}
echo implode("</li>\n", $nav_items);
?></li>
			<li id="page_item_logout" class="seperator"><a href="admin.php?logout=logout" title="<?php _e('Log out of your current session'); ?>"><?php _e('Log out'); ?></a></li>
		</ul>
<?php
$subnavigation = array(
	'admin-home.php' => array(
		array(_r('Home'), 'admin-home.php', ''),
	),
	'admin-feeds.php' => array(
		array(_r('Manage'), 'admin-feeds.php', ''),
	),
	'admin-settings.php' => array(
		array(_r('General'), 'admin-settings.php', 'settings'),
	),
);
$subnavigation = apply_filters('subnavigation', $subnavigation, $navigation, $current_page);
if( isset($subnavigation[ strtolower($current_page) ]) && !empty($subnavigation[ strtolower($current_page) ]) ) {
	echo '<ul id="subnavigation">';
	foreach($subnavigation[strtolower($current_page)] as $subnav_item) {
		if($out_page == $subnav_item[1])
			$subnav_items[] = "<li class='current'><a href='admin.php?page={$nav_item[2]}'>{$nav_item[0]}</a>";
		else
			$subnav_items[] = "<li><a href='admin.php?page={$nav_item[2]}'>{$nav_item[0]}</a>";
	}
	echo implode("</li>\n", $subnav_items), '</li></ul>';
}
?>
	</div>
</div>
<div id="main">
<?php
if( ($result = apply_filters( 'alert_box', $result )) != '') {
	echo '<div id="alert" class="fade">' . $result . '</div>';
}
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
	echo "\nRegister Globals: " . (ini_get('register_globals') == '' ? 'Off' : 'On');
	echo "\nMagic Quotes: " . (get_magic_quotes_gpc() ? 'Off' : 'On');
	echo "\nMagic Quotes (runtime): " . (get_magic_quotes_runtime() ? 'Off' : 'On');
	flush();
	if( !is_array($user_settings = get_option('auth') ) ||
		!isset($user_settings['user']) ||
		!isset($user_settings['pass']) ||
		$user_settings['pass'] == 'password') {
		echo "\nError with authentication settings";
		flush();
	}
	echo "\nCurrent path to Lilina: ", LILINA_PATH;
	echo "\nCurrent path to includes folder: ", LILINA_INCPATH;
	echo "\nCurrent installation path: ", get_option('baseurl');
	flush();
	echo "\nNow attempting to include all files: ";
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
	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	require_once(LILINA_INCPATH . '/contrib/gettext.php');
	require_once(LILINA_INCPATH . '/contrib/parseopml.php');
	require_once(LILINA_INCPATH . '/contrib/streams.php');
	flush();
	echo "\nAll files successfully included";
	echo "\nSettings dump:";
	flush();
	var_dump($settings);
	flush();
	echo "\nDiagnostic finished</pre>"; 
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
<p id="footer"><?php
_e('Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a>');
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/<?php _e('en'); ?>:start"><?php _e('Documentation');
?></a> and <a href="http://getlilina.org/forums/" title="<?php _e('Support on the Forums');?>"><?php _e('Support'); ?></a></p>
</body>
</html>