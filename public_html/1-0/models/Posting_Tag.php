<?php
/**
 * User: JDorado
 * Date: 11/15/13
 */
 
class Posting_Tag extends _Model {


    const TABLE = 'posting_tag';
    const PRIMARY_KEY_FIELD = 'posting_tag_id';
    const DAY_TIME = 86400;

    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }



    public function create($request_data = array())
    {
       $error = NULL;
       $values = array();
       $fields = array(
           'posting_id',
           'user_id',
           'created_at',
           'x',
           'y',
           'message'
       );

       $values['created_at'] = date('Y-m-d h:i:s');

       foreach ($fields as $field) {
           if (array_key_exists($field, $request_data)) {
               $values[$field] = $request_data[$field];
           }
       }

       $logger = new Jk_Logger(APP_PATH . 'logs/posting_tags.log');
       $logger->LogInfo("ADDING POSTING TAG with data: ", var_export($request_data, true));

       try {
           $insert_id = $this->do_db_save($values, NULL);

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
            'x',
            'y',
            'message'
        );

        foreach ($fields as $field) {
           if (array_key_exists($field, $request_data)) {
               $values[$field] = $request_data[$field];
           }
        }

        $logger = new Jk_Logger(APP_PATH . 'logs/posting_tags.log');
        $logger->LogInfo("ADDING POSTING TAG with data: ", var_export($request_data, true));


        $where_sql = "
            WHERE  posting_tag_id = :posting_tag_id
        ";

        $where_values = array();
        $where_values['posting_tag_id'] = $request_data['posting_tag_id'];

        try {
            $update = $this->db_update($fields, $where_sql, $where_values);

           return array(
               strtolower(self::PRIMARY_KEY_FIELD) => $update,
           );

        } catch (Exception $e) {
           self::$Exception_Helper->server_error_exception("Unable to edit posting tag.");
           return null;
        }
    }


    public function getPostingTags($request_data, $with_details=false)
    {
        $where_sql = "";

        $values['posting_id'] = $request_data['posting_id'];

        $query = "
            SELECT mt.*
            FROM   {$this->table} as mt
            WHERE  mt.posting_id = :posting_id
        ";

        $posting_tags = $this->fetch($query, $values);

        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($posting_tags) ) );

        return $posting_tags;
    }


    public function remove($request_data)
    {
        $values['posting_tag_id'] = $request_data['posting_tag_id'];
        $values['posting_id'] = $request_data['posting_id'];

        $query = "
            DELETE mt.*
            FROM {$this->table} as mt
            WHERE mt.posting_tag_id = :posting_tag_id
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



    protected function trace($m)
    {
        $m = is_object($m) ||  is_array($m) ? var_export($m) : $m;
        $logger = new Jk_Logger(APP_PATH . 'logs/posting_tags.log');
        $logger->LogInfo("$m");
    }
    /*
    private function validateTagAdd($request_data)
    {
        $total_faves = self::getUserFaves($request_data);
        $max_promotes = 3;
        if($total_faves && count($total_faves) >= $max_promotes) return false;

        return true;
    }
    */



}

?> 