<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table_Column_Float extends Database_Table_Column_Int {
	
	public $exact;
	
	public function __construct($exact = false)
	{
		$this->exact = $exact;
		
		parent::__construct();
	}
}