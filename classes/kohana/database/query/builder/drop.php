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
	
	protected $_object;
	protected $_drop_type;
	
	public function __construct($type, $object)
	{
		$this->_drop_type = $type;
		$this->_object = $object;
		
		parent::__construct(Database_Query_Type::DROP, '');
	}
	
	public function compile(Database $db)
	{	
		switch($this->_drop_type)
		{
			case 'database':
				return 'DROP DATABASE '.$db->quote($this->_object->name);
			case 'table':
				return 'DROP TABLE '.$db->quote_table($this->_object->name);
			case 'column':
				return 'DROP COLUMN '.$db->quote_identifier($this->_object->name);
			default:
				throw new Database_Exception('Invalid drop object');
		}
		
		return $query;
	}
	
	public function reset()
	{
		$this->_object = NULL;
	}
}