<?php

class Lilina_Iterable_Iterator extends ArrayIterator {
	/**
	 * Apply a callback to all items
	 *
	 * @param callback $callback
	 */
	public function each($callback) {
		foreach ($this as $key => $val) {
			call_user_func($callback, $key, $val);
		}
	}
}