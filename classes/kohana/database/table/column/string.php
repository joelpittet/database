<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table_Column_String extends Database_Table_Column {
	
	public $character_set;
	public $collation_name;
	
	public $maximum_length;
	public $octet_length;
	
	public $exact;
	
	public function __construct($exact = false)
	{
		$this->exact = $exact;
	}
	
	public function load_schema( & $table, $schema)
	{
		$this->character_set = $schema['CHARACTER_SET_NAME'];
		$this->collation_name = $schema['COLLATION_NAME'];
		$this->maximum_length = $schema['CHARACTER_MAXIMUM_LENGTH'];
		$this->octet_length = $schema['CHARACTER_OCTET_LENGTH'];
		
		parent::load_schema($table, $schema);
	}
}