<?php
namespace app\Models;

use app\Models\BaseModel;
use Carbon\Carbon;

class TestModel extends BaseModel{

	public function testRedis(){
		$this->redis->set(config('redis.prefix').'test','123');
		$a = $this->redis->get(config('redis.prefix').'test');

		$this->redis->setex(config('redis.prefix').'testttl',100,'456');
		$b = $this->redis->get(config('redis.prefix').'testttl');

		$res = [
			'a' => $a,
			'b' => $b
		];
		return $res;
	}

	public function testMysql(){
		$insert = $this->db->insert('cloudnetlot_users')
			->set('name','test')
			->set('create_time',Carbon::now()->timestamp)
			->query()
			->getResult();

		$update = $this->db->update('cloudnetlot_users')
			->set('name','aaaa')
			->where('id',$insert['insert_id'])
			->query()
			->getResult();
		
		$select = $this->db->select('*')
			->from('cloudnetlot_users')
			->query()
			->getResult();

		$delete = $this->db->delete()
			->from('cloudnetlot_users')
			->where('id',$insert['insert_id'])
			->query()
			->getResult();
		//如果匿名函数中抛出异常则事务回滚，正常则事务自动提交
		$trans = [];
		$this->db->begin(function() use (&$trans){
			$trans[] = $this->db->select("*")->from("cloudnetlot_users")->query()->getResult();
	        $trans[] = $this->db->select("*")->from("cloudnetlot_users")->query()->getResult();
		});
		//同步mysq
		//get_class(get_instance()->getMysql());
		$res = [
			'insert' => $insert,
			'update' => $update,
			'select' => $select,
			'delete' => $delete,
			'trans' => $trans
		];
		unset($trans);
		return $res;
	}
}