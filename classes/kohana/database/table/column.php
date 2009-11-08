<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Table column object
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Table_Column {
	
	public $name;
	public $default;
	public $is_nullable;
	public $description;
	public $is_primary;
	public $datatype;
	
	public function compile_datatype()
	{
		$db = Database::instance();
		
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
		if( ! $column->is_nullable)
		{
			$sql .= 'NOT NULL ';
		}
		
		if($column->is_primary)
		{
			$sql .= 'PRIMARY KEY ';
		}
		
		if($column->default != NULL)
		{
			$sql .= 'DEFAULT '.$db->escape($column->default);
		}
		
		return $sql;
	}
}