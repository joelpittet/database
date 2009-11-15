<?php defined('SYSPATH') or die('No direct script access.');

class Database_Table_Column {
	
	// Editable
	public $name;
	public $default;
	public $is_nullable;
	public $is_primary;
	public $datatype;
	
	// Not editable
	public $ordinal_position;
	public $table;
	
	protected $_loaded = false;
	protected $_original_name;
	
	public function load_schema( & $table, $schema)
	{
		$this->table = & $table;
		
		$this->_original_name = $schema['COLUMN_NAME'];

		$this->default = $schema['COLUMN_DEFAULT'];
		$this->is_nullable = $schema['IS_NULLABLE'] == 'YES';
		$this->is_primary = $schema['COLUMN_KEY'] == 'PRI';
		$this->ordinal_position = $schema['ORDINAL_POSITION'];
		
		//Lets fetch any aditional parametres eg enum()
		preg_match("/^\S+\((.*?)\)/", $schema['COLUMN_TYPE'], $matches);
				
		if(isset($matches[1]))
		{
			//Replace all quotations
			$params = str_replace('\'', '', $matches[1]);
					
			if(strpos($params, ',') === false)
			{
				//Return value as it is
				$this->datatype = array($schema['DATA_TYPE'], $params);
			}
			else
			{
				//Commer seperated values are exploded into an array
				$this->datatype = array($schema['DATA_TYPE'], explode(',', $params));
			}
		}
		else
		{
			//No additional params
			$this->datatype = array($schema['DATA_TYPE']);
		}

		$this->_loaded = true;
	}
	
	public function create()
	{
		DB::alter($this->table->name)
			->add($this)
			->execute();
	}
	
	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function save()
	{
		DB::alter($this->table->name)
			->modify($this->_original_name, $this)
			->execute();
	}
	
	public function drop()
	{
		DB::alter($this->table->name)
			->drop($this->name);
	}
	
	public function compile()
	{
		$db = $this->table->database;
		
		return Database_Query_Builder::compile_column($db, $this);
	}
	
	public function compile_datatype()
	{
		$db = $this->table->database;
		
		list($type, $params) = $this->datatype;
		
		if(isset($params))
		{
			$sql .= strtoupper($type).'(';
				
			if(is_array($params))
			{
				foreach($params as & $param)
				{
					if( ! ctype_digit($param))
					{
						$param = $db->escape($param);
					}
				}

				$sql .= implode($params, ',');
			}
			else
			{
				if( ! ctype_digit($params))
				{
					$params = $db->escape($params);
				}
				
				$sql .= $params;
			}
				
			$sql .= ')';
		}
		else
		{
			$sql .= strtoupper($type);
		}
		
		return $sql;
	}
	
	public function compile_constraints()
	{
		$db = Database::instance();
		
		if( ! $column->is_nullable)
		{
			$sql .= 'NOT NULL ';
		}
		
		if($column->default != NULL)
		{
			$sql .= 'DEFAULT '.$db->escape($column->default);
		}
		
		return $sql;
	}
}