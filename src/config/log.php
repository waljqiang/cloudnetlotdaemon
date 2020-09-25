<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-10
 * Time: 下午5:58
 */

return [
	'log' => [
		'active' => 'file',
		'log_level' => env('LOG_LEVEL',ERROR),
		'log_name' => env('LOG_NAME','cloudnetlotdaemon'),

		'file' => [
			'log_max_files' => env('FILE_LOG_MAX',15),
			'efficiency_monitor_enable' => env('EFF_MONITOR_ENABLE',FALSE)
		],

		'syslog' => [
			'ident' => "cloudnetlotdaemon",
			'efficiency_monitor_enable' => env('EFF_MONITOR_ENABLE',TRUE)
		],

		'graylog' => [
			'udp_send_port' => env('GRAYLOG_SEND_PORT',12500),
			'ip' => env('GRAYLOG_IP','127.0.0.1'),
			'port' => env('GRAYLOG_PORT',12201),
			'api_port' => env('GRAYLOG_API_PORT',9000),
			'efficiency_monitor_enable' => env('EFF_MONITOR_ENABLE',TRUE)
		]
	]
];