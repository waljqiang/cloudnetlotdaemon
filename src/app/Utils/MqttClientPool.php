<?php
namespace app\Utils;

use Server\Asyn\AsynPool;
use Server\CoreBase\SwooleException;
use app\Utils\Mqtt;

class MqttClientPool extends AsynPool{
	const AsynName = 'mqttClient';
    /**
     * @var HttpClient
     */
    protected $mqtt;

    public function __construct($config){
        parent::__construct($config);
        $options = array_intersect_key($this->config->get("mqtt"),[
            "address" => "127.0.0.1",
            "port" => "1883",
            "username" => "",
            "password" => "",
            "clean" => true,
            "qos" => "0",
            "keepalive" => "10",
            "timeout" => "60",
            "retain" => "0"
        ]);
        $this->mqtt = new Mqtt($options);
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