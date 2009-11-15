<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Query_Builder_Create extends Database_Query_Builder {
	
	protected $_table;
	
	public function __construct(Database_Table $table)
	{
		if($table->loaded())
		{
			//TODO: Throw error, table cant be loaded
		}
		
		$this->_table = $table;
		
		parent::__construct(Database::CREATE, '');
	}
	
	public function compile(Database $db)
	{
		$query = 'CREATE TABLE '.$db->quote_table($this->_table).' (';
		$columns = array();
		
		foreach($this->_columns as $column)
		{
			$columns[] = $column->compile();
		}
		
		$query .= implode($columns, ',');
		
		$query .= ');';
		
		return $query;
	}
	
	public function reset()
	{
		$this->_table = NULL;
	}
}