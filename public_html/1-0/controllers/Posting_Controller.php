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

         $message = new Posting_Like();
         $data = $message->addLike($request_data);

         return static::wrap_result(($message->hasError()? false:true), $data, 200, $message->getErrors() );
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