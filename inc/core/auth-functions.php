<?php
function lilina_admin_auth($user = '', $pass = '') {
	function lilina_auth($un = '', $pw = '') {
		session_start();
		$check	= !empty($un);
		if(is_array($authdata)) {
			return true;
		}
		elseif($check) {
			if $settings['auth']['user'] == $un) {
				if($settings['auth']['pass'] == $pw) {
					$_SESSION['authdata'] = array('login' => $un);
					return true;
				}
				else {
					return 'pw';
				}
			}
			else {
				return 'un';
			}
			unset($authdata);
			return false;
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
					echo 'Your password is incorrect. Please make sure you have spelt it correctly.<br />';
					$highlight_pw	= 'color:#FF615A;';
				break;
				case 'un':
					echo 'Your username is incorrect. Please make sure you have spelt it correctly.<br />';
					$higlight_un	= 'color:#FF615A;';
				break;
			}
		}
		echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
		<span style="' . $highlight_un . '">
			<label for="user">Username</label><input type="text" name="user" id="user"><br />
		</span>
		<span style="' . $higlight_pw . '">
			<label for="pass">Password:</label><input type="password" name="pass"><br />
		</span>
		<input type="submit" value="log in">
		</form>';
		
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
		//...Boolean false, we aren't logged in
		return false;
	}
	else {
		//...Unknown, just to be sure, we aren't logged in
		return false;
	}
}
?> 