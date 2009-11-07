<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Database_Query_Builder extends Database_Query {

	/**
	 * Compiles an array of JOIN statements into an SQL partial.
	 *
	 * @param   object  Database instance
	 * @param   array   join statements
	 * @return  string
	 */
	public static function compile_join(Database $db, array $joins)
	{
		$statements = array();

		foreach ($joins as $join)
		{
			// Compile each of the join statements
			$statements[] = $join->compile($db);
		}

		return implode(' ', $statements);
	}

	/**
	 * Compiles an array of conditions into an SQL partial. Used for WHERE
	 * and HAVING.
	 *
	 * @param   object  Database instance
	 * @param   array   condition statements
	 * @return  string
	 */
	public static function compile_conditions(Database $db, array $conditions)
	{
		$last_condition = NULL;

		$sql = '';
		foreach ($conditions as $group)
		{
			// Process groups of conditions
			foreach ($group as $logic => $condition)
			{
				if ($condition === '(')
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Include logic operator
						$sql .= ' '.$logic.' ';
					}

					$sql .= '(';
				}
				elseif ($condition === ')')
				{
					$sql .= ')';
				}
				else
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Add the logic operator
						$sql .= ' '.$logic.' ';
					}

					// Split the condition
					list($column, $op, $value) = $condition;

					// Append the statement to the query
					$sql .= $db->quote_identifier($column).' '.strtoupper($op).' '.$db->quote($value);
				}

				$last_condition = $condition;
			}
		}

		return $sql;
	}

	/**
	 * Compiles a table column from a given parameter array.
	 *
	 * @param   object  Database instance
	 * @param   string  Column name
	 * @param   array   Column parameters
	 * @return  string
	 */
	public static function compile_column(Database $db, $name, array $params)
	{
		$data_type = $params['datatype'];
		$options = $params['options'];
		
		$data = $db->quote_identifier($name).' ';
		
		if(arr::is_assoc($data_type))
		{
			$type = key($data_type);
			$params = $data_type[$type];
			
			$data .= strtoupper($type).'(';
				
			if(is_array($params))
			{
				array_map(array($db, 'quote'), $params);
				$data .= implode($params, ',');
			}
			else
			{
				$data .= $params;
			}
				
			$data .= ')';
		}
		else
		{
			$data .= strtoupper($data_type[0]);
		}
		
		$options = array_map('strtoupper', $options);
		
		$options = implode($options, ' ');
		
		$data .= ' '.$options;
		
		return $data;
	}

	/**
	 * Compiles an array of ORDER BY statements into an SQL partial.
	 *
	 * @param   object  Database instance
	 * @param   array   sorting columns
	 * @return  string
	 */
	public static function compile_order_by(Database $db, array $columns)
	{
		$sort = array();
		foreach ($columns as $group)
		{
			list ($column, $direction) = $group;

			if ( ! empty($direction))
			{
				// Make the direction uppercase
				$direction = ' '.strtoupper($direction);
			}

			$sort[] = $db->quote_identifier($column).$direction;
		}

		return 'ORDER BY '.implode(', ', $sort);
	}

	/**
	 * Reset the current builder status.
	 *
	 * @return  $this
	 */
	abstract public function reset();

} // End Database_Query_Builder
