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
		self::$codes['admin.feeds.invalid_url']         = 10;
		self::$codes['admin.feeds.no_url']              = 11;
		self::$codes['admin.feeds.no_id_or_url']        = 12;
		self::$codes['admin.feeds.invalid_id']          = 13;
		self::$codes['admin.feeds.protocol_error']      = 14;
		self::$codes['admin.feeds.feed_already_exists'] = 15;

		// 20-39 = admin.importer.
		// 20-29 = admin.importer.greader.
		self::$codes['admin.importer.greader.invalid_auth'] = 20;

		// 40-49 = admin.plugins.
		self::$codes['admin.plugins.invalid_path'] = 40;
		self::$codes['admin.plugins.not_found'] = 41;

		// 100-199 = api.
		// 100-109 = api.itemupdater.
		self::$codes['api.itemupdater.itemerror']           = 100;
		self::$codes['api.itemupdater.ajax.unknown']        = 101;
		self::$codes['api.itemupdater.ajax.no_id']          = 102;
		self::$codes['api.itemupdater.ajax.action_unknown'] = 103;

		// 110-119 = api.items.
		self::$codes['api.items.no_method'] = 110;

		// 200-299 = db.
		// 200-219 = db.general
		self::$codes['db.general.missingtable'] = 200;
		self::$codes['db.general.datatypewrong'] = 201;

		// 220-239 = db.insert
		self::$codes['db.insert.duplicate'] = 220;
		self::$codes['db.insert.missingprimary'] = 221;

		// 240-259 = db.update
		self::$codes['db.update.missingwhere'] = 240;

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