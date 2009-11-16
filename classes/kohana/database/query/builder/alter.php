<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for ALTER statements.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Query_Builder_Alter extends Database_Query_Builder {
	
	// Table name - ALTER TABLE '_table'; ...
	protected $_table;
	
	// New table name - ALTER TABLE '_table' RENAME '_name';
	protected $_name = NULL;
	
	// Columns to add - ALTER TABLE '_table' ADD COLUMNS ( ... )
	protected $_add_columns = array();
	
	// Columns to modify - ALTER TABLE '_table' MODIFY COLUMNS ( ... )
	protected $_modify_columns = array();
	
	// Columns to add - ALTER TABLE '_table' DROP COLUMN ...
	protected $_drop_columns = array();
	
	
	/**
	 * Set the table for alteration.
	 *
	 * @param   string The table name
	 * @return  void
	 */
	public function __construct( Database_Table $table)
	{
		if( ! $table->loaded())
		{
			throw new Database_Exception('Table tbl must be loaded to perform alter queries.', array(
				'tbl' => $table->name
			));
		}
		
		$this->_table = $table;
	}
	
	/**
	 * Rename the table.
	 *
	 * @param   string The new name
	 * @return  void
	 */
	public function rename($name)
	{
		$this->_name = $name;
		
		$this->execute();
		
		return $this;
	}
	
	/**
	 * Add a column
	 *
	 * @param   object The column object.
	 * @return  void
	 */
	public function add( Database_Table_Column $column)
	{
		if($column->loaded())
		{
			$column = clone $column;
		}
		
		$this->_add_columns[] = $column;
		
		return $this;
	}
	
	/**
	 * Modify a column.
	 *
	 * @param	string	The name of the column you wish to modify.
	 * @param   object	The new column data.
	 * @return  void
	 */
	public function modify( Database_Table_Column $new_column, $existing_column)
	{
		if ( ! is_object($existing_column))
		{
			$existing_column = $this->_table->columns(true, $existing_column);
			
			if(count($existing_column) !== 1)
			{
				throw new Database_Exception('col could not be found in tbl', array(
					'col' => ucfirst($existing_column),
					'tbl' => $this->_table->name
				));
			}
		}
		
		$this->_modify_columns[$existing_column->name] = $new_column;
		
		return $this;
	}
	
	/**
	 * Drop a column.
	 *
	 * @param   string The name of the column to drop
	 * @return  void
	 */
	public function drop($column)
	{
		$this->_drop_columns[] = $column;
		
		return $this;
	}
	
	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   object  Database instance
	 * @return  string
	 */
	public function compile(Database $db)
	{
		$sql = 'ALTER TABLE '.$db->quote_table($this->_table->name).' ';
		$lines = array();
		
		if ($this->_name !== NULL)
		{
			$sql .= 'RENAME TO '.$db->quote_table($this->_name).'; ';
		}
		elseif (count($this->_add_columns) > 0)
		{
			$columns = array();
			
			$sql .= 'ADD ';
			
			foreach($this->_add_columns as $column)
			{
				$columns[] = Database_Query_Builder::compile_column($column);
			}
			
			$sql .= implode($columns, ',').' ';
		}
		elseif (count($this->_modify_columns) > 0)
		{
			$columns = array();
			
			$sql .= 'MODIFY ';
			
			foreach($this->_modify_columns as $original_name => $column)
			{
				$columns[] = Database_Query_Builder::compile_column($column);
			}
			
			$sql .= implode($columns, ',').'; ';
		}
		elseif (count($this->_drop_columns) > 0)
		{
			foreach($this->_drop_columns as $column)
			{
				$drop = new Database_Query_Builder_Drop('column', $column);
				$sql .= $drop->compile($column->table->database).';';
			}
		}
		
		return $sql;
	}
	
	public function reset()
	{
		$this->_add_columns = array();
		$this->_modify_columns = array();
		$this->_drop_columns = array();
		
		$this->_name = NULL;
	}
	
} // END Kohana_Database_Query_Builder_Alter