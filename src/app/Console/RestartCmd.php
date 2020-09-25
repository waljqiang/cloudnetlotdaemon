<?php
namespace app\Console;

use app\AppServer;
use Server\Start;
use Server\Console\RestartCmd as Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        Start::setDaemonize();
        $io->success("Restart success.");
        $server = new AppServer();
        $server->start();
        return 1;
    }
}