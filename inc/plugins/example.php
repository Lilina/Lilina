<?php
/*
Plugin Name: CSS Naked Day
Plugin URI: http://lilina.cubegames.net/docs/plugins:naked
Description: Disables style sheets for CSS Naked Day. See the plugin URI for more info.
Author: Ryan McCue
Version: 1.0
Min Version: 1.0
Author URI: http://cubegames.net
License: GPL
*/
if($_GET['action'] !== 'style') {
	defined('LILINA') or die('Restricted access');
	/**
	 * Replaces built in stylesheet loader
	 */
	function stylesheet_load($type='default'){
		global $settings;
		if(!is_naked_day()) {
			return $settings['baseurl'] . 'inc/templates/' . $settings['template'] . '/' . $file;
		}
		return $settings['baseurl'] . 'inc/plugins/example.php?action=style';
	}
	/**
	 * From the CSS Naked Day website
	 * @author Dustin Diaz
	 * @link http://naked.dustindiaz.com/
	 */
	function is_naked_day() {
		$start = date('U', mktime(-12,0,0,04,05,date('Y')));
		$end = date('U', mktime(36,0,0,04,05,date('Y')));
		$z = date('Z') * -1;
		$now = time() + $z;	
		if ( $now >= $start && $now <= $end ) {
			return true;
		}
		return false;
	}
	/**
	 * Display a notice to the users why the styles are disabled
	 */
	function naked_notice() {
		echo '<h3>What happened to the design?</h3> <p>To know more about why styles are disabled on this website visit the <a href="http://naked.dustindiaz.com" title="Web Standards Naked Day Host Website">Annual CSS Naked Day</a> website for more information.</p>';
	}
	/**
	 * Register our plugin
	 */
	register_plugin('example.php','CSS Naked Day');
	register_plugin_function('body_top', 'naked_notice');
}
else {
	header('Content-Type: text/css; charset=utf-8');
	echo '/* Don\'t you know? It\'s CSS Naked Day today. Check it out at http://naked.dustindiaz.com/ */';
}
?>