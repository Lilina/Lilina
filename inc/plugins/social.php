<?php
/*
Plugin Name: Google Analytics
Plugin URI: http://lilina.cubegames.net/plugins/analytics
Description: Adds the Google Analytics 
Author: Ryan McCue
Author URI: http://cubegames.net
Version: 1.0
Min Version: 1.0
Init: analytics_init
*/
if($lilina['plugins-sys']['version'] >= 1.0) {
	
}
else {
	trigger_plugin_error('version', 'This plugin is not compatible with your version of Lilina'
}

function social_init(){
	register_plugin_function(
							//Function name
							'social_insert',
							//Hook name
							'item_bottom'
							);
}

function social_insert
?> 