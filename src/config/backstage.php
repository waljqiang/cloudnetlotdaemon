<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-10
 * Time: 下午5:58
 */

return [
	'backstage' => [
		'enable' => env('BACKSTAGE_ENABLE',FALSE),
		'xdebug_enable' => env('BACKSTAGE_XDEBUG_ENABLE',TRUE),//是否启用xdebug
		'port' => env('BACKSTAGE_PORT',18000),//web页面访问端口
		'socket' => env('BACKSTAGE_SOCKET','0.0.0.0'),
		'websocket_port' => env('BACKSTAGE_WEBSOCKET_PORT',18084),//提供的ws端口
		'bin_path' => env('BACKSTAGE_BIN_PATH','/bin/exec/backstage')//设置路径
	]
];