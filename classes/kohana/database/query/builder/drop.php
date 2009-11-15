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
	
	public function __construct($object)
	{
		$this->_object = $object;
		
		parent::__construct(Database_Query_Type::DROP, '');
	}
	
	public function compile(Database $db)
	{
		$class = get_parent_class($this->_object);
		$class = $class ? $class : get_class($this->_object);
		
		switch($class)
		{
			case 'Kohana_Database':
				$query = 'DROP DATABASE '.$db->quote($this->_object->name);
				break;
			case 'Kohana_Database_Table':
				$query = 'DROP TABLE '.$db->quote_table($this->_object->name);
				break;
			case 'Kohana_Database_Table_Column':
				$query = 'DROP COLUMN '.$db->quote_identifier($this->_object->name);
				break;
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