<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Table column integer object
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Table_Column_Int extends Database_Table_Column {
	
	public $is_unsigned;
	public $is_auto_increment;
	
	public $precision;
	public $scale;
	
	public $maximum_value;
	
	public function compile_constraints()
	{
		$sql = '';
		
		if($this->is_unsigned)
		{
			$sql .= 'UNSIGNED ';
		}
		
		if($this->is_auto_increment)
		{
			$sql .= 'AUTO_INCREMENT ';
		}
		
		$sql .= parent::compile_constraints();
		
		return $sql;
	}
}