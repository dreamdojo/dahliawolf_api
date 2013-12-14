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
            $cached_inages_obj = json_decode($cached_content);

            //$cache_key = base64_encode(self::getCacheKey($cache_key_params));

            $response = $cached_inages_obj;

            return $response;
        }

        //// not cached.. force cache..
        //self::setUseCache(true);

        $image_bank = new Image_Bank();

        $image_feed = $image_bank->getFeed($params);
        $cache_key = self::getCacheKey($cache_key_params);

        $response = array('object_id' => base64_encode($cache_key), 'images' => $image_feed);

        //just cache it!!
        self::cacheContent($cache_key_params, json_encode($response),  RedisCache::TTL_HOUR);

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


        self::trace("user_bank_images: " . var_export($user_bank_images, true) );

        if($user_bank_images && @count($bank_images_params) >= 5 )
        {
            //sorry pal no mo images fo u!!!
            $response  = array(
                                'error' => "Sorry due to high demand we have temporally limited the number of images you can post from the D\W Image Bank to 5 per day",
                                'data' => null,
                                'posting_id' => null
            );

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


        ############# all good continue to post image #############
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


        //check for dupes
        $image = new Image();
        $posted_image_data = $image->getImage($image_params);


        self::trace("does image already exist with params?: " . var_export($image_params, true) . "\nposted_image_id: " . var_export($posted_image_data, true) );

        if( $posted_image_data && $posted_image_data['repo_image_id'])
        {
            $cache_key = base64_decode($params['object_id']);
            self::flushCacheObject($cache_key);

            self::trace("ERROR: O.ops This Image has already been posted by another user" );
            return array('error' => 'O.Ops This Image has already been posted by another user.');

        }else{

            $new_image_id = $image->addImage($image_params);
            self::trace("adding posting image.... \nnew_image_id: " . var_export($new_image_id, true) );

            $params['new_image_id'] = $new_image_id;
            $params['deleted'] = null;

            self::trace("adding posting from image bank...." );
            $new_post_data = $posting->addPostingFromBankImage($params);

            if($new_post_data)
            {
                $new_post_data['new_image_id']  = $new_image_id;
                $new_post_data['new_image_url'] = $repo_image_data['baseurl'] . $repo_image_data['imageURL'];

                self::trace("success adding new post: " . var_export($new_post_data, true) );


                ///flush current feed block
                $cache_key = base64_decode($params['object_id']);
                self::flushCacheObject($cache_key);


                //finish
                return $new_post_data;
            }


            ////
            self::trace("could not save new posting from bank image...." );
            return null;

        }



    }


}


?> 