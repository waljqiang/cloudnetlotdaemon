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
return [
	'device' => [
		'typeinfo' => [
			'encode' => '1',
			'system' => '2',
			'network' => '3',
			'wifi' => '4',
			'user' => '5',
			'time_reboot' => '6',
			'upgrade' => '7',
			'bind' => '8',
			'upinfofail' => '9'
		],
		'status' => [
			'online' => 1,
			'offline' => 0
		]
	]
];
