<?php
namespace Waljqiang\Mqtt;

use sskaje\mqtt\MQTT as MqttServer;
use sskaje\mqtt\Debug;
use sskaje\mqtt\Utility;

/* phpMQTT */
class Mqtt extends MqttServer{

    public function __construct($server){
        parent::__construct($server);
        # Set Socket Context
        $context = stream_context_create();
        $this->socket->setContext($context);
    }

    public function setDebug($status,$level = Debug::NOTICE){
        $status ? Debug::Enable() : Debug::Disable();
        Debug::SetLogPriority($level);
    }

    public function setClientID($clientid){
        # Check Client ID
        Utility::CheckClientID($clientid);
        $this->clientid = $clientid;
    }

    /**
     * Invoke Functions in Message Handler
     *
     * @param string $name
     * @param array  $params
     * @return bool
     */
    protected function call_handler($name, array $params=array()){
        if ($this->handler === null) {
            return false;
        }

        $name = 'on' . ucwords($name);

        if (!is_callable(array($this->handler, $name))) {
            Debug::Log(Debug::ERR, "call_handler function {$name} NOT CALLABLE");
            return false;
        }

        call_user_func_array(array($this->handler, $name), $params);
        return true;
    }

}
