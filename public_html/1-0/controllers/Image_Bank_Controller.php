<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Image_Bank_Controller extends _Controller{


    public function get_feed( $params = array() )
    {
        $random_seed = rand(1,5);
        $params['random_seed'] = $random_seed;

        $cache_key_params = self::getCacheParams($params, __FUNCTION__);

        if($cached_content = self::getCachedContent($cache_key_params) )
        {
            return json_decode($cached_content);
        }

        //// not cached.. force cache..
        //self::setUseCache(true);


        $image_bank = new Image_Bank();


        $image_feed = $image_bank->getFeed($params);

        //just cache it!!
        self::cacheContent($cache_key_params, json_encode($image_feed),  RedisCache::TTL_HOUR);


        return $image_feed;

    }




}


?> 