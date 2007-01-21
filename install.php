<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		install.php
Purpose:	Template for the installer pages
Notes:		
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
define('LILINA', 1);
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
//Initialize variables
$page					= htmlentities($_GET['page']);
$from					= htmlentities($_POST['from']);
$sitename				= htmlentities($_POST['sitename']);
$sitelink				= htmlentities($_POST['url']);
$username				= htmlentities($_POST['username']);
$password				= htmlentities($_POST['password']);
$error['sitename']		.= (isset($sitename)) ? true : false;
$error['url']			.= (isset($sitelink)) ? true : false;
$error['username']		.= (isset($username)) ? true : false;
$error['password']		.= (isset($password)) ? true : false;
require_once('./inc/core/install-functions.php');
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
		<div id="menu">
			<ul>
				<li><a href="http://cubegames.net">Cube Games</a></li>
				<li><a href="http://lilina.cubegames.net">Lilina Home</a></li>
				<li id="last"><a href="http://lilina.cubegames.net/wordpress/vanilla/">Support</a></li>
				<li id="logo"><a href="http://cubegames.net">Cube Games</a></li>
			</ul>
		</div>
		<div id="content">
		<?php
		if($error['sitename'] || $error['url'] || $error['username'] || $error['password']) {
			lilina_install_page($from, $error);
		}
		else {
			if(!lilina_check_installed()) {
				echo $page;
				echo isset($page);
				if(isset($page)) {
					if(!is_numeric($page)) {
						lilina_install_err(0,$page);
					}
					else {
						lilina_install_page($page);
					}
				}
				else {
					lilina_install_page(1);
				}
			}
			else {
				lilina_install_err(1);
			}
		}
		?>
		</div>
		<div id="footer">
			<p>Powered by Lilina 1.0 | Copyright Cube Games and Ryan McCue</p>
		</div>
	</body>
</html>