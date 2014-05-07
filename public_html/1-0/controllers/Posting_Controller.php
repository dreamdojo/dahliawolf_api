<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */

class Posting_Controller  extends  _Controller
{
    public function __construct()
    {
        //self::delete_post($_GET);
    }


    public function get_posting($request_data)
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->getPostDetails($request_data);

        return  $response;
    }

    public function get_all($params = array())
    {
        $loc_use_cache = false;
        $cache_key_params = self::getCacheParams($params, __FUNCTION__);
        if (!empty($params['like_day_threshold']) && (int) $params['like_day_threshold'] > 4)
        {
            $loc_use_cache = true;
            if($cached_content = self::getCachedContent($cache_key_params) )
            {
                $cached_obj = json_decode($cached_content);
                $response = $cached_obj;

                //// return else keep looking.
                if( $cached_obj && count($cached_obj->posts) > 1 )
                {
                    //self::trace("self::getCachedContent" . $cached_content);
                    return $response;
                }
            }
        }


        $this->load('Posting');

        $posting = new Posting();
        $posts = $posting->getAll($params);
        $response = array('object_id' => null, 'posts' => $posts);

        if (empty($result))
        {
            $cache_key = self::getCacheKey($cache_key_params);
            $response = array('object_id' => base64_encode($cache_key), 'posts' => $posts);

            if($loc_use_cache && !$posts['error'] && count($posts) > 0 ){
                //just cache it!!
                self::cacheContent($cache_key_params, json_encode($response),  RedisCache::TTL_HOUR);
            }
        }

        return  $response;
    }

    public function get_by_user($request_data = array())
    {
        $this->load('Posting');

        $message = new Posting();
        $response = $message->getByUser($request_data);

        return  $response;
    }

    public function delete_post($request_data = array())
    {
        $this->load('Posting');

        $message = new Posting();
        $response = $message->deletePost($request_data);

        return  $response;
    }


    public function promote($request_data)
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->promotePost($request_data);

        return  $response;
    }


    public function fave($request_data)
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->favePost($request_data);

        return  $response;
    }

    public function remove_fave($request_data)
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->removeFave($request_data);

        return  $response;
    }


    public function add_tag($request_data)
    {
        $this->load('Posting_Tag');

        $posting = new Posting_Tag();
        $response = $posting->create($request_data);

        return  $response;
    }

    public function remove_tag($request_data)
    {
        $this->load('Posting_Tag');

        $posting = new Posting_Tag();
        $response = $posting->remove($request_data);

        return  $response;
    }


    public function edit_tag($request_data)
    {
        $this->load('Posting_Tag');

        $posting = new Posting_Tag();
        $response = $posting->edit($request_data);

        return  $response;
    }


    public function get_tags($request_data)
    {
        $this->load('Posting_Tag');

        $posting = new Posting_Tag();
        $response = $posting->getPostingTags($request_data);

        return  $response;
    }



    public function add_comment($request_data)
    {
        $this->load('Posting_Comment');
        $this->load('Points');

        $posting = new Posting_Comment();
        $response = $posting->create($request_data);

        $request_data['point_id'] = 3;
        $request_data['points'] = 10;
        $this->Points->addPoints($request_data);

        return  $response;
    }

    public function remove_comment($request_data)
    {
        $this->load('Posting_Comment');

        $posting = new Posting_Comment();
        $response = $posting->remove($request_data);

        return  $response;
    }


    public function edit_comment($request_data)
    {
        $this->load('Posting_Comment');

        $posting = new Posting_Comment();
        $response = $posting->edit($request_data);

        return  $response;
    }


    public function get_comments($request_data)
    {
        $this->load('Posting_Comment');

        $posting = new Posting_Comment();
        $response = $posting->getPostingComments($request_data);

        return  $response;
    }



    public function get_user_faves($request_data)
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->getUserFaves($request_data);

        return  $response;
    }

    public function getPromote($request_data)
    {
        $this->load('Posting_Promote');

        /** @var Posting_Promote $promotes */
        $promotes = new Posting();
        $response = $promotes->getPromotes($request_data);

        return  $response;
    }


    public function get_lovers( $request_data = array() )
    {

        $posting = new Posting();
        $response = $posting->getLovers($request_data);

        return  $response;

    }

    public function get_reposters( $request_data = array() )
    {

        $posting = new Posting();
        $response = $posting->getReposters($request_data);

        return  $response;

    }


    public function add_like( $request_data = array() )
    {
        $this->load('Posting_Like');
        $this->load('Points');

         $posting_like = new Posting_Like();
         $data = $posting_like->addLike($request_data);

        $request_data['point_id'] = 3;
        $request_data['points'] = 3;
        $this->Points->addPoints($request_data);

         return static::wrap_result(($posting_like->hasError()? false:true), $data, 200, $posting_like->getErrors() );
    }


    public function delete_like( $request_data = array() )
    {
        $this->load('Posting_Like');

         $message = new Posting_Like();
         $data = $message->deleteLike($request_data);

         return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
    }



    public function get_bank_images($request_data = array())
    {
        $this->load('Posting');

        $posting = new Posting();
        $data = $posting->getPostingBankImages($request_data);

        return static::wrap_result(($posting->hasError()? false:true), $data, 200, $posting->getErrors() );
    }


    public function repost($params = array() )
    {
        $this->load('Points');
        $posting_model = new Posting();
        $posting_entity=  $posting_model->getPostingEntity($params);
        $params['og_id'] = $posting_entity['user_id'];

        if($posting_entity)
        {
            $repost = new Posting_Repost();
            $repost_id = $repost->addRepost($params);

            $request_data['point_id'] = 3;
            $request_data['points'] = 5;
            $request_data['user_id'] = $params['og_id'];
            $this->Points->addPoints($request_data);

            return $repost_id;
        }

        return array('error' => "failed to load posting with posting_id: {$params['posting_id']}");
    }

    public function delete_repost($params = array() )
    {
        $posting_model = new Posting();


        $repost = new Posting_Repost();
        $repost_id = $repost->deleteRepost($params);
        return $repost_id;


        return array('error' => "failed to load posting with posting_id: {$params['posting_id']}");
    }


    public function post_image( $params = array() )
    {
       $image_params = array
       (
           'repo_image_id'      => $_REQUEST['id'],
           'imagename'          => $_REQUEST['imageURL'],
           'source'             => $_REQUEST['baseurl'],
           'dimensionsX'        => $_REQUEST['dimensionsX'],
           'dimensionsY'        => $_REQUEST['dimensionsY'],
           'domain'             => $_REQUEST['domain'],
           'attribution_url'    => $_REQUEST['attribution_url'],
           'status'             => 'Posted'
       );


       ############# all good.. continue to post image #############
        $image = new Image();
        $new_image_id = $image->addImage($image_params);

        // Then save post
        $post_params = array(
            'image_id' => $new_image_id ,
            'created' => date('Y-m-d H:i:s'),
            'user_id' => $_REQUEST['user_id'],
            'description' => !empty($_REQUEST['description']) ? $_REQUEST['description'] : ''
        );

        $posting = new Posting();
        $new_posting_id = $posting->addPost($post_params);

        $new_post_data = array();
        $new_post_data['points_earned'] = $posting->getPointsEarned();
        $new_post_data['posting_id']    = $new_posting_id;


        return $new_post_data;
    }

    public function test_get_all_with30days($params)
    {
        $cache_key_params = self::getCacheParams($params, __FUNCTION__);

        if($cached_content = self::getCachedContent($cache_key_params) )
        {
            $cached_obj = json_decode($cached_content);
            $response = $cached_obj;

            //// return else keep looking.
            if( $cached_obj && count($cached_obj->posts) > 1 )
            {
                return $response;
            }
        }

        $posts = self::get_all( $params );
        $cache_key = self::getCacheKey($cache_key_params);

        $response = array('object_id' => base64_encode($cache_key), 'posts' => $posts['posts']);

        if( $posts['posts'] && count($posts['posts']) > 0 ){
            //just cache it!!
            self::cacheContent($cache_key_params, json_encode($response),  RedisCache::TTL_HOUR*2);
        }

        return $response;
    }

}

?>