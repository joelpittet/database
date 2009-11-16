<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Database {
	
	protected static $_instances = array();
	
	public static function factory(array $settings, $name = 'default', $interface = 'mysql')
	{
		$class = 'Database_Interface_'.ucfirst($interface);
		
		if( ! class_exists($class))
		{
			//Throw Error
		}
		
		return self::$_instances[$name] = new $class($name, $settings);
	}
	
	public static function instance($name = 'default')
	{
		if( ! isset(self::$_instances[$name]))
		{
			//THROW ERROR
		}
		
		return self::$_instances[$name];
	}
	
	public $table_prefix;
	public $last_query;
	
	protected $_database_name;
	protected $_config;
	protected $_instance;
	
	public function __construct($name, array $config)
	{
		$this->table_prefix = arr::get($config, 'table_prefix', NULL);
		$this->_instance = get_class($this);
		$this->_config = $config;
	}
	
	final public function __destruct()
	{
		$this->disconnect();
	}
	
	public function get_type($datatype)
	{
		$datatype = strtolower($datatype);
		
		switch($datatype)
		{
			// EXACT STRING
				// SQL-92
			case 'bit':
			case 'char':
			case 'character':
			case 'character varying':
			case 'date':
			case 'national char':
			case 'interval':
			case 'national character':
			case 'nchar':
				return new Database_Table_Column_String($datatype, true);
			
			// STRING
				// SQL-92
			case 'bit varying':
			case 'char varying':
			case 'national char varying':
			case 'national character varying':
			case 'nchar varying':
			case 'time':
			case 'time with time zone':
			case 'varchar':
				//SQL:1999
			case 'char large object':
			case 'character large object':
			case 'clob':
			case 'national character large object':
			case 'nchar large object':
			case 'nclob':
			case 'time without time zone':
			case 'timestamp without time zone':
				return new Database_Table_Column_String($datatype);
				
			// BINARY STRING
				// SQL:1999
			case 'binary large object':
			case 'blob':
			case 'binary varying':
			case 'varbinary':
				return new Database_Table_Column_Binary($datatype);
				
			// EXACT BINARY STRING
				// SQL:2008
			case 'binary':
				return new Database_Table_Column_Binary($datatype, true);
			
			// FLOAT EXACT
				// SQL-92
			case 'dec':
			case 'decimal':
			case 'numeric':
				return new Database_Table_Column_Float($datatype, true);
				
			// FLOAT
				// SQL-92
			case 'double precision':
			case 'float':
			case 'real':
				return new Database_Table_Column_Float($datatype);
				
			// INT
				// SQL-92
			case 'int':
			case 'integer':
				return new Database_Table_Column_Int($datatype, 2147483647, -2147483648);
				
			// SMALLINT
				// SQL-92
			case 'smallint':
				return new Database_Table_Column_Int($datatype, 32767, -32768);
				
			// BIGINT
				// SQL:2003
			case 'bigint':
				return new Database_Table_Column_Int($datatype, 9223372036854775807, -9223372036854775808);
				
			// BOOL
				// SQL:1999
			case 'boolean':
				return new Database_Table_Column_Bool($datatype);
		}
		
		throw new Database_Exception('Datatype dt could not be associated with any standard', 
			array('dt' => $datatype));
	}
	
	public function get_tables($details = FALSE, $like = NULL)
	{
		$database = $this->_database_name;

		if (is_string($like))
		{
			// Specific tables
			$result = $this->query(Database_Query_Type::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Tables WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME LIKE '.$this->quote($this->table_prefix.$like), FALSE);
		}
		else
		{
			// All tables
			$result = $this->query(Database_Query_Type::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Tables WHERE TABLE_SCHEMA = '.$this->quote($database), FALSE);
		}

		$tables = array();
		
		if($details)
		{
			if(count($result) === 1)
			{
				$tables = new Database_Table($this, $result[0]);
			}
			else
			{
				foreach ($result as $row)
				{
					$tables[$row['TABLE_NAME']] = new Database_Table($this, $result[0]);
				}
			}
		}
		else
		{
			if(count($result) === 1)
			{
				$tables = $row['TABLE_NAME'];
			}
			else
			{
				foreach ($result as $row)
				{
					$tables[] = $row['TABLE_NAME'];
				}
			}
		}

		return $tables;
	}
	
	public function get_columns( Database_Table & $table, $details = FALSE, $like = NULL)
	{
		$database = $this->_database_name;
		
		if (is_string($like))
		{
			$result = $this->query(Database_Query_Type::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME = '.$this->quote($this->table_prefix.$table->name).' AND COLUMN_NAME LIKE '.$this->quote($like), FALSE);
		}
		else
		{
			$result = $this->query(Database_Query_Type::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME = '.$this->quote($this->table_prefix.$table->name), FALSE);
		}
		
		$columns = array();
		
		if($details)
		{
			if(count($result) === 1)
			{
				$columns = $this->get_type(strtolower($result[0]['DATA_TYPE']));
				$columns->load_schema($table, $result[0]);
			}
			else
			{
				foreach ($result as $row)
				{
					$column = $this->get_type(strtolower($row['DATA_TYPE']));
					$column->load_schema($table, $row);
					$columns[$row['COLUMN_NAME']] = $column;
				}
			}
		}
		else
		{
			if(count($result) === 1)
			{
				$columns = $row['COLUMN_NAME'];
			}
			else
			{
				foreach ($result as $row)
				{
					$columns[] = $row['COLUMN_NAME'];
				}
			}
		}
		
		return $columns;
	}
	
	public function quote($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif ($value === TRUE)
		{
			return "'1'";
		}
		elseif ($value === FALSE)
		{
			return "'0'";
		}
		elseif (is_object($value))
		{
			if ($value instanceof Database_Query)
			{
				// Create a sub-query
				return '('.$value->compile($this).')';
			}
			elseif ($value instanceof Database_Expression)
			{
				// Use a raw expression
				return $value->value();
			}
			else
			{
				// Convert the object to a string
				return $this->quote((string) $value);
			}
		}
		elseif (is_array($value))
		{
			return '('.implode(', ', array_map(array($this, __FUNCTION__), $value)).')';
		}
		elseif (is_int($value))
		{
			return (int) $value;
		}

		return $this->escape($value);
	}
	
	public function quote_table($value)
	{
		// Assign the table by reference from the value
		if (is_array($value))
		{
			$table =& $value[0];
		}
		else
		{
			$table =& $value;
		}

		if (is_string($table) AND strpos($table, '.') === FALSE)
		{
			// Add the table prefix for tables
			$table = $this->table_prefix.$table;
		}

		return $this->quote_identifier($value);
	}
	
	public function quote_identifier($value)
	{
		if ($value === '*')
		{
			return $value;
		}
		elseif (is_object($value))
		{
			if ($value instanceof Database_Query)
			{
				// Create a sub-query
				return '('.$value->compile($this).')';
			}
			elseif ($value instanceof Database_Expression)
			{
				// Use a raw expression
				return $value->value();
			}
			else
			{
				// Convert the object to a string
				return $this->quote_identifier((string) $value);
			}
		}
		elseif (is_array($value))
		{
			// Separate the column and alias
			list ($value, $alias) = $value;

			return $this->quote_identifier($value).' AS '.$this->quote_identifier($alias);
		}

		if (strpos($value, '"') !== FALSE)
		{
			// Quote the column in FUNC("ident") identifiers
			return preg_replace('/"(.+?)"/e', '$this->quote_identifier("$1")', $value);
		}
		elseif (strpos($value, '.') !== FALSE)
		{
			// Split the identifier into the individual parts
			$parts = explode('.', $value);

			if ($prefix = $this->table_prefix)
			{
				// Get the offset of the table name, 2nd-to-last part
				// This works for databases that can have 3 identifiers (Postgre)
				$offset = count($parts) - 2;

				// Add the table prefix to the table name
				$parts[$offset] = $prefix.$parts[$offset];
			}

			// Quote each of the parts
			return implode('.', array_map(array($this, __FUNCTION__), $parts));
		}
		else
		{
			return $this->_identifier.$value.$this->_identifier;
		}
	}
}