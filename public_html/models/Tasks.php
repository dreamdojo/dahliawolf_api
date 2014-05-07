<?php

class Tasks extends db {

        private $table = 'geobots_todo';

        public function __construct() {
            parent::__construct();
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
                $data = $this->run($query, $params);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }
        }
    }
?>