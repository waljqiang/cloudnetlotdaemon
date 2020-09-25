<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-14
 * Time: 下午1:58
 */

use Server\CoreBase\PortManager;

return [
    'ports' => [
        [
            'socket_type' => PortManager::SOCK_TCP,
            'socket_name' => env('TCP_NAME','0.0.0.0'),
            'socket_port' => env('TCP_PORT',9091),
            'pack_tool' => 'LenJsonPack',
            'route_tool' => 'NormalRoute',
            'middlewares' => ['LicenseTcpMiddleware','MonitorMiddleware'],
            'method_prefix' => 'tcp_'
        ],
        [
            'socket_type' => PortManager::SOCK_HTTP,
            'socket_name' => env('HTTP_NAME','0.0.0.0'),
            'socket_port' => env('HTTP_PORT',9092),
            'route_tool' => 'NormalRoute',
            'middlewares' => ['LicenseHttpMiddleware','MonitorMiddleware', 'NormalHttpMiddleware'],
            'method_prefix' => 'http_'
        ],
        [
            'socket_type' => PortManager::SOCK_WS,
            'socket_name' => env('WS_NAME','0.0.0.0'),
            'socket_port' => env('WS_PORT',9093),
            'route_tool' => 'NormalRoute',
            'pack_tool' => 'NonJsonPack',
            'opcode' => PortManager::WEBSOCKET_OPCODE_TEXT,
            'middlewares' => ['MonitorMiddleware', 'NormalHttpMiddleware']
        ]
    ]
];