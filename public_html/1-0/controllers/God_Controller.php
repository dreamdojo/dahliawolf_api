<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class God_Controller extends _Controller
{
    public function get_data( $params = array() )
    {
        $god = new God();

        $data = $god->getData($params);

        $response = array('data' => $data);

        return $response;
    }
    public function get_associate_data($params = array()) {
        $god = new God();

        $data = $god->getAssociateData($params['username']);

        $response = array('data' => $data);

        return $response;
    }
    public function get_last_logins($params = Array()) {
        $god = new God();

        $data = $god->getLastLoginByDays($params);

        $response = array('data' => $data);

        return $response;
    }

    public function get_all_emails($params = Array()) {
        $god = new God();

        $data = $god->exportAllEmails();

        $response = array('data' => $data);

        return $response;
    }
}


?> 