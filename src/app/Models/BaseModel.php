<?php
namespace app\Models;

use Server\CoreBase\Model;

class BaseModel extends Model{
	public function initialization(&$context){
		parent::initialization($context);
	}

	/**
	 * 功能描述:获取数据表
	 *
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	protected function getTable($name){
		return config('mysql.prefix') . '_' . $name;
	}

	/**
	 * 功能描述:获取单条数据
	 */
	public function getInfo($where = [],$table='',$fields='*',$group=NULL,$having=NULL,$order=NULL,$limit=NULL){
		try{
	        $sql = 'SELECT '. $this->parseFields($fields) . ' FROM ' . $this->getTable($table)
	        	. $this->parseOptions($where)
	        	. $this->parseGroup($group)
	        	. $this->parseHaving($having)
	        	. $this->parseOrder($order)
	        	. $this->parseLimit($limit);
	        $res = $this->db->query($sql)->getResult();
	        return !empty($res['result']) ? $res['result'][0] : [];
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	return false;
	    }
    }

    /**
     * 功能描述:获取多条记录
     */
    public function getInfos($where = [],$table='',$fields='*',$group=NULL,$having=NULL,$order=NULL,$limit=NULL){
    	try{
	        $sql='SELECT '.$this->parseFields($fields) . ' FROM ' . $this->getTable($table)
	            . $this-> parseOptions($where)
	            . $this->parseGroup($group)
	            . $this->parseHaving($having)
	            . $this->parseOrder($order)
	            . $this->parseLimit($limit);
	        $res = $this->db->query($sql)->getResult();
	        return $res['result'];
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	return false;
	    }
    }

    /**
    * 更新记录
    * @param array $data  需要更新的字段
    * @param array $options 条件
    * @param string $table 需要指定的操作表
    * @return int 更新的记录数
    */
    public function save($data,$options = [],$table = ''){
    	try{
	        $this->beforeSave();
	        $sql = "UPDATE " . $this->getTable($table) . " SET ";
	        foreach ($data as $k => $v) {
	            $sql .= "`" . $k ."`='" . $v . "',";
	        }
	        $sql = substr($sql,0,strrpos($sql,','));
	        $sql .= $this->parseOptions($options);
	        $sql = $this->parseSql($sql);
	        $res = $this->db->query($sql);
	        $this->afterSave();
	        return isset($res['affected_rows']) && $res['affected_rows'] > 0 && $res['result'] ? true : false;
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	return false;
	    }    
    }

