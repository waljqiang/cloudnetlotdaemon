<?php
return [
	'error' => [
		'enalbe' => env('ERROR_ENABLE',TRUE),//错误收集上报系统
		'http_show' => env('ERROR_HTTP_SHOW',TRUE),//是否显示在http上
		'url' => env('ERROR_URL','http://127.0.0.1:8091/Error'),//访问地址，需自己设置ip：port
		'redis_prefix' => env('ERROR_REDIS_PREFIX','cloudnetlot:sd-error'),
		'redis_timeOut' => env('ERROR_REDIS_TIMEOUT',36000),
		'dingding_enable' => env('ERROR_DINGDING_ENBALE',FALSE),
		'dingding_url' => env('ERROR_DINGDING_URL','https://oapi.dingtalk.com'),
		'dingding_robot' => env('ERROR_DINGDING_ROBOT','/robot/send?access_token=***')//钉钉机器人，需自己申请
	]
];