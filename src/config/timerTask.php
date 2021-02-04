<?php
return [
    'timerTask' => [
        [
            'start_time' => 'Y-m-d 02:00:00',
            'task_name' => 'CheckTask',
            'method_name' => 'checkLicense',
            'interval_time' => '86400',
            'delay' => false
        ],
        [
            'start_time' => 'Y-m-d 23:59:59',
            'task_name' => 'StaticsTask',
            'method_name' => 'ClientsForDeviceByHours',
            'interval_time' => '86400',
            'delay' => true
        ],
        //下面例子表示在每天的14点到20点间每隔1秒执行一次
        /*[
            //'start_time' => 'Y-m-d 14:00:00',
            //'end_time' => 'Y-m-d 20:00:00',
            'task_name' => 'TestTask',
            'method_name' => 'test',
            'interval_time' => '1',
        ],*/
        //下面例子表示在每天的14点到15点间每隔1秒执行一次，一共执行5次
       /* [
            'start_time' => 'Y-m-d 14:00:00',
            'end_time' => 'Y-m-d 15:00:00',
            'task_name' => 'TestTask',
            'method_name' => 'test',
            'interval_time' => '1',
            'max_exec' => 5,
        ],*/
        //下面例子表示在每天的0点执行1次(间隔86400秒为1天)
        /*[
            'start_time' => 'Y-m-d 23:59:59',
            'task_name' => 'TestTask',
            'method_name' => 'test',
            'interval_time' => '86400',
        ],*/
       //下面例子表示在每天的0点执行1次
        /*[
            'start_time' => 'Y-m-d 14:53:10',
            'end_time' => 'Y-m-d 14:54:11',
            'task_name' => 'TestTask',
            'method_name' => 'test',
            'interval_time' => '1',
            'max_exec' => 1,
        ],*/
    ]
];