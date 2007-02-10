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
//Timer doesn't need settings so we don't have to wait for them
require_once('./inc/core/misc-functions.php');
$timer_start = lilina_timer_start();
//Protect from register_globals
$settings	= 0;
$authed		= 0;
$page		= htmlentities($_GET['page']);
$action		= htmlentities($_GET['action']);
$product	= htmlentities($_GET['product']);
$name		= htmlentities($_GET['url']);
$url		= htmlentities(urlencode($_GET['name']));
$data		= file_get_contents($settings['files']['feeds']) ;
$data		= unserialize( base64_decode($data) ) ;
function get_feeds() {
	return $data['feeds'];
}
function import_opml($opml_file) {
	require_once('./inc/contrib/parseopml.php');
	//Caution: $opml_file does nothing yet
	return parse_opml($opml_file);
}
//Require our settings, must be first required file
require_once('./inc/core/conf.php');
require_once('./inc/core/lib.php');
//Insert authentication handling
$authed = 1;
if(!$authed == true){
	display_login();
	die();
}
switch($page) {
	case 'feeds': 
		$out_page = 'admin-feeds.php';
	case 'settings':
		$out_page = 'admin-settings.php';
	default:
		$out_page = 'admin-feeds.php';
}
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
											'feed' => 'http://liberta-project.net/rss.xml',
											'name' => 'Liberta Project'),
										array(
											'feed' => 'http://cubegames.net/wordpress/feed/',
											'name' => 'Cube Games Blog'),
										array(
											'feed' => 'http://lilina.cubegames.net/feed/',
											'name' => 'Lilina News Aggregator Blog')
									)
					);*/
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
		
	break;
	case 'import':
		import_opml($url);
	break;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php echo $settings['sitename'];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
//Add templates code here
?>
<link rel="stylesheet" type="text/css" href="templates/default/admin.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>
<?php
if($authed == true) {
?>
<div id="pagetitle">
	<h1>Control Panel</h1>
	<span id="viewsite">
		<a href="http://cubegames.net/">View site</a>
	</span>
</div>
<div id="navigation">
	<div id="links_container">
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
		</ul>
    </div>
</div>
<?php if($result){
echo '<div>';
echo $result;
echo '</div>';
}
?>
<div id="main" style="float:right;">
<?php
if($out_page){
	require_once('./inc/pages/'.$out_page);
}
else {
	echo $out;
}
?>
</div>
<?php
}
else{
?>
<a href="<?php echo $settings['baseurl']; ?>">
	<img src="i/logo.jpg" alt="<?php echo $settings['sitename'];?>" title="<?php echo $settings['sitename'];?>" />
	</a>
  	<?php if($settings['output']['rss']){?>RSS: <a href="rss.php"><img src="i/feed.png" alt="RSS feed" title="RSS feed" /></a><?php } ?>
  	<?php if($settings['output']['atom']){?>Atom: <a href="rss.php?output=atom"><img src="i/feed.png" alt="Atom feed" title="Atom feed" /></a><?php } ?>
	&nbsp;&nbsp;
	|
    <a href="javascript:visible_mode(true);">
    <img src="i/arrow_out.png" alt="Expand" /> expand</a>
    <a href="javascript:visible_mode(false);">
    <img src="i/arrow_in.png" alt="Collapse" /> collapse</a>
	|
    <a href="cache/opml.xml">OPML</a>
	|
    <a href="#sources">SOURCES</a>
	<div style="float: right;">
		<ul>
		<?php
		for($q=0;$q<count($settings['interface']['times']);$q++){
			$current_time = $settings['interface']['times'][$q];
			if(is_int($current_time)){
				echo '<li><a href="index.php?hours='.$current_time.'"><span>'.$current_time.'h</span></a></li>';
			}
			else {
				switch($current_time) {
					case 'week':
						echo '<li><a href="index.php?hours=168"><span>week</span></a></li>';
					break;
					case 'all':
						echo '<li><a href="index.php?hours=-1"><span>all</span></a></li>';
					break;
				}
			}
		}
		?>
		</ul>
    </div>
</div>
<?php
}
?>
	<div id="footer">
		<a href="http://lilina.cubegames.net/"><img src="i/logo_small.jpg" alt="Lilina News Aggregator" /></a>
		This page was last generated on
		<?php echo date('Y-m-d \a\t g:i a'); ?> and took
		<?php echo lilina_timer_end($timer_start); ?> seconds
	</div>
</body>
</html>
