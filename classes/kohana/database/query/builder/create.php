<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for CREATE statements.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Query_Builder_Create extends Database_Query_Builder {
	
	protected $_columns;
	protected $_table;
	protected $_params;
	
	public function __construct($table, array $columns, array $params)
	{
		$this->_table = $table;
		$this->_params = $params;
		$this->_columns = $columns;
		
		parent::__construct(Database_Query_Type::CREATE, '');
	}
	
	public function compile(Database $db)
	{
		$sql = 'CREATE TABLE '.$db->quote_table($this->_table->name);
		
		if(count($this->_table->columns()) > 0)
		{
			$columns[] = Database_Query_Builder::compile_column($db, $column);
		}
		
		return $sql.';';
	}
	
	public function reset()
	{
		$this->_columns = array();
		$this->_params = array();
		$this->_table = NULL;
	}
}