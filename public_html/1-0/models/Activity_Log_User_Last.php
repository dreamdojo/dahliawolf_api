<?php
/**
 * User: JDorado
 * Date: 11/26/13
 */
 
class Activity_Log_User_Last extends _Model
{

    const TABLE = 'activity_log_last_user_time';
   	const PRIMARY_KEY_FIELD = 'activity_log_last_user_time_id';

    private $table = 'activity_log_last_user_time';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


    public function addLast($params = array())
    {
        $data = array(
            'last_activity_time' => '',
            'user_id' => ''
        );

        $values = array(
            'last_activity_time' => time(),
            'user_id' => $params['user_id']
        );


        try {
            $insert_id = $this->do_db_save($values, $data);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
            );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to save user last log time. ". $e->getMessage());
        }

    }

    public function getUserLast($params = array())
    {
        $error = NULL;

        if (empty($params['user_id'])) {
            $error = 'Invalid user id.';
            return array('error' => $error);
        }

        $query = " SELECT
                   *
                   FROM {$this->table}
                   WHERE user_id = :user_id
                   ORDER BY last_activity_time DESC
                   LIMIT 1
       ";

        $values = array(
            ':user_id' => $params['user_id']
        );

        $data = $this->fetch($query, $values);

        if ($data === false || !$data[0]) {
            return -1;
        }

        $user_last = (object)$data[0];

        return ($user_last->last_activity_time ? (int)$user_last->last_activity_time : -1);
    }
}

?> 