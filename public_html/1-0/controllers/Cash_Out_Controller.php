<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Cash_Out_Controller extends _Controller
{
    public function create($params = array()) {
        $model = new Cash_Out();

        // Validations
        $input_validations = array(
            'user_id' => array(
                'label' => 'User ID'
            , 'rules' => array(
                    'is_set' => NULL
                , 'is_int' => NULL
                )
            ),
            'amount' => array(
                'label'=>'Amount Needed',
                'rules' => array(
                    'is_set' => NULL
                )
            )
        );
        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $data = $model->create($params);

        return $data;
    }

    public function get($params = array()) {
        $model = new Cash_Out();

        $return = $model->get($params);

        return $return;
    }
}


?> 