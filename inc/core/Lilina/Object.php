<?php

abstract class Lilina_Object {
	protected static function &_from_obj($class, $obj) {
		$vars = get_object_vars($obj);
		$real = new $class();
		foreach ($vars as $name => $value) {
			$real->$name = $value;
		}

		return $real;
	}
}