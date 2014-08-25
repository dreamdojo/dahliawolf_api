<?php
/**
 * User: JDorado
 * Date: 11/26/13
 */
 
class User_Talents_Controller extends _Controller
{
    public function get($params = array()) {
        $input_validations = array(
            'user_id' => array(
                'label' => 'Search term',
                'rules' => array(
                    'is_string' => NULL
                )
            )
        );

        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $model = new User_Talents();
        $data = $model->get_rows(array('user_id'=>$params['user_id']));

        return $data;
    }

    public function create($params = array()) {
        $input_validations = array(
            'user_id' => array(
                'label' => 'Search term',
                'rules' => array(
                    'is_string' => NULL
                )
            ),
            'talent_id' => array(
                'label' => 'Search term',
                'rules' => array(
                    'is_string' => NULL
                )
            )
        );

        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $model = new User_Talents();
        $data = $model->db_insert(
            array(
                'user_id'=>$params['user_id']
                , 'talent_id'=>$params['talent_id']
            )
        );

        return $data;
    }

    public function delete($params = array()) {
        $input_validations = array(
            'user_id' => array(
                'label' => 'Search term',
                'rules' => array(
                    'is_string' => NULL
                )
            ),
            'user_talent_id' => array(
                'label' => 'Search term',
                'rules' => array(
                    'is_string' => NULL
                )
            )
        );

        $this->Validate->add_many($input_validations, $params, true);
        $this->Validate->run();

        $model = new User_Talents();

        $where_sql = "user_id = :userId AND user_talent_id = :talentId";

        $params = array(
            ":talentId" =>  $params["user_talent_id"],
            ":userId" =>  $params["user_id"]
        );

        $model->db_delete($where_sql, $params);
    }
}

?>