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


}

?>