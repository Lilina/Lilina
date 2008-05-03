<?php
/**
 * Functions that work with serialized files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

/**
 * lilina_load_feeds() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_load_feeds($data_file) {
	$data = file_get_contents($data_file) ;
	$data = unserialize( base64_decode($data) ) ;
	if(!$data || !is_array($data)) {
		$data = array();
	}
	return $data;
}

/**
 * available_templates() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
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

/**
 * available_locales() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
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
 * recursive_array_code() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function recursive_array_code($vars) {
	global $level_count;
	foreach($vars as $var => $value) {
		if(is_array($value)) {
			$content .= "\n" . str_repeat("\t", $level_count) . 'array(';
			$level_count++;
			$content .= recursive_array_code($value);
		}
		else
			$content .= "\n" . str_repeat("\t", $level_count) . "'$var' => '$value',";
	}
	while($level_count > 1) {
		$level_count--;
		$content .= "\n" . str_repeat("\t", $level_count) . '),';
	}
	return $content;
}

/**
 * save_settings() - {@internal Missing Short Description}}
 *
 *
 */
function save_settings() {
	global $settings, $default_settings;
	$vars = array_diff_assoc_recursive($settings, $default_settings);
	/** We want to ignore these, as they are set in conf.php */
	unset($vars['cachedir'], $vars['files']);
	$content = '<' . '?php';

	global $level_count; $level_count = 1;
	foreach($vars as $var => $value) {
		if(is_array($value)) {
			$content .= "\n\$settings['{$var}'] = array(";
			$content .= recursive_array_code($value);
			$content .= "\n);";
		}
		else
			$content .= "\n\$settings['{$var}'] = '{$value}';";
	}
	var_dump($content);
}

/**
 * generate_nonce() - Generates nonce
 *
 * Uses the current time
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function generate_nonce() {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	return md5($time . get_option('auth', 'user') . get_option('auth', 'pass'));
}

/**
 * check_nonce() - Checks whether supplied nonce matches current nonce
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($nonce) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	$current_nonce = md5($time . get_option('auth', 'user') . get_option('auth', 'pass]'));
	if($nonce !== $current_nonce) {
		return false;
	}
	return true;
}

/**
 * get_temp_dir() - Get a temporary directory to try writing files to
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function get_temp_dir() {
	if ( defined('LILINA_TEMP_DIR') )
		return trailingslashit(LILINA_TEMP_DIR);

	$temp = LILINA_PATH . '/cache';
	if ( is_dir($temp) && is_writable($temp) )
		return $temp;

	if  ( function_exists('sys_get_temp_dir') )
		return trailingslashit(sys_get_temp_dir());

	return '/tmp/';
}

/**
 * get_filesystem_method() - Get a temporary directory to try writing files to
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function get_filesystem_method() {
	$tempFile = tempnam(get_temp_dir(), 'LILINAUPDATE');

	if ( getmyuid() == fileowner($tempFile) ) {
		unlink($tempFile);
		return 'direct';
	} else {
		unlink($tempFile);
	}

	if ( extension_loaded('ftp') ) return 'ftpext';
	if ( extension_loaded('sockets') || function_exists('fsockopen') ) return 'ftpsockets'; //Sockets: Socket extension; PHP Mode: FSockopen / fwrite / fread
	return false;
}

/**
 * request_filesystem_credentials() - Retrieve the FTP access credentials if needed
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function request_filesystem_credentials($form_post, $type = '', $error = false) {
	$req_cred = apply_filters('request_filesystem_credentials', '', $form_post, $type, $error);
	if ( '' !== $req_cred )
		return $req_cred;

	if ( empty($type) )
		$type = get_filesystem_method();

	if ( 'direct' == $type )
		return true;
		
	if( ! $credentials = get_option('ftp_credentials') )
		$credentials = array();
	// If defined, set it to that, Else, If POST'd, set it to that, If not, Set it to whatever it previously was(saved details in option)
	$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : (!empty($_POST['hostname']) ? $_POST['hostname'] : $credentials['hostname']);
	$credentials['username'] = defined('FTP_USER') ? FTP_USER : (!empty($_POST['username']) ? $_POST['username'] : $credentials['username']);
	$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : (!empty($_POST['password']) ? $_POST['password'] : $credentials['password']);
	$credentials['ssl']      = defined('FTP_SSL')  ? FTP_SSL  : (!empty($_POST['ssl'])      ? $_POST['ssl']      : $credentials['ssl']);

	if ( ! $error && !empty($credentials['password']) && !empty($credentials['username']) && !empty($credentials['hostname']) ) {
		$stored_credentials = $credentials;
		unset($stored_credentials['password']);
		update_option('ftp_credentials', $stored_credentials);
		return $credentials;
	}
	$hostname = '';
	$username = '';
	$password = '';
	$ssl = '';
	if ( !empty($credentials) )
		extract($credentials, EXTR_OVERWRITE);
	if( $error )
		echo '<div id="message" class="error"><p>' . __('<strong>Error:</strong> There was an error connecting to the server, Please verify the settings are correct.') . '</p></div>';
?>
<form action="<?php echo $form_post ?>" method="post">
	<h2><?php _e('FTP Connection Information') ?></h2>
	<p><?php _e('To perform the requested update, FTP connection information is required.') ?></p>
		<p class="option">
			<label for="hostname"><?php _e('Hostname:') ?></label>
			<input name="hostname" type="text" id="hostname" value="<?php echo attribute_escape($hostname) ?>"<?php if( defined('FTP_HOST') ) echo ' disabled="disabled"' ?> size="40" />
		</p>
		<p class="option">
			<label for="username"><?php _e('Username:') ?></label>
			<input name="username" type="text" id="username" value="<?php echo attribute_escape($username) ?>"<?php if( defined('FTP_USER') ) echo ' disabled="disabled"' ?> size="40" />
		</p>
		<p class="option">
			<label for="password"><?php _e('Password:') ?></label>
			<input name="password" type="password" id="password" value=""<?php if( defined('FTP_PASS') ) echo ' disabled="disabled"' ?> size="40" /><?php if( defined('FTP_PASS') && !empty($password) ) echo '<em>'._r('(Password not shown)').'</em>'; ?>
		</p>
		<p class="option">
			<label for="ssl"><?php _e('Use SSL:') ?></label>
			<select name="ssl" id="ssl"<?php if( defined('FTP_SSL') ) echo ' disabled="disabled"' ?>>
			<?php
			foreach ( array(0 => _r('No'), 1 => _r('Yes') ) as $key => $value ) :
				$selected = ($ssl == $value) ? 'selected="selected"' : '';
				echo "\n\t<option value='$key' $selected>" . $value . '</option>';
			endforeach;
			?>
			</select>
		</p>
		<input type="submit" name="submit" value="<?php _e('Proceed'); ?>" />
</form>
<?php
	return false;
}

/**
 * lilina_filesystem() - Retrieve the FTP access credentials if needed
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function lilina_filesystem( $args = false, $preference = false ) {
	global $lilina_filesystem;

	$method = get_filesystem_method($preference);
	if ( ! $method )
		return false;

	require_once(LILINA_INCPATH . '/core/class-wp-filesystem-' . $method . '.php');
	$method = "lilina_filesystem_$method";

	$lilina_filesystem = new $method($args);

	if ( $lilina_filesystem->errors->get_error_code() )
		return false;

	if ( !$lilina_filesystem->connect() )
		return false; //There was an erorr connecting to the server.

	return true;
}
?>