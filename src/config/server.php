<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

/**
 * 服务器设置
 */
return [
    'name' => env('SERVER_NAME','cloudnetlotdaemon'),
    'auto_reload_enable' => env('SERVER_AUTORELOAD',FALSE),//是否启用代码更新自动reload
    'allow_ServerController' => env('SERVER_ALLOW_SERVER',FALSE),//是否允许访问Server中的Controller，如果不允许将禁止调用Server包中的Controller
    'allow_MonitorFlowData' => env('SERVER_ALLOW_MONITOR',TRUE),//是否允许监控流量数据
    'server' => [
        'send_use_task_num' => env('SERVER_USE_TASK_NUM',500),
        'set' => [
            'log_file' => env('SERVER_LOG_FILE',LOG_DIR . '/swoole.log'),
            'pid_file' => env('SERVER_PID_FILE',PID_DIR . '/server.pid'),
            'log_level' => env('SERVER_LOG_LEVEL',5),
            'reactor_num' => env('SERVER_REACTORS',4), //reactor thread num
            'worker_num' => env('SERVER_WORKERS',4), //worker process num,
            'backlog' => env('SERVER_BACKLOG',128),   //listen backlog
            'open_tcp_nodelay' => env('SERVER_OPEN_TCP_NODELAY',1),
            'socket_buffer_size' => env('SERVER_BUFFER_SIZE',1024 * 1024 * 1024),
            'dispatch_mode' => env('SERVER_DISPATCH_MODE',2),
            'task_worker_num' => env('SERVER_TASK_WORKERS',1),
            'task_max_request' => env('SERVER_TASK_MAX_REQUEST',5000),
            'enable_reuse_port' => env('SERVER_ENABLE_REUSE_PORT',TRUE),
            'heartbeat_idle_time' => env('SERVER_HEART_IDLE_TIME',120),//2分钟后没消息自动释放连接
            'heartbeat_check_interval' => env('SERVER_HEART_CHECK_INTERVAL',60),//1分钟检测一次
            'max_connection' => env('SERVER_MAX_CONNECTION',100000)
        ]
    ],
    'coroution' => [
        'timerOut' => env('SERVER_COROUTION_TIMEOUT',5000),
    ]
];