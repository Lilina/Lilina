<?php
/**
* Authentication functions for the administration panel
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

defined('LILINA') or die('Restricted access');

function lilina_auth($un, $pw) {
	global $settings;
	if(!empty($un) && !empty($pw)) {
		//Check the username and password
		if ($un === $settings['auth']['user']) {
			if($pw === md5($settings['auth']['pass'])) {
				//All details good to go, lets
				//indicate we are logged in
				$_SESSION['is_logged_in'] = true;
				return true;
			}
			else {
				//Error, 
				return 'pw';
			}
		}
		else {
			return 'un';
		}
	}
	else {
		return false;
	}
}

/**
* Function to authenticate user
*
* @param string $user Supplied username
* @param string $pass Supplied password
* @return boolean True if logged in, false if not, however should never return false, since it should die()
*/
function lilina_admin_auth($user, $pass) {
	function lilina_form($error = false) {
		$highlight_pw	= '';
		$highlight_un	= '';
		if($error) {
			switch($error) {
				case 'pw':
					$error_message = _r('Your password') . ' ' . _r('is incorrect. Please make sure you have spelt it correctly.') . '<br />';
					$highlight_pw	= 'color:#FF615A;';
				break;
				case 'un':
					$error_message = _r('Your username') . ' ' . _r('is incorrect. Please make sure you have spelt it correctly.') . '<br />';
					$higlight_un	= 'color:#FF615A;';
				break;
			}
		}
		$content = '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
		<table>
			<tr style="' . $highlight_un . '">
				<td><label for="user">'._r('Username').':</label></td>
				<td><input type="text" name="user" id="user" /></td>
			</tr>
			<tr style="' . $highlight_pw . '">
				<td><label for="pass">'._r('Password').':</label></td>
				<td><input type="password" name="pass" id="pass" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;">
					<input type="submit" value="'._r('Login').'" />
				</td>
			</tr>
		</table>
		</form>';
		require_once('./inc/pages/admin-login.php');
		die();
	}
	
	//Are we logged in?
	$logged_in	= lilina_auth($user, $pass);
	//And we got back...
	if(is_string($logged_in)) {
		//...A string, an error must have been returned
		lilina_form($logged_in);
		return false;
	}
	elseif($logged_in === true) {
		//...Boolean true, we are logged in
		return true;
	}
	elseif($logged_in === false) {
		//...Boolean false, we aren't logged in, let's show the form
		lilina_form($logged_in);
		return false;
	}
	else {
		//...Unknown, just to be sure, we aren't logged in
		return false;
	}
}
?> 