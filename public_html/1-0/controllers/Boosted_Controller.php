<?php
/**
 * User: JDorado
 * Date: 9/26/13
 */
 
class Boosted_Controller extends _Controller
{
    public function load($request_data = array())
    {
        //$this->load('Boosted');

        /** @var Sharing_Abstract $model_instance */
        $boosted = new Boosted();

        $data = $boosted->processImage($request_data);

        return static::wrap_result( ($boosted->hasError()? false:true), $data, 200, $boosted->getErrors() );
    }
}

?>