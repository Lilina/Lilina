<?php

class Lilina_DB {
	protected static $adapter = null;

	public static function &get_adapter() {
		return self::$adapter;
	}

	public static function set_adapter(Lilina_DB_Adapter &$adapter) {
		self::$adapter = $adapter;
	}
}