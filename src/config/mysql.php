<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午4:49
 */
return [
	'mysql' => [
		'enable' => env('MYSQL_ASYN_ENABLE',true),
		'active' => 'master',
		'asyn_max_count' => env('MYSQL_ASYN_MAX',10),
		'prefix' => env('MYSQL_TABLE_PREFIX','cnl'),
		'master' => [
			'host' => env('MYSQL_HOST','127.0.0.1'),
			'port' => env('MYSQL_PORT',3306),
			'user' => env('MYSQL_USER','root'),
			'password' => env('MYSQL_PASSWORD','root'),
			'database' => env('MYSQL_DBNAME','cloudnetlot'),
			'charset' => env('MYSQL_CHARSET','utf8')
		],
		'slave' => [
			'host' => env('MYSQL_HOST','192.168.33.11'),
			'port' => env('MYSQL_PORT',3306),
			'user' => env('MYSQL_USER','root'),
			'password' => env('MYSQL_PASSWORD','root'),
			'database' => env('MYSQL_DBNAME','cloudnetlot'),
			'charset' => env('MYSQL_CHARSET','utf8')
		]
	]
];