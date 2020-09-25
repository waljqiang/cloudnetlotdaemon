# swooledistribute
cloudnetlotdaemon 

#命令
进入到bin目录，可以执行list查看命令
php start_swoole_server.php list

* 启动

    调试模式
    php start_swoole_server.php start
    
    守护进程模式
    php start_swoole_server.php start -d

    调试
    php start_swoole_server.php start --debug

    附加过滤器
    php start_swoole_server.php start --debug --f abc

* 重启
    php start_swoole_server.php restart

    php start_swoole_server.php reload

* 停止
    php start_swoole_server.php stop
    php start_swoole_server.php kill

* 单元测试
    php start_swoole_server.php test
    php start_swoole_server.php test xxx

* 远程断点调试
    php start_swoole_server.php -xdebug

* 代码覆盖率收集
    php start_swoole_server.php -coverage

# 开发注意
* 不要使用sleep、exit、die等语句，sleep请使用sleepCoroutine代替

#方法

* get_instance(),获取SwooleDistributedServer实例，可以在任何地方使用

SwooleDistributedServer其他方法
* getMysql 获取同步mysql
* getRedis 获取同步redis
* sendToAllWorks 发送给所有的进程，$callStaticFuc为静态方法,会在每个进程都执行
* sendToAllAsynWorks 发送给所有的异步进程，$callStaticFuc为静态方法,会在每个进程都执行
* sendToRandomWorker 发送给随机进程
* sendToOneWorker 发送给指定进程
* isReload 是否是重载
* sendToAll 广播
* sendToUid 向uid发送消息
* pubToUid 向uid发布消息
* sendToUids 批量发送消息
* getSubMembersCountCoroutine 获取Topic的数量
* getSubMembersCoroutine 获取Topic的Member
* getUidTopicsCoroutine 获取uid的所有订阅
* addSub 添加订阅
* removeSub 移除订阅
* pub 发布订阅
* addAsynPool 添加异步连接池
* getAsynPool 获取连接池
* isCluster 是否开启集群
* isConsul 是否开启Consul
* kickUid 踢用户下线
* bindUid 将fd绑定到uid,uid不能为0
* unBindUid 解绑uid，链接断开自动解绑
* coroutineUidIsOnline uid是否在线
* coroutineCountOnline 获取在线人数
* coroutineGetAllUids 获取所有在线uid
* stopTask 向task发送强制停止命令
* getServerAllTaskMessage 获取服务器上正在运行的Task
* getBindIp 获取本机ip
* getUidInfo 获取uid信息

#配置
获取配置信息
config($key,$default='');
get_instance()->config->get($key,$default='');

#redis
* 在Controller,Model,Task共同的基类CoreBase中默认获取了redisPool
    
    ```
    public function initialization(&$context)
    {
        $this->setContext($context);
        $this->redis = $this->loader->redis("redisPool");
        $this->db = $this->loader->mysql("mysqlPool",$this);
    }
    ```
* 同步redis，通过以下两种方法均可以获取
```
    $redisSync = $this->redis_pool->getSync();
    get_instance()->getRedis();
```
* 异步redis支持的方法可以查看 Server\Asyn\Redis\RedisAsynPool类

#控制器
* 继承app\Controllers\Base.php
* initialization();初始化方法，不要在__construct()进行初始化。
* defaultMethod()；控制器方法不存在时的默认方法，可重写该方法
* onExceptionHandle();当控制中存在异常时，进入此方法,可重写该方法
* 在Controller的基类CoreBase中默认获取了redisPool、mysqlPool的异步客户端
* 
```
    public function initialization(&$context)
    {
        $this->setContext($context);
        $this->redis = $this->loader->redis("redisPool");
        $this->db = $this->loader->mysql("mysqlPool",$this);
    }
    ```

#Model
* 在Model的基类CoreBase中默认获取了redisPool、mysqlPool的异步客户端

```
    public function initialization(&$context)
    {
        $this->setContext($context);
        $this->redis = $this->loader->redis("redisPool");
        $this->db = $this->loader->mysql("mysqlPool",$this);
    }
    ```

* db操作方法请查看Server\Asyn\Mysql\Miner类，异步结果集处理请查看Server\Asyn\Mysql\MysqlSyncHelp类

* 查询

```
    $value = $this->db->Select('*')
            ->from('MysqlTest')
            ->where('townid', 10000)
            ->query()
            >getResult();
```

* 更新

```
    $value = $this->db->update('MysqlTest')
            ->set("score","score+1",false)
            ->where('townid', 10000)
            ->query()
            >getResult();
```

