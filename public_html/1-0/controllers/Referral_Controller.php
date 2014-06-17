<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Referral_Controller extends _Controller
{
    public function add_referral( $params = array() )
    {
        $this->load('Referral');

        // Validations
        $input_validations = array(
            'user_id' => array(
                'label' => 'User ID'
            , 'rules' => array(
                    'is_set' => NULL
                , 'is_int' => NULL
                )
            ),
            'new_member_id' => array(
                'label' => 'User ID'
            , 'rules' => array(
                    'is_set' => NULL
                , 'is_int' => NULL
                )
            )
        );
        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $data = $this->Referral->addReferral($params);

        $response = array('data' => $data);

        return $response;
    }
    public function get_referrals( $params = array() )
    {
        $this->load('Referral');

        // Validations
        $input_validations = array(
            'user_id' => array(
                'label' => 'User ID'
            , 'rules' => array(
                    'is_set' => NULL
                , 'is_int' => NULL
                )
            )
        );
        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $data = $this->Referral->getReferrals($params);

        $response = array('data' => $data);

        return $response;
    }
}


?> 