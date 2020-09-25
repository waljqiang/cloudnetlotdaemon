<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 18-3-21
 * Time: 下午5:10
 */

namespace APP\Controllers;


use Server\CoreBase\Controller;

class AppController extends Controller{
    public function tcp_onConnect(){
    	$this->log("tpc[$this->fd] connected",INFO);
    }

    public function tcp_onClose(){
    	$this->log("tpc[$this->fd] closed",INFO);
    }
}