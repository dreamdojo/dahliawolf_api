<?php
    class Designs extends _Model
    {

        const  ACTIVITY_ENTITY_ID = 6;
        const  ACTIVITY_ID_POSTED_IMAGE = 6;

        protected $fields = array(
            'created',
            'user_id',
            'designer_id',
            'status'
        );

        const TABLE = 'designs';
        const PRIMARY_KEY_FIELD = 'design_id';

        private $table = self::TABLE;

        public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }

        public function getByUser($params = array())
        {
            $values = array();

            $user_id = $params['user_id'];

            $query = "
                SELECT DISTINCT
                    design_images.*, designs.*
                FROM dahliawolf_v1_2013.designs
                   LEFT JOIN dahliawolf_v1_2013.design_images ON design_images.design_id = designs.design_id
                WHERE user_id = ".$user_id." AND designs.design_id IS NOT NULL
                GROUP BY designs.design_id
      			";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = $this->fetch($query, $values);
                return array('designs' => $data );

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }
        public function getDesign($params = array())
        {
            $values = array();

            $user_id = $params['user_id'];
            $design_id = $params['design_id'];

            $query = "
                SELECT
                designs.* FROM dahliawolf_v1_2013.designs WHERE user_id = ".$user_id." AND design_id =
      			".$params['design_id'];

            $q = "
                SELECT
                design_images.* FROM dahliawolf_v1_2013.design_images WHERE design_id = ".$design_id."
      			";
            $que = "
                SELECT
                design_notes.* FROM dahliawolf_v1_2013.design_notes WHERE design_id = ".$design_id."
      			ORDER BY design_notes.design_note_id DESC
      			";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);
                $data['images'] = $this->query($q, $values);
                $data['notes'] = $this->query($que, $values);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function updateDesign($params = array())
        {
            $values = array();

            $user_id = $params['user_id'];
            $design_id = $params['design_id'];
            $field_id = $params['field_id'];
            $value = $params['value'];

            $query = "
                UPDATE dahliawolf_v1_2013.designs
                SET designs.".$field_id." = '".$value."'
                WHERE designs.user_id = ".$user_id." AND designs.design_id = ".$design_id."
            ";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = self::$dbs[$this->db_host][$this->db_name]->select_single($query);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function addImageNote($params = array())
        {
            $values = array();

            $design_id = $params['design_image_id'];
            $value = $params['note'];

            $query = "
                UPDATE dahliawolf_v1_2013.design_images
                SET description = '".$value."'
                WHERE design_images.design_image_id = ".$design_id."
            ";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = self::$dbs[$this->db_host][$this->db_name]->exec($query);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function submitDesign($params = array())
        {
            $values = array();

            $user_id = $params['user_id'];
            $design_id = $params['design_id'];

            $query = "
                UPDATE dahliawolf_v1_2013.designs
                SET status = 'submitted'
                WHERE designs.user_id = ".$user_id." AND designs.design_id = ".$design_id."
            ";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = self::$dbs[$this->db_host][$this->db_name]->select_single($query);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function addImage($params = array())
        {
            $values = array();
            $values[':designId'] = $params['design_id'];
            $values[':fileName'] = $params['image_name'];

            $query = "
            			INSERT INTO dahliawolf_v1_2013.design_images (design_id, file_name)
            			VALUES (:designId, :fileName)
            		";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = $this->query($query, $values);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }

        public function addNote($params = array())
        {
            $values = array();
            $values[':designId'] = $params['design_id'];
            $values[':note'] = $params['note'];
            $values[':userId'] = $params['user_id'];

            $query = "
            			INSERT INTO dahliawolf_v1_2013.design_notes (design_id, user_id, note)
            			VALUES (:designId, :userId, :note)
            		";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = $this->query($query, $values);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }
    }
?>