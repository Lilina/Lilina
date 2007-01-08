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
global $activated_plugins;
$activated_plugins	= @file_get_contents($settings['files']['plugins']) ;
$activated_plugins	= unserialize( base64_decode( $activated_plugins ) ) ;

//get_hooked( hook_name );
function get_hooked($hook) {
	return $hooked_plugins[$hook];
}

function register_plugin($file, $name, $description = '') {
	$registered_plugins[$name]	= array(
										'file'	=> $file,
										'desc'	=> $description
										);
}

function register_plugin_function($function, $hook, $params = '') {
	$hooked_plugins[$hook][]	= array(
										'file'	=> $file,
										'desc'	=> $description
										);
}

function activate_plugin($plugin) {
	$activated_plugins[] 		= $plugin;
}

function get_plugins() {
	for($plugin = 0; $plugin < count($activated_plugins); $plugin++){
		$plugin_name	= $activated_plugins[$plugin]
		require_once('./inc/plugins/' . $registered_plugins[$plugin_name]['file']);
	}
}
?>