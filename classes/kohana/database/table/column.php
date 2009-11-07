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
	public $datatype;
	public $description;
	public $is_primary;
	
}