<?php
namespace app\Console;

use app\AppServer;
use Server\Start;
use Server\Console\RestartCmd as Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Server\SwooleServer;
use Server\CoreBase\PortManager;

class RestartCmd extends Command{
	protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $server_name = $this->config['name'] ?? 'SWD';
        $master_pid = exec("ps -ef | grep $server_name-Master | grep -v 'grep ' | awk '{print $2}'");
        if (empty($master_pid)) {
            $io->warning("$server_name server not running");
            return;
        }
        $command = $this->getApplication()->find('stop');
        $arguments = array(
            'command' => 'stop',
            '-n' => $input->getOption('no-interaction')
        );
        $greetInput = new ArrayInput($arguments);
        $code = $command->run($greetInput, $output);
        if ($code == 1) {
            return;
        }

        $userProcesses = $this->config->get('public.process.defined');
        $userProcessesNum = 0;
        if(!empty($userProcesses)){
            foreach ($userProcesses as $process => $num) {
                $userProcessesShow[] = [
                    $process,
                    $num
                ];
                $userProcessesNum += $num;
            }
        }

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
                    $userProcessesNum
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

        //添加用户自定义进程显示
        if(!empty($userProcessesNum)){
            $io->section('processes of the user defined information');
            $io->table(
                ['P_NAME','P_NUM'],
                $userProcessesShow
            );
        }

        Start::setDaemonize();
        $io->success("Restart success.");
        $server = new AppServer();
        $server->start();
        return 1;
    }
}