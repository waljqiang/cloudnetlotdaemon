<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

return [
	'amqp' => [
		'active' => 'local',
		'local' => [
			'host' => env('AMQP_HOST','localhost'),
			'port' => env('AMQP_PORT',5672),
			'user' => env('AMQP_USER','guest'),
			'password' => env('AMQP_PARSSWORD','guest'),
			'vhost' => env('AMQP_VHOST','/')
		]
	]
];

