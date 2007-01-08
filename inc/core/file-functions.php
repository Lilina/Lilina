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
function lilina_save_times($times) {
	// save times
	$ttime = serialize($times);
	$fp = fopen($settings['files']['times'],'w') ;
	fputs($fp, $ttime) ;
	fclose($fp) ;
}
?>