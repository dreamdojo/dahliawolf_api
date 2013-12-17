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

        $message = new Posting();
        $response = $message->getPostDetails($request_data);

        return  $response;
    }

    public function get_all($request_data = array())
    {
        $this->load('Posting');

        $posting = new Posting();
        $response = $posting->getAll($request_data);

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

        $posting = new Posting_Comment();
        $response = $posting->create($request_data);

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


    public function add_like( $request_data = array() )
    {
        $this->load('Posting_Like');

         $posting_like = new Posting_Like();
         $data = $posting_like->addLike($request_data);

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