<?php
/**
 * Miscellaneous functions
 *
 * Any and all functions that don't fit anywhere else
 *
 * @todo Need to move functions to appropriate files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//Stop hacking attempts
defined('LILINA') or die('Restricted access');

/**
 * @todo Document
 */
//require_once('./inc/core/file-functions.php');
require_once(LILINA_INCPATH . '/contrib/HTMLPurifier.standalone.php');
require_once(LILINA_INCPATH . '/core/version.php');

define('MAGPIE_CACHE_ON',1) ;
define('MAGPIE_CACHE_FRESH_ONLY', true) ;
//define('MAGPIE_CACHE_DIR', './cache');
define('MAGPIE_OUTPUT_ENCODING', $settings['encoding']);
define('MAGPIE_USER_AGENT','Lilina/'. $lilina['core-sys']['version'].'; '.$settings['baseurl']) ;
require_once(LILINA_INCPATH . '/contrib/magpie.php');

$empty_ico_data = base64_decode(
'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAABMLAAATCwAAAAAA' .
'AAAAAAAVXcwQKXLZjzWF6P81hen/NILo/zN/5v8yfOX/Mnnj/zF24v8wdOH/MHHf/y9v3v8vbt3/' .
'MHDe/yly2Y8VXcwQKXLZj0Gd+f85mfj/N5L1/zWL8f80he7/Mn/r/zF56P8vdOb/Lm7j/y1q4P8s' .
'Zd7/K2Hc/ype2v82cOL/KXLZjzaI6f85nPr/Op37/1Kl+f9prff/NYvx/zSF7v8yf+v/ZJru/2OW' .
'7P8ubuP/R3zk/2CL5v9FdeD/Kl7a/zBw3v82iuz/OZr5/1Kp+//z+f///////6fO+v81i/H/Z6Tz' .
'///////Y5vv/L3Tm/5e38f//////lbLu/yth3P8vbt3/NYnr/ziX9/9qs/r////////////N5f3/' .
'N5L1/46/+P//////v9f5/zF56P++1Pf//////4mr7v8sZd7/L2/e/zWH6v83lPb/OJf3/8Hf/f/O' .
'5v7/hML8/0Wf+f/m8v7//////4Cy9P8yf+v/8vf+//////9Viej/LWrg/zBx3/81hen/NpH0/zeU' .
'9v84l/f/OZr5/0ai+v+12v3//////+bx/v9Ck/L/c6z0///////y9/7/L3Tm/y5u4/8wdOH/NIPo' .
'/zWN8v82kfT/UKH3/4O++v/m8v7///////P5//9erPr/N5L1/+bx/f//////mb/1/zF56P8vdOb/' .
'MXbi/zOB5/80ifD/aKn1//////////////////P5//9er/v/Op37/7XZ/P//////8vj+/0GN7/8y' .
'f+v/MXno/zJ54/8zf+b/M4Xu/2em9P/y+P7/zeP8/4/D+v9Envj/OZr5/7Xa/f///////////3W0' .
'+P81i/H/NIXu/zJ/6/8yfOX/Mn3l/zKB7P8zhe7/NInw/zWN8v82kfT/aa/4/9rs/v//////////' .
'/4TC/P85mfj/N5L1/zWL8f80he7/M3/m/zJ75P8xfer/TJHu/5nD9/+z0/r/5vH9////////////' .
'8/n//3e5+/85nPr/Op37/zmZ+P83kvX/NYvx/zSC6P8xeeP/L3jn/2Sd7//////////////////y' .
'+P7/m8j5/0Sb9v84l/f/OZr5/zmc+v86nfv/OZn4/zeS9f81hen/MXjh/y505f9Jier/mL30/5jA' .
'9f9mo/L/NInw/zWN8v82kfT/N5T2/ziX9/85mvn/OZz6/zqd+/85mfj/NYXo/yly2Y85fun/LnTl' .
'/y945/8xfer/MoHs/zOF7v80ifD/NY3y/zaR9P83lPb/OJf3/zma+f85nPr/QZ35/yly2Y8VXcwQ' .
'KXLZjzF44f8xeeP/Mnvk/zJ95f8zf+b/M4Hn/zSD6P81hen/NYfq/zWJ6/82iuz/Nojp/yly2Y8V' .
'XcwQgAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
'AAAAAAAAgAEAAA==');

