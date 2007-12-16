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

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

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

require_once(LILINA_INCPATH . '/core/plugin-functions.php');

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
/**
 * Generates nonce
 *
 * Uses the current time
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function generate_nonce() {
	global $settings;
	$time = ceil(time() / 43200);
	return md5($time . $settings['auth']['user'] . $settings['auth']['pass']);
}
/**
 * Checks whether supplied nonce matches current nonce
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($nonce) {
	global $settings;
	$time = ceil(time() / 43200);
	$current_nonce = md5($time . $settings['auth']['user'] . $settings['auth']['pass']);
	if($nonce !== $current_nonce) {
		return false;
	}
	return true;
}

function available_templates() {
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/templates/')) {
		//Go through all entries
		while (false !== ($dir = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($dir != '.' && $dir != '..') {
				if (is_dir(LILINA_INCPATH . '/templates/' . $dir)) {
					if(file_exists(LILINA_INCPATH . '/templates/' . $dir . '/style.css')) {
						$list[] = LILINA_INCPATH . '/templates/' . $dir . '/style.css';
					}
				} 
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	foreach($list as $the_template) {
		$temp_data = implode('', file($the_template));
		preg_match("|Name:(.*)|i", $temp_data, $name);
		preg_match("|Real Name:(.*)|i", $temp_data, $real_name);
		preg_match("|Description:(.*)|i", $temp_data, $desc);
		$templates[]	= array(
								'name' => trim($name[1]),
								'real_name' => trim($real_name[1]),
								'description' => trim($desc[1])
								);
	}
	return $templates;
}

function available_locales() {
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/locales/')) {
		//Go through all entries
		while (false !== ($file = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($file != '.' && $file != '..') {
				if (!is_dir(LILINA_INCPATH . '/locales/' . $file)) {
					//Only add plugin files
					if(strpos($file,'.mo') !== FALSE) {
						$locale_list[] = $file;
					}
				}
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	foreach($locale_list as $locale) {
	echo $locale;
		//Quick and dirty name
		$locales[]	= array('name' => str_replace('.mo', '', $locale),
							'file' => $locale);
	}
	return $locales;
}

/**
 * Adds a notice to the top of the page
 *
 * Concatenates the string as a paragraph to the global $result variable
 * @param string $message Notice to add
 */
function add_notice($message) {
	global $result;
	$result .= "<p>$message</p>\n";
}

/**
 * Adds a technical notice to the top of the page
 *
 * Concatenates the string as a paragraph to the global $result variable
 * @param string $message Notice to add
 */
function add_tech_notice($message) {
	global $result;
	$result .= '<p class="tech_notice"><span class="actual_notice">' . $message . '</span></p>' . "\n";
}

/**
 *
 *
 */
function add_feed($url, $name = '', $original_url = false) {
	global $settings, $data;
	//Fix users' kludges; They'll thank us for it
	$url	= str_replace(array('feed://http://', 'feed://http//', 'feed://', 'feed:http://', 'feed:'), 'http://', $url);
	$feed_info = fetch_rss($url);
	if(!$feed_info && !$original_url) {
		//Try again, but autodiscover
		$auto = lilina_get_rss($url);
		if(is_array($auto)) {
			foreach($auto as $new_feed) {
				$new_result = add_feed($new_feed, $name, $url);
				if($new_result) { return true; }
			}
			//If we're still going, it failed
			add_notice(printf(_r('Couldn\'t add feed: %s is not a valid URL or the server could not be accessed.'), $url) . '<br />');
			add_tech_notice(_r('Magpie said: ') . magpie_error());
			return false;
		}
		else {
			//No feeds autodiscovered;
			add_notice(sprintf(_r('Couldn\'t add feed: %s is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery.'), $url));
			return false;
		}
	}
	elseif(!$feed_info && is_string($original_url)) {
		//May be more feeds to check, don't print an error; The original does that for us
		return false;
	}
	if(empty($name)) {
		//Get it from the feed
		$name = $feed_info->channel['title'];
	}
	if(empty($url)) {
		//Now this we do care about
		add_notice(_r('Couldn\'t add feed: No feed URL supplied'));
		return false;
	}
	$feed_num	= count($data['feeds']);
	$data['feeds'][$feed_num]['feed']	= $url;
	$data['feeds'][$feed_num]['name']	= $name;
	$data['feeds'][$feed_num]['cat']	= 'default'; //$add_category;
	$sdata	= base64_encode(serialize($data)) ;
	$fp		= fopen($settings['files']['feeds'],'w') ;
	if(!$fp) {
		add_notice(sprintf(_r('An error occurred when saving to %s and your data may not have been saved'), $settings['files']['feeds']));
		return false;
	}
	fputs($fp,$sdata) ;
	fclose($fp) ;
	add_notice(sprintf(_r('Added feed "%1$s"'), $name));
	return true;
}

