<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-2-27
 * Time: 下午4:07
 */
return [
	'httpClient' => [
		'asyn_max_count' => env('HTTP_CLIENT_ASYN_MAX',10),
	],
	'tcpClient' => [
		'asyn_max_count' => env('TCP_CLIENT_ASYN_MAX',10),
		'test' => [
			'pack_tool' => 'LenJsonPack'
		],
		'consul' => [
			'pack_tool' => 'LenJsonPack'
		],
		'consul_MathService' => [
			'pack_tool' => 'LenJsonPack'
		]
	]
];