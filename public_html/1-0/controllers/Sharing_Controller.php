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
        $model_instance_name = "{$type}_Share";
        $this->load("$model_instance_name");

        $model_instance = new $model_instance_name();
        if( !is_null($model_instance) ) return $model_instance;

        return null;
    }


    public function get_post_shares($request_data = array())
    {
        $type = $request_data['type'];

        /** @var Sharing_Abstract $model_instance */
        $model_instance = self::getModelInstance($type);

        $data = $model_instance->getShares($request_data);

        return static::wrap_result(($model_instance->hasError()? false:true), $data, 200, $model_instance->getErrors() );
    }


    public function get_total($request_data = array())
    {
        $type = $request_data['type'];

        /** @var Sharing_Abstract $model_instance */
        $model_instance = self::getModelInstance($type);

        /** @var  Posting_Share $share */
        $share = new Posting_Share();
        $data = $share->getTotalShares($request_data);

        return static::wrap_result(($share->hasError()? false:true), $data, 200, $share->getErrors() );
    }

    public function delete($request_data = array())
    {
        $type = $request_data['type'];

        /** @var Sharing_Abstract $model_instance */
        $model_instance = self::getModelInstance($type);


        $data = $model_instance->deleteShare($request_data);

        return static::wrap_result(($model_instance->hasError()? false:true), $data, 200, $model_instance->getErrors() );
    }


    public function get_sharing_counts($request_data = array())
    {
        $type = $request_data['type'];

        /** @var Sharing_Abstract $model_instance */
        $model_instance = self::getModelInstance($type);

        $data = $model_instance ->getSharesCount($request_data);

        return static::wrap_result(($model_instance->hasError()? false:true), $data, 200, $model_instance->getErrors() );
    }




}

?>