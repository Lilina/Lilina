<?php

class Lilina_Iterable implements IteratorAggregate {
	protected $iterator;

	/**
	 * Get the current item
	 *
	 * Convienience method, if you're trying to loop through the
	 * items, ensure you use the Iterator
	 * @return Lilina_Feed
	 */
	public function &current() {
		return $this->getIterator()->current();
	}

	/**
	 * Get the previous item
	 *
	 * @since 1.0
	 *
	 * @return bool|Lilina_Feed False if item doesn't exist, otherwise returns the specified item
	 */
	public function &previous() {
		$previous = $this->getIterator()->previous();
		$this->getIterator()->next();

		return $previous;
	}

	/**
	 * Get the next item
	 *
	 * @since 1.0
	 *
	 * @return bool|Lilina_Item False if item doesn't exist, otherwise returns the specified item
	 */
	public function &next() {
		return $this->getIterator()->next();
	}

	/**
	 * Apply a callback to all items
	 *
	 * @param callback $callback
	 */
	public function each($callback) {
		return $this->getIterator()->each($callback);
	}

	/**
	 * Get the items iterator
	 *
	 * @return Lilina_Items_Iterator
	 */
	public function getIterator() {
		if (!empty($this->iterator)) {
			return $this->iterator;
		}

		$this->iterator = new Lilina_Iterable_Iterator($this->data);
		return $this->iterator;
	}
}