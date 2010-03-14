<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database connection wrapper.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Database {

	// Query types
	const SELECT =  1;
	const INSERT =  2;
	const UPDATE =  3;
	const DELETE =  4;
	
	 /**
	* @var array SQL standard types
	*/
	protected static $_types = array(
		// SQL-92
		'bit'								=> array('type' => 'string', 'exact' => TRUE),
		'bit varying'						=> array('type' => 'string'),
		'char'								=> array('type' => 'string', 'exact' => TRUE),
		'char varying'						=> array('type' => 'string'),
		'character'							=> array('type' => 'string', 'exact' => TRUE),
		'character varying'					=> array('type' => 'string'),
		'date'								=> array('type' => 'string'),
		'dec'								=> array('type' => 'float', 'exact' => TRUE),
		'decimal'							=> array('type' => 'float', 'exact' => TRUE),
		'double precision'					=> array('type' => 'float'),
		'float'								=> array('type' => 'float'),
		'int'								=> array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
		'integer'							=> array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
		'interval'							=> array('type' => 'string'),
		'national char'						=> array('type' => 'string', 'exact' => TRUE),
		'national char varying' 			=> array('type' => 'string'),
		'national character' 				=> array('type' => 'string', 'exact' => TRUE),
		'national character varying' 		=> array('type' => 'string'),
		'nchar' 							=> array('type' => 'string', 'exact' => TRUE),
		'nchar varying'						=> array('type' => 'string'),
		'numeric'							=> array('type' => 'float', 'exact' => TRUE),
		'real'								=> array('type' => 'float'),
		'smallint'							=> array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
		'time'								=> array('type' => 'string'),
		'time with time zone'				=> array('type' => 'string'),
		'timestamp'							=> array('type' => 'string'),
		'timestamp with time zone'			=> array('type' => 'string'),
		'varchar'							=> array('type' => 'string'),
		 
		// SQL:1999
		//'array','ref','row'
		'binary large object'				=> array('type' => 'string', 'binary' => TRUE),
		'blob'								=> array('type' => 'string', 'binary' => TRUE),
		'boolean'							=> array('type' => 'bool'),
		'char large object'					=> array('type' => 'string'),
		'character large object'			=> array('type' => 'string'),
		'clob'								=> array('type' => 'string'),
		'national character large object'	=> array('type' => 'string'),
		'nchar large object'				=> array('type' => 'string'),
		'nclob'								=> array('type' => 'string'),
		'time without time zone'			=> array('type' => 'string'),
		'timestamp without time zone'		=> array('type' => 'string'),
		 
		// SQL:2003
		'bigint'							=> array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
		 
		// SQL:2008
		'binary'							=> array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
		'binary varying'					=> array('type' => 'string', 'binary' => TRUE),
		'varbinary'							=> array('type' => 'string', 'binary' => TRUE),
	);

	/**
	 * @var  array  Database instances
	 */
	public static $instances = array();

	/**
	 * Get a singleton Database instance. If configuration is not specified,
	 * it will be loaded from the database configuration file using the same
	 * group as the name.
	 *
	 * @param   string   instance name
	 * @param   array    configuration parameters
	 * @return  Database
	 */
	public static function instance($name = 'default', array $config = NULL)
	{
		if ( ! isset(Database::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this database
				$config = Kohana::config('database')->$name;
			}

			if ( ! isset($config['type']))
			{
				throw new Kohana_Exception('Database type not defined in :name configuration',
					array(':name' => $name));
			}

			// Set the driver class name
			$driver = 'Database_'.ucfirst($config['type']);

			// Create the database connection instance
			new $driver($name, $config);
		}

		return Database::$instances[$name];
	}

	/**
	 * @var  string  the last query executed
	 */
	public $last_query;

	// Character that is used to quote identifiers
	protected $_identifier = '"';

	// Instance name
	protected $_instance;

	// Raw server connection
	protected $_connection;

	// Configuration array
	protected $_config;

	/**
	 * Stores the database configuration locally and name the instance.
	 *
	 * @return  void
	 */
	final protected function __construct($name, array $config)
	{
		// Set the instance name
		$this->_instance = $name;
		
		// Store the config locally
		$this->_config = $config;

		if (isset($config['connection']['dsn']) AND preg_match('/dbname=([^;\b]+)/', $config['connection']['dsn'], $matches))
		{
			// Grab database name from dsn if possible
			$this->_database_name = $matches[1];
		}
		else
		{
			// Fallback to connection database name
			$this->_database_name = $config['connection']['database'];
		}

		// Store the database instance
		Database::$instances[$name] = $this;
	}

	/**
	 * Disconnect from the database when the object is destroyed.
	 *
	 * @return  void
	 */
	final public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Returns the database instance name.
	 *
	 * @return  string
	 */
	final public function __toString()
	{
		return $this->_instance;
	}

	/**
	 * Connect to the database.
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	abstract public function connect();

	/**
	 * Disconnect from the database
	 *
	 * @return  boolean
	 */
	abstract public function disconnect();

	/**
	 * Set the connection character set.
	 *
	 * @throws  Database_Exception
	 * @param   string   character set name
	 * @return  void
	 */
	abstract public function set_charset($charset);

	/**
	 * Perform an SQL query of the given type.
	 *
	 * @param   integer  Database::SELECT, Database::INSERT, etc
	 * @param   string   SQL query
	 * @param   string   result object class, TRUE for stdClass, FALSE for assoc array
	 * @return  object   Database_Result for SELECT queries
	 * @return  array    list (insert id, row count) for INSERT queries
	 * @return  integer  number of affected rows for all other queries
	 */
	abstract public function query($type, $sql, $as_object);

	/**
	 * Count the number of records in a table.
	 *
	 * @param   mixed    table name string or array(query, alias)
	 * @return  integer
	 */
	public function count_records($table)
	{
		// Quote the table name
		$table = $this->quote_identifier($table);

		return $this->query(Database::SELECT, 'SELECT COUNT(*) AS total_row_count FROM '.$table, FALSE)
			->get('total_row_count');
	}

	/**
	 * List all of the tables in the database. Optionally, a LIKE string can
	 * be used to search for specific tables.
	 *
	 * @param   string   table to search for
	 * @param   bool     grab extended table information
	 * @return  array
	 */
	public function list_tables($like = NULL, $details = FALSE)
	{
		$database = $this->_database_name;

		if (is_string($like))
		{
			// Specific tables
			$result = $this->query(Database::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Tables WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME LIKE '.$this->quote($like), FALSE);
		}
		else
		{
			// All tables
			$result = $this->query(Database::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Tables WHERE TABLE_SCHEMA = '.$this->quote($database), FALSE);
		}

		$tables = array();

		if ($details)
		{
			foreach ($result as $row)
			{
				// Grab table details as table => details
				$tables[$row['TABLE_NAME']] = $row;
			}
		}
		else
		{
			foreach ($result as $row)
			{
				// Grab table name
				$tables[] = $row['TABLE_NAME'];
			}
		}

		return $tables;
	}

	/**
	 * Lists all of the columns in a table. Optionally, a LIKE string can be
	 * used to search for specific fields.
	 *
	 * @param   string  table to get columns from
	 * @param   string  column to search for
	 * @param   bool    grab extended column information
	 * @return  array
	 */
	public function list_columns($table, $like = NULL, $details = FALSE)
	{
		$database = $this->_database_name;

		if (is_string($like))
		{
			$result = $this->query(Database::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME = '.$this->quote($this->table_prefix().$table).' AND COLUMN_NAME LIKE '.$this->quote($like), FALSE);
		}
		else
		{
			$result = $this->query(Database::SELECT, 'SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_SCHEMA = '.$this->quote($database).' AND TABLE_NAME = '.$this->quote($this->table_prefix().$table), FALSE);
		}
		
		$columns = array();
		
		//Get all static variables belonging to the active driver
		$vars = get_class_vars('Database_'.ucfirst($this->_config['type']));

		if ($details)
		{
			foreach ($result as $row)
			{
				$db = Database::instance();
				
				$type = strtolower($row['DATA_TYPE']);
				$column = new Database_Table_Column;
				
				if (isset($vars['_types'][$type]))
				{
					//Found native type, use it.
					$type = $vars['_types'][$type]['type'];
				}
				elseif (isset(Database::$_types[$type]))
				{
					//Could not find native type, overloaded to defaults.
					$type = Database::$_types[$type]['type'];
				}
				else
				{
					throw new Kohana_Exception('Datatype not recognised :data_type', 
						array('data_type' => $type));
				}
				
				if(class_exists($class = 'Database_Table_Column_'.ucfirst($type)))
				{
					$column = new $class;
				}
				
				switch($type)
				{
					//Load all datatype specific values
					case 'float' :
					case 'int' : 
						$column->precision = $row['NUMERIC_PRECISION'];
						$column->scale = $row['NUMERIC_SCALE'];
						break;
					case 'string' :
						$column->character_set = $row['CHARACTER_SET_NAME'];
						$column->collation_name = $row['COLLATION_NAME'];
						$column->maximum_length = $row['CHARACTER_MAXIMUM_LENGTH'];
						$column->octet_length = $row['CHARACTER_OCTET_LENGTH'];
						break;
				}
				
				//Load all the non specific datatype values
				$column->name = $row['COLUMN_NAME'];
				$column->default = $row['COLUMN_DEFAULT'];
				$column->is_nullable = $row['IS_NULLABLE'] == 'YES';
				$column->is_primary = $row['COLUMN_KEY'] == 'PRI';
				$column->descrition = $row['COLUMN_COMMENT'];
				
				//Lets fetch any aditional parametres eg enum()
				preg_match("/^\S+\((.*?)\)/", $row['COLUMN_TYPE'], $matches);
				
				if(isset($matches[1]))
				{
					//Replace all quotations
					$params = str_replace('\'', '', $matches[1]);
					
					if(strpos($params, ',') === false)
					{
						//Return value as it is
						$column->datatype = array($row['DATA_TYPE'], $params);
					}
					else
					{
						//Commer seperated values are exploded into an array
						$column->datatype = array($row['DATA_TYPE'], explode(',', $params));
					}
				}
				else
				{
					//No additional params
					$column->datatype = array($row['DATA_TYPE']);
				}
				
				//Add it to the column stack
				$columns[] = $column;
			}
		}
		else
		{
			foreach ($result as $row)
			{
				// Grab column names
				$columns[] = $row['COLUMN_NAME'];
			}
		}

		return $columns;
	}

	/**
	 * Return the table prefix.
	 *
	 * @return  string
	 */
	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}

	/**
	 * Quote a value for an SQL query.
	 *
	 * @param   mixed   any value to quote
	 * @return  string
	 */
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

	/**
	 * Quote a database table name and adds the table prefix if needed
	 *
	 * @param   mixed   table name or array(table, alias)
	 * @return  string
	 */
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
			$table = $this->table_prefix().$table;
		}

		return $this->quote_identifier($value);
	}

	/**
	 * Quote a database identifier, such as a column name. Adds the
	 * table prefix to the identifier if a table name is present.
	 *
	 * @param   mixed   any identifier
	 * @return  string
	 */
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

			if ($prefix = $this->table_prefix())
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

	/**
	 * Sanitize a string by escaping characters that could cause an SQL
	 * injection attack.
	 *
	 * @param   string   value to quote
	 * @return  string
	 */
	abstract public function escape($value);

} // End Database_Connection
