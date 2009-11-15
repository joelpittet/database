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
		
		return $this->database->get_columns($this->name, $details, $like);
	}
	
	public function add_column(Database_Table_Column & $column)
	{
		if($this->_loaded)
		{
			DB::alter($this->name)
				->add($column)
				->execute();
		}
		
		$this->_columns[] = $column;
		$column->table = &$this;
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
	
	public function __clone()
	{
		$this->_loaded = false;
	}
}