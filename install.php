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

/**
 * Stops hacking later on
 */
define('LILINA', 1);
header('Content-Type: text/html; charset=UTF-8');

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
//Initialize variables
if(!empty($_POST['page'])) {
	$page				= htmlentities($_POST['page']);
}
elseif(!empty($_GET['page'])) {
	$page				= htmlentities($_GET['page']);
}
else {
	$page				= false;
}
$from					= (isset($_POST['from'])) ? htmlentities($_POST['from']) : false;
$sitename				= (isset($_POST['sitename'])) ? htmlentities($_POST['sitename']) : false;
$url					= (isset($_POST['url'])) ? htmlentities($_POST['url']) : false;
$username				= (isset($_POST['username'])) ? htmlentities($_POST['username']) : false;
$password				= (isset($_POST['password'])) ? htmlentities($_POST['password']) : false;
$error					= ((!$sitename || !$url || !$username || !$password) && $page && $page != 1) ? true : false;
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
		<div id="container">
			<div id="header">
				<img src="i/logo.jpg" alt="Lilina Logo" />
				<h1>Lilina News Aggregator</h1>
				<h2>Installation Step <?php echo $page;?></h2>
			</div>
			<div id="menu">
				<ul>
					<li><a href="http://lilina.cubegames.net/">Lilina Website</a></li>
					<li><a href="http://lilina.cubegames.net/wordpress/vanilla/">Forums</a></li>
					<li><a href="http://lilina.cubegames.net/support/">Support</a></li>
				</ul>
			</div>
			<div id="content">
<?php
//Make sure Lilina's not installed
if(@file_exists('./conf/settings.php')) {
	die('<p>Lilina is already installed. <a href="index.php">Head back to the main page</a></p></div></div></body></html>');
}
if($error) {
	$page--;
}
switch($page) {
	case 1:
		if($error) {
		}
?>
<p>Let's get started on the installation!</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table style="width:100%">
		<tr><td colspan="2"><h2>General Settings</h2></td></tr>
		<tr<?php if(!$sitename) echo ' class="highlight"';?>>
			<td class="label"><label for="sitename"><span class="label">Name of site</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$sitename) ? 'Lilina News Aggregator' : $sitename;?>" name="sitename" id="sitename" size="40" /></td>
		</tr>
		<tr<?php if(!$url) echo ' class="highlight"';?>>
			<td class="label"><label for="url"><span class="label">URL for Lilina</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$url) ? 'http://localhost/' : $url;?>" name="url" id="url" size="40" /></td>
		</tr>
		<tr><td colspan="2"><h2>Security Settings</h2></td></tr>
		<tr<?php if(!$username) echo ' class="highlight"';?>>
			<td class="label"><label for="username"><span class="label">Admin Username</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$username) ? 'admin' : $username;?>" name="username" id="username" size="40" /></td>
		</tr>
		<tr<?php if(!$password) echo ' class="highlight"';?>>
			<td class="label"><label for="password"><span class="label">Admin Password</span></label></td>
			<td class="formw"><input type="text" value="<?php echo (!$password) ? generate_password() : $password;?>" name="password" id="password" size="40" /></td>
		</tr>	
		<tr>
			<td colspan="2"><span class="formw">
				<input type="hidden" value="2" name="page" id="page" />
				<input type="submit" value="Next &raquo;" />
			</span>
			</td>
		</tr>
	</table>
	<div style="clear:both;">&nbsp;</div>
</form>
<?php
		break;
	case 2:
?>
<p>Now saving settings to conf/settings.php - Stand by...</p>
<?php
		flush();
		$raw_php		= "<?php
\$settings['sitename'] = '$sitename';
\$settings['baseurl'] = '$url';
\$settings['auth'] = array(
							'user' => '$username',
							'pass' => '" . md5($password) . "'
							);
?>";
		$settings_file	= @fopen('./conf/settings.php', 'w+');
		if($settings_file){
			fputs($settings_file, $raw_php) ;
			fclose($settings_file) ;
?>
<p>Lilina has been set up on your server and is ready to run. Open <a href="admin.php">your admin panel</a> and add some feeds.</p>
<dl>
	<dt>Username</dt>
	<dd><?php echo $username;?></dd>
	<dt>Password</dt>
	<dd><?php echo $password;?></dd>
</dl>
<p>Were you expecting more steps? Sorry to disappoint. All done! :)</p>
<?php
		}
		else {
			echo 'I couldn\'t open conf/settings.php to write to.
			Please make sure that the conf directory is writable and that the server can write to it.
			You can also save the following text as conf/settings.php<br /><pre>';
			highlight_string($raw_php);
			echo '</pre>
			<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
			<input type="hidden" name="sitename" value="'.$sitename.'" />
			<input type="hidden" name="url" value="'.$url.'" />
			<input type="hidden" name="username" value="'.$username.'" />
			<input type="hidden" name="password" value="'.$password.'" />
			<input type="hidden" name="page" value="2">
			<input type="submit" value="Try again" />
			</form>';
		}
		break;
	case 0:
	case false:
	default:
?>
<p>Welcome to Lilina installation. We're now going to start installing. Make sure that the conf folder exists and is <a href="readme.html#permissions">writable</a>.</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="Install &raquo;" />
</form>
<?php
		break;
}
		/*if(isset($from) && is_numeric($from)) {
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
	}*/
?>
			</div>
		</div>
		<div id="footer">
			<p>Powered by Lilina 1.0 | Copyright Lilina Development Team</p>
		</div>
	</body>
</html>