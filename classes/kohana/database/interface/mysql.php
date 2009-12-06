<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Interface_Mysql extends Database implements Database_Interface {
	
	protected static $_set_names;
	
	protected $_connection;
	protected $_identifier = '`';
	
	public function __construct($name, array $config)
	{
		parent::__construct($name, $config);

		$this->_database_name = $config['connection']['database'];
	}
	
	public function get_type($datatype)
	{
		$datatype = strtolower($datatype);
		
		switch($datatype)
		{
			// Strings
			
			case 'year':
			case 'tinytext':
			case 'text':
			case 'set':
			case 'nvarchar':
			case 'national varchar':
			case 'mediumtext':
			case 'longtext':
			case 'enum':
			case 'datetime':
				return new Database_Table_Column_String($datatype);
				
			// Decimals
			
			case 'numeric unsigned':
			case 'fixed unsigned':
			case 'decimal unsigned':
				return new Database_Table_Column_Float($datatype, true, 0);
			
			case 'real unsigned':
			case 'float unsigned':
			case 'double unsigned':
			case 'double precision unsigned':
				return new Database_Table_Column_Float($datatype, false, 0);
				
			case 'fixed':
				return new Database_Table_Column_Float($datatype, true);
				
			case 'double':
				return new Database_Table_Column_Float($datatype);
				
			// Binaries
			
			case 'tinyblob':
			case 'mediumblob':
			case 'longblob':
				return new Database_Table_Column_Binary($datatype);
				
			// Integers
				
			case 'integer unsigned':
			case 'int unsigned':
				return new Database_Table_Column_Int($datatype, 4294967295, 0);
				
			case 'bigint unsigned':
				return new Database_Table_Column_Int($datatype, 18446744073709551615, 0);
			
			case 'mediumint':
				return new Database_Table_Column_Int($datatype, 8388607, -8388608);
				
			case 'mediumint unsigned':
				return new Database_Table_Column_Int($datatype, 16777215, 0);
				
			case 'smallint unsigned':
				return new Database_Table_Column_Int($datatype, 65535, 0);
				
			case 'tinyint':
				return new Database_Table_Column_Int($datatype, 127, -128);
			
			case 'tinyint unsigned':
				return new Database_Table_Column_Int($datatype, 255, 0);
		}
		
		return parent::get_type($datatype);
	}
	
	public function connect()
	{
		if ($this->_connection)
			return;

		if (self::$_set_names === NULL)
		{
			// Determine if we can use mysql_set_charset(), which is only
			// available on PHP 5.2.3+ when compiled against MySQL 5.0+
			self::$_set_names = ! function_exists('mysql_set_charset');
		}

		// Extract the connection parameters, adding required variabels
		extract($this->_config['connection'] + array(
			'database'   => '',
			'hostname'   => '',
			'port'       => NULL,
			'socket'     => NULL,
			'username'   => '',
			'password'   => '',
			'persistent' => FALSE,
		));

		// Clear the connection parameters for security
		unset($this->_config['connection']);

		try
		{
			if (empty($persistent))
			{
				// Create a connection and force it to be a new link
				$this->_connection = mysql_connect($hostname, $username, $password, TRUE);
			}
			else
			{
				// Create a persistent connection
				$this->_connection = mysql_pconnect($hostname, $username, $password);
			}
		}
		catch (ErrorException $e)
		{
			// No connection exists
			$this->_connection = NULL;

			throw $e;
		}

		if ( ! mysql_select_db($database, $this->_connection))
		{
			// Unable to select database
			throw new Database_Exception(':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		if ( ! empty($this->_config['charset']))
		{
			// Set the character set
			$this->set_charset($this->_config['charset']);
		}
	}
	
	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if (is_resource($this->_connection))
			{
				$status = mysql_close($this->_connection);
			}
		}
		catch (Exception $e)
		{
			// Database is probably not disconnected
			$status = is_resource($this->_connection);
		}

		return $status;
	}
	
	public function query($type, $sql, $as_object)
	{
			// Make sure the database is connected
		$this->_connection or $this->connect();

		if ( ! empty($this->_config['profiling']))
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start("Database ({$this->_instance})", $sql);
		}

		// Execute the query
		if (($result = mysql_query($sql, $this->_connection)) === FALSE)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(':error [ :query ]',
				array(':error' => mysql_error($this->_connection), ':query' => $sql),
				mysql_errno($this->_connection));
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === Database_Query_Type::SELECT)
		{
			// Return an iterator of results
			return new Database_Interface_Mysql_Query_Result($result, $sql, $as_object);
		}
		elseif ($type === Database_Query_Type::INSERT)
		{
			// Return a list of insert id and rows created
			return array(
				mysql_insert_id($this->_connection),
				mysql_affected_rows($this->_connection),
			);
		}
		else
		{
			// Return the number of rows affected
			return mysql_affected_rows($this->_connection);
		}
	}
	
	public function set_charset($charset)
	{
			// Make sure the database is connected
		$this->_connection or $this->connect();

		if (self::$_set_names === TRUE)
		{
			// PHP is compiled against MySQL 4.x
			$status = (bool) mysql_query('SET NAMES '.$this->quote($charset), $this->_connection);
		}
		else
		{
			// PHP is compiled against MySQL 5.x
			$status = mysql_set_charset($charset, $this->_connection);
		}

		if ($status === FALSE)
		{
			throw new Database_Exception(':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}
	}
	
	public function escape($value)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if (($value = mysql_real_escape_string((string) $value, $this->_connection)) === FALSE)
		{
			throw new Database_Exception(':error',
				array(':error' => mysql_errno($this->_connection)),
				mysql_error($this->_connection));
		}

		// SQL standard is to use single-quotes for all values
		return "'$value'";
	}
}