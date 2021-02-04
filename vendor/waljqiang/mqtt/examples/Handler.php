<?php
use Waljqiang\Mqtt\MessageHandler;
use Waljqiang\Mqtt\Mqtt;

class Handler extends MessageHandler{

    public function onPuback(Mqtt $mqtt, $puback_object){
        $msgid = $puback_object->getMsgID();
        echo "======== puback: Remove from queue msgid={$msgid}\n";
        unset($this->waitQueue[$msgid]);
    }
}

