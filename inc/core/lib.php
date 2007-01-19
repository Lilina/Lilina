<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		lib.php
Purpose:	Standard require()s and misc.
			functions
Notes:		Move to appropriate files,
			file-functions.php, misc-functions.php etc.
			Move defines and lilina versions
			to seperate files
			CAUTION: HERE BE DRAGONS!
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
//Stop hacking attempts
defined('LILINA') or die('Restricted access');
require_once('./inc/core/conf.php');
require_once('./inc/core/file-functions.php');
require_once('./inc/contrib/HTMLPurifier.php');

//Language specific files:
switch($settings['lang']){
	case 'portugese':
		require_once('./lang/pt_PT.php');
	break;
	case 'english':
	default:
		require_once('./lang/en_EN.php');
	break;
}

/*require_once('./chkenv.php');
require_once('./google.php');
require_once('./delicious.php');*/

// NO NEED TO EDIT BELOW THIS LINE!

//Backwards compatibility only
$LILINAVERSION	= '1.0' ;
$lilina			= array(
						'core-sys'		=> array(
												'version'	=> 1.0
												),
						'plugin-sys'	=> array(
												'version'	=> 1.0
												),
						'template-sys'	=> array(
												'version'	=> 1.0
												),
						);

define('MAGPIE_CACHE_ON',1) ;
define('MAGPIE_CACHE_FRESH_ONLY', true) ;
define('MAGPIE_DIR', './inc/contrib/');
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_USER_AGENT','lilina'. $lilina['core-sys']['version'].'  (+http://lilina.cubegames.net/)') ;
error_reporting(E_ERROR);
require_once(MAGPIE_DIR.'magpie.php');

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


function getRSSLocation($html, $location){
/*
	getRSSLocation() was found at 
	http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP
*/
    if(!$html or !$location){
        return false;
    }else{
        //search through the HTML, save all <link> tags
        //and store each link's attributes in an associative array
        preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
        $links = $matches[1];
        $final_links = array();
        $link_count = count($links);
        for($n=0; $n<$link_count; $n++){
            $attributes = preg_split('/\s+/s', $links[$n]);
            foreach($attributes as $attribute){
                $att = preg_split('/\s*=\s*/s', $attribute, 2);
                if(isset($att[1])){
                    $att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
                    $final_link[strtolower($att[0])] = $att[1];
                }
            }
            $final_links[$n] = $final_link;
        }
        //now figure out which one points to the RSS file
        for($n=0; $n<$link_count; $n++){
            if(strtolower($final_links[$n]['rel']) == 'alternate'){
                if(strtolower($final_links[$n]['type']) == 'application/rss+xml'){
                    $href = $final_links[$n]['href'];
                }
                if(!$href and strtolower($final_links[$n]['type']) == 'text/xml'){
                    //kludge to make the first version of this still work
                    $href = $final_links[$n]['href'];
                }
                if(!$href and strtolower($final_links[$n]['type']) == 'application/atom+xml'){
                    //kludge to make the first version of this still work
                    $href = $final_links[$n]['href'];
                }
                if($href){
                    if(strstr($href, "http://") !== false){ //if it's absolute
                        $full_url = $href;
                    }else{ #otherwise, 'absolutize' it
                        $url_parts = parse_url($location);
						// echo "<pre>" ; print_r($url_parts) ; echo "</pre>" ;
                        //only made it work for http:// links. Any problem with this?
                        $full_url = "http://$url_parts[host]";
                        if(isset($url_parts['port'])){
                            $full_url .= ":$url_parts[port]";
                        }
                        if($href{0} != '/'){ //it's a relative link on the domain
                               if (substr($url_parts['path'],-1)!='/') {
                                        $full_url .= dirname($url_parts['path']);
                                } else {
                                        $full_url .= $url_parts['path'] ;
                                }
                        }
                        $full_url .= $href;
                    }
                    return $full_url;
                }
            }
        }
        return false;
    }
}

function getFaviconURL($location){
    if(!$location){
        return false;
    }else{
	$url_parts = parse_url($location);
	$full_url = "http://$url_parts[host]";
	if(isset($url_parts['port'])){
		$full_url .= ":$url_parts[port]";
	}
	$favicon_url = $full_url . "/favicon.ico" ;
    }
    return $favicon_url;
}

function channelFavicon($location) {
	global $empty_ico_data ;
	$ico_url = getFaviconURL($location) ;
	if(!$ico_url) {
		return false ;
	}
	$cached_ico = './cache/' . md5($ico_url) . ".ico" ;
    	// echo "<br> $ico_url , $cached_ico " ;
	// Serve from the cache if it is younger than $cachetime
	if (file_exists($cached_ico) && (time() - $cachetime < filemtime($cached_ico))) return $cached_ico ;
	if (!$data = @file_get_contents($ico_url)) $data=$empty_ico_data ;
	if (stristr($data,'html')) $data=$empty_ico_data ;
	$fp = fopen($cached_ico,'w') ;
	fputs($fp,$data) ;
	fclose($fp) ;
	return $cached_ico ;
}

// use time() for items with no timestamp and save it in

function create_time($s) {
	global $time_table;

	$md5 = md5($s);
	if ($time_table[$md5] <= 0) {
		$time_table[$md5] = time() + $settings['offset'] * 60 * 60;
	}
	return $time_table[$md5];
}

/* Function used to sort rss items in chronological order */
function date_cmp($a, $b) {
   if ($a['date_timestamp'] == $b['date_timestamp'] ) {
		#descending order
		return ( strcmp($a['title'], $b['title']) == 1 ) ? -1 : 1; 
   }    
   return ($a['date_timestamp'] > $b['date_timestamp'] ) ? -1 : 1;
}

/*function parseHtml($val){
	if($settings['encoding']!='utf-8'){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', $settings['encoding']); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$purifier = new HTMLPurifier($config);
		}
    else {
		$purifier = new HTMLPurifier();
		}		
	$val = $purifier->purify($val);
	if($settings['encoding']!='utf-8') $val = escapeNonASCIICharacters($val);  
	return $val;
}*/
// adapted from utf8ToUnicode by Henri Sivonen and
// hsivonen@iki.fi at <http://iki.fi/hsivonen/php-utf8/>
function escapeNonASCIICharacters($str) {
   
    $mState = 0; // cached expected number of octets after the current octet
                 // until the beginning of the next UTF8 character sequence
    $mUcs4  = 0; // cached Unicode character
    $mBytes = 1; // cached expected number of octets in the current sequence
   
    // original code involved an $out that was an array of Unicode
    // codepoints.  Instead of having to convert back into UTF-8, we've
    // decided to directly append valid UTF-8 characters onto a string
    // $out once they're done.  $char accumulates raw bytes, while $mUcs4
    // turns into the Unicode code point, so there's some redundancy.
   
    $out = '';
    $char = '';
   
    $len = strlen($str);
    for($i = 0; $i < $len; $i++) {
        $in = ord($str{$i});
        $char .= $str[$i]; // append byte to char
        if (0 == $mState) {
            // When mState is zero we expect either a US-ASCII character
            // or a multi-octet sequence.
            if (0 == (0x80 & ($in))) {
                // US-ASCII, pass straight through.
                if (($in <= 31 || $in == 127) &&
                    !($in == 9 || $in == 13 || $in == 10) // save \r\t\n
                ) {
                    // control characters, remove
                } else {
                    $out .= $char;
                }
                // reset
                $char = '';
                $mBytes = 1;
            } elseif (0xC0 == (0xE0 & ($in))) {
                // First octet of 2 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;
            } elseif (0xE0 == (0xF0 & ($in))) {
                // First octet of 3 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;
            } elseif (0xF0 == (0xF8 & ($in))) {
                // First octet of 4 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;
            } elseif (0xF8 == (0xFC & ($in))) {
                // First octet of 5 octet sequence.
                //
                // This is illegal because the encoded codepoint must be
                // either:
                // (a) not the shortest form or
                // (b) outside the Unicode range of 0-0x10FFFF.
                // Rather than trying to resynchronize, we will carry on
                // until the end of the sequence and let the later error
                // handling code catch it.
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x03) << 24;
                $mState = 4;
                $mBytes = 5;
            } elseif (0xFC == (0xFE & ($in))) {
                // First octet of 6 octet sequence, see comments for 5
                // octet sequence.
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 1) << 30;
                $mState = 5;
                $mBytes = 6;
            } else {
                // Current octet is neither in the US-ASCII range nor a
                // legal first octet of a multi-octet sequence.
                $mState = 0;
                $mUcs4  = 0;
                $mBytes = 1;
                $char = '';
            }
        } else {
            // When mState is non-zero, we expect a continuation of the
            // multi-octet sequence
            if (0x80 == (0xC0 & ($in))) {
                // Legal continuation.
                $shift = ($mState - 1) * 6;
                $tmp = $in;
                $tmp = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;
               
                if (0 == --$mState) {
                    // End of the multi-octet sequence. mUcs4 now contains
                    // the final Unicode codepoint to be output
                   
                    // Check for illegal sequences and codepoints.
                   
                    // From Unicode 3.1, non-shortest form is illegal
                    if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                        (4 < $mBytes) ||
                        // From Unicode 3.2, surrogate characters = illegal
                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                        // Codepoints outside the Unicode range are illegal
                        ($mUcs4 > 0x10FFFF)
                    ) {
                       
                    } elseif (0xFEFF != $mUcs4 && // omit BOM
                        !($mUcs4 >= 128 && $mUcs4 <= 159) // omit non-SGML
                    ) {
                        $out .= '&#' . $mUcs4 . ';';
                    }
                    // initialize UTF8 cache (reset)
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                    $char = '';
                }
            } else {
                // ((0xC0 & (*in) != 0x80) && (mState != 0))
                // Incomplete multi-octet sequence.
                // used to result in complete fail, but we'll reset
                $mState = 0;
                $mUcs4  = 0;
                $mBytes = 1;
                $char ='';
            }
        }
    }
    return $out;
}


?>