/**
 * Gets the URL for a favicon for a given URL
 *
 * @todo Document
 * @see channel_favicon
 * @param string $location Web site to look for favicon at
 * @return string
 */
function get_favicon_url($location){
	if(!$location) {
		return false;
	}
	else {
		$url_parts		= parse_url($location);
		$full_url		= 'http://' . $url_parts['host'];
		if(isset($url_parts['port'])){
			$full_url	.= ':' . $url_parts['port'];
		}
		$favicon_url	= $full_url . '/favicon.ico';
	}
	return $favicon_url;
}

/**
 * Gets the favicon for a feed and caches it
 *
 * @todo Document
 * @param string $location Web site location
 * @return string
 */
function channel_favicon($location) {
	global $empty_ico_data, $settings ;
	$cached_ico			= $settings['cachedir'] . md5($location) . '.ico' ;
	$cached_ico_url		= $settings['baseurl'] . 'cache/' . md5($location) . '.ico';
	// Serve from the cache if it is younger than $cachetime
	if (file_exists($cached_ico) && (time() - $settings['cachetime'] < filemtime($cached_ico))){
		return $cached_ico_url;
	}
	$ico_url			= get_favicon_url($location) ;
	if(!$ico_url) {
		return false ;
	}
    	// echo "<br> $ico_url , $cached_ico " ;
	if (!$data = @file_get_contents($ico_url)) {
		$data			= $empty_ico_data;
	}
	elseif (stristr($data,'html')) {
		$data			= $empty_ico_data;
	}
	$fp					= fopen($cached_ico,'w');
	fputs($fp,$data);
	fclose($fp);
	return $cached_ico_url;
}

/**
 * Creates a timestamp via time() and saves it in the $time_table variable
 *
 * @todo Document
 * @param string $s Name of item, must be unique
 * @return int
 */

function create_time($s) {
	global $time_table, $settings;

	$md5 = md5($s);
	if (!isset($time_table[$md5]) || $time_table[$md5] <= 0) {
		$time_table[$md5] = time() + ($settings['offset'] * 60 * 60);
	}
	return $time_table[$md5];
}

/**
 * Function used to sort rss items in chronological order
 *
 * @todo Document
 * @param array $a First feed item
 * @param array $b Second feed item
 * @return int
 */
function date_cmp($a, $b) {
   if ($a['date_timestamp'] == $b['date_timestamp'] ) {
		#descending order
		return ( strcmp($a['title'], $b['title']) == 1 ) ? -1 : 1; 
   }    
   return ($a['date_timestamp'] > $b['date_timestamp'] ) ? -1 : 1;
}

if(!function_exists('parse_w3cdtf')) {
	/**
	 * From MagpieRSS' rss_utils.inc - Converts a -- date formatted string to
	 * time from epoch in seconds
	 *
	 * @todo Document
	 * @param string $date_str -- formatted date
	 * @return int
	 */
	function parse_w3cdtf ( $date_str ) {
	    // regex to match wc3dtf
	    $pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";
	    
	    if ( preg_match( $pat, $date_str, $match ) ) {
	        list( $year, $month, $day, $hours, $minutes, $seconds) = 
	            array( $match[1], $match[2], $match[3], $match[4], $match[5], $match[7]);
	        
	        // calc epoch for current date assuming GMT
	        $epoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year);
	        
	        $offset = 0;
	        if ( $match[10] == 'Z' ) {
	            // zulu time, aka GMT
	        }
	        else {
	            list( $tz_mod, $tz_hour, $tz_min ) =
	                array( $match[8], $match[9], $match[10]);
	            
	            // zero out the variables
	            if ( ! $tz_hour ) { $tz_hour = 0; }
	            if ( ! $tz_min ) { $tz_min = 0; }
	        
	            $offset_secs = (($tz_hour*60)+$tz_min)*60;
	            
	            // is timezone ahead of GMT?  then subtract offset
	            if ( $tz_mod == '+' ) {
	                $offset_secs = $offset_secs * -1;
	            }
	            
	            $offset = $offset_secs; 
	        }
	        $epoch = $epoch + $offset;
	        return $epoch;
	    }
	    else {
	        return -1;
	    }
	}
}
?>