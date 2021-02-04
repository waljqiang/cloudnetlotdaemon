<?php

namespace app;

use Server\CoreBase\HttpInput;
use Server\CoreBase\Loader;
use Server\SwooleDistributedServer;
use Server\Components\Process\ProcessManager;
use Server\Asyn\TcpClient\TcpClientPool;
use app\Utils\GuzzleHttpClientPool;
use app\Utils\YunlotPool;
use app\Utils\MqttClientPool;
use app\Utils\HashbindPool;
use app\Utils\HashprtidsPool;
use app\Utils\HashcltidsPool;
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-9-19
 * Time: 下午2:36
 */
class AppServer extends SwooleDistributedServer
{
    /**
     * 可以在这里自定义Loader，但必须是ILoader接口
     * AppServer constructor.
     */
    public function __construct()
    {
        $this->setLoader(new Loader());
        parent::__construct();
    }

    /**
     * 开服初始化(支持协程)
     * @return mixed
     */
    public function onOpenServiceInitialization()
    {
        parent::onOpenServiceInitialization();
    }

    /**
     * 这里可以进行额外的异步连接池，比如另一组redis/mysql连接
     * @param $workerId
     * @return void
     * @throws \Server\CoreBase\SwooleException
     * @throws \Exception
     */
    public function initAsynPools($workerId)
    {
        parent::initAsynPools($workerId);
        $this->addAsynPool('TCP_CLIENT',new TcpClientPool($this->config,'test',"192.168.33.11:9091"))
;
        //IP地址定位
        $this->addAsynPool('GetIPAddress',new GuzzleHttpClientPool($this->config));
        //云平台设备终端协议
        $this->addAsynPool('yunlotPool',new YunlotPool($this->config));
        //mqtt客户端
        $this->addAsynPool("mqttClientPool",new MqttClientPool($this->config));
        $this->addAsynPool("bindPool",new HashbindPool($this->config));
        //产品ID解析异步客户端
        $this->addAsynPool("prtidPool",new HashprtidsPool($this->config));
        //客户端ID解析异步客户端
        $this->addAsynPool("cltidPool",new HashcltidsPool($this->config));
    }

    /**
     * 用户进程
     * @throws \Exception
     */
    public function startProcess()
    {
        parent::startProcess();
        $userProcesses = $this->config->get("public.process.defined");
        if(!empty($userProcesses)){
            foreach ($userProcesses as $userProcess => $num) {
                if($num > 1){
                    for($i = 1;$i <= $num;$i++){
                        ProcessManager::getInstance()->addProcess($userProcess,$userProcess::NAME . $i);
                    }
                }else{
                    ProcessManager::getInstance()->addProcess($userProcess,$userProcess::NAME);
                }
            }
        }
    }

    /**
     * 可以在这验证WebSocket连接,return true代表可以握手，false代表拒绝
     * @param HttpInput $httpInput
     * @return bool
     */
    public function onWebSocketHandCheck(HttpInput $httpInput)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCloseMethodName()
    {
        return 'onClose';
    }

    /**
     * @return string
     */
    public function getEventControllerName()
    {
        return 'AppController';
    }

    /**
     * @return string
     */
    public function getConnectMethodName()
    {
        return 'onConnect';
    }

}
