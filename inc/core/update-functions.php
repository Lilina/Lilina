<?php
/**
 *
 * @package Lilina
 * @subpackage Updater
 */
/**
 * Checks to see if a new version of Lilina is available
 * @author WordPress
 */
function lilina_version_check() {
	if ( !function_exists('fsockopen') || strpos($_SERVER['PHP_SELF'], 'install.php') !== false || defined('LILINA_INSTALLING') || !is_admin() )
		return;

	global $lilina;
	//Just to make sure
	require_once(LILINA_INCPATH . '/core/version.php');
	require_once(LILINA_INCPATH . '/core/conf.php');
	$lilina_version = $lilina['core-sys']['version'];
	$php_version = phpversion();

	$current = get_option('update_status');
	$locale = get_option('lang');

	if (
		isset( $current->last_checked ) &&
		43200 > ( time() - $current->last_checked ) &&
		$current->version_checked == $lilina_version
	)
		return false;

	$new_option = '';
	$new_option->last_checked = time(); // this gets set whether we get a response or not, so if something is down or misconfigured it won't delay the page load for more than 3 seconds, twice a day
	$new_option->version_checked = $lilina_version;

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$request = new SimplePie_File("http://api.getlilina.org/version-check/lilina-core/?version=$lilina_version&php=$php_version&locale=$locale",
		2, //Timeout
		0, //No. of redirects allowed
		$headers,
		"Lilina/$lilina_version;  " . get_option('baseurl')
	);
	update_option('update_status', $new_option);
	return var_dump($request);

	if ( $request->success ) {
		$body = trim( $request->body );
		$body = str_replace(array("\r\n", "\r"), "\n", $body);

		$returns = explode("\n", $body);

		$new_option->response = $returns[0];
		if ( isset( $returns[1] ) )
			$new_option->url = $returns[1];
	}
}

/**
 * @todo Document
 * @author WordPress
 */
function lilina_footer_version() {
	global $lilina;
	$cur = get_option('update_status');
	if(!is_admin() || !is_object($cur)) {
		echo $lilina['core-sys']['version'];
	}

	switch ( $cur->response ) {
		case 'development' :
			printf(' | '._r( 'You are using a development version (%s). Thanks! Make sure you <a href="%s">stay updated</a>.' ), $lilina['core-sys']['version'], 'http://getlilina.org/download/#svn');
		break;

		case 'upgrade' :
			printf(' | <strong>'._r( 'Your installation of Lilina (%s) is out of date. <a href="%s">Please update</a>.' ).'</strong>', $lilina['core-sys']['version'], $cur->url);
		break;

		case 'latest' :
		default :
			printf(' | '._r( 'Version %s' ), $lilina['core-sys']['version']);
		break;
	}
}

/**
 * @todo Document
 * @author WordPress
 */
function update_nag() {
	$cur = get_option( 'update_status' );

	if ( ! isset( $cur->response ) || $cur->response != 'upgrade' )
		return false;

	$msg = sprintf(_r('A new version of Lilina is available! <a href="%s">Please update now</a>.'), $cur->url);

	echo "<div id='update-nag'>$msg</div>";
}
?>