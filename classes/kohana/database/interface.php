<?php defined('SYSPATH') or die('No direct script access.');

interface Kohana_Database_Interface {
	
	public function connect();
	public function disconnect();
	public function query($type, $sql, $as_object);
	public function set_charset($charset);
	public function escape($value);
	public function get_type($datatype);
	public function __construct($name, array $config);
	
}