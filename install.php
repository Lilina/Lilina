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
//Initialize variables
if(!empty($_POST['page'])) {
	$page				= htmlentities($_POST['page']);
}
elseif(!empty($_GET['page'])) {
	$page				= htmlentities($_GET['page']);
}
else {
	$page				= 1;
}
$from					= (isset($_POST['from'])) ? htmlentities($_POST['from']) : false;
$sitename				= (isset($_POST['sitename'])) ? htmlentities($_POST['sitename']) : false;
$url					= (isset($_POST['url'])) ? htmlentities($_POST['url']) : false;
$username				= (isset($_POST['username'])) ? htmlentities($_POST['username']) : false;
$password				= (isset($_POST['password'])) ? htmlentities($_POST['password']) : false;
$retrieved_settings		= array($sitename, $url, $username, $password);
$error					= array('sitename','url','username','password');
$error['sitename']		= ($sitename) ? false : true;
$error['url']			= ($url) ? false : true;
$error['username']		= ($username) ? false : true;
$error['password']		= ($password) ? false : true;
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
			//Display the error
			lilina_install_page($from, $error);
		}
		else {
			//Make sure Lilina's not installed
			if(!lilina_check_installed()||1==1) {
				if(isset($from) && is_numeric($from)) {
					//We already came from a page, lets display the next
					if(!is_numeric($from)) {
						lilina_install_err(0,$from);
					}
					else {
						if($from < 3) {
							//We add one so it displays the next page
							$from++;
							lilina_install_page($from);
						}
						else {
							//Otherwise, we must be on the last page
							//We should never get this
							lilina_install_err(0,$from);
						}
					}
				}
				else {
					//Not from a page
					if(!is_numeric($page)) {
						lilina_install_err(0,$page);
					}
					else {
						lilina_install_page($page);
					}
				}
			}
			else {
				//Woops, we are already installed
				lilina_install_err(1);
			}
		}
		?>
		</div>
		<div id="footer">
			<p>Powered by Lilina 1.0 | Copyright Lilina Development Team</p>
		</div>
	</body>
</html>