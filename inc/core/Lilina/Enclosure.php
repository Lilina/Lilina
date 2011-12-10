<?php

class Lilina_Enclosure extends Lilina_Object {
	public $url;
	public $type;
	public $length;

	public static function &from_obj($obj) {
		return parent::_from_obj(__CLASS__, $obj);
	}
}