<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午4:49
 */
return [
	'mqtt' => [
		'address' => env('MQ_ADDRESS','127.0.0.1'),
		'port' => env('MQ_PORT',1883),
		'username' => env('MQ_USERNAME','cloudnetlot'),
		'password' => env('MQ_PASSWORD','admin@cloudnetlot'),
		'clean' => env('MQ_CLEAN',TRUE),
		'keepalive' => env('MQ_KEEPALIVE',30),
		'qos' => env('MQ_QOS',0),
		'retain' => env('MQ_RETAIN',0),
		'timeout' => env('MQ_TIMEOUT',30),
		"topic" => [
			"deviceup" => "+/+/dev2app",
			"devicedown" => "+/+/app2dev",
			"online" => "\$SYS/brokers/+/clients/+/connected",
			"offline" => "\$SYS/brokers/+/clients/+/disconnected"
		],
	]
];