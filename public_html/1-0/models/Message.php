<?php
/**
 * User: JDorado
 * Date: 7/19/13
 */

class Message extends _Model{

    const TABLE = 'message';
    const PRIMARY_KEY_FIELD = 'message_id';

    private $table = self::TABLE;

    public function __construct()
    {
        parent::__construct();
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

        try {
            $insert_id = $this->do_db_save($values, $data);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
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

        try {
            $insert_id = $this->db_update()$values, $data);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                    //'model_data' => $data
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to send message.". $e->getMessage());
        }

    }

}

?>