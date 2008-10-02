<?php
/**
 * Feed handling functions
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

/**
 * lilina_return_items() - Takes an array of feeds and returns all channels and all items from them
 *
 * Takes an input array and parses it using the SimplePie library. Returns a SimplePie object.
 * @param array $input Input array of user specified feeds
 * @return object SimplePie object with all feed's associated data
 */
function lilina_return_items($input) {
	global $lilina;

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');

	$feed = new SimplePie();
	$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; ('.get_option('baseurl').'; http://getlilina.org/; Allow Like Gecko) SimplePie/' . SIMPLEPIE_BUILD);
	/** This disables sorting too, we handle that ourselves later */
	$feed->set_stupidly_fast(true);
	$feed->set_cache_location(get_option('cachedir'));
	$feed->set_favicon_handler(get_option('baseurl') . 'lilina-favicon.php');

	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];

	$feed->set_feed_url($feed_list);
	$feed->init();

	/** We need this so we have something to work with. */
	$feed->get_items();

	if(!isset($feed->data['ordered_items'])) {
		$feed->data['ordered_items'] = $feed->data['items'];
	}
	/** We disable sorting previously; so we force it here */
	usort($feed->data['ordered_items'], array(&$feed, 'sort_items'));
	usort($feed->data['items'], array(&$feed, 'sort_items'));
	//var_dump($feed);
	return apply_filters('return_items', $feed);
}

/**
 * lilina_parse_html() - Parses HTML with HTML Purifier using filters
 *
 * Wrapper function for HTML Purifier; sets our settings such as the cache directory and purifies
 * both arrays and strings
 * @global Cache the HTMLPurifier object for later
 * @param mixed $val_array Array or string to parse/purify
 * @return mixed Array or string of purified HTML
 */
function lilina_parse_html($val_array){
	require_once(LILINA_INCPATH . '/contrib/HTMLPurifier.standalone.php');
	global $purifier;
	if(!isset($purifier) || !is_a($purifier, 'HTMLPurifier')) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', get_option('encoding')); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
		$config->set('Cache', 'SerializerPath', get_option('cachedir'));
		$purifier = new HTMLPurifier($config);
	}
	if(is_array($val_array)) {
		if(empty($val_array)) return $val_array;
		foreach($val_array as $this_array) {
			if(is_array($this_array)) {
				$purified_array[] = $purifier->purifyArray($this_array);
			}
			else {
				$purified_array[] = $purifier->purify($this_array);
			}
		}
	}
	else {
		$purified_array = $purifier->purify($val_array);
	}
	return apply_filters('parse_html', $purified_array);
}


/**
 * add_feed() - Adds a new feed
 *
 * Adds the specified feed name and URL to the global <tt>$data</tt> array. If no name is set
 * by the user, it fetches one from the feed. If the URL specified is a HTML page and not a
 * feed, it lets SimplePie do autodiscovery and uses the XML url returned.
 * @todo Document parameters
 * @global array Contains all feeds, this is what we add the new feed to
 * @global array Contains current version number
 * @return bool True if succeeded, false if failed
 */
function add_feed($url, $name = '', $cat = 'default') {
	global $data, $lilina;
	/** Fix users' kludges; They'll thank us for it */
	if(empty($url)) {
		if(function_exists('_r'))
			add_notice(_r("Couldn't add feed: No feed URL supplied"));
		else
			add_notice("Couldn't add feed: No feed URL supplied");
		return false;
	}

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$feed_info = new SimplePie();
	$feed_info->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; ('.get_option('baseurl').'; http://getlilina.org/; Allow Like Gecko) SimplePie/' . SIMPLEPIE_BUILD);
	$feed_info->set_stupidly_fast( true );
	$feed_info->enable_cache(false);
	$feed_info->set_feed_url( $url );
	$feed_info->init();
	$feed_error = $feed_info->error();
	$feed_url = $feed_info->subscribe_url();

	if(!empty($feed_error)) {
		//No feeds autodiscovered;
		if(function_exists('_r'))
			MessageHandler::add_error(sprintf(_r( "Couldn't add feed: %s is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery." ), $url ));
		else
			MessageHandler::add_error("Couldn't add feed: $url is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery.");

		return false;
	}

	if(empty($name)) {
		//Get it from the feed
		$name = $feed_info->get_title();
	}

	$data['feeds'][] = array(
		'feed'	=> $feed_url,
		'url'	=> $feed_info->get_link(),
		'name'	=> $name,
		'cat'	=> $cat,
	);
	if(function_exists('_r'))
		MessageHandler::add( sprintf( _r('Added feed "%1$s"'), $name ) );
	else
		MessageHandler::add( "Added feed \"$name\"");

	add_action( 'send_headers', 'save_feeds' );
	return true;
}

/**
 * save_feeds() - Saves all feeds to the file specified in the settings
 *
 * Serializes, then base 64 encodes
 */
function save_feeds() {
	global $data;
	if(!is_writable(get_option('files', 'feeds'))) {
		if(function_exists('_r'))
			add_notice(sprintf(_r('%s is not writable by the server. Please make sure the server can write to it'), get_option('files', 'feeds')));
		else
			add_notice(sprintf('%s is not writable by the server. Please make sure the server can write to it', get_option('files', 'feeds')));
		return false;
	}
	$sdata	= base64_encode(serialize($data)) ;
	$fp		= fopen(get_option('files', 'feeds'),'w') ;
	if(!$fp) {
		if(function_exists('_r'))
			add_notice(sprintf(_r('An error occurred when saving to %s and your data may not have been saved'), get_option('files', 'feeds')));
		else
			add_notice(sprintf('An error occurred when saving to %s and your data may not have been saved', get_option('files', 'feeds')));
		return false;
	}
	fputs($fp,$sdata) ;
	fclose($fp) ;
}

/**
 * import_opml() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function import_opml($opml_url) {
	if(empty($opml_url)) {
		add_notice(sprintf(_r('No OPML specified')));
		return false;
	}

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$opml = new SimplePie_File($opml_url);
	$opml = new OPML($opml->body);

	if(!empty($opml->error) || empty($opml->data)) {
		add_notice(sprintf(_r('The OPML file could not be read. The parser said: %s'), $imported_feeds->error));
		return false;
	}
	$feeds_num = 0;
	foreach($opml->data as $cat => $feed) {
		if(!isset($feed['xmlurl']) && isset($feed[0]['xmlurl'])) {
			foreach($feed as $subfeed) {
				$feeds[] = array('url' => $subfeed['xmlurl'], 'title' => $subfeed['title'], 'cat' => $cat);
				++$feeds_num;
			}
			continue;
		}

		$feeds[] = array('url' => $feed['xmlurl'], 'title' => $feed['title'], 'cat' => '');
		++$feeds_num;
	}
	add_notice(sprintf(_r('Adding %d feed(s)'), $feeds_num));
	return $feeds;
}
?>
