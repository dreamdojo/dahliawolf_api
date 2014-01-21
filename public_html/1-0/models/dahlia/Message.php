<?php
/**
 * User: JDorado
 * Date: 7/19/13
 */

class Message extends _Model{

    const TABLE = 'message';
    const PRIMARY_KEY_FIELD = 'message_id';

    CONST ACTIVITY_ID_SENT_MESSAGE = 38;
    CONST ACTIVITY_ID_RECEIVED_MESSAGE = 39;


    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function sendMessage($data = array())
    {
        $error = NULL;

        $values = array();

        $fields = array(
            'from_user_id',
            'to_user_id',
            'body',
            'header',
            'created_at'
        );

        //static vars
        $data['created_at'] = date('Y-m-d h:i:s');
        $data['read_timestamp'] = NULL;

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        $logger = new Jk_Logger(APP_PATH.'logs/user_messages.log');
        $logger->LogInfo("SENDING MESSAGE with data: ", var_export($data, true));

        $activity_log = new Activity_Log();


        User::setDataTable("user_username");
        $user_model = new User($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE);

        $sending_user_data = $user_model->getUserById(trim($values['from_user_id'], '@'));

        $sending_user_username = $sending_user_data['username'];
        $logger->LogInfo( sprintf( "FETCHED SENDING USER: %s: ", var_export($sending_user_data, true)) );


        if(isset($data['to_user_name']))
        {
            $messages_sent = array();
            $users = explode(',', $data['to_user_name']);
            if(is_array($users) && count($users) > 0) foreach($users as $user_name)
            {
                $user_data = $user_model->getUserByUsername(trim($user_name, '@'));
                $to_user_id = $user_data['user_id'];
                /// replace with current user id
                $values['to_user_id'] = $to_user_id;
                if(!$to_user_id || $to_user_id=='') continue;

                $logger->LogInfo("SENDING MESSAGE TO USERNAME: $user_name USER ID: $to_user_id");

                try {
                    $message_id =  $this->do_db_save($values, $data);
                    $messages_sent[$to_user_id] = $message_id;
                    if( intval($message_id ) > 0 ) self::logActivity($values['to_user_id'], "{$sending_user_username} sent you a message",  $message_id, 'message', self::ACTIVITY_ID_RECEIVED_MESSAGE);


                } catch(Exception $e) {
                    self::$Exception_Helper->server_error_exception("Unable to send message.". $e->getMessage());
                }
            }

            return array(
                strtolower( self::PRIMARY_KEY_FIELD .'s') => $messages_sent,
                //'model_data' => $data
            );

        }

        try {
            $message_id = $this->do_db_save($values, $data);
            if( intval($message_id) > 0 ) self::logActivity($values['to_user_id'], "{$sending_user_username} sent you a message",  $message_id, 'message', self::ACTIVITY_ID_RECEIVED_MESSAGE);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $message_id,
                    //'model_data' => $data
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to send message.". $e->getMessage());
        }

    }

    public function fetchMessages($params = array())
    {
        $error = NULL;
        $values = array();
        if(!$params['to_user_id']) self::addError('invalid_user_id', 'invalid user id');

        $bind_fields = array(
            'to_user_id',
            'from_user_id'
        );

        //// from_user_id will auto bind if sent
        //if(array_key_exists('from_user_id', $data)) $bind_fields['from_user_id'] = $data['from_user_id'];

        foreach ($bind_fields as $field) {
            if (array_key_exists($field, $params)) {
                $values[$field] = $params[$field];
            }
        }

        if(array_key_exists('from_user_id', $params))
        {
            $where_sql = 'AND mt.from_user_id = :from_user_id';
        }

        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.to_user_id = :to_user_id
            {$where_sql}
            ORDER by mt.message_id DESC
        ";

        $data = $this->fetch($query, $values);

        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($data) ) );

        try {
            return array('messages' => $data );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get user messages". $e->getMessage());
        }

    }

    public function markAsRead($request_data)
    {
        $error = NULL;

        // Update quantity
        $readtimestamp_data = array(
            'read_timestamp' =>time()
        );

        $where_values = array(
            'message_id' => $request_data['message_id']
        );


        try {
            $insert_id = $this->db_update($readtimestamp_data, 'message_id = :message_id', $where_values, false);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                    //'model_data' => $data
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to send message.". $e->getMessage());
        }

    }


    private function logActivity($user_id, $note="@user sent you a message", $entity_id, $entity = 'message', $activity_id=self::ACTIVITY_ID_RECEIVED_MESSAGE )
    {
    	$activity = array(
            'user_id' => $user_id,
            'activity_id' => $activity_id,
            'note' => $note,
            'api_website_id' => 2,
            'entity' => $entity,
            'entity_id' => $entity_id

    	);

        $activity_log = new Activity_Log();
    	$data = $activity_log::saveActivity( $activity );

    	return $data;
    }



}

?>