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
defined('LILINA') or die('Restricted access');
global $activated_plugins, $registered_plugins, $hooked_plugins;
$activated_plugins	= @file_get_contents($settings['files']['plugins']) ;
$activated_plugins	= unserialize( base64_decode( $activated_plugins ) ) ;

//get_hooked( hook_name );
function get_hooked($hook) {
	return $hooked_plugins[$hook];
}

function call_hooked($hook, $pos, $args){
	//Get list of plugins hooked here...
	$plugins = get_hooked($hook);
	for($plugin = 0; $plugin < count($plugins); $plugin++) {
		if(isset($pos) && $plugins[$plugin]['pos'] == $pos) {
			$plugin_function = $plugins[$plugin]['func'];
			$plugin_function($args);
		}
		elseif(!isset($pos)) {
			$plugin_function = $plugins[$plugin]['func'];
			$plugin_function($args);
		}
	}
}

function register_plugin($file, $name) {
	$registered_plugins[$name]	= array(
										'file'	=> $file
										);
}

function register_plugin_function($function, $hook, $position) {
	$hooked_plugins[$hook][]	= array(
										'func'	=> $function,
										'pos'	=> $position
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

function plugins_meta($plugin_file) {
	//Thanks to Wordpress, admin-functions.php, lines 1525-1534
	$plugin_data = implode('', file($plugin_file));
	preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	//If the plugin sets the version...
	if (preg_match("|Version:(.*)|i", $plugin_data, $version)) {
		//...Let it
		$version = trim($version[1]); //F1
	}
	else {
		//...Otherwise assume it's 1.0
		$version = 1.0;
	}
	//If the plugin sets the version...
	if (preg_match("|Min Version:(.*)|i", $plugin_data, $min_version)) {
		//...Let it
		$version = trim($min_version[1]); //F1
	}
	else {
		//...Otherwise assume it's the current version of Lilina
		$version = 1.0;
	}
	//Set the $plugin array for returning
	$plugin					= array();
	$plugin['name']			= $plugin_name[1]; //F1
	$plugin['uri']			= $plugin_uri[1]; //F1
	$plugin['description']	= $description[1]; //F1
	$plugin['author']		= $author_name[1]; //F1
	$plugin['author_uri']	= $author_uri[1]; //F1
	$plugin['version']		= $version[1]; //F1
	//Footnote 1: 	The 1st item [0] is the item found while the 2nd [1] is the content
	//				We always want the content, so we use $metadata[1]
}
?>