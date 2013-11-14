<?php
/**
 * User: JDorado
 * Date: 8/28/13
 */

class Posting_Fave extends _Model
{
    const TABLE = 'posting_fave';
    const PRIMARY_KEY_FIELD = 'posting_fave_id';
    const DAY_TIME = 86400;

    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


    public function create($request_data = array())
    {

        if( self::validateFaveAdd($request_data) == false )
        {
            self::$Exception_Helper->server_error_exception("Unable to fave posting. user has reached max faves or has active faves");
            return null;
        }

        $error = NULL;

        $values = array();

        $fields = array(
            'posting_id',
            'user_id',
            'created_at',
        );

        //static vars
        $lifespan = self::DAY_TIME*30;
        $values['created_at'] = date('Y-m-d h:i:s');

        /*
        $values['start_date'] = date('Y-m-d h:i:s');
        $values['end_date'] = date('Y-m-d h:i:s', time()+$lifespan);
        */

        foreach ($fields as $field) {
            if (array_key_exists($field, $request_data)) {
                $values[$field] = $request_data[$field];
            }
        }

        $logger = new Jk_Logger(APP_PATH . 'logs/posting_faves.log');
        $logger->LogInfo("ADDING POSTING FAVE with data: ", var_export($request_data, true));


        try {
            $insert_id = $this->do_db_save($values, NULL);

            return array(
                strtolower(self::PRIMARY_KEY_FIELD) => $insert_id,
            );

        } catch (Exception $e) {
            if( stripos($e->getMessage(), 'Duplicate') > -1 )
            {
                self::$Exception_Helper->server_error_exception("This Posting is already in your faves");
            }else{
                self::$Exception_Helper->server_error_exception("Unable to fave posting.");
            }

            return null;
        }
    }


    public function getUserFaves($request_data, $with_details=false)
    {
        $where_sql = "";

        $values['user_id'] = $request_data['user_id'];
        #$values['posting_id'] = $request_data['posting_id'];

        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.user_id = :user_id
        ";

        $user_faves = $this->fetch($query, $values);

        $this->load('Posting');

        $posting_ids= array();
        if($user_faves && $with_details)foreach($user_faves as &$fave_data)
        {
            $posting_ids[] = $fave_data['posting_id'];
        }

        $params = array(

            'posting_ids' =>   $posting_ids,
            'user_id' => $request_data['user_id']
        );

        if($request_data['viewer_user_id']) $params['viewer_user_id'] = $request_data['viewer_user_id'];

        if($user_faves && $with_details) {
            $posting = new Posting();
            $posting_datas = $posting->getByIdsArray($params);

            if($posting_datas && $posting_datas['posts'])
            {
                foreach($user_faves as &$fave_data)
                {
                    foreach($posting_datas['posts'] as $posting_data)
                    {

                        if($posting_data['posting_id'] == $fave_data['posting_id'])
                        {
                            $fave_data['posting_data'] = $posting_data;
                            break;
                        }
                    }
                }
            }

        }

        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($user_faves) ) );

        return $user_faves;
    }


    public function remove($request_data)
    {
        $values['user_id'] = $request_data['user_id'];
        $values['posting_id'] = $request_data['posting_id'];

        $query = "
            DELETE mt.*
            FROM {$this->table} as mt
            WHERE mt.posting_id = :posting_id
              AND mt.user_id = :user_id
        ";

        $data = $this->query($query, $values);
        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($data) ) );

        return $data;
    }

    public function removeFave($request_data)
    {
        $where_sql = "";

        if(array_key_exists('from_user_id', $request_data))
        {
            $where_sql = 'AND mt.from_user_id = :from_user_id';
        }

        $values['user_id'] = $request_data['user_id'];
        #$values['posting_id'] = $request_data['posting_id'];

        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.user_id = :user_id
            #AND mt.posting_id =  :posting_id
            {$where_sql}
        ";

        $data = $this->fetch($query, $values);
        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($data) ) );

        return $data;
    }


    private function validateFaveAdd($request_data)
    {
        $total_faves = self::getUserFaves($request_data);
        $max_promotes = 3;
        if($total_faves && count($total_faves) >= $max_promotes) return false;

        return true;
    }


}

?>