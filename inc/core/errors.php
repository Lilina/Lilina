<?php
/**
* Error handler
*
* Error handler for Magpie and all other errors
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

defined('LILINA') or die('Restricted access');
global $end_errors;
$end_errors = '';
// we will do our own error handling
error_reporting(0);
/**
 * Our error handler. Not exactly the best, but good enough
 */
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){
	global $end_errors;
	global $settings;
	if(!defined('E_STRICT')) {
		//Hack for PHP 4
		define('E_STRICT', 2048);
	}
	//If debug mode isn't on...
	if($settings['debug']!==true){
		//Check if we want to handle the error
		switch($errno) {   
			case E_USER_NOTICE:
			case E_NOTICE:
			case E_USER_WARNING:
			case E_COMPILE_WARNING:
			case E_CORE_WARNING:
			case E_WARNING:
			case E_STRICT:
				//We don't care, bye bye!
				return;
			case E_USER_ERROR:
			case E_COMPILE_ERROR:
			case E_CORE_ERROR:
			case E_ERROR:
			case E_PARSE:
			default:
				//Step right in, Sir ;)
				break;
		}
	}
	// timestamp for the error entry
	$dt = date("Y-m-d H:i:s (T)");

	// define an assoc array of error string
	$errortype = array (
				E_ERROR				=> _r('Error'),
				E_WARNING			=> _r('Warning'),
				E_PARSE				=> _r('Parsing Error'),
				E_NOTICE			=> _r('Notice'),
				E_CORE_ERROR		=> _r('Core Error'),
				E_CORE_WARNING		=> _r('Core Warning'),
				E_COMPILE_ERROR		=> _r('Compile Error'),
				E_COMPILE_WARNING	=> _r('Compile Warning'),
				E_USER_ERROR		=> _r('User Error'),
				E_USER_WARNING		=> _r('User Warning'),
				E_USER_NOTICE		=> _r('User Notice'),
				E_STRICT			=> _r('Runtime Notice')
				);
	// set of errors for which a var trace will be saved
	$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
	$err = "<errorentry>\n";
	$err .= "\t<datetime>" . $dt . "</datetime>\n";
	$err .= "\t<errornum>" . $errno . "</errornum>\n";
	$err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
	$err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
	$err .= "\t<scriptname>" . $filename . "</scriptname>\n";
	$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";
	if (in_array($errno, $user_errors)) {
		$err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
	}
	$err .= "</errorentry>\n\n";
	if(strpos($errmsg, 'Magpie')===false){
		if($settings['debug'] === true) {
			echo '<div style="border:1px solid #e7dc2b;background: #fff888;"><h1>Error in script!</h1><pre>'
			. htmlentities($err) . '</pre>';
		}
	}
	else {
		$end_errors .= '<div style="border:1px solid #e7dc2b;background: #fff888;">
<p>There was an error fetching feed ' .
substr(strpos($errmsg, 'Failed to fetch:'),strpos($errmsg, '(')) .
'<br />Error returned was ' .
substr(strpos($errmsg, '('), strpos($errmsg, ')')) .
'</p></div>';
	}
}
$old_error_handler = set_error_handler('userErrorHandler');
?> 