* 插入
```
    $insert = $this->db->insert('cloudnetlot_users')
            ->set('name','test')
            ->set('create_time',Carbon::now()->timestamp)
            ->query()
            ->getResult();
```

* 删除
```
    $value = $this->db->delete()
            ->from('MysqlTest')
            ->where('townid', 10000)
            ->query()
            >getResult();
```

* insertInto,updateInto,replaceInto 批量操作
```
    $value = $this->db->insertInto('account')
            ->intoColumns(['uid', 'static'])
            ->intoValues([[36, 0], [37, 0]])
            ->query()
            >getResult();
```

* 超时

```
    $result = $this->db->select('*')->from('account')->limit(1)->query(null,function (MySqlCoroutine $mySqlCoroutine){
            $mySqlCoroutine->setTimeout(1000);
            $mySqlCoroutine->noException("test");
            $mySqlCoroutine->dump();
        });
    echo $result->num_rows();
```

* 其他方法

```
    getStatement(true);//获取sql
    result_array();// 直接返回result
    row_array($index); 返回某一个
    row(); 返回第一个
    num_rows(); 返回数量
    insert_id(); 返回insert_id
    dump(); 打印执行的sql命令
```

* 事务
begin开启一个事务，在回调中执行事务，如果抛出异常则自动回滚，如果正常则自动提交。

```
    $this->db->begin(function (){
       $result = $this->db->select("*")->from("account")->query();
       var_dump($result['client_id']);
       $result = $this->db->select("*")->from("account")->query();
       var_dump($result['client_id']);
    });
```

* 加载mysq异步客户端
    $this->loader->mysql("mysqlPool",$this);
* 同步mysql,同步mysql返回的是一个PDO连接，可以使用pdo开头的方法
```
    this->mysql_pool->getSync();
    get_instance()->getMysql();
```

#task

* 不能使用异步客户端,但是model中的redis及mysql除外，因为这两个会自动转换

* 不能使用进程间通信

#MQTT
* 实现了用户自定义mqtt客户端进程，用来接收指定topic的订阅消息，topic可以通过public.topics.device_upinfo配置

#日志

* 控制器、模型的基类Server\CoreBase\CoreBase中加载，只需如下使用即可
$this->log('123',DEBUG);
* 提供了公共方法logger($message,$level);
#Task
* 为同步任务，不能调用异步客户端，但框架对redis和mysql客户端做了处理，可以自动切换在worker中使用异步在task中使用同步，这是可以调用的

#加载器

* 加载一个model：$this->loader->model('TestModel',$this);
* 加载一个task:$this->loader->task('TestTask',$this);

#TCP
* send();
* sendToUid();
* sendToUids();
* sendToAll();
* bindUid();
* kickUid();
* addSub();
* removeSub();
* sendPub();
* getFdInfo();

# 改造

* 使用vlucas/phpdotenv将配置文件按环境区分,.env中可设置配置如下:

| 字段| 描述| 默认值 |
|:---:|:---:|:---:|
|AMQP_HOST|AMQP地址|localhost|
|AMQP_PORT|AMQP端口|5672|
|AMQP_USER|AMQP用户|guest|
|AMQP_PARSSWORD|AMQP密码|guest|
|AMQP_VHOST|AMQP虚拟目录|/|
|BACKSTAGE_ENABLE|是否启用backstage|TRUE|
|BACKSTAGE_XDEBUG_ENABLE|是否启用xdebug|TRUE|
|BACKSTAGE_PORT||Web页面访问端口|
|BACKSTAGE_SOCKET|WS访问地址|0.0.0.0|
|BACKSTAGE_WEBSOCKET_PORT|WS访问端口|18083|
|BACKSTAGE_BIN_PATH|设置路径|/bin/exec/backstage|
|CATCACHE_ENABLE|是否开启高速缓存|TRUE|
|CATCACHE_AUTO_SAVE_TIME|自动存盘时间|1000|
|CATCACHE_SAVE_DIR|落地文件夹|BIN_DIR . '/cache/'|
|HTTP_CLIENT_ASYN_MAX|HTTP异步客户端最大数|10|
|TCP_CLIENT_ASYN_MAX|TCP异步客户端最大数|10|
|CONSUL_ENABLE|是否启用consul|FALSE|
|CONSUL_DATACENTER|数据中心|cloudnetlot|
|CONSUL_CLIENT_ADDR|开放给本地|127.0.0.1|
|CONSUL_LEADER|服务器名称，同种服务应该设置同样的名称，用于leader选举|cloudnetlotdaemon|
|CONSUL_NODE_NAME|node的名字，每一个都必须不一样,也可以为空自动填充主机名|''|
|CONSUL_DATA_DIR|默认放在临时文件下|/tmp/consul|
|CONSUL_START_JOIN|join地址，多个地址用,分割|127.0.0.1|
|CONSUL_BIND_NET_DEV|本地网卡设备|enp2s0|
|CONSUL_WATCHES|监控服务|''|
|CONSUL_SERVICES|发布服务|''|
|CONSUL_CLUSTER_ENABLE|是否开启集群|TRUE|
|CONSUL_CLUSTER_PORT|TCP集群端口|9999|
|CONSUL_FUSE_THRESHOLD|阈值|0.01|
|CONSUL_FUSE_CHECKTIME|检查时间|2000|
|CONSUL_FUSE_TRYTIME|尝试打开的间隔|1000|
|CONSUL_FUSE_TRYMAX|尝试多少个|3|
|ERROR_ENABLE|是否启用错误收集上报系统|TRUE|
|ERROR_HTTP_SHOW|是否显示在HTTP上|TRUE|
|ERROR_URL|访问地址|http://127.0.0.1:8091/Error|
|ERROR_REDIS_PREFIX|rediskey前缀|cloudnetlot:sd-error|
|ERROR_REDIS_TIMEOUT|REDIS过期时间|36000|
|ERROR_DINGDING_ENBALE|是否启用钉钉|FALSE|
|ERROR_DINGDING_URL|钉钉地址|https://oapi.dingtalk.com|
|ERROR_DINGDING_ROBOT|钉钉机器人||
|LOG_LEVEL|日志级别|ERROR|
|LOG_NAME|日志名|cloudnetlot|
|FILE_LOG_MAX|文件日志最大文件数|15|
|EFF_MONITOR_ENABLE|是否启用efficiency_monitor||
|MYSQL_ASYN_MAX|异步mysql客户端最大数|10|
|MYSQL_ASYN_ENABLE|是否启用异步mysql客户端|TRUE|
|MYSQL_HOST|mysql地址|127.0.0.1|
|MYSQL_PORT|mysql端口|3306|
|MYSQL_USER|mysql用户名|root|
|MYSQL_PASSWORD|mysql密码|root|
|MYSQL_DBNAME|mysql数据库名|cloudnetlot|
|MYSQL_CHARSET|mysql字符集|utf8|
|TCP_NAME|TCP地址|0.0.0.0|
|TCP_PORT|TCP端口|9091|
|HTTP_NAME|HTTP地址|0.0.0.0|
|HTTP_PORT|HTTP端口|9092|
|WS_NAME|WS地址|0.0.0.0|
|WS_PORT|WS端口|9093|
|REDIS_ASYN_ENABLE|是否启用异步redis客户端|TRUE|
|REDIS_ASYN_MAX|Redis异步客户端最大数|10|
|REDIS_HOST|redis地址|127.0.0.1|
|REDIS_PORT|redis端口|6379|
|REDIS_PASSWORD|redis认证密码|''|
|REDIS_DB|redis数据库|0|

|SERVER_NAME|服务名|cloudnetlotdaemon|
|SERVER_AUTORELOAD|是否启用代码自动更新|FALSE|
|SERVER_ALLOW_SERVER|是否允许访问server中的controller|FALSE|
|SERVER_ALLOW_MONITOR|是否允许监控流量数据|TRUE|
|SERVER_USE_TASK_NUM|最大用户任务数|500|
|SERVER_LOG_FILE|服务日志文件|LOG_DIR . '/swoole.log|
|SERVER_PID_FILE|服务pid文件|PID_DIR . '/server.pid|
|SERVER_LOG_LEVEL|服务日志级别|5|
|SERVER_REACTORS|reactor thread num|4|
|SERVER_WORKERS|服务进程数|4|
|SERVER_BACKLOG|listen backlog|128|
|SERVER_OPEN_TCP_NODELAY||1|
|SERVER_BUFFER_SIZE||1024*1024*1024|
|SERVER_DISPATCH_MODE||2|
|SERVER_TASK_WORKERS||1|
|SERVER_TASK_MAX_REQUEST||5000|
|SERVER_ENABLE_REUSE_PORT||TRUE|
|SERVER_HEART_IDLE_TIME|心跳检测时间间隔|120|
|SERVER_HEART_CHECK_INTERVAL|心跳检测频率|60|
|SERVER_MAX_CONNECTION||1000000|
|SERVER_COROUTION_TIMEOUT||5000|

#使用要求
* http请求方法使用http_前缀
* tcp请求方法使用tcp_前缀
* websocket请求方法使用ws_前缀
* models下统一拆分为业务逻辑及数据层
* 缓存操作统一用文件类实现，不能使用redis直接访问