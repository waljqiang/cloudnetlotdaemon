<?php
namespace app\Utils;

use Server\Asyn\AsynPool;
use Server\CoreBase\SwooleException;
use Waljqiang\Mqtt\Mqtt;

class MqttClientPool extends AsynPool{
	const AsynName = 'mqttClient';
    /**
     * @var HttpClient
     */
    public $mqtt;

    public function __construct($config){
        parent::__construct($config);
        $this->mqtt = new Mqtt([
			"address" => $this->config->get("mqtt.address"),
			"port" => $this->config->get("mqtt.port"),
			"username" => $this->config->get("mqtt.username"),
			"password" => $this->config->get("mqtt.password"),
		],[
			"clean" => $this->config->get("mqtt.clean"),
			"will" => NULL,
			"mode" => 0,
			"keepalive" => $this->config->get("mqtt.keepalive"),
			"timeout" => $this->config->get("mqtt.timeout"),
		]);
        $this->client_max_count = $this->config->get('httpClient.asyn_max_count', 10);
    }

    /**
     * 获取同步
     * @throws SwooleException
     */
    public function getSync(){
        throw new SwooleException('暂时没有yunlot的同步方法');
    }

    /**
     * @return string
     */
    public function getAsynName(){
        return self::AsynName . ":" . $this->name;
    }


    /**
     * 销毁Client
     * @param $client
     */
    protected function destoryClient($client){
        $client->close();
    }

    /**
     * 销毁整个池子
     */
    public function destroy(&$migrate = []){
        $migrate = parent::destroy($migrate);
        $this->mqtt = null;
        return $migrate;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function __call($method,$args){
    	return call_user_func_array([$this->mqtt,$method],$args);
    }
}