<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		auth-functions.php
Purpose:	Functions that handle authentication
			for the administration section
Notes:		lilina_auth() and lilina_form()
			are only to be called from
			lilina_admin_auth()
Functions:	lilina_admin_auth(	$username_from_user,
								$password_from_user,
								$settings_array
								);
			lilina_auth(		$username_from_user,
								$password_from_user,
								$settings_array
								);
			lilina_form( $error_code );
			
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
function lilina_admin_auth($user, $pass) {
	function lilina_auth($un, $pw) {
		global $settings;
		if(isset($un) && isset($pw)) {
			//Check the username and password
			if ($un === $settings['auth']['user']) {
				if($pw === $settings['auth']['pass']) {
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
	function lilina_form($error = false) {
		$highlight_pw	= '';
		$highlight_un	= '';
		if($error) {
			switch($error) {
				case 'pw':
					$error_message = 'Your password is incorrect. Please make sure you have spelt it correctly.<br />';
					$highlight_pw	= 'color:#FF615A;';
				break;
				case 'un':
					$error_message = 'Your username is incorrect. Please make sure you have spelt it correctly.<br />';
					$higlight_un	= 'color:#FF615A;';
				break;
			}
		}
		$content = '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
		<table>
			<tr style="' . $highlight_un . '">
				<td><label for="user">Username</label></td>
				<td><input type="text" name="user" id="user" /></td>
			</tr>
			<tr style="' . $higlight_pw . '">
				<td><label for="pass">Password:</label></td>
				<td><input type="password" name="pass" id="pass" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;">
					<input type="submit" value="Login" />
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