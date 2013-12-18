<?php
/**
 * User: JDorado
 * Date: 12/3/13
 */
 
class Tests_Controller extends _Controller
{

    protected static $start_time;


    public function timeout()
    {
        $timeout = new Timer();
        $start_time = $timeout->getTime();

        //run
        $timeout->run(120);

        self::addData('test_data' , $timeout->getData());

        self::addData(__FUNCTION__ . " END TIME: ",  $timeout::getTotaltime($start_time));

        return self::getData();
    }

    public function test_redis()
    {
        $redis = new RedisCache;
        $return = array();

        self::$start_time = $redis::getTime();

        $return[] = "TEST START TIME: " . $redis::getTotaltime(self::$start_time);
        $return[] = "";
        $return[] = "";

        self::saveSingleRandom($return);
        self::save1000Randoms($return);


        $end_time = $redis::getTime();
        $return[] =  "TEST RUNNING TIME: " . $redis::getTotaltime(self::$start_time);

        return $return;

    }


    protected function saveSingleRandom(&$return)
    {
        $redis = new RedisCache;
        $start_time = $redis::getTime();

        $return[] = __FUNCTION__ . " START TIME: " . $redis::getTotaltime($start_time);


        //$rand_key = $redis::saveRandom();
        $rand_key = md5(rand(0, 999999));
        //$return[] = "getting random key... ";
        //$return[] = "random key: $rand_key";

        //$return[] = "saving random key value... ";
        $to_save_random_key_val = md5(rand(0, 999999));
        $redis::save($rand_key, $to_save_random_key_val);

        $rand_key_val = $redis::get($rand_key);
        //$return[] = "random key saved value: $rand_key_val";

        $end_time = $redis::getTime();
        $return[] =  __FUNCTION__ . " end time:" . $redis::getTotaltime($start_time);

        $return[] = "";
        $return[] = "";
    }

    protected function save1000Randoms(&$return)
    {
        $redis = new RedisCache;
        $start_time = $redis::getTime();

        $return[] = __FUNCTION__ . " START TIME: " . $redis::getTotaltime($start_time);

        $test_samples = 50;

        $i = 0;

        $to_save_sinlen_key_val = self::getRandomString( 32 );


        while( $i < $test_samples )
        {
            //$rand_key = $redis::saveRandom();
            $rand_key = md5(rand(0, 999999));
            //$return[] = "getting random key... ";
            //$return[] = "random key: $rand_key";

            //$return[] = "saving random key value... ";
            //$to_save_random_key_val = md5(rand(0, 999999));
            $to_save_random_key_val = self::getRandomString( rand(16392, 32784));
            $redis::save($rand_key, $to_save_sinlen_key_val);


            $rand_key_val = $redis::get($rand_key);
            //$return[] = "random key saved value: $rand_key_val";

            $i++;
        }

        $end_time = $redis::getTime();
        $return[] = "done saving $test_samples keys";
        $return[] =  __FUNCTION__ . " end time: " . $redis::getTotaltime($start_time);

        $return[] = "";
        $return[] = "";

    }

    protected function getRandomString($len=10)
    {
        $seeds = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $i=0;
        $rand = "";
        while($len > $i)
        {
            $seed_i = rand(0, strlen($seeds));
            $rand .= $seeds{$seed_i};

            //
            $i++;
        }

        return $rand;
    }


}

