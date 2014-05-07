<?php
/**
 * User: JDorado
 * Date: 12/3/13
 */
 
class RedisCache extends Jk_Base{

    const DEFAULT_TTL = 86400;
    const TTL_HOUR = 360000;
    const TTL_DAY = 86400;

    protected static $is_init = false;

    protected static $default_server = '127.0.0.1';
    protected static $default_server_port = 6379;
    protected static $is_valid_connection = false;
    
    protected static $logger;


    /** @var $redis Redis  **/
    protected static $redis =  null;


    protected static function init()
    {
        if(self::$is_init && self::$redis===null && self::$is_valid_connection) {
            return self::$redis;
        }

        self::$redis = new Redis();

        self::$redis->connect(self::$default_server, self::$default_server_port, 3.5, NULL, 100);
        self::$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
        self::$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        self::$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);

        self::$redis->setOption(Redis::OPT_PREFIX, 'dahliawolf_');

        if( self::$redis->ping() != false)
        {
            self::$is_valid_connection = true;
            return self::$redis;
        }

        return null;

    }

    /** @return Redis */
    protected function getRedis()
    {
        if(self::init()){
            return self::$redis;
        }

        //// should throw an exception
        return null;
    }


    public static function save($key, $val, $ttl=self::DEFAULT_TTL)
    {
        self::trace(" Redis::save $key");
        self::getRedis()->setex($key, $ttl, $val);
    }


    public static function get($key)
    {
        $content = self::getRedis()->get($key);
        self::trace(" Redis::get $key - cached? ". ($content?"TRUE":"FALSE") );
        return $content;
    }


    public static function delete($key)
    {
        self::trace(" Redis::delete $key");
        return self::getRedis()->del($key);
    }


    public static function saveRandom()
    {
        $key = self::getRedis()->randomKey();
        return $key;
    }


    public function exists($key)
    {
        return self::getRedis()->exists($key);
    }


    protected static function trace($m, $general_log=false)
    {
        $m = ( is_array($m) || is_object($m) ?  json_encode($m) : "$m");
        if(self::$logger==null) self::$logger = new Jk_Logger(APP_PATH . sprintf('logs/%s.log', 'RedisCache' ), Jk_Logger::DEBUG);

        self::$logger->LogInfo($m);
    }




}

