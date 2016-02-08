<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/
    'default' => 'sqlsrv',

	'connections' => array(

//		'mysql' => array(
//			'driver'    => 'mysql',
//			'host'      => 'localhost',
//			'database'  => 'rate_management',
//			'username'  => 'root',
//			'password'  => '',
//			'charset'   => 'utf8',
//			'collation' => 'utf8_unicode_ci',
//			'prefix'    => '',
//		),

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'prefix'   => '',
        ],
        'sqlsrv2' => [
            'driver'   => 'sqlsrv',
            'host'     => getenv('DB_HOST2'),
            'database' => getenv('DB_DATABASE2'),
            'username' => getenv('DB_USERNAME2'),
            'password' => getenv('DB_PASSWORD2'),
            'prefix'   => '',
        ],
        'sqlsrv3' => [
            'driver'   => 'sqlsrv',
            'host'     => getenv('DB_HOST3'),
            'database' => getenv('DB_DATABASE3'),
            'username' => getenv('DB_USERNAME3'),
            'password' => getenv('DB_PASSWORD3'),
            'prefix'   => '',
        ],
        'sqlsrvcdr' => [
            'driver'   => 'sqlsrv',
            'host'     => getenv('DB_HOSTCDR'),
            'database' => getenv('DB_DATABASECDR'),
            'username' => getenv('DB_USERNAMECDR'),
            'password' => getenv('DB_PASSWORDCDR'),
            'prefix'   => '',
        ],
	),

);
