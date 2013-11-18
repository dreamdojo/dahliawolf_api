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


}

?>