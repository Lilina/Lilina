<?php
/*
Plugin Name: Lil Lilina
Plugin URI: http://lilina.cubegames.net/wiki/Plugins:Lil_Lilina
Description: Serves a special version of Lilina to mobile devices
Version: 1.0
Min Version: 1.0
Author: Ryan McCue
Author URI: http://cubegames.net
*/
/* Thanks to http://dev.mobi/node/472 */
function lil_check(){
	$mobile_browser = '0';

	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i',
		strtolower($_SERVER['HTTP_USER_AGENT']))) {
		++$mobile_browser;
	}

	if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or 
		((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
		++$mobile_browser;
	}

	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
	$mobile_agents = array(
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
		'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
		'wapr','webc','winw','winw','xda','xda-');

	if(in_array($mobile_ua,$mobile_agents)) {
		++$mobile_browser;
	}
	if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
		++$mobile_browser;
	}
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
		$mobile_browser=0;
	}


	if($mobile_browser>0) {
		return true;
	}
	return false;
}

//Replaces built in function
function template_load() {
	global $settings;
	if(!lil_check()) {
		require_once(LILINA_INCPATH . '/templates/' . $settings['template'] . '/index.php');
	}
	else {
		//Mobile template
		$mob_temp	= LILINA_INCPATH . '/templates/' . $settings['template'] . '/mobile.php';
		if(file_exists($mob_temp)) {
			require_once($mob_temp);
		}
		else {
			require_once(LILINA_INCPATH . '/plugins/mobile/template.php');
		}
	}
}
register_plugin('mobile.php', 'Lil Lilina');
?> 