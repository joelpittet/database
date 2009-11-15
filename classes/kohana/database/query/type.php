<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Query_Type {
	
	// DML
	const SELECT   = 1;
	const INSERT   = 2;
	const UPDATE   = 3;
	const DELETE   = 4;
	
	// DDL
	const CREATE   = 5;
	const ALTER    = 6;
	const DROP     = 7;
	const TRUNCATE = 8;
	
}