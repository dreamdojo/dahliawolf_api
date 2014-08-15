<?php
/**
 * User: JDorado
 * Date: 11/26/13
 */
 
class Promos_Controller extends _Controller
{
    public function get() {
        $model = new Promos();

        $retVal = $model->get();

        return $retVal;
    }

}

?>