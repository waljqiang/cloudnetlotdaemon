<?php
namespace app\Controllers;

use Server\CoreBase\Controller;

class Base extends Controller{
	public function initialization($controllerName, $methodName){
        parent::initialization($controllerName, $controllerName);
    }

    public function defaultMethod(){
		$this->http_output->end(["status" => config("exceptions.HTTP_REQUEST_NO_EXISTS"),"message" => "Request is not exists"]);
	}

	public function onExceptionHandle(\Throwable $e, $handle = null){
		$this->http_output->end(["status" => $e->getCode(),"message" => $e->getMessage()]);
	}
}