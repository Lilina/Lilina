<?php

class Lilina_Author extends Lilina_Object {
	public $name;
	public $url;

	public static function &from_obj($obj) {
		return parent::_from_obj(__CLASS__, $obj);
	}
}