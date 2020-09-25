<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-2-27
 * Time: 上午10:13
 */

namespace app\Utils;

use Server\Asyn\AsynPool;
use Server\CoreBase\SwooleException;
use app\Utils\GuzzleHttpClient;

class GuzzleHttpClientPool extends AsynPool{
    const AsynName = 'guzzle_http_client';
    /**
     * @var HttpClient
     */
    public $httpClient;

    public function __construct($config){
        parent::__construct($config);
        $this->httpClient = new GuzzleHttpClient();
        $this->client_max_count = $this->config->get('httpClient.asyn_max_count', 10);
    }

    /**
     * 获取同步
     * @throws SwooleException
     */
    public function getSync(){
        throw new SwooleException('暂时没有HttpClient的同步方法');
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
        $this->httpClient = null;
        return $migrate;
    }

    public function setName($name){
        $this->name = $name;
    }
}