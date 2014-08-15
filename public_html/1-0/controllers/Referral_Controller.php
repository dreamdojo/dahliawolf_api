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
        $storeCredit = new Store_Credit();
        $commission = new Commission();

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
        $storeCredit->add_user_credit($params['new_member_id'], 10);

        $referralAccount = $this->Referral->getTotalReferrals($params['user_id']);
        $com_amount = 0;
        if($referralAccount <= 5) {
            $com_amount = 10;
            $commission->add_user_commission($params['user_id'], $com_amount);
        }

        $response = array('data' => $data);

        return $response;
    }

    public function playground($params = Array()) {
        $this->load('Referral');
        $com_amount = $this->Referral->getTotalReferrals($params['user_id']);
        echo $com_amount;
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