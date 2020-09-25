<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

/**
 * 选择数据库环境
 */
$password = env('REDIS_PASSWORD','');
$local = [
	'ip' => env('REDIS_HOST','127.0.0.1'),
	'port' => env('REDIS_PORT',6379),
	'password' => env('REDIS_PASSWORD',''),
	'select' => env('REDIS_DB',0)
];
if(!empty($password))
	$local['password'] = $password;
return [
	'redis' => [
		'enable' => env('REDIS_ASYN_ENABLE',TRUE),
		'active' => 'local',
		'asyn_max_count' => env('REDIS_ASYN_MAX',10),
		'prefix' => env('REDIS_PREFIX',''),
		'local' => $local
	]
];
