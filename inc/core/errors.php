<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		errors.php
Purpose:	Error handler
Notes:		Must turn off the non-fatal ones
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
$end_errors = '';
// we will do our own error handling
error_reporting(0);
$log = '<?xml version="1.0" encoding="UTF-8"?>
<errlog generator="Lilina">';
// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){
	// timestamp for the error entry
	$dt = date("Y-m-d H:i:s (T)");
	//Hack for old systems
	if(defined('E_STRICT')) {
		$errortype[E_STRICT] = 'Runtime Notice';
	}

	// define an assoc array of error string
	// in reality the only entries we should
	// consider are E_WARNING, E_NOTICE, E_USER_ERROR,
	// E_USER_WARNING and E_USER_NOTICE
	$errortype = array (
				E_ERROR              => 'Error',
				E_WARNING            => 'Warning',
				E_PARSE              => 'Parsing Error',
				E_NOTICE            => 'Notice',
				E_CORE_ERROR        => 'Core Error',
				E_CORE_WARNING      => 'Core Warning',
				E_COMPILE_ERROR      => 'Compile Error',
				E_COMPILE_WARNING    => 'Compile Warning',
				E_USER_ERROR        => 'User Error',
				E_USER_WARNING      => 'User Warning',
				E_USER_NOTICE        => 'User Notice',
				E_STRICT            => 'Runtime Notice',
				E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
				);
	switch($errno) {   
		case E_USER_NOTICE:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_COMPILE_WARNING:
		case E_CORE_WARNING:
		case E_WARNING:
		case E_STRICT:
			$care = false;
			break;
		case E_USER_ERROR:
		case E_COMPILE_ERROR:
		case E_CORE_ERROR:
		case E_ERROR:
			$care = true;
			break;
		case E_PARSE:
			$care = true;
			break;
		default:
			$care = true;
			break;
	}
	if($settings['debug']===true){
		//If we are in debug mode, we care no matter what
		$care = true;
	}
	//Errors we care about
	if($care==true) {
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
		?><h1>Error in script!</h1>
<p>An error in the script has been detected. Please report this to the site administrator with the following report:</p>
<pre style="border: 1px #000000 solid; background: #D9FFD9;">
<?php
			echo htmlentities($err);
?>
</pre>
	<?php
		}
		else {
			$end_errors .= '
<h1>Feed error</h1>
<p>There was an error fetching feed ' .
substr(strpos($errmsg, 'Failed to fetch:'),strpos($errmsg, '(')) .
'<br />Error returned was ' .
substr(strpos($errmsg, '('), strpos($errmsg, ')')) .
'</p>';
		}
		//echo $err;

		// save to the error log, and e-mail me if there is a critical user error

		$log .= $err;
		if ($errno == E_USER_ERROR) {
			mail($settings['owner']['email'], 'Critical User Error', $err);
		}
	}
}
$log .= '</errlog>';
error_log($log, 3, './error.log');

$old_error_handler = set_error_handler("userErrorHandler");

?> 