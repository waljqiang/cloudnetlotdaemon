<?php
namespace Waljqiang\Mqtt;

use sskaje\mqtt\MessageHandler as BaseHandler;

class MessageHandler extends BaseHandler{
	public $waitQueue = [];

	public function onConnack($mqtt, $connack_object){
		
    }

    public function onDisconnect($mqtt){
    	
    }

    public function onSuback($mqtt, $suback_object){
    	
    }

    public function onUnsuback($mqtt, $unsuback_object){
    	
    }

    public function onPublish($mqtt, $public_object){
    	
    }

    public function onPuback($mqtt, $puback_object){
    	
    }

    public function onPubrec($mqtt, $pubrec_object){
    	
    }

    public function onPubrel($mqtt, $pubrel_object){
    	
    }

    public function onPubcomp($mqtt,$pubcomp_object){

    }

    public function onPingresp($mqtt,$pingresp_object){

    }

}