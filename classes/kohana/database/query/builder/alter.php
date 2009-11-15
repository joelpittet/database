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
			//TODO: Throw error, table must be loaded
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
			//TODO: Throw exception, must clone column before adding it to a table	
		}
		
		$this->_add_columns[] = $column;
	}
	
	/**
	 * Modify a column.
	 *
	 * @param	string	The name of the column you wish to modify.
	 * @param   object	The new column data.
	 * @return  void
	 */
	public function modify( Database_Table_Column $new_column, $existing_column = NULL)
	{
		if($existing_column === NULL)
		{
			if( ! $column->loaded() OR $column->table != $this->_table)
			{
				//TODO: Throw error
			}
			
			$this->_modify_columns[$new_column->name] = $new_column;
		}
		else
		{
			
		}
	}
	
	/**
	 * Drop a column.
	 *
	 * @param   string The name of the column to drop
	 * @return  void
	 */
	public function drop($column)
	{
		$this->_drop_columns[] = $column_name;
	}
	
	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   object  Database instance
	 * @return  string
	 */
	public function compile(Database $db)
	{
		$query = 'ALTER TABLE '.$db->quote_table($this->_table).' ';
		$lines = array();
		
		if($this->_name !== NULL)
		{
			$lines[] = 'RENAME TO '.$db->quote_table($this->_name).'; ';
		}
		
		if(count($this->_add_columns) > 0)
		{
			$columns = array();
			
			$sql = $query.'ADD(';
			
			foreach($this->_add_columns as $name => $params)
			{
				$columns[] = Database_Query_Builder::compile_column($name, $params);
			}
			
			$sql .= implode($columns, ',').'); ';
			
			$lines[] = $sql;
		}
		
		if(count($this->_modify_columns) > 0)
		{
			$columns = array();
			
			$sql = $query.'MODIFY(';
			
			foreach($this->_modify_columns as $name => $params)
			{
				$columns[] = Database_Query_Builder::compile_column($name, $params);
			}
			
			$sql .= implode($columns, ',').'); ';
			
			$lines[] = $sql;
		}
		
		if(count($this->_drop_columns) > 0)
		{
			foreach($this->_drop_columns as $name)
			{
				$drop = new Database_Query_Builder_Drop('column', $name);
				$lines[] = $drop->compile().';';
			}
		}
	}
	
	public function reset()
	{
		$this->_add_columns = array();
		$this->_modify_columns = array();
		$this->_drop_columns = array();
		
		$this->_name = NULL;
	}
	
} // END Kohana_Database_Query_Builder_Alter