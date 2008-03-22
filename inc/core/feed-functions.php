<?php
/**
 * Feed handling functions
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');

/**
 * lilina_return_items() - Takes an array of feeds and returns all channels and all items from them
 *
 * Takes an input array and parses it using the SimplePie library. Returns a SimplePie object.
 * @param array $input Input array of user specified feeds
 * @return object SimplePie object with all feed's associated data
 */
function lilina_return_items($input) {
	global $lilina;
	// Include the SimplePie library
	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$feed = new SimplePie();
	$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; '.get_option('baseurl'));
	$feed->set_stupidly_fast(true);
	$feed->set_cache_location(LILINA_PATH . '/cache');
	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];
	$feed->set_feed_url($feed_list);
	$feed->init();
	return $feed;
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
register_filter('the_title', 'lilina_parse_html');
register_filter('the_content', 'lilina_parse_html');
register_filter('the_summary', 'lilina_parse_html');
register_filter('return_output', 'lilina_parse_html');

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
	$url	= str_replace(array('feed://http://', 'feed://http//', 'feed://', 'feed:http://', 'feed:'), 'http://', $url);
	if(empty($url)) {
		add_notice(_r("Couldn't add feed: No feed URL supplied"));
		return false;
	}

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$feed_info = new SimplePie();
	$feed_info->set_useragent( 'Lilina/' . $lilina['core-sys']['version'].'; ' . get_option('baseurl') );
	$feed_info->set_stupidly_fast( true );
	$feed_info->set_cache_location(LILINA_PATH . '/cache');
	$feed_info->set_feed_url( $url );
	$feed_info->init();
	$feed_error = $feed_info->error();
	$feed_url = $feed_info->subscribe_url();

	if(!empty($feed_error)) {
		//No feeds autodiscovered;
		add_notice( sprintf( _r( "Couldn't add feed: %s is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery." ), $url ) );
		add_tech_notice($feed_error, $url);
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
		add_notice( sprintf( _r('Added feed "%1$s"'), $name ) );
	else
		add_notice( "Added feed \"$name\"");
	add_action( 'send_headers', 'save_feeds' );
	return true;
}

/**
 * save_feeds() - Saves all feeds to the file specified in the settings
 *
 * Serializes, then base 64 encodes
 */
function save_feeds() {
	global $data, $settings;
	if(!is_writable($settings['files']['feeds'])) {
		add_notice(sprintf(_r('%s is not writable by the server. Please make sure the server can write to it'), $settings['files']['feeds']));
		return false;
	}
	$sdata	= base64_encode(serialize($data)) ;
	$fp		= fopen($settings['files']['feeds'],'w') ;
	if(!$fp) {
		add_notice(sprintf(_r('An error occurred when saving to %s and your data may not have been saved'), $settings['files']['feeds']));
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
	if(!empty($opml_url)) {
		$imported_feeds = parse_opml($opml_url);
		if(is_array($imported_feeds)) {
			$feeds_num = 0;
			foreach($imported_feeds as $imported_feed) {
				if(!isset($imported_feed['TYPE'])) {
					//This is just so we are nice to our ancestors, like 0.7
					if(isset($imported_feed['XMLURL']) && !empty($imported_feed['XMLURL'])) {
						$imported_feed['TYPE'] = 'rss';
					}
				}
				elseif($imported_feed['TYPE'] != 'rss' && $imported_feed['TYPE'] != 'atom') {
					continue;
				}
				//Make sure we blank it
				$this_feed = array('url' => '', 'title' => '');
				if(!isset($imported_feed['XMLURL']) || empty($imported_feed['XMLURL'])) {
					if(!isset($imported_feed['HTMLURL']) || empty($imported_feed['HTMLURL'])) {
						//Can't live without a URL
						continue;
					}
					else {
						$this_feed['url'] = $imported_feed['HTMLURL'];
					}
				}
				else {
					$this_feed['url'] = $imported_feed['XMLURL'];
				}
				if(!isset($imported_feed['TEXT']) || empty($imported_feed['TEXT'])) {
					if(!isset($imported_feed['TITLE']) || empty($imported_feed['TITLE'])) {
						//We'll need to get it via Magpie later
						$this_feed['title'] = '';
					}
					else {
						$this_feed['title'] = $imported_feed['TITLE'];
					}
				}
				else {
					$this_feed['title'] = $imported_feed['TEXT'];
				}
				add_feed($this_feed['url'], $this_feed['title']);
				++$feeds_num;
			}
			add_notice(sprintf(_r('Added %d feed(s)'), $feeds_num));
		}
		else {
			add_notice(_r('The OPML file could not be read.'));
			add_tech_notice(_r('The parser said: ') . $imported_feeds);
		}
	}
	else {
		add_notice(sprintf(_r('No OPML specified')));
	}
}
?>