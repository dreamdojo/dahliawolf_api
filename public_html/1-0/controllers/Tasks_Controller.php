<?php
    class Tasks_Controller extends _Controller {

        public function get_tasks($params = array()) {
            $this->load('Tasks');

            // Validations
            $input_validations = array(
            );
            $this->Validate->add_many($input_validations, $params, true);
            $this->Validate->run();

            $data = $this->Tasks->getTasks(
                array()
            );

            return static::wrap_result(true, $data);
        }
    }
?>