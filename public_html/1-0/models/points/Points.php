<?php
    class Points extends _Model
    {

        const  ACTIVITY_ENTITY_ID = 6;
        const  ACTIVITY_ID_POSTED_IMAGE = 6;

        protected $points_earned=0;

        protected $fields = array(
            'created',
            'user_id',
            'point_id',
            'points'
        );

        const TABLE = 'user_point';
        const PRIMARY_KEY_FIELD = 'user_point_id';

        private $table = self::TABLE;

        /*public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }*/

        public function getUserLevel($points) {
            $q = "
                SELECT * FROM dahliawolf_v1_2013.membership_level
            ";
            $values = Array();

            $data = $this->fetch($q, $values);

            $needed = 10000000000;
            $prevHi = 0;
            foreach($data as $x=>$level) {
                if($points > $level['points']) {
                    if($level['points'] >= $prevHi) {
                        $cur_level = $level['name'];
                        $prevHi = $level['points'];
                    }
                }
                if($points < $level['points']) {
                    if(($level['points'] - $points) < $needed)
                        $needed = $level['points'] - $points;
                }
            }
            $ret_level = Array(
                'level'=> $cur_level,
                'needed' => $needed
            );
            return $ret_level;
        }

        public function getByUser($params = array())
        {
            $values = array();

            $user_id = $params['user_id'];

            $query = "
                SELECT
                SUM(points) AS points
                FROM dahliawolf_v1_2013.user_point WHERE user_id = ".$user_id."
      			";

            if (isset($_GET['t'])) {
                print_r($params);
                echo "$query\n";
                print_r($values);
                //die();
            }

            try {
                $data = $this->fetch($query, $values);

                $data[0]['level'] = $this->getUserLevel($data[0]['points']);
                return $data[0];

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }

        }
        public function addPoints($params = array())
        {

            if(isset($params['posting_id'])) {
                $sql = "
                    SELECT user_id FROM dahliawolf_v1_2013.posting WHERE posting_id = ".$params['posting_id']."
                ";
                $values = Array();
                $ret = $this->fetch($sql, $values);
                $params['user_id'] = $ret[0]['user_id'];
            }

            $params = array(
                ':userId' => $params['user_id'],
                ':pointId' => $params['point_id'],
                ':points' => $params['points'],
            );

            $query = "
                INSERT INTO dahliawolf_v1_2013.user_point (user_id, point_id, points)
                VALUES (:userId, :pointId, :points);
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