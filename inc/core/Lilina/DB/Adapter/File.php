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
			$this->options = $options['orderby']['key'];
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
			if (!empty($options['orderby']['direction']) && $options['orderby']['direction'] === 'desc') {
				 $data = array_reverse($data);
			}
		}

		// Finally, filter fields we don't want
		if ($options['fields'] !== null) {
			$this->options = $options['fields'];
			$data = array_map(array($this, 'fields'), $data);

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

		$this->options = null;
		return $data;
	}

	public function insert($data, $options) {
		
	}

	public function update($data, $options) {
		
	}

	/**
	 * Filters down to just the fields we want
	 *
	 * Also removes any rows which are missing the fields we want
	 * @param array $row
	 * @return array|boolean Returns the filtered array, or false if the row is missing fields
	 */
	protected function fields($row) {
		$keys = array_fill_keys($this->options, true);
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
		return $a[$this->options] - $b[$this->options];
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
		return strcmp($a[$this->options], $b[$this->options]);
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
		return strcasecmp($a[$this->options], $b[$this->options]);
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