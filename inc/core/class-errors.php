<?php
class Errors {
	protected static $codes;
	
	protected static function init() {
		self::$codes = array();

		// 0-99 = admin.
		// 0-9 = admin.ajax.
		self::$codes['admin.ajax.unknown']       = 1;
		self::$codes['admin.ajax.no_method']     = 2;
		self::$codes['admin.ajax.missing_param'] = 3;

		// 10-19 = admin.feeds.
		self::$codes['admin.feeds.invalid_url']  = 10;
		self::$codes['admin.feeds.no_url']       = 11;
		self::$codes['admin.feeds.no_id_or_url'] = 12;
		self::$codes['admin.feeds.invalid_id']   = 13;

		// 800-899 = auth.
		self::$codes['auth.none']                = 800;
		// 900-999 = update.
		self::$codes['update.core']              = 900;
		self::$codes['update.plugin']            = 901;
		self::$codes['update.theme']             = 902;
	}
	public static function get_code($name) {
		if(empty(self::$codes))
			self::init();

		if(!isset(self::$codes[$name])) {
			echo($name);
			return -1;
		}

		return self::$codes[$name];
	}
}