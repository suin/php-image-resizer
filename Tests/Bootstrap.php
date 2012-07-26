<?php
if ( version_compare(PHP_VERSION, '5.3.0', '<') )
{
	echo "This tests requires PHP 5.3.0 or later", PHP_EOL;
	exit(1);
}

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load Source
spl_autoload_register(function($c) { @include_once strtr($c, '\\_', '//').'.php'; });
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__DIR__).'/Source');