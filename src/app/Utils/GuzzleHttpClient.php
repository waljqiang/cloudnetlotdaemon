<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-9-2
 * Time: 下午1:44
 */
namespace app\Utils;

use Server\CoreBase\SwooleException;
use Server\Memory\Pool;
use GuzzleHttp\Client;

class GuzzleHttpClient{
    protected $guzzleClient;

    public function __construct(){
        $this->guzzleClient = new Client();
    }

    public function __call($method,$args){
    	return call_user_func_array([$this->guzzleClient,$method],$args);
    }

}