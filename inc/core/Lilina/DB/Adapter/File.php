<?php

class Lilina_DB_Adapter_File implements Lilina_DB_Adapter {
	protected $directory;
	protected $options;
	protected $tables = array();

	public function __construct($options) {
		$this->directory = $options;
	}

	protected function load($table) {
		if (!empty($this->tables[$table])) {
			return $this->tables[$table];
		}

		$file = $this->directory . DIRECTORY_SEPARATOR . $table . '.data';
		$this->tables[$table] = json_decode(file_get_contents($file), true);
		return $this->tables[$table];
	}

	/**
	 * Retrieve rows from the database
	 *
	 * @param array $options Options array, see source for reference
	 * @return array Rows which match all the conditions
	 */
	public function retrieve($options) {
		$default = array(
			'table' => null,
			'fields' => null,
			'where' => array(),
			'limit' => null,
			'offset' => 0,
			'orderby' => array(),
			'fetchas' => 'array'
		);
		$options = array_merge($default, $options);

		$data = $this->load($options['table']);

		// Check conditions
		if (!empty($options['where'])) {
			foreach ($options['where'] as $condition) {
				$this->temp = $condition;
				$data = array_filter($data, array($this, 'where_filter'));
				$this->temp = null;
			}
		}

		// Cut down to just what we need
		if ($options['limit'] !== null) {
			$data = array_slice($data, $options['offset'], $options['limit']);
		}
		elseif ($options['offset'] !== null) {
			$data = array_slice($data, $options['offset']);
		}

		// Order our data
		if ($options['orderby'] !== null && !empty($options['orderby']['key'])) {
			$this->temp = $options['orderby']['key'];
			if (empty($options['orderby']['compare'])) {
				$options['orderby']['compare'] = 'str';
			}

			switch ($options['orderby']['compare']) {
				case 'str':
					usort($data, array($this, 'order_str'));
					break;
				case 'strcase':
					usort($data, array($this, 'order_strcase'));
					break;
				case 'int':
					usort($data, array($this, 'order_int'));
					break;
			}
			$this->temp = null;

			if (!empty($options['orderby']['direction']) && $options['orderby']['direction'] === 'desc') {
				 $data = array_reverse($data);
			}
		}

		// Finally, filter fields we don't want
		if ($options['fields'] !== null) {
			$this->temp = $options['fields'];
			$data = array_map(array($this, 'fields'), $data);
			$this->temp = null;

			// This removes all the rows which have missing fields
			$data = array_filter($data);
		}

		if ($options['fetchas'] !== 'array') {
			foreach ($data as $id => $row) {
				$data[$id] = new $options['fetchas']();
				foreach ($row as $k => $v) {
					$data[$id]->$k = $v;
				}
			}
		}

		return $data;
	}

	public function insert($data, $options) {
		
	}

	/**
	 * Update rows in the database
	 *
	 * @param array $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function update($data, $options) {
		$default = array(
			'table' => null,
			'where' => array(),
			'limit' => null,
		);
		$options = array_merge($default, $options);

		if (empty($options['table'])) {
			throw new Lilina_DB_Exception('Table must be specified');
		}
		if (empty($options['where'])) {
			throw new Lilina_DB_Exception('Condition must be specified for update');
		}

		$current = $this->load($options['table']);

		$actual = $current;
		foreach ($options['where'] as $condition) {
			$this->temp = $condition;
			$actual = array_filter($actual, array($this, 'where_filter'));
			$this->temp = null;
		}

		$actual = array_keys($actual);
		if ($options['limit'] !== null) {
			$actual = array_slice($actual, 0, $options['limit']);
		}

		foreach ($actual as $key) {
			$current[$key] = array_merge($current[$key], $data);
		}

		$this->save($options['table'], $current);

		return true;
	}

	/**
	 * Filters down to just the fields we want
	 *
	 * Also removes any rows which are missing the fields we want
	 * @param array $row
	 * @return array|boolean Returns the filtered array, or false if the row is missing fields
	 */
	protected function fields($row) {
		$keys = array_fill_keys($this->temp, true);
		$row = array_intersect_key($row, $keys);
		$missing = array_diff_key($keys, $row);

		// Remove any rows with missing fields
		if (!empty($missing)) {
			var_dump($missing);
			return false;
		}

		return $row;
	}

	/**
	 * Order for an integer value
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_int($a, $b) {
		return $a[$this->temp] - $b[$this->temp];
	}

	/**
	 * Order for a string value
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_str($a, $b) {
		return strcmp($a[$this->temp], $b[$this->temp]);
	}

	/**
	 * Order for a string value (case-insensitive)
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_strcase($a, $b) {
		return strcasecmp($a[$this->temp], $b[$this->temp]);
	}

	protected function where_filter($row) {
		list($key, $op, $val) = $this->temp;
		switch ($op) {
			case '==':
				return $row[$key] == $val;
			case '===':
				return $row[$key] === $val;
			case '!=':
				return $row[$key] != $val;
			case '!==':
				return $row[$key] !== $val;
			case '>':
				return $row[$key] > $val;
			case '>=':
				return $row[$key] >= $val;
			case '<':
				return $row[$key] <= $val;
			case '<=':
				return $row[$key] <= $val;
			default:
				throw new Lilina_DB_Exception('Operator not valid');
		}
	}
}