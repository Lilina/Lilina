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
$l10n					= array();
$l10n['locale_dir']		= $settings['path'] . '/inc/locale';
$l10n['default_locale']	= 'en_US';


require_once('./inc/contrib/gettext.inc');

$supported_locales = array('en_US', 'sr_CS', 'de_CH');
$encoding = 'UTF-8';

$locale = (isset($_GET['lang']))? $_GET['lang'] : $l10n['default_locale'];

// gettext setup
T_setlocale(LC_MESSAGES, $locale);
// Set the text domain as 'messages'
$domain = 'messages';
bindtextdomain($domain, $l10n['locale_dir']);
// bind_textdomain_codeset is supported only in PHP 4.2.0+
if (function_exists('bind_textdomain_codeset')) {
	bind_textdomain_codeset($domain, $encoding);
}
textdomain($domain);

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
print _("This is how the story goes.\n\n");
for ($number=6; $number>=0; $number--) {
	print sprintf(T_ngettext("%d pig went to the market\n", 
				"%d pigs went to the market\n", $number), 
			$number );
}
print "</pre>\n";
?>
