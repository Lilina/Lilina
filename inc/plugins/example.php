<?php
/*
Plugin Name: Google Analytics
Plugin URI: http://lilina.cubegames.net/plugins/analytics
Description: Adds the Google Analytics 
Author: Ryan McCue
Version: 1.0
Min Version: 1.0
Author URI: http://cubegames.net
License: GPL
*/
if($lilina['plugins-sys']['version'] >= 1.0) {
	
}
function analytics_settings(){
	get_settings('analytics');
}
function analytics_init(){
	register_plugin_function(
							//Function name
							'hack_wp',
							//Hook name
							'hook_before_sanitize'
							);
}
register_plugin(
//File
'hacks.php',
//Name
'Wordpress Hacks',
//Initialization function
'analytics_init',
);
?> 