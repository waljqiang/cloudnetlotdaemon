<?php
namespace app\Middlewares;

use Server\Components\Middleware\HttpMiddleware;
use Waljqiang\Signature\Signature;
use Carbon\Carbon;

class LicenseHttpMiddleware extends HttpMiddleware{
	
	public function __construct(){
		parent::__construct();
	}

	public function before_handle(){
        if(env('SELF_BUILD')){	
            $this->checkLicense();
        }
	}

	public function after_handle($path){
		
	}

	private function checkLicense(){
		if(LICENSE === false){
            logger("You are not have license","ERROR");
            $this->end(config("exceptions.LICENSE_NO"),"No License");
            $this->interrupt();
		}

        $data = json_decode(Signature::decrypto(LICENSE));

        if(!$data){
            $this->end(config("exceptions.LICENSE_INVALID"),"The license is invalid");
            $this->interrupt();
        }

        $host = env('APP_HOST');
        $domain = json_decode($data->domain,true);

        if(!$domain || !in_array($host,$domain)){
            $this->end(config("exceptions.LICENSE_INVALID"),"The license is invalid");
            $this->interrupt();
        }

        if(Carbon::now()->timestamp > $data->expireIn){
            $this->end(config("exceptions.LICENSE_EXPIRE_IN"),"The license is expired");
            $this->interrupt();
        }
    }

    private function end($code,$data){
        $this->response->header("Content-Type","text/html; charset=UTF-8");
        $this->response->end(json_encode([
                "status" => $code,
                "message" => $data
            ],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

}