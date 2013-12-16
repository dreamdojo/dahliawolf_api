<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Image_Bank_Controller extends _Controller
{
    public function get_feed( $params = array() )
    {
        $random_seed = rand(1,5);
        $params['random_seed'] = $random_seed;

        $cache_key_params = self::getCacheParams($params, __FUNCTION__);

        if($cached_content = self::getCachedContent($cache_key_params) )
        {
            $cached_images_obj = json_decode($cached_content);

            //$cache_key = base64_encode(self::getCacheKey($cache_key_params));
            //self::trace("self::getCachedContent" . $cached_content);

            $response = $cached_images_obj;

            //// return else keep looking.
            if( $cached_images_obj && count($cached_images_obj->images) > 1 )
            {
                //self::trace("self::getCachedContent" . $cached_content);
                return $response;
            }

        }

        //// not cached.. force cache..
        //self::setUseCache(true);


        $image_bank = new Image_Bank();

        $image_feed = $image_bank->getFeedByIds($params);
        $cache_key = self::getCacheKey($cache_key_params);

        $response = array('object_id' => base64_encode($cache_key), 'images' => $image_feed);

        if(!$image_feed['error'] && count($image_feed) > 0 ){
            //just cache it!!
            self::cacheContent($cache_key_params, json_encode($response),  RedisCache::TTL_HOUR);

        }

        return $response;
    }


    public function test_feed($params)
    {
        $image_bank = new Image_Bank();
        $image_feed = $image_bank->getFeedByIds($params);

        $random_seed = rand(1,5);
        $params['random_seed'] = $random_seed;


        $cache_key_params = self::getCacheParams($params, __FUNCTION__);
        $cache_key = self::getCacheKey($cache_key_params);

        $response = array('object_id' => base64_encode($cache_key), 'images' => $image_feed);

        //just cache it!!
        //self::cacheContent($cache_key_params, json_encode($response),  RedisCache::TTL_HOUR);

        return $response;
    }

    public function post_image( $params = array() )
    {

        ############# check for user posting limist #############
        $posting = new Posting();

        $bank_images_params = array(
            'user_id'       => $params['user_id'],
            'repo_image_id' => $params['repo_image_id'],
            'limit_per_day' => 1
        );

        $user_bank_images = $posting->getPostingBankImages($bank_images_params);


        self::trace("user_bank_images count: " . count($user_bank_images) );

        if($user_bank_images && count($user_bank_images) >= 50 )
        {
            //sorry pal no mo images fo u!!!
            $response  = array(
                                'error' => "Sorry due to high demand we have temporally limited the number of images you can post from the D\W Image Bank to 5 per day",
                                'data' => null,
                                'posting_id' => null
            );

            self::trace("user_bank_images: {$response['error']}" );
            ///// cant continue posting!!
            return $response;
        }


        ############# check for valid image bank id #############
        $image_bank = new Image_Bank();
        $repo_image_data = $image_bank->getBankImage($params);

        if(!$repo_image_data)
        {
            return array('error' => 'image id is not valid');
        }


        ############# valid image bank id continue to post image #############
        $image_params = array
        (
            'repo_image_id'     => $repo_image_data['id'],
            'imagename'         => $repo_image_data['imageURL'],
            'source'            => $repo_image_data['baseurl'],
            'dimensionsX'       => $repo_image_data['dimensionsX'],
            'dimensionsY'       => $repo_image_data['dimensionsY'],
            'domain'            => $repo_image_data['domain'],
            'attribution_url'   => $repo_image_data['attribution_url'],
            'status'            => 'Posted'
        );


        ############# user has not reached limit.. now check for dupes #############
        $image = new Image();
        $posted_image_data = $image->getImageByRepoId($image_params);


        self::trace("does image already exist with params?: " . var_export($image_params, true) . "\nposted_image_id: " . var_export($posted_image_data, true) );

        if( $posted_image_data && $posted_image_data['repo_image_id'])
        {
            $cache_key = base64_decode($params['object_id']);
            self::flushCacheObject($cache_key);

            $image_bank = new Image_Bank();
            $data = $image_bank->setPostedStatus($repo_image_data);

            self::trace("ERROR: O.ops This Image has already been posted by another user" );
            return array('error' => 'OOps This Image has already been posted by another user.');

        }


        ############# all good.. continue to post image #############
        $new_image_id = $image->addImage($image_params);
        self::trace("adding posting image.... \nnew_image_id: " . var_export($new_image_id, true) );

        $params['new_image_id'] = $new_image_id;
        $params['image_id'] = $new_image_id;
        $params['deleted'] = null;

        self::trace("adding posting from image bank...." );
        $new_posting_id = $posting->addPostingFromBankImage($params);

        if($new_posting_id)
        {
            $new_post_data = array();
            $new_post_data['points_earned'] = $posting->getPointsEarned();
            $new_post_data['posting_id']    = $new_posting_id;
            $new_post_data['new_image_id']  = $new_image_id;
            $new_post_data['new_image_url'] = $repo_image_data['baseurl'] . $repo_image_data['imageURL'];

            self::trace("success adding new post: " . var_export($new_post_data, true) );

            ///flush current feed block
            $cache_key = base64_decode($params['object_id']);
            self::flushCacheObject($cache_key);

            ########### updating repo image.. mark as posted
            self::trace("success adding new post: " . var_export($new_post_data, true) );


            $image_bank = new Image_Bank();
            $data = $image_bank->setPostedStatus($repo_image_data);

            //finish
            return $new_post_data;
        }


        ////
        self::trace("could not save new posting from bank image...." );
        return null;

    }


}


?> 