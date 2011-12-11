<?php

abstract class Lilina_Adapter_Base {
	protected static function object_to_array(&$obj) {
		if (is_callable(array($obj, '_db_export'), false, $callable)) {
			return $callable($options);
		}
		else {
			return get_object_vars($obj);
		}
	}
}