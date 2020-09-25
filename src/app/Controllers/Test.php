<?php
namespace app\Controllers;

use app\Controllers\Base;
use app\Services\TestService;

class Test extends Base{
	public $testModel;
	public $tcpClient;

	public $testService;

	public function initialization($controllerName, $methodName){
		parent::initialization($controllerName,$methodName);
		$this->testService = $this->loader->model(TestService::class,$this);
		$this->tcpClient = get_instance()->getAsynPool("TCP_CLIENT");
	}

	//http://192.168.33.11:9092/Test/test?params=100
	public function http_test(){
		$params = $this->http_input->get('params');
		$this->http_output->end($params);
	}

	public function http_config(){
		$a = get_instance()->config->get("consul","");
		$b = config("consul","");
		$c = $this->config->get("consul","");
		$this->http_output->end(["a" => $a,"b" => $b,"c" => $c]);
	}

	public function http_redis(){
		$res = $this->testService->testRedis();
        $this->http_output->end($res);
	}

	public function http_redislua(){
		$value = $this->redis->evalSha(getLuaSha1('rpops_from_count'), ['cnl:device:queue:data',100],2,[1,2,3]);
		$this->http_output->end($value);
	}

	public function http_log(){
		$this->log('123',DEBUG);
		logger('123',INFO);
	}

	public function http_mysql(){
		$a = $this->testService->testMysql();
		$this->http_output->end($a);
	}

	//http://192.168.33.11:9092/Test/tcp?params=789
	public function http_tcp(){
		$data = ["controller_name" => "Test","method_name" => "test","data"=>$this->http_input->get('params')];
		$this->tcpClient->setPath("/Test/test",$data);
		$result = $this->tcpClient->coroutineSend($data);
		$this->http_output->end($result);
	}

	public function tcp_test(){
		$this->send($this->client_data->data);
	}

	public function http_upinfo(){
		$this->testService->upinfo();
	}

}