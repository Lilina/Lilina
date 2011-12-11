<?php

class Lilina_Items_Iterator extends ArrayIterator {
	public function each($callback) {
		foreach ($this as $key => $val) {
			call_user_func($callback, $key, $val);
		}
	}
}