/**
 *
 */
function import_opml($opml_url) {
	if(!empty($opml_url)) {
		$imported_feeds = parse_opml($opml_url);
		if(is_array($imported_feeds)) {
			$feeds_num = 0;
			foreach($imported_feeds as $imported_feed) {
				if($imported_feed['TYPE'] != 'rss' && $imported_feed['TYPE'] != 'atom') {
					continue;
				}
				//Make sure we blank it
				$this_feed = array('url' => '', 'title' => '');
				if(!isset($imported_feed['XMLURL']) || empty($imported_feed['XMLURL'])) {
					if(!isset($imported_feed['HTMLURL']) || empty($imported_feed['HTMLURL'])) {
						//Can't live without a URL
						continue;
					}
					else {
						$this_feed['url'] = $imported_feed['HTMLURL'];
					}
				}
				else {
					$this_feed['url'] = $imported_feed['XMLURL'];
				}
				if(!isset($imported_feed['TEXT']) || empty($imported_feed['TEXT'])) {
					if(!isset($imported_feed['TITLE']) || empty($imported_feed['TITLE'])) {
						//We'll need to get it via Magpie later
						$this_feed['title'] = '';
					}
					else {
						$this_feed['title'] = $imported_feed['TITLE'];
					}
				}
				else {
					$this_feed['title'] = $imported_feed['TEXT'];
				}
				add_feed($this_feed['url'], $this_feed['title']);
				++$feeds_num;
			}
			add_notice(sprintf(_r('Added %d feed(s)'), $feeds_num));
		}
		else {
			add_notice(_r('The OPML file could not be read.'));
			add_tech_notice(_r('The parser said: ') . $imported_feeds);
		}
	}
	else {
		add_notice(sprintf(_r('No OPML specified')));
	}
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
		unset($data['feeds'][$remove_id]);
		$data['feeds'] = array_values($data['feeds']);
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen($settings['files']['feeds'],'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(_r('Removed feed'));
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
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/fat.js"></script>
<script type="text/javascript" src="<?php echo $settings['baseurl']; ?>inc/js/admin.js"></script>
</head>
<body id="admin-<?php echo $out_page; ?>" class="admin-page">
<div id="header">
	<h1 id="sitetitle"><a href="<?php echo $settings['baseurl']; ?>"><?php echo $settings['sitename']; ?></a></h1>
	<div id="navigation">
	    <h2>Navigation</h2>
		<ul>
			<li class="page_item"><a href="admin.php">Home</a></li>
			<li class="page_item"><a href="admin.php?page=feeds" title="<?php _e('Add, change and remove feeds'); ?>"><?php _e('Feeds'); ?></a></li>
			<li class="page_item"><a href="admin.php?page=settings" title="<?php _e('Change settings and run a diagnostic test'); ?>"><?php _e('Settings'); ?></a></li>
			<li class="page_item seperator"><a href="http://lilina.cubegames.net/docs/<?php _e('en'); ?>:start" title="<?php _e('Documentation and Support on the Wiki');?>"><?php _e('Lilina Documentation'); ?></a></li>
			<li class="page_item"><a href="http://lilina.cubegames.net/forums/" title="<?php _e('Support on the Forums');?>"><?php _e('Lilina Forums'); ?></a></li>
			<li id="page_item_logout" class="page_item seperator"><a href="admin.php?logout=logout" title="<?php _e('Log out of your current session'); ?>"><?php _e('Log out'); ?></a></li>
		</ul>
	</div>
</div>
<div id="main">
<?php
if(isset($result) && !empty($result)) {
	echo '<div id="alert" class="fade">' . $result . '</div>';
}
?>
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
<p id="footer"><?php printf(_r('Powered by <a href="http://lilina.cubegames.net/">Lilina News Aggregator</a> %s'), $lilina['core-sys']['version']); 
	call_hooked('admin_footer', $out_page); ?> | <a href="http://lilina.cubegames.net/docs/<?php _e('en'); ?>:start"><?php _e('Documentation and Support'); ?></p>
</body>
</html>