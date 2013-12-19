<?php

/**
 * User: JDorado
 * Date: 11/15/13
 */

class Posting_Comment extends _Model {


    CONST ACTIVITY_ID_SENT_COMMENT = 25;
    CONST ACTIVITY_ID_RECEIVED_COMMENT = 32;

    const TABLE = 'comment';
    const PRIMARY_KEY_FIELD = 'comment_id';
    const DAY_TIME = 86400;



    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function getUserId($data = array())
    {
        $posting_id = $data['posting_id'];

        $sql = "SELECT
                       user.*
                   FROM user_username user
                    JOIN posting ON posting.posting_id = {$posting_id}
                   WHERE posting.user_id = user.user_id ";

        try {
            $data = $this->fetch($sql, array());
            return ($data && $data[0] ? $data[0]['user_id'] : null);

        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    public function create($request_data = array())
    {

       $error = NULL;
       $values = array();
       $fields = array(
           'user_id',
           'posting_id',
           'comment',
       );

       $values['created_at'] = date('Y-m-d h:i:s');

       foreach ($fields as $field) {
           if (array_key_exists($field, $request_data)) {
               $values[$field] = $request_data[$field];
           }
       }

       self::trace("ADDING POSTING COMMENT with data: ", var_export($request_data, true));


       try {
           $insert_id = $this->do_db_save($values, NULL);

           $posting_owner_user_id = self::getUserId($request_data);

           self::logActivity($posting_owner_user_id, self::ACTIVITY_ID_RECEIVED_COMMENT, 'Received a comment on an image', 'comment', $insert_id );

           return array(
               strtolower(self::PRIMARY_KEY_FIELD) => $insert_id,
           );

       } catch (Exception $e) {
           self::$Exception_Helper->server_error_exception("Unable to create posting tag.");
           return null;
       }
    }


    public function edit($request_data = array())
    {
        $error = NULL;
        $values = array();
        /**/
        $fields = array(
            'user_id',
            'posting_id',
            'comment',
        );

        foreach ($fields as $field) {
           if (array_key_exists($field, $request_data)) {
               $values[$field] = $request_data[$field];
           }
        }


        self::trace("EDIT POSTING COMMENT with data: ", var_export($request_data, true));


        $where_sql = "
            WHERE  comment_id = :comment_id
        ";

        $where_values = array();
        $where_values['comment_id'] = $request_data['comment_id'];

        try {
            $update = $this->db_update($fields, $where_sql, $where_values);

           return array(
               strtolower(self::PRIMARY_KEY_FIELD) => $update,
           );

        } catch (Exception $e) {
           self::$Exception_Helper->server_error_exception("Unable to edit posting comment.");
           return null;
        }
    }


    public function getPostingComments($request_data, $with_details=false)
    {
        $where_sql = "";

        $values['posting_id'] = $request_data['posting_id'];
        #$values['user_id'] = $request_data['user_id'];

        $query = "
            SELECT  mt.*,
                    user.username,
                    user.avatar
            FROM   {$this->table} as mt
              JOIN user_username user ON user.user_id = mt.user_id
            WHERE mt.posting_id = :posting_id
        ";
        /* AND mt.user_id = :user_id */
        //echo ($query);

        $comments = $this->fetch($query, $values);

        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($comments) ) );

        return $comments;
    }


    public function remove($request_data)
    {
        $values['comment_id'] = $request_data['comment_id'];
        $values['posting_id'] = $request_data['posting_id'];

        $query = "
            DELETE mt.*
            FROM {$this->table} as mt
            WHERE mt.comment_id = :comment_id
              AND mt.posting_id = :posting_id
        ";

        //self::trace( sprintf("$query\n %s, request_data: %s\n$query vals: %s", $query, var_export($request_data, true), var_export($values, true) ) );

        try{
            $data = $this->query($query, $values);
            self::trace( sprintf("QUERY RETURNED: %s results", count($data) ) );
            return true;
        }catch (Exception $e){
            self::trace( sprintf("QUERY RETURNED FAILED!!!") );
            return false;
        }

        return $data;
    }

    protected function logActivity($user_id, $activity_id, $note, $entity = NULL, $entity_id = NULL)
    {
        $activity_log = new Activity_Log();

        $params = array(
            'user_id' => $user_id,
             'activity_id' => $activity_id,
             'note' => $note,
             'api_website_id' => 2,
             'entity' => $entity,
             'entity_id' => $entity_id,
        );

        $data = Activity_Log::saveActivity($params);

    }


}

?>