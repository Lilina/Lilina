<?php
/**
 * Localisation based on gettext example
 * @author Danilo Segan <danilo@kvota.net>
 * @author Steven Armstrong <sa@c-area.ch>
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');
if(!defined('LC_ALL')) {
	/**
	 * Gettext needs these constants
	 */
	define('LC_CTYPE', 0);
	define('LC_NUMERIC', 1);
	define('LC_TIME', 2);
	define('LC_COLLATE', 3);
	define('LC_MONETARY', 4);
	define('LC_MESSAGES', 5);
	define('LC_ALL', 6);
}
elseif(!defined('LC_MESSAGES')) {
	define('LC_MESSAGES', 5);
}

require_once(LILINA_INCPATH . '/contrib/streams.php');
require_once(LILINA_INCPATH . '/contrib/gettext.php');


// Variables

global $text_domains, $default_domain, $LC_CATEGORIES, $EMULATEGETTEXT, $CURRENTLOCALE;
$text_domains = array();
$default_domain = 'messages';
$LC_CATEGORIES = array('LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_MESSAGES', 'LC_ALL');
$EMULATEGETTEXT = 0;
$CURRENTLOCALE = '';


// Utility functions

/**
 * Utility function to get a StreamReader for the given text domain.
 */
function _get_reader($domain=null, $category=5, $enable_cache=true) {
	global $text_domains, $default_domain, $LC_CATEGORIES;
	if (!isset($domain)) $domain = $default_domain;
	if (!isset($text_domains[$domain]->l10n)) {
		// get the current locale
		$locale = _setlocale(LC_MESSAGES, 0);
		$p = isset($text_domains[$domain]->path) ? $text_domains[$domain]->path : './';
		//$path = $p . "$locale/". $LC_CATEGORIES[$category] ."/$domain.mo";
		$path = $p . $locale . '.mo';
		if (file_exists($path)) {
			$input = new FileReader($path);
		}
		else {
			$input = null;
		}
		$text_domains[$domain]->l10n = new gettext_reader($input, $enable_cache);
	}
	return $text_domains[$domain]->l10n;
}

/**
 * Returns whether we are using our emulated gettext API or PHP built-in one.
 */
function locale_emulation() {
    global $EMULATEGETTEXT;
    return $EMULATEGETTEXT;
}

/**
 * Checks if the current locale is supported on this system.
 */
function _check_locale() {
    global $EMULATEGETTEXT;
    return !$EMULATEGETTEXT;
}

/**
 * Get the codeset for the given domain.
 */
function _get_codeset($domain=null) {
	global $text_domains, $default_domain, $LC_CATEGORIES;
	if (!isset($domain)) $domain = $default_domain;
	return (isset($text_domains[$domain]->codeset))? $text_domains[$domain]->codeset : ini_get('mbstring.internal_encoding');
}

/**
 * Convert the given string to the encoding set by bind_textdomain_codeset.
 */
function _encode($text) {
	/**
	 * Hack for systems that don't have the multibyte extension enabled
	 */
	if(!function_exists('mb_detect_encoding')) {
		return $text;
	}
	$source_encoding = mb_detect_encoding($text);
	$target_encoding = _get_codeset();
	if ($source_encoding != $target_encoding) {
		return mb_convert_encoding($text, $target_encoding, $source_encoding);
	}
	else {
		return $text;
	}
}




// Custom implementation of the standard gettext related functions

/**
 * Sets a requested locale, if needed emulates it.
 */
function _setlocale($category, $locale) {
    global $CURRENTLOCALE, $EMULATEGETTEXT;
    if ($locale === 0) { // use === to differentiate between string "0"
        if ($CURRENTLOCALE != '') 
            return $CURRENTLOCALE;
        else 
            // obey LANG variable, maybe extend to support all of LC_* vars
            // even if we tried to read locale without setting it first
            return _setlocale($category, $CURRENTLOCALE);
    } else {
        $ret = 0;
        if (function_exists('setlocale')) // I don't know if this ever happens ;)
           $ret = setlocale($category, $locale);
        if (($ret and $locale == '') or ($ret == $locale)) {
            $EMULATEGETTEXT = 0;
            $CURRENTLOCALE = $ret;
        } else {
  	    if ($locale == '') // emulate variable support
 	        $CURRENTLOCALE = getenv('LANG');
	    else
	        $CURRENTLOCALE = $locale;
            $EMULATEGETTEXT = 1;
        }
        return $CURRENTLOCALE;
    }
}

