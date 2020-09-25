<?php
namespace app\Utils;

use Server\Asyn\AsynPool;
use Server\CoreBase\SwooleException;
use Waljqiang\Yunlot\Yunlot;

class YunlotPool extends AsynPool{
	const AsynName = "yunlot";
    /**
     * @var HttpClient
     */
    public $yunlot;

    public function __construct($config){
        parent::__construct($config);
        $this->yunlot = new Yunlot(["protocol" => $this->config->get("yunlot.protocol","v1.0"),"encodetype" => $this->config->get("yunlot.encodetype","1"),"token" => $this->config->get("yunlot.token","cloudnetlot"),"key" => $this->config->get("yunlot.key","abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG")]);
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
        $this->yunlot = null;
        return $migrate;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function __call($method,$args){
    	return call_user_func_array([$this->yunlot,$method],$args);
    }
}