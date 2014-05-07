<?php
    class Designs_Controller extends _Controller {

        public function get_designs($params = array()) {
            $this->load('Designs');

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

            $data = $this->Designs->getByUser(
                array(
                    'user_id' => $params['user_id']
                )
            );

            return static::wrap_result(true, $data);
        }

        public function get_design($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                    , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                 'design_id' => array(
                    'label' => 'Design id'
                    , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    )
                    )
                )
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Designs->getDesign(
                array(
                    'user_id' => $params['user_id']
                , 'design_id' => $params['design_id']
                )
            );

            return static::wrap_result(true, $data);
        }

        public function update_design($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                    'design_id' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    ),
                    'field_id' => array(
                        'label' => 'Field id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    )
                )
            );
            if($params['field_id'] == 'description' || $params['field_id'] == 'name' || $params['field_id'] == 'category') {
                $this->Validate->add_many($input_validations, $params, true);
                $this->Validate->run();

                $data = $this->Designs->updateDesign(
                    array(
                        'user_id' => $params['user_id']
                    , 'design_id' => $params['design_id']
                    , 'field_id' => $params['field_id']
                    , 'value' => $params['value']
                    )
                );
            } else {
                $data = Array('success', false);
            }

            return static::wrap_result(true, $data);
        }

        public function add_image($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                    'design_id' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    ),
                    'image_name' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    )
                )
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Designs->addImage(
                array(
                    'user_id' => $params['user_id']
                , 'design_id' => $params['design_id']
                , 'image_name' => $params['image_name']
                )
            );

            return static::wrap_result(true, $data);
        }

        public function add_note($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                    'design_id' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    ),
                    'note' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    )
                )
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Designs->addNote(
                array(
                    'user_id' => $params['user_id']
                , 'design_id' => $params['design_id']
                , 'note' => $params['note']
                )
            );

            return static::wrap_result(true, $data);
        }
        public function add_image_note($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                    'design_image_id' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    ),
                    'note' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    )
                )
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Designs->addImageNote(
                array(
                'design_image_id' => $params['design_image_id']
                , 'note' => $params['note']
                )
            );

            return static::wrap_result(true, $data);
        }

        public function submit_design($params = array()) {
            $this->load('Designs');

            // Validations
            $input_validations = array(
                'user_id' => array(
                    'label' => 'User ID'
                , 'rules' => array(
                        'is_set' => NULL
                    , 'is_int' => NULL
                    ),
                    'design_id' => array(
                        'label' => 'Design id'
                    , 'rules' => array(
                            'is_set' => NULL
                        , 'is_int' => NULL
                        )
                    )
                )
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Designs->submitDesign(
                array(
                    'user_id' => $params['user_id']
                , 'design_id' => $params['design_id']
                )
            );

            return static::wrap_result(true, $data);
        }

    }
?>