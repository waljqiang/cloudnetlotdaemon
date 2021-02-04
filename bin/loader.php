<?php
use \Dotenv\Dotenv;
use Waljqiang\Signature\Signature;
use Carbon\Carbon;

define('DEBUG',\Monolog\Logger::DEBUG);
define('INFO',\Monolog\Logger::INFO);
define('NOTICE',\Monolog\Logger::NOTICE);
define('WARNING',\Monolog\Logger::WARNING);
define('ERROR',\Monolog\Logger::ERROR);
define('CRITICAL',\Monolog\Logger::CRITICAL);
define('ALERT',\Monolog\Logger::ALERT);
define('EMERGENCY',\Monolog\Logger::EMERGENCY);
define('LICENSE',@file_get_contents(MYROOT . "/license.txt"));

//loader .env
$dotenv = Dotenv::create(MYROOT);
$dotenv->load();

//license
if(env('SELF_BUILD')){
	if(LICENSE === false){
        echo "\033[38;5;1m You are not have license \n \033[0m";
        exit;
	}

    $data = json_decode(Signature::decrypto(LICENSE));

    if(!$data){
        echo "\033[38;5;1m The license is invalid \n \033[0m";
        exit;
    }

    $host = env('APP_HOST');
    $domain = json_decode($data->domain,true);

    if(!$domain || !in_array($host,$domain)){
        echo "\033[38;5;1m The license is not support $host \n \033[0m";
        exit;
    }

    if(Carbon::now()->timestamp > $data->expireIn){
        echo "\033[38;5;1m The license is expired \n \033[0m";
        exit;
    }
}

require_once APP_DIR . '/Helpers/Common.php';