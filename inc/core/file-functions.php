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

function lilina_load_times($times) {

}
// index.php, line 200
function lilina_save_times($times) {
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
// feed-functions.php, line 38-43
function lilina_parse_html($val){
	if($settings['encoding']!='utf-8'){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', 'utf-8'); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$purifier = new HTMLPurifier($config);
		}
    else {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', $settings['encoding']); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$purifier = new HTMLPurifier();
		}		
	$val = $purifier->purify($val);
	if($settings['encoding']!='utf-8') {
		$val = escapeNonASCIICharacters($val);
	}
	return $val;
}
?>