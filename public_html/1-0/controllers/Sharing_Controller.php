<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */

class Sharing_Controller  extends  _Controller
{
    public function __construct()
    {
        //self::add_share($_GET);
    }


    public function add_share($request_data)
    {
        $this->load('Posting_Share');

        $share = new Posting_Share();
        $data = $share->addShare($request_data);

        return static::wrap_result(true, $data);
    }



}

?>