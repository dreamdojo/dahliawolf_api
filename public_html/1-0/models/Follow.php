<?php
/**
 * User: JDorado
 * Date: 8/2/13
 */

class Follow extends _Model {
	const TABLE = 'follow';
	const PRIMARY_KEY_FIELD = 'follow_id';

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

        /*
        $logger->LogInfo(
                sprintf("user follow init...\n params: %s\nbind values: %s \ndb settings %s ",
                var_export($data, true),
                var_export($values,true),
                var_export(self::getDbCredentials(), true)
        ));
        */

        try {
            $logger->LogInfo(sprintf("save user follow... \nfields: %s", json_pretty($fields) ));
            $insert_id = $this->do_db_save($values, $data);
            return array(
                strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                //'model_data' => $data
                );

        } catch(Exception $e) {
            $logger->LogInfo("Unable to follow users.". $e->getMessage());
            //self::$Exception_Helper->server_error_exception("Unable to follow users.". $e->getMessage());
        }

    }

}
?>