<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */

class Posting_Controller  extends  _Controller
{
    public function __construct()
    {
        //self::add_share($_GET);
    }


    public function delete_post($request_data = array())
    {
        $this->load('Posting');

        $message = new Posting();
        $response = $message->deletePost($request_data);

        return  $response;
    }


}

?>