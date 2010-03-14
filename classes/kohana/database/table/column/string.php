<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Table column string object
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Database_Table_Column_String extends Database_Table_Column {
	
	public $character_set;
	public $collation_name;
	
	public $maximum_length;
	public $octet_length;
	
}