<?php
/**
 *
 * @package Lilina
 * @subpackage Localisation
 */

class Locale {
	private static $messages = array();
	private static $plural_function;
	private static $locale;

	/**
	 * Sets the locale for Lilina
	 *
	 * Loads the .mo file in LANGDIR constant path from Lilina root.
	 * The translated (.mo) file is named based off of the locale.
	 * 
	 * @param string $locale A language code like 'en' or 'en-us' or 'x-sneddy', will be lowercased
	 */
	public static function set($locale) {
		self::$locale = strtolower($locale);
		self::load('default', LILINA_PATH . LANGDIR . "/$locale.mo");
	}

	/**
	 * Gets the current locale
	 *
	 * If the locale is set, then it will filter the locale
	 * in the 'locale' filter hook and return the value.
	 *
	 * If the locale is not set already, then the locale
	 * option is used if it is defined. Then it is filtered
	 * through the 'locale' filter hook and the value for the
	 * locale global set and the locale is returned.
	 *
	 * The process to get the locale should only be done once
	 * but the locale will always be filtered using the
	 * 'locale' hook.
	 *
	 * @since 1.0
	 * @uses apply_filters() Calls 'locale' hook on locale value
	 * @uses $locale Gets the locale stored in the global
	 *
	 * @return string The locale of the blog or from the 'locale' hook
	 */
	public static function get() {
		if (isset(self::$locale))
			return apply_filters( 'locale', self::$locale );

		if (get_option('locale'))
			$locale = get_option('locale');

		if (empty($locale))
			$locale = '';

		$locale = apply_filters('locale', $locale);

		self::set($locale);
		return $locale;
	}

	/**
	 * Loads MO file into the list of domains
	 *
	 * If the domain already exists, the inclusion will fail. If the
	 * MO file is not readable, the inclusion will fail.
	 *
	 * On success, the mofile will be placed in the $messages array by
	 * $domain and will be an gettext_reader object.
	 *
	 * @since 1.0
	 * @uses CacheFileReader Reads the MO file
	 * @uses gettext_reader Allows for retrieving translated strings
	 *
	 * @param string $domain Unique identifier for retrieving translated strings
	 * @param string $mofile Path to the .mo file
	 * @return null On failure returns null and also on success returns nothing.
	 */
	public static function load($domain, $mofile) {
		if (isset(self::$messages[$domain]))
			return;

		if ( is_readable($mofile))
			$input = new CachedFileReader($mofile);
		else
			return;

		self::$messages[$domain] = new gettext_reader($input);
	}

	/**
	 * Loads default translated strings based on locale
	 *
	 * Loads the .mo file in LANGDIR constant path from root.
	 * The translated (.mo) file is named based off of the locale.
	 *
	 * @since 1.0
	 */
	public static function load_default_textdomain() {
		$locale = self::get();
		if ( empty($locale) )
			$locale = 'en';

		$mofile = LILINA_PATH . LANGDIR . "/$locale.mo";

		self::load('default', $mofile);
	}

	/**
	 * Loads the plugin's translated strings
	 *
	 * If the path is not given then it will be the root of the plugin
	 * directory. The .mo file should be named based on the domain with a
	 * dash followed by a dash, and then the locale exactly.
	 *
	 * The plugin may place all of the .mo files in another folder and set
	 * the $path based on the relative location from ABSPATH constant. The
	 * plugin may use the constant PLUGINDIR and/or plugin_basename() to
	 * get path of the plugin and then add the folder which holds the .mo
	 * files.
	 *
	 * @since 1.0
	 *
	 * @param string $domain Unique identifier for retrieving translated strings
	 * @param string $path Optional. Path of the folder where the .mo files reside.
	 */
	public static function load_plugin_textdomain($domain, $path = false) {
		$locale = get_locale();
		if ( empty($locale) )
			$locale = 'en';

		if ( false === $path )
			$path = PLUGINDIR;

		$mofile = LILINA_PATH . "$path/$domain-$locale.mo";
		self::load($domain, $mofile);
	}

	/**
	 * Includes theme's translated strings for the theme
	 *
	 * If the current locale exists as a .mo file in the theme's root directory, it
	 * will be included in the translated strings by the $domain.
	 *
	 * The .mo files must be named based on the locale exactly.
	 *
	 * @since 1.0
	 *
	 * @param string $domain Unique identifier for retrieving translated strings
	 */
	public static function load_theme_textdomain($domain) {
		$locale = get_locale();
		if ( empty($locale) )
			$locale = 'en_US';

		$mofile = get_template_directory() . "/$locale.mo";
		self::load($domain, $mofile);
	}