    /**
    * 插入单条记录
    * @param array $data 要插入的数据
    * @param boolen $replace 是否替换
    * @param string $table 需要制定的表名
    * @return int 插入记录的ID
    */
    public function add($data,$replace=false,$table=''){
    	try{
	        $this->beforeAdd();
	        $field = implode('`,`',array_keys($data));
	        $field = '`' . $field . '`';
	        $values = array_values($data);
	        $str = "'" . implode("','", $data) . "'";
	        $sql = (true===$replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->getTable($table) . '(' . $field . ')VALUES(' . $str . ')';
	        $sql = $this->parseSql($sql);
	        $res = $this->db->query($sql);
	        $this->afterAdd();
	        return isset($res['insert_id']) && $res['insert_id'] > 0 && $res['result'] ? $res['insert_id'] : false;
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	return false;
	    }
    }

    /**
    * 插入多条记录
    * @param array $datas 需要插入的数据
    * @param string $table 需要指定的操作表
    * @return int 插入的记录数
    */
    public function addAll($datas,$table='',$replace = false){
        try{
	        $this->beforeAdd();
	        $field = implode('`,`',array_keys($datas[0]));
	        $field = '`' . $field . '`';
	        $sql = (true===$replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->getTable($table) . '(' . $field . ')VALUES';
	        foreach ($datas as $data) {
	            $str = implode("','", $data);
	            $sql .= "('" . $str . "'),";
	        }
	        $sql = substr($sql,0, strrpos($sql,','));
	        $sql = $this->parseSql($sql);
	        $res = $this->db->query($sql);
	        $this->afterAdd();
	        return isset($res['affected_rows']) && $res['affected_rows'] > 0 && $res['result'] ? true : false;
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
	    	return false;
	    }
    }

    /**
    * 删除记录
    * @param array $options 条件
    * @param $table 需要指定的操作表
    * @return int 删除的记录数
    */
    public function delete($options,$table=''){
        try{
	        $this->beforeDelete();
	        $sql = 'DELETE FROM ' . $this->getTable($table) . $this->parseOptions($options);
	        $res = $this->db->query($sql);
	        $this->afterDelete();
	        return isset($res['affected_rows']) && $res['affected_rows'] > 0 && $res['result'] ? true : false;
	    }catch(\Exception $e){
	    	logger("sql exception : " . $e->getMessage(),ERROR);
    		return false;
	    }
    }

    public function beforeSave(){
    	
    }

    public function afterSave(){

    }

    public function beforeAdd(){

    }

    public function afterAdd(){

    }

    public function beforeDelete(){

    }

    public function afterDelete(){

    }

    /*
    * 解析字段
    */
    protected  function parseFields($fields){
        if(is_array($fields)){
            array_walk($fields,[$this,'addSpecialChar']);
            $fieldsStr=implode(',',$fields);
        }elseif(is_string($fields)&&!empty($fields)){
            if(strpos($fields,'`')===false){
                $fields=explode(',',$fields);
                array_walk($fields,[$this,'addSpecialChar']);
                $fieldsStr=implode(',',$fields);
            }else{
                $fieldsStr=$fields;
            }
        }else{
            $fieldsStr='*';
        }
        return $fieldsStr;
    }

    /**
     * 数组条件转换为mysql条件
     */
    protected function parseOptions($options){
        $res = ' WHERE ';
        if(empty($options)){
            return ' WHERE 1';
        }
        if(is_array($options)){
            foreach ($options as $k => $v) {
                if(is_array($v)){
                    $res .= $this->arraySql($k,$v) . ' AND ';
                }else{
                    $res .= '`' . $k . '` = "' . $v . '" AND ';
                }
            }
            $res = substr($res,0,strrpos($res,'AND'));
        }else{
            $res .= $options;
        }
        return $res;
    }

    /**
     * 解析group by
     * @param unknown $group
     * @return string
     */
    protected  function parseGroup($group){
        $groupStr='';
        if(is_array($group)){
            $groupStr .= ' GROUP BY ' . implode(',',$group);
        }elseif(is_string($group)&&!empty($group)){
            $groupStr .= ' GROUP BY ' . $group;
        }
        return empty($groupStr) ? '' : $groupStr;
    }
    /**
     * 对分组结果通过Having子句进行二次删选
     * @param unknown $having
     * @return string
     */
    protected  function parseHaving($having){
        $havingStr = '';
        if(is_string($having)&&!empty($having)){
            $havingStr .= ' HAVING ' . $having;
        }
        return $havingStr;
    }
    /**
     * 解析Order by
     * @param unknown $order
     * @return string
     */
    protected  function parseOrder($order){
        $orderStr = '';
        if(is_array($order)){
            $str = '';
           foreach($order as $key=>$val){
               $str .= "$key $val ,";
           }
           $newstr = substr($str,0,strlen($str)-1);
            $orderStr .= ' ORDER BY ' . $newstr;
        }elseif(is_string($order)&&!empty($order)){
            $orderStr .= ' ORDER BY ' . $order;
        }
        return $orderStr;
    }
    /**
     * 解析限制显示条数limit
     * limit 3
     * limit 0,3
     * @param unknown $limit
     * @return unknown
     */
    protected  function parseLimit($limit){
        $limitStr = '';
        if(is_array($limit)){
            if(count($limit)>1){
                $limitStr .= ' LIMIT ' . $limit[0] . ',' . $limit[1];
            }else{
                $limitStr .= ' LIMIT ' . $limit[0];
            }
        }elseif(is_string($limit)&&!empty($limit)){
            $limitStr .= ' LIMIT ' . $limit;
        }
        return $limitStr;
    }

    /**
     * 通过反引号引用字段，
     * @param unknown $value
     * @return string
     */
    protected  function addSpecialChar(&$value){
        if($value==='*'||strpos($value,'.')!==false||strpos($value,'`')!==false){
            //不用做处理
        }elseif(strpos($value,'`')===false){
            $value='`'.trim($value).'`';
        }
        return $value;
    }

    protected function arraySql($key,$data){
        $res = '';
        $type = strtoupper($data[0]);
        switch ($type){
            case 'IN' :
                $res .= '`' . $key . '` IN ("' . implode('","', $data[1]) . '")';
                break;
            case 'NOTIN' :
                $res .= '`' . $key . '` NOT IN ("' . implode('","', $data[1]) . '")';
                break;
            case 'EQ' :
                $res .= '`' . $key . '` = ' . '"' . $data[1] . '"';
                break;
            case 'NEQ' :
                $res .= '`' . $key . '` != "' . $data[1] . '"';
                break;
            case 'GT' :
                $res = '`' . $key . '` > "' . $data[1] . '"';
                break;
            case 'EGT' :
                $res = '`' . $key . '` >= "' . $data[1] . '"';
                break;
            case 'LT' :
                $res = '`' . $key . '` < "' . $data[1] . '"';
                break;
            case 'ELT' :
                $res = '`' . $key . '` <= "' . $data[1] .'"';
                break;
            case 'LIKE' :
                $res = '`' . $key . '` LIKE "%' . $data[1] . '%"';
                break;
            case 'BETWEEN' :
                $res .= '`' . $key . '` BETWEEN "' . implode('" AND "', $data[1]) . '"';
                break;
            default:
                break;
        }
        return $res;
    }

    protected function parseSql($sql){
        return str_replace('\\', '\\\\', $sql);
    }
}