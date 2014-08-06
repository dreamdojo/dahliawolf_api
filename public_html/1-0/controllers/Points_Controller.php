<?php
    class Points_Controller extends _Controller {

        public function get_user_points($params = array()) {
            $this->load('Points');

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

            $data = $this->Points->getByUser(
                array(
                    'user_id' => $params['user_id']
                )
            );

            return static::wrap_result(true, $data);
        }
    }
?>