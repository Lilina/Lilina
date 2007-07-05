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
defined('LILINA') or die('Restricted access');

function lilina_check_installed() {
	if(@file_exists('./conf/settings.php')) {
		return true;
	}
	return false;
}

function lilina_install_err($error = 0, $args = '') {
	switch($error) {
		//0: Page not found
		case 0:
			echo 'I couldn\'t find the page you asked for. Report the following to the <a href="http://lilina.cubegames.net/wordpress/vanilla/">Lilina forums</a>:
			<pre>Err 0; Page ' . $args . '</pre>';
			break;
		//1: settings.php found, must already be installed
		case 1:
			echo 'Lilina is already installed on this server. <a href="index.php">Return to Lilina</a>.';
			break;
		//2: Failed opening file
		case 2:
			echo 'I couldn\'t open ' . $args[0] . ' to write to. Please make sure that the conf directory is writable and that the server can write to it. You can also save the following text as ' . $args[0] . '<br /><pre>';
			highlight_string($args[1]);
			echo '</pre>
			<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
			<input type="hidden" name="sitename" value="'.$args[2][0].'" />
			<input type="hidden" name="url" value="'.$args[2][1].'" />
			<input type="hidden" name="username" value="'.$args[2][2].'" />
			<input type="hidden" name="password" value="'.$args[2][3].'" />
			<input type="hidden" name="from" value="1">
			<input type="submit" value="Try again" />
			</form>';
			break;
		default:
			break;
	}
}

function lilina_install_page($page, $error = array()) {
	switch($page) {
		case 0:
		case 1:
			require_once('./inc/pages/install-start.php');
			break;
		case 2:
			require_once('./inc/pages/install-finish.php');
			break;
		default:
			lilina_install_err(0, $page);
			break;
	}
}

function lilina_set_settings($args) {
	$new_settings	= array(
							'sitename'	=> $args[0],
							'baseurl'	=> $args[1],
							'auth'		=> array(
												'user'	=> $args[2],
												'pass'	=> $args[3]
												)
							);
	$raw_php		= '<?php';
	foreach($new_settings as $name => $value) {
		if(is_array($value)) {
			$raw_php .= "\n\$settings['$name'] = array(";
			foreach($value as $name2 => $value2) {
				if($name2 == 'pass') {
					$value	= md5($value2);
				}
				$raw_php	.= "'$name2' => '$value2',";
			}
			$raw_php	.= "'blank' => 'blank');";
		}
		else {
			$raw_php	.= "\n\$settings['$name'] = '$value';";
		}
	}
	$raw_php		.= '?>';
	$settings_file	= @fopen('./conf/settings.php', 'w+');
	if(!$settings_file) {
		lilina_install_err(2, array('settings.php',$raw_php,$args));
		return false;
	}
	fputs($settings_file, $raw_php) ;
	fclose($settings_file) ;
	return true;
}
?>