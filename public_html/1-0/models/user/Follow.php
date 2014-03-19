<?php
/**
 * User: JDorado
 * Date: 8/2/13
 */

class Follow extends _Model {
	const TABLE = 'follow';
	const PRIMARY_KEY_FIELD = 'follow_id';

    const ACTIVITY_ID_USER_FOLLOWING = 34;
    const ACTIVITY_ID_USER_FOLLOWED = 37;

    protected $fields = array(
   		'created',
   		 'user_id',
   		 'follower_user_id'
   	);

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );

        self::setDataTable('follow');
        self::setPrimaryField('follow_id');
    }



    public function followUser($data = array())
    {
        $error = NULL;

        $values = array();

        (int) $data['user_follow_id'] > 0 ? $data['follower_user_id'] = $data['user_follow_id'] : '';

        $user_id =  $data['follower_user_id'];

        $data['follower_user_id'] = $data['user_id'];
        $data['user_id'] =  $user_id;

        $fields = array(
            'user_id',
            'follower_user_id',
        );
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        $logger = new Jk_Logger(APP_PATH.'logs/follow.log');


        $logger->LogInfo(
                sprintf("user follow init...\n params: %s\nbind values: %s \ndb settings %s ",
                var_export($data, true),
                var_export($values,true),
                var_export(self::getDbCredentials(), true)
        ));


        try {
            $logger->LogInfo(sprintf("save user follow... \nfields: %s", json_pretty($values) ));
            $insert_id = $this->do_db_save($values, $data);

            // Log activity
            //log_activity($_REQUEST['user_id'], 34, 'Started Following you', 'follow', $user['data']);
            //////logActivity($user_id,         $note="@user Started Following you", $follow_id, $entity = 'message', $activity_id=self::ACTIVITY_ID_USER_FOLLOWING )
            self::logActivity($data['user_id'], $insert_id, $note='started following you', 'follow', $activity_id=self::ACTIVITY_ID_USER_FOLLOWING);

            // Log activity
            //log_activity($_REQUEST['follower_user_id'], 37, 'Followed another user', 'follow', $user['data']);
            //////logActivity($user_id,                  $note="@user Started Following you", $follow_id, $entity = 'message', $activity_id=self::ACTIVITY_ID_USER_FOLLOWING )
            self::logActivity($data['follower_user_id'], $insert_id, $note="Followed another user", 'follow', $activity_id=self::ACTIVITY_ID_USER_FOLLOWED);


            $logger->LogInfo("follow entity_id: $insert_id");
            return array(
                strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                //'model_data' => $data
                );

        } catch(Exception $e) {
            $logger->LogInfo("Unable to follow users.". $e->getMessage());
            //self::$Exception_Helper->server_error_exception("Unable to follow users.". $e->getMessage());
        }

    }


    public function removeFollow($data = array())
    {
        $error = NULL;

        $values = array();

        (int) $data['user_follow_id'] > 0 ? $data['follower_user_id'] = $data['user_follow_id'] : '';

        $user_id =  $data['follower_user_id'];

        $data['follower_user_id'] = $data['user_id'];
        $data['user_id'] =  $user_id;

        $fields = array(
            'user_id',
            'follower_user_id',
        );
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        $logger = new Jk_Logger(APP_PATH.'logs/follow.log');

        $where_sql = "user_id = :user_id
                    AND follower_user_id = :follower_user_id";

        try {
            $logger->LogInfo(sprintf("save user follow... \nfields: %s", json_pretty($fields) ));
            $insert_id = $this->db_delete($where_sql, $values);
            return array(
                strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                //'model_data' => $data
                );

        } catch(Exception $e) {
            $logger->LogInfo("Unable to follow users.". $e->getMessage());
            //self::$Exception_Helper->server_error_exception("Unable to follow users.". $e->getMessage());
        }

    }


    private function logActivity($user_id, $follow_id, $note="@user started following you", $entity = 'follow', $activity_id=self::ACTIVITY_ID_USER_FOLLOWING )
    {

    	$activity = array(
            'user_id' => $user_id,
            'activity_id' => $activity_id,
            'note' => $note,
            'api_website_id' => 2,
            'entity' => $entity,
            'entity_id' => $follow_id

    	);

        $activity_log = new Activity_Log();
    	$data = $activity_log::saveActivity( $activity );

    	return $data;
    }






}
?>