/**
 * Sets the path for a domain.
 */
function _bindtextdomain($domain, $path) {
	global $text_domains;
	// ensure $path ends with a slash
	if ($path[strlen($path) - 1] != '/') $path .= '/';
	elseif ($path[strlen($path) - 1] != '\\') $path .= '\\';
	$text_domains[$domain]->path = $path;
}

/**
 * Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
 */
function _bind_textdomain_codeset($domain, $codeset) {
	global $text_domains;
	$text_domains[$domain]->codeset = $codeset;
}

/**
 * Sets the default domain.
 */
function _textdomain($domain) {
	global $default_domain;
	$default_domain = $domain;
}

/**
 * Lookup a message in the current domain.
 */
function _gettext($msgid) {
	$l10n = _get_reader();
	//return $l10n->translate($msgid);
	return _encode($l10n->translate($msgid));
}
/**
 * Alias for gettext.
 */
function __($msgid) {
	return _gettext($msgid);
}
/**
 * Plural version of gettext.
 */
function _ngettext($single, $plural, $number) {
	$l10n = _get_reader();
	//return $l10n->ngettext($single, $plural, $number);
	return _encode($l10n->ngettext($single, $plural, $number));
}

/**
 * Override the current domain.
 */
function _dgettext($domain, $msgid) {
	$l10n = _get_reader($domain);
	//return $l10n->translate($msgid);
	return _encode($l10n->translate($msgid));
}
/**
 * Plural version of dgettext.
 */
function _dngettext($domain, $single, $plural, $number) {
	$l10n = _get_reader($domain);
	//return $l10n->ngettext($single, $plural, $number);
	return _encode($l10n->ngettext($single, $plural, $number));
}

/**
 * Overrides the domain and category for a single lookup.
 */
function _dcgettext($domain, $msgid, $category) {
	$l10n = _get_reader($domain, $category);
	//return $l10n->translate($msgid);
	return _encode($l10n->translate($msgid));
}
/**
 * Plural version of dcgettext.
 */
function _dcngettext($domain, $single, $plural, $number, $category) {
	$l10n = _get_reader($domain, $category);
	//return $l10n->ngettext($single, $plural, $number);
	return _encode($l10n->ngettext($single, $plural, $number));
}

function translate($text, $domain) {
	if($domain == 'default') {
		$domain	= 'messages';
	}
	return _dgettext($domain, $text);
}
function _e($text, $domain='default') {
	echo translate($text, $domain);
}
function _r($text, $domain='default') {
	return translate($text, $domain);
}
function _n($single, $plural, $number, $domain='default') {
	_dngettext($domain, $single, $plural, $number);
}
/*
Now declared in admin.php

function available_locales() {
	$available_locales = array();
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/locales/')) {
		//Go through all entries
		while (false !== ($file = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($file != '.' && $file != '..') {
				if (is_dir($directory . '/' . $file)) {
					//Found a directory, let's see if a plugin exists in it,
					//with the same name as the directory
					if(file_exists($directory . '/' . $file . '/' . $file . '.mo')) {
						$file = str_replace('.mo', '', $file);
						$available_locales[] = $file . '/' . $file;
					}
				} else {
					//Only add plugin files
					if(strpos($file,'.mo') !== FALSE) {
						$available_locales[] = str_replace('.mo', '', $file);
					}
				}
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	return $available_locales;
}*/

function locale_available($locale) {
	return @file_exists(LILINA_INCPATH . '/locales/' . $locale . '.mo');
}
/*
if(locale_available($settings['lang'])) {
	echo "<p>locale '$settings[lang]' is supported by Lilina, using it now.</p>\n";
}
else {
	echo "<p>locale '$settings[lang]' is <em>not</em> supported by Lilina, using the default locale 'en'.</p>\n";
	$settings['lang'] = 'en';
}*/
// gettext setup
// Sets global $locale variable
_setlocale(LC_ALL, 'en');
// Constructs the path to the .mo, as /inc/locales/[lang].mo
_bindtextdomain('messages', LILINA_INCPATH . '/locales');
_bind_textdomain_codeset('messages', $settings['encoding']);
_textdomain('messages');
?>
