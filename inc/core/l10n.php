<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		l10n.php
Purpose:	Localisation
Notes:		//MUSTFIX: Change all code to use this
			Based off example code by
			Danilo Segan <danilo@kvota.net>
			and Steven Armstrong <sa@c-area.ch>
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
if(!defined('LC_ALL')) {
	//Gettext needs these constants
	define('LC_CTYPE', 0);
	define('LC_NUMERIC', 1);
	define('LC_TIME', 2);
	define('LC_COLLATE', 3);
	define('LC_MONETARY', 4);
	define('LC_MESSAGES', 5);
	define('LC_ALL', 6);
}

//Get the drop-in PHP-gettext, probably need to combine the two files
require_once('./inc/contrib/gettext.inc');
global $l10n;
$l10n						= array();
$l10n['locale_dir']			= $settings['path'] . '/inc/locale';
$l10n['default_locale']		= 'en_US';
$l10n['supported_locales']	= array('en_US', 'sr_CS', 'de_CH');
$l10n['encoding']			= 'UTF-8';
$l10n['locale']				= $l10n['default_locale'];
if($settings['lang']) {
	switch($settings['lang']) {
		case 'english':
			//Assume US english
			$l10n['locale']	= 'en_US';
		default:
			//If specified language is supported, use it
			if(isset($l10n['supported_locales'][$settings['lang']])) {
				
			}
	}
}

// gettext setup
_setlocale(LC_MESSAGES, $locale);
// Set the text domain as 'messages'
$domain = 'messages';
_bindtextdomain($domain, $l10n['locale_dir']);
// bind_textdomain_codeset is supported only in PHP 4.2.0+
if (function_exists('bind_textdomain_codeset')) {
	bind_textdomain_codeset($domain, $encoding);
}
_textdomain($domain);

function translate($text, $domain) {
	global $l10n;
	if($domain == 'default') {
		$domain	= $l10n['locale'];
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
print "<p>";
foreach($supported_locales as $l) {
	print "[<a href=\"?lang=$l\">$l</a>] ";
}
print "</p>\n";

if (!locale_emulation()) {
	print "<p>locale '$locale' is supported by your system, using native gettext implementation.</p>\n";
}
else {
	print "<p>locale '$locale' is _not_ supported on your system, using the default locale '". $l10n['default_locale'] ."'.</p>\n";
}

// using PHP-gettext
print "<pre>";
//print _("This is how the story goes.\n\n");
for ($number=6; $number>=0; $number--) {
	print sprintf(T_ngettext("%d pig went to the market\n", 
				"%d pigs went to the market\n", $number), 
			$number );
}
print "</pre>\n";*/
?>
