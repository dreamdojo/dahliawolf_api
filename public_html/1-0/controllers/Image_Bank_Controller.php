<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Image_Bank_Controller extends _Controller{


    public function get_feed( $params = array() )
    {

        $image_bank = new Image_Bank();


        $image_feed = $image_bank->getFeed($params);

        return $image_feed;

    }




}


?> 