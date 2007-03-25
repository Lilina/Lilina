<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		misc-functions.php
Purpose:	Miscellaneous functions
Notes:		
Functions:	lilina_time_start();
			lilina_time_end( $timer_start_time );
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');

// index.php, line 23
function lilina_timer_start() {
	//call_hooked('lilina_timer_start', 'start');
	//Start measuring execution time
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	//call_hooked('lilina_timer_start', 'end', $starttime);
	return $starttime;
}
// index.php, line 290
function lilina_timer_end($starttime) {
	//call_hooked('lilina_timer_end', 'start');
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	$totaltime = round($totaltime, 2);
	//call_hooked('lilina_timer_start', 'end', $totaltime);
	return $totaltime;
}
?>