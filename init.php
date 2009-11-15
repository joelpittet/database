<?php defined('SYSPATH') or die('No direct script access.');

$config = Kohana::config('database');

foreach($config as $name => $settings)
{
	Database::factory($settings, $name, $settings['type']);
}