	/**
	 * Retrieve the translated text
	 *
	 * If the domain is set in the $messages array, then the text is run
	 * through the domain's translate method. After it is passed to
	 * the 'gettext' filter hook, along with the untranslated text as
	 * the second parameter.
	 *
	 * If the domain is not set, the $text is just returned.
	 *
	 * @since 1.0
	 * @uses apply_filters() Calls 'gettext' on domain translated text
	 *		with the untranslated text as second parameter
	 *
	 * @param string $text Text to translate
	 * @param string $domain Domain to retrieve the translated text
	 * @return string Translated text
	 */
	public static function translate($text, $domain = 'default') {
		$text = apply_filters('pre_gettext', $text, $domain);

		if (isset($messages[$domain]))
			return apply_filters('gettext', $messages[$domain]->translate($text), $text, $domain);
		else
			return apply_filters('gettext', $text, $text, $domain);
	}
	
	/**
	 * Retrieve the plural or single form based on the amount
	 *
	 * If the domain is not set in the $messages list, then a comparsion
	 * will be made and either $plural or $single parameters returned.
	 *
	 * If the domain does exist, then the parameters $single, $plural,
	 * and $number will first be passed to the domain's ngettext method.
	 * Then it will be passed to the 'ngettext' filter hook along with
	 * the same parameters. The expected type will be a string.
	 *
	 * @since 1.0
	 * @uses apply_filters() Calls 'ngettext' hook on domains text returned,
	 *		along with $single, $plural, and $number parameters. Expected to return string.
	 *
	 * @param string $single The text that will be used if $number is 1
	 * @param string $plural The text that will be used if $number is not 1
	 * @param int $number The number to compare against to use either $single or $plural
	 * @param string $domain Optional. The domain identifier the text should be retrieved in
	 * @return string Either $single or $plural translated text
	 */
	public static function ngettext($single, $plural, $number, $domain = 'default') {
		if (isset(self::$messages[$domain])) {
			return apply_filters('ngettext', self::$messages[$domain]->ngettext($single, $plural, $number), $single, $plural, $number);
		} else {
			if ($number != 1)
				return $plural;
			else
				return $single;
		}
	}
}

/**
 * Retrieve a translated string
 *
 * _r() is a convenience function which retrieves the translated
 * string from the translate().
 *
 * @see Locale::translate() An alias of translate()
 * @since 1.0
 *
 * @param string $text Text to translate
 * @param string $domain Optional. Domain to retrieve the translated text
 * @return string Translated text
 */
function _r($text, $domain = 'default') {
	return Locale::translate($text, $domain);
}

/**
 * Display a translated string
 *
 * _e() is a convenience function which displays the returned
 * translated text from translate().
 *
 * @see Locale::translate() Echos returned translate() string
 * @since 1.0
 *
 * @param string $text Text to translate
 * @param string $domain Optional. Domain to retrieve the translated text
 */
function _e($text, $domain = 'default') {
	echo Locale::translate($text, $domain);
}

/**
 * Retrieve context translated string
 *
 * Quite a few times, there will be collisions with similar
 * translatable text found in more than two places but with
 * different translated context.
 *
 * In order to use the separate contexts, the _c() function
 * is used and the translatable string uses a pipe ('|')
 * which has the context the string is in.
 *
 * When the translated string is returned, it is everything
 * before the pipe, not including the pipe character. If
 * there is no pipe in the translated text then everything
 * is returned.
 *
 * @since 1.0
 *
 * @param string $text Text to translate
 * @param string $domain Optional. Domain to retrieve the translated text
 * @return string Translated context string without pipe
 */
function _c($text, $domain = 'default') {
	$whole = Locale::translate($text, $domain);
	$last_bar = strrpos($whole, '|');
	if ( false == $last_bar ) {
		return $whole;
	} else {
		return substr($whole, 0, $last_bar);
	}
}

/**
 * Display a translated string
 *
 * __ngettext() is a convenience function which displays the returned
 * translated text from Locale::ngettext().
 *
 * @see Locale::ngettext() Echos returned Locale::ngettext() string
 * @since 1.0
 *
 * @param string $single The text that will be used if $number is 1
 * @param string $plural The text that will be used if $number is not 1
 * @param int $number The number to compare against to use either $single or $plural
 * @param string $domain Optional. The domain identifier the text should be retrieved in
 * @return string Either $single or $plural translated text
 */
function __ngettext($single, $plural, $number, $domain = 'default') {
	Locale::ngettext($single, $plural, $number, $domain);
}

/**
 * __ngettext_noop() - register plural strings in POT file, but don't translate them
 *
 * Used when you want do keep structures with translatable plural strings and
 * use them later.
 *
 * Example:
 *  $messages = array(
 *  	'post' => __ngettext_noop('%s post', '%s posts'),
 *  	'page' => __ngettext_noop('%s pages', '%s pages')
 *  );
 *  ...
 *  $message = $messages[$type];
 *  $usable_text = sprintf(__ngettext($message[0], $message[1], $count), $count);
 *
 * @since 1.0
 * @param $single Single form to be i18ned
 * @param $plural Plural form to be i18ned
 * @param $number Not used, here for compatibility with __ngettext, optional
 * @param $domain Not used, here for compatibility with __ngettext, optional
 * @return array array($single, $plural)
 */
function __ngettext_noop($single, $plural, $number=1, $domain = 'default') {
	return array($single, $plural);
}