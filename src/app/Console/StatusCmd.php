<?php
/**
 * Created by PhpStorm.
 * User: waljqiang
 * Date: 18-1-22
 * Time: 上午10:59
 */

namespace app\Console;

use Server\Console\StatusCmd as Command;
use Server\CoreBase\PortManager;
use Server\SwooleServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusCmd extends Command{

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $server_name = $this->config['name'] ?? 'SWD';
        $master_pid = exec("ps -ef | grep $server_name-Master | grep -v 'grep ' | awk '{print $2}'");
        if(empty($master_pid)){
            $io->note("$server_name server not run");
            return;
        }
        /*----------add user process start--------------------*/
        exec("ps -ef | grep $server_name | grep -v 'grep' | grep -v 'vi' | grep -v 'sudo' | awk '{print $8}'",$res);
        $arr = [
        	'cloudnetlotdaemon-Master',
        	'cloudnetlotdaemon-Manager',
        	'cloudnetlotdaemon-Tasker',
        	'cloudnetlotdaemon-Worker',
        	'cloudnetlotdaemon-SDHelpProcess',
        	'cloudnetlotdaemon-ClusterProcess',
        	'cloudnetlotdaemon-CatCacheProcess'
        ];
        $processes = array_count_values($res);
        $userProcesses = 0;
        $userProcessShow = [];
        foreach ($processes as $key => $value) {
        	if(!in_array($key,$arr)){
        		$userProcesses += $value;
        		$userProcessShow[] = [
	                $key,
	                $value,
	                'TRUE'
	            ];
        	}else{
        		$userProcessShow[] = [
	                $key,
	                $value,
	                'FALSE'
	            ];
        	}   
        }
        /*---------------------add user process end----------------*/
        $io->title('WELCOME START SWOOLE DISTRIBUTED, HAVE FUN!');
        $io->table(
            [
                "System",
                "PHP Version",
                "Swoole Version",
                "SwooleDistributed Version",
                "Worker Num",
                "Task Num",
                "User Process Num"
            ],
            [
                [
                    PHP_OS,
                    PHP_VERSION,
                    SWOOLE_VERSION,
                    SwooleServer::version,
                    $this->config->get('server.set.worker_num', 0),
                    $this->config->get('server.set.task_worker_num', 0),
                    $userProcesses
                ]
            ]
        );
        $io->section('Port information');
        $ports = $this->config['ports'];
        $show = [];
        foreach ($ports as $key => $value) {
            $middleware = '';
            foreach ($value['middlewares'] ?? [] as $m) {
                $middleware .= '[' . $m . ']';
            }
            $show[] = [
                PortManager::getTypeName($value['socket_type']),
                $value['socket_name'],
                $value['socket_port'],
                $value['pack_tool'] ?? PortManager::getTypeName($value['socket_type']),
                $middleware
            ];
        }
        $show[] = [
            'CLUSTER',
            '0.0.0.0',
            $this->config->get('cluster.port', '--'),
            $this->config->get('consul.enable', false) ? '<question>OPEN</question>' : '<question>CLOSE</question>'];
        $io->table(
            ['S_TYPE', 'S_NAME', 'S_PORT', 'S_PACK', 'S_MIDD'],
            $show
        );
        /*---------------------add process show start----------------*/
        $io->section('Process information');
        $io->table(
            ['P_NAME', 'P_NUM', 'P_IS_USER'],
            $userProcessShow
        );
        /*---------------------add process show end------------------*/
        $io->note("$server_name server already running");
    }
}