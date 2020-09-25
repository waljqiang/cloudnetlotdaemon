<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

return [
	'http' => [
		'index' => 'index.html',
		'root' => [
			'default' => [
				'render' => 'server::welcome'
			]
		],
		//'gzip_off' => FALSE
	]
];

