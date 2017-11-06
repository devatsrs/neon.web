<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/
	'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => 'sqlsrv',

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

	'connections' => array(

		'sqlite' => array(
			'driver'   => 'sqlite',
			'database' => __DIR__.'/../database/production.sqlite',
			'prefix'   => '',
		),

		'mysql' => array(
			'driver'    => 'mysql',
			'host'     => getenv('DB_HOST'),
			'database' => getenv('DB_DATABASE'),
			'username' => getenv('DB_USERNAME'),
			'password' => substr(getenv('DB_PASSWORD'),5),
			'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
		),

		'pgsql' => array(
			'driver'   => 'pgsql',
			'host'     => 'localhost',
			'database' => 'forge',
			'username' => 'forge',
			'password' => '',
			'charset'  => 'utf8',
			'prefix'   => '',
			'schema'   => 'public',
		),

        /** Primary RM Database **/
        'sqlsrv' => [
            'driver'   => 'mysql',
            'host'     => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => substr(getenv('DB_PASSWORD'),5),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'   => '',
        ],
        /** Billing Database **/
        'sqlsrv2' => [
            'driver'   => 'mysql',
            'host'     => getenv('DB_HOST2'),
            'database' => getenv('DB_DATABASE2'),
            'username' => getenv('DB_USERNAME2'),
            'password' => substr(getenv('DB_PASSWORD2'),5),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'   => '',
        ],
        /** CDR Database **/
        'sqlsrvcdr' => [
            'driver'   => 'mysql',
            'host'     => getenv('DB_HOSTCDR'),
            'database' => getenv('DB_DATABASECDR'),
            'username' => getenv('DB_USERNAMECDR'),
            'password' => substr(getenv('DB_PASSWORDCDR'),5),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'   => '',
        ],
        /** OLD RM Server Database **/
        'sqlsrv3' => [
            'driver'   => 'mysql',
            'host'     => getenv('DB_HOST3'),
            'database' => getenv('DB_DATABASE3'),
            'username' => getenv('DB_USERNAME3'),
            'password' => substr(getenv('DB_PASSWORD3'),5),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'   => '',
        ],
        /** PBX Server Database **/
        'pbxmysql' => [
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOSTPBX'),
            'database'  => getenv('DB_DATABASEPBX'),
            'username'  => getenv('DB_USERNAMEPBX'),
            'password'  => substr(getenv('DB_PASSWORDPBX'),5),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],
		/** Neon Report Database **/
		'neon_report' => [
			'driver'    => 'mysql',
			'host'      => getenv('DB_HOSTREPORT'),
			'database'  => getenv('DB_DATABASEREPORT'),
			'username'  => getenv('DB_USERNAMEREPORT'),
			'password'  => substr(getenv('DB_PASSWORDREPORT'),5),
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
			'strict'    => false,
		],

	),

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => array(

		'cluster' => false,

		'default' => array(
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		),

	),

);
