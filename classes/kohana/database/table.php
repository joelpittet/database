<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table {
	
	public $database;
	public $name;
	public $type;
	public $catalog;
	
	protected $_loaded = false;
	protected $_columns = array();
	
	public function __construct($database, $information_schema = NULL)
	{
		$this->database = $database;
		
		if($information_schema !== NULL)
		{
			$this->name = $information_schema['TABLE_NAME'];
			$this->type = $information_schema['TABLE_TYPE'];
			$this->catalog = $information_schema['TABLE_CATALOG'];
			
			$this->_loaded = true;
		}
	}
	
	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function columns($details = FALSE, $like = NULL)
	{
		if( ! $this->_loaded)
		{
			return $this->_columns;
		}
		
		return $this->database->get_columns($this, $details, $like);
	}
	
	public function compile_constraints()
	{
		$db = $this->database;
		$columns = $this->columns(true);
		
		$primary_keys = array();
		$unique_keys = array();
		
		foreach($columns as $column)
		{
			if($column->is_primary)
			{
				$primary_keys[] = $column;
			}
			elseif($column->is_unique)
			{
				$unique_keys[] = $column;
			}
		}
		
		$constrains = array();
		
		// PROCESS PRIMARY KEYS
		// NAMING: pk_field1_field2_...
		
		$key_name = 'pk_';
		$keys = '';
		
		foreach($primary_keys as $name => $key)
		{
			$key_name .= $key->name.'_';
			$keys .= $db->quote_identifier($key->name).',';
		}
		
		$key_name = rtrim($key_name, '_');
		$keys = rtrim($keys, ',');
		
		$constrains[] = 'CONSTRAINT '.$key_name.' PRIMARY KEY ('.$keys.')';
		
		// PROCESS UNIQUE KEYS
		// NAMING: key_field
		
		foreach($unique_keys as $key)
		{
			$constrains[] = 'CONSTRAINT key_'.$key->name.' UNIQUE('.$db->quote_identifier($key->name).')';
		}
		
		return implode(',', $constrains);
	}
	
	public function add_column(Database_Table_Column & $column)
	{
		$column->table =& $this;
		
		if($this->_loaded)
		{
			DB::alter($this)
				->add($column)
				->execute();
		}
		
		$this->_columns[] = $column;
	}
	
	public function drop()
	{
		DB::drop($this)
			->execute();
	}
	
	public function create()
	{
		DB::create($this)
			->execute();
	}
	
	public function rename($new_name)
	{
		DB::alter($this)
			->rename($new_name)
			->execute();
			
		$this->name = $new_name;
	}
	
	public function __clone()
	{
		$this->_loaded = false;
	}
}