<?php
//Index.php, line 107
function hook_before_parse(){
	//Get list of plugins here:
	$plugins = get_hooked('hook_before_parse');
	for($plugin = 0; $plugin < count($plugins); $plugin++) {
		$plugin_function = 0;
		$plugin_function = $plugins[;
	}
}