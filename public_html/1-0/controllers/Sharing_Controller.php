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


    public function add_share($request_data = array())
    {
        $this->load('Posting_Share');

        $share = new Posting_Share();
        $data = $share->addShare($request_data);

        return static::wrap_result( ($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }


    protected function getModelInstance($type='posting')
    {

    }


    public function get_post_shares($request_data = array())
    {
        $this->load('Posting_Share');

        $share = new Posting_Share();
        $data = $share->getShares($request_data);

        return static::wrap_result(($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }


    public function get_total($request_data = array())
    {
        $this->load('Posting_Share');

        /** @var  Posting_Share $share */
        $share = new Posting_Share();
        $data = $share->getTotalShares($request_data);

        return static::wrap_result(($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }

    public function delete($request_data = array())
    {
        $this->load('Posting_Share');

        $share = new Posting_Share();
        $data = $share->deleteShare($request_data);

        return static::wrap_result(($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }


    public function get_sharing_counts($request_data = array())
    {
        $this->load('Posting_Share');

        $share = new Posting_Share();
        $data = $share->getSharesCount($request_data);

        return static::wrap_result(($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }




}

?>