<?php
namespace app\Utils;

use Server\Asyn\AsynPool;
use Server\CoreBase\SwooleException;
use app\Utils\Hashids;

class HashprtidsPool extends AsynPool{
	const AsynName = 'Hashprtids';
    /**
     * @var HttpClient
     */
    public $hashprtids;

    public function __construct($config){
        parent::__construct($config);
       $this->hashprtids = new Hashids($this->config->get("hashids.prt.salt"),$this->config->get("hashids.prt.length"),$this->config->get("hashids.prt.alphabet"),$this->config->get("hashids.prt.header"),$this->config->get("hashids.enable"));
        $this->client_max_count = $this->config->get('httpClient.asyn_max_count', 10);
    }

    /**
     * 获取同步
     * @throws SwooleException
     */
    public function getSync(){
        throw new SwooleException('暂时没有hashprtids的同步方法');
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
        $this->hashprtids = null;
        return $migrate;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function __call($method,$args){
    	return call_user_func_array([$this->hashprtids,$method],$args);
    }
}