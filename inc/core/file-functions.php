<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		file-functions.php
Purpose:	Functions which involve file access
Notes:		
Functions:
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');

function lilina_load_times() {
	global $settings;
	if (file_exists($settings['files']['times'])) {
		$time_table = file_get_contents($settings['files']['times']) ;
		$time_table = unserialize($time_table) ;
	}
	else {
		$time_table = array();
	}
	return $time_table;
}
// index.php, line 200
function lilina_save_times($times) {
	global $settings;
	// save times
	$ttime = serialize($times);
	$fp = fopen($settings['files']['times'],'w') ;
	fputs($fp, $ttime) ;
	fclose($fp) ;
}
// index.php, line 41
function lilina_load_feeds($data_file) {
	$data = file_get_contents($data_file) ;
	$data = unserialize( base64_decode($data) ) ;
	return $data;
}
?>