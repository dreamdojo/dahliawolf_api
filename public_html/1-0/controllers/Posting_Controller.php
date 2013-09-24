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


    public function get_all($request_data = array())
    {
        $this->load('Posting');

        $message = new Posting();
        $response = $message->getAll($request_data);

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

        $message = new Posting();
        $response = $message->promotePost($request_data);

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




}

?>