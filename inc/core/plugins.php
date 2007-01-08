<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		plugins.php
Purpose:	Plugin controls
Notes:		
Functions:
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
//Stop hacking attempts
define('LILINA',1) ;
$activated_plugins	= file_get_contents($settings['files']['plugins']) ;
$activated_plugins	= unserialize( base64_decode( $hooked_plugins ) ) ;

//get_hooked( hook_name );
function get_hooked($hook) {
	return $hooked_plugins[$hook];
}

function register_plugin($file, $name, $description = '') {
	$registered_plugins[$name] = array(
										'file'	=> $file,
										'desc'	=> $description
										);
}

function register_plugin_function($function, $hook, $params = '') {
	$hooked_plugins[$hook][] = array(
										'file'	=> $file,
										'desc'	=> $description
										);
}
?>