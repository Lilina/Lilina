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
	if ( strpos($_SERVER['REQUEST_URI'], 'install.php') !== false || defined('LILINA_INSTALLING') || !is_admin() )
		return;

	$lilina_version = LILINA_CORE_VERSION;
	$php_version = phpversion();
	// We need this for unique identification of installations, but we take the hash of it
	$id = sha1(get_option('baseurl'));

	$data = new DataHandler();
	$current = $data->load('core-update-check.data');
	if($current !== null)
		$current = unserialize($current);

	$locale = get_option('locale');

	if (
		isset( $current->last_checked ) &&
		43200 > ( time() - $current->last_checked ) &&
		$current->version_checked == $lilina_version
	)
		return false;

	$new_option = '';
	$new_option->last_checked = time(); // this gets set whether we get a response or not, so if something is down or misconfigured it won't delay the page load for more than 3 seconds, twice a day
	$new_option->version_checked = $lilina_version;

	try {
		$headers = apply_filters('update_http_headers', array('X-Install-ID' => $id));
		$request = new HTTPRequest('', 2);
		$response = $request->get("http://api.getlilina.org/core/version-check/1.2/?version=$lilina_version&php=$php_version&locale=$locale", $headers);
	}
	catch (Exception $e) {
		$response = (object) array('success' => false);
	}

	if ( !$response->success ) {
		// Save it anyway
		$data->save('core-update-check.data', serialize($new_option));
		return false;
	}

	$body = trim( $response->body );
	$body = str_replace(array("\r\n", "\r"), "\n", $body);

	$returns = explode("\n", $body);

	$new_option->response = $returns[0];
	if ( isset( $returns[1] ) )
		$new_option->url = $returns[1];
	if ( isset( $returns[2] ) )
		$new_option->download = $returns[2];
	if ( isset( $returns[3] ) )
		$new_option->version = $returns[3];

	$data->save('core-update-check.data', serialize($new_option));
	return $new_option;
}

/**
 * @todo Document
 * @author WordPress
 */
function lilina_footer_version() {
	$data = new DataHandler();
	$cur = $data->load('core-update-check.data');
	if($cur === null)
		return false;

	$cur = unserialize($cur);
	if(empty($cur->response))
		return false;

	switch ( $cur->response ) {
		case 'development' :
			printf(' | '._r( 'You are using a development version (%1$s). Thanks! Make sure you <a href="%2$s">stay updated</a>.' ), LILINA_CORE_VERSION, $cur->url);
		break;

		case 'upgrade' :
			printf(' | <strong>'._r( '<a href="%1$s">Get Version %2$s</a>.' ).'</strong>', $cur->url, $cur->version);
		break;

		case 'latest' :
		default :
			printf(' | '._r( 'Version %s' ), LILINA_CORE_VERSION);
		break;
	}

	if ( is_file(LILINA_PATH . '/.svn/entries') ) {
		$file = file(LILINA_PATH . '/.svn/entries');
		printf(' | ' . _r('SVN revision %s'), trim($file[3]));
	}
}

/**
 * @todo Document
 * @author WordPress
 */
function update_nag() {
	$data = new DataHandler();
	$cur = $data->load('core-update-check.data');
	if($cur === null)
		return false;

	$cur = unserialize($cur);

	if ( !isset( $cur->response ) || $cur->response != 'upgrade' )
		return false;

	$msg = sprintf(_r('Lilina %1$s is available! <a href="%2$s">Please update now</a>.'), $cur->version, $cur->url);

	echo "<div id='update-nag' class='message'>$msg</div>";
}
?>
