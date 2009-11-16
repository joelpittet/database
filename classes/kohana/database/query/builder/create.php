<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Query_Builder_Create extends Database_Query_Builder {
	
	protected $_table;
	
	public function __construct(Database_Table $table)
	{
		if($table->loaded())
		{
			throw new Database_Exception('Cannot create loaded table.');
		}
		
		$this->_table = $table;
		
		parent::__construct(Database_Query_Type::CREATE, '');
	}
	
	public function compile(Database $db)
	{
		$sql = 'CREATE TABLE '.$db->quote_table($this->_table->name);
		
		if(count($this->_table->columns()) > 0)
		{
			$sql .= ' (';
			
			$columns = array();
			
			foreach($this->_table->columns(true) as $column)
			{
				$columns[] = $column->compile();
			}
			
			$sql .= implode($columns, ',').','.$this->_table->compile_constraints().')';
		}
		
		return $sql.';';
	}
	
	public function reset()
	{
		$this->_table = NULL;
	}
}