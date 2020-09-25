<?php
namespace app\Tasks;

use Server\CoreBase\Task;
use Waljqiang\Signature\Signature;
use Carbon\Carbon;

class CheckTask extends Task{

	public function checkLicense(){
		if(env('SELF_BUILD')){
            if(LICENSE === false){
	            logger("You are not have license","ERROR");
	            $this->stop();
			}

	        $data = json_decode(Signature::decrypto(LICENSE));

	        if(!$data){
	        	$this->stop();
	        }
	        
	        $host = env('APP_HOST');
	        $domain = json_decode($data->domain,true);

	        if(!$domain || !in_array($host,$domain)){
	        	$this->stop();
	        }

	        if(Carbon::now()->timestamp > $data->expireIn){
	        	$this->stop();
	        }
	    }
	}

	public function stop(){
		$server_name = $this->config['name'] ?? 'SWD';
	    $master_pid = exec("ps -ef | grep $server_name-Master | grep -v 'grep ' | awk '{print $2}'");
	    $master_pid && posix_kill($master_pid, SIGTERM);
	}
}