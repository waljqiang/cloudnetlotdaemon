##APP
APP_HOST=192.168.111.208
SELF_BUILD=TRUE
APP_LANG=zh
##SERVER
SERVER_NAME=cloudnetlotdaemon

##LOG
#node的名字，每一个都必须不一样
LOG_NAME=cloudnetlotdaemon
LOG_LEVEL=DEBUG


##PORT
#TCP
TCP_NAME=0.0.0.0
TCP_PORT=9091
#HTTP
HTTP_NAME=0.0.0.0
HTTP_PORT=9092
#WS
WS_NAME=0.0.0.0
WS_PORT=9093

##CLIENT
#HTTP_CLIENT_ASYN_MAX=10
#TCP_CLIENT_ASYN_MAX=10

##MYSQL
MYSQL_HOST=192.168.111.208
MYSQL_PORT=9094
MYSQL_USER=root
MYSQL_PASSWORD=admin@123
MYSQL_DBNAME=cloudnetlot
MYSQL_CHARSET=utf8
MYSQL_ASYN_MAX=10

##REDIS
REDIS_ASYN_MAX=10
REDIS_HOST=192.168.111.208
REDIS_PORT=9095
REDIS_PASSWORD=1f494c4e0df9b837dbcc82eebed35ca3f2ed3fc5f6428d75bb542583fda2170f
REDIS_DB=0
REDIS_PREFIX=cnl:

##MQTT
MQ_ADDRESS=192.168.111.208
MQ_PORT=9096
MQ_USERNAME=admin
MQ_PASSWORD=123456