<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for DROP statements.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Query_Builder_Drop extends Database_Query_Builder {
	
	protected $_type;
	protected $_name;
	
	public function __construct($type, $name)
	{
		$this->_type = strtoupper($type);
		$this->_name = $name;
		
		parent::__construct(Database::DROP, '');
	}
	
	public function compile(Database $db)
	{
		switch($type)
		{
			case 'DATABASE' :
				$query = 'DROP DATABASE '.$db->quote($this->_name);
			case 'TABLE' :
				$query = 'DROP TABLE '.$db->quote_table($this->_name);
			case 'COLUMN' :
				$query = 'DROP COLUMN '.$db->quote_identifier($this->_name);
			default:
				throw new Kohana_Exception('Invalid drop type :type', 
					array('type' => $this->_type));
		}

		return $query;
	}
}