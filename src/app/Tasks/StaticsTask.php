<?php
namespace app\Tasks;

use Server\CoreBase\Task;
use Carbon\Carbon;
use app\Services\StaticsService;
use app\Models\DeviceModel;

class StaticsTask extends Task{
	private $staticsService;
	private $deviceModel;

	public function initialization($task_id, $from_id, $worker_pid, $task_name, $method_name, $context){
		parent::initialization($task_id, $from_id, $worker_pid, $task_name, $method_name, $context);
		$this->staticsService = $this->loader->model(StaticsService::class,$this);
		$this->deviceModel = $this->loader->model(DeviceModel::class,$this);
	}

	public function ClientsForDeviceByHours(){
		logger("start statics clients for device by hours task",DEBUG);
		$flag = true;
		while($flag){
			$datas = $this->deviceModel->getInfos(["status" => ["IN",[1,3]],"retry" => ["LT",config("public.statics.retry")]],"device_clients_nums","*",NULL,NULL,["id" => "ASC"],[config("public.statics.handle_num")]);
			if(!empty($datas)){
				$this->staticsService->ClientsForDeviceByHours($datas);
			}else{
				$flag = false;
			}
		}
		logger("complete statics clients for device by hours task",DEBUG);
	}

	public function stop(){
		$server_name = $this->config['name'] ?? 'SWD';
	    $master_pid = exec("ps -ef | grep $server_name-Master | grep -v 'grep ' | awk '{print $2}'");
	    $master_pid && posix_kill($master_pid, SIGTERM);
	}
}