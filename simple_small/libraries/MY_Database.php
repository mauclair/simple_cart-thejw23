<?php defined('SYSPATH') or die('No direct script access.');

/**
* Database addon for showing list_fields() in profiler
*
* @author           thejw23
* @copyright        (c) 2009 thejw23
* @license          http://www.opensource.org/licenses/isc-license.txt
* @version          0.1    
*/

class Database extends Database_Core {


	/**
	 * list fields in table              
	 *
	 * @param   string  table name
	 * @return  array
	 */
	public function list_fields($table = '')
	{
		$this->link or $this->connect();
          
          $start = microtime(TRUE);
		
          $result = $this->driver->list_fields($this->config['table_prefix'].$table);
          
          $stop = microtime(TRUE);
		
		if ($this->config['benchmark'] == TRUE)
		{
			// Benchmark the query
			self::$benchmarks[] = array('query' => 'Get fields from: '.$table, 'time' => $stop - $start, 'rows' => count($result));
		}
		
          return  $result;
	}
	
	/**
	 * Adds an "IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @param   bool    Generate a NOT IN clause instead
	 * @return  Database_Core  This Database object.
	 */
	public function in($field, $values = '', $not = FALSE)
	{
		if (is_array($field))
		{
			foreach ($field as $key=>$value)
			{
				$field = $key;
				$values = $value;
			}
		}
		
		if (is_array($values))
		{
			$escaped_values = array();
			foreach ($values as $v)
			{
				if (is_numeric($v))
				{
					$escaped_values[] = $v;
				}
				else
				{
					$escaped_values[] = "'".$this->driver->escape_str($v)."'";
				}
			}
			$values = implode(",", $escaped_values);
		}

		$where = $this->driver->escape_column(((strpos($field,'.') !== FALSE) ? $this->config['table_prefix'] : ''). $field).' '.($not === TRUE ? 'NOT ' : '').'IN ('.$values.')';
		$this->where[] = $this->driver->where($where, '', 'AND ', count($this->where), -1);

		return $this;
	}

	/**
	 * Adds a "NOT IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @return  Database_Core  This Database object.
	 */
	public function notin($field, $values)
	{
		if (is_array($field))
		{
			foreach ($field as $key=>$value)
			{
				$field = $key;
				$values = $value;
			}
		}
		
		return $this->in($field, $values, TRUE);
	}  

}