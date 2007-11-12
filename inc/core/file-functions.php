<?php
/**
 * Functions that work with serialized files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

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
	if(!$time_table || !is_array($time_table)) {
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
	if(!$data || !is_array($data)) {
		$data = array();
	}
	return $data;
}
?>