<?php
//Hack for Wordpress
function hack_wp(){
	//Wordpress is naughty and doesn't use HTML in RSS feeds.
	//Only uses linebreaks instead. Let's fix that
	if(stripos($items['feeds']['generator'],'wordpress') === TRUE){
		//Might be alright if full-text is on,
		//so we still check first:
		if(!$summary){
			$summary = nl2br($item['description']);
		}
	}
}
function hacks_init(){
register_plugin(
//File
'hacks.php',
//Name
'Wordpress Hacks',
//Description (easier to use a variable)
'Fix Wordpress\'s reluctantcy to use HTML by adding line breaks.',
);
register_plugin_function(
//Function name
'hack_wp',
//Hook name
'hook_before_sanitize',
//Function parameters
'');
}

?> 