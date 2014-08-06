<?php
    class Tasks extends _Model
    {

        const  ACTIVITY_ENTITY_ID = 6;
        const  ACTIVITY_ID_POSTED_IMAGE = 6;

        protected $points_earned=0;

        protected $fields = array(
            'created',
            'complete',
            'task',
            'action_id'
        );

        const TABLE = 'geobots_todo';
        const PRIMARY_KEY_FIELD = 'task_id';

        private $table = self::TABLE;

        /*public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }*/

        public function getTasks($params = array())
        {
            $values = array();

            $query = "
                SELECT
                geobots_todo.*
                FROM dahliawolf_v1_2013.geobots_todo WHERE run_count < 5
      			";
            $q = "
                UPDATE dahliawolf_v1_2013.geobots_todo
                SET geobots_todo.run_count = geobots_todo.run_count + 1
                WHERE
                geobots_todo.run_count < 5
            ";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = $this->fetch($query, $values);
                $this->fetch($q, $values);

                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }
        public function addTask($params = array())
        {

            $params = array(
                ':task' => $params['task'],
                ':actionId' => $params['action_id']
            );

            $query = "
                INSERT INTO dahliawolf_v1_2013.geobots_todo (task, action_id)
                VALUES (:task, :actionId);
      			";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($params);
                //die();
            }

            try {
                $data = $this->fetch($query, $params);
                return $data[0];

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }
    }
?>