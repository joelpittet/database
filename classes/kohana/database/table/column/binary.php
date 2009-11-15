<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table_Column_Binary extends Database_Table_Column_String {
	
	public function __construct($exact = false)
	{
		$this->exact = $exact;
	}
}