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

//get_hooked( hook_name );
function get_hooked($hook) {
	
}


//Index.php, line 107
function hook_before_parse(){
	//Get list of plugins here:
	$plugins = get_hooked('hook_before_parse');
	for($plugin = 0; $plugin < count($plugins); $plugin++) {
		$plugin_function = 0;
		$plugin_function = $plugins[;
	}
}