<?php
/**
 * User: JDorado
 * Date: 8/28/13
 */

class Posting_Promote extends _Model
{
    const TABLE = 'posting_promote';
    const PRIMARY_KEY_FIELD = 'posting_promote_id';
    const DAY_TIME = 86400;

    private $table = self::TABLE;

    public function create($request_data = array())
    {

        if( self::validatePromotion($request_data) == false )
        {
            self::$Exception_Helper->server_error_exception("Unable to promote posting. user has reached max promotes or has active promotes");
            return null;
        }

        $error = NULL;

        $values = array();

        $fields = array(
            'posting_id',
            'user_id',
            'lifespan',
            'created_at'
        );

        //static vars
        $values['created_at'] = date('Y-m-d h:i:s');
        $values['lifespan'] = self::DAY_TIME*30;

        foreach ($fields as $field) {
            if (array_key_exists($field, $request_data)) {
                $values[$field] = $request_data[$field];
            }
        }

        $logger = new Jk_Logger(APP_PATH . 'logs/posting_promote.log');
        $logger->LogInfo("ADDING POSTING PROMOTE with data: ", var_export($request_data, true));


        try {
            $insert_id = $this->do_db_save($values, NULL);
            return array(
                strtolower(self::PRIMARY_KEY_FIELD) => $insert_id,
            );

        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to promote posting." . $e->getMessage());
            return null;
        }
    }


    public function getPromotes($request_data)
    {
        $where_sql = "";

        if(array_key_exists('from_user_id', $request_data))
        {
            $where_sql = 'AND mt.from_user_id = :from_user_id';
        }

        $values['user_id'] = $request_data['user_id'];
        $values['posting_id'] = $request_data['posting_id'];

        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.user_id = :user_id
            AND mt.posting_id =  :posting_id
            {$where_sql}
        ";

        $data = $this->fetch($query, $values);
        self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($data) ) );

        return $data;
    }

    private function validatePromotion($request_data)
    {
        $where_sql = "";

        if(array_key_exists('from_user_id', $request_data))
        {
            $where_sql = 'AND mt.from_user_id = :from_user_id';
        }

        $values['user_id'] = $request_data['user_id'];
        $values['posting_id'] = $request_data['posting_id'];

        $interval = 30*self::DAY_TIME;

        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.user_id = :user_id
            AND mt.posting_id =  :posting_id
            AND mt.lifespan BETWEEN DATE_ADD(NOW(), INTERVAL -{$interval} DAY) and NOW()
            {$where_sql}
        ";

        $active_promotes  = $this->fetch($query, $values);
        self::trace( sprintf("$query\n ACTIVE PROMOTES ?: %s", ($active_promotes && count($active_promotes) > 0? var_dump($active_promotes) : "NULL" ) ) );

        if($active_promotes && count($active_promotes) > 0) return false;

        $total_promotes = self::getPromotes($request_data);
        $max_promotes = 2;
        if($active_promotes && count($total_promotes) > $max_promotes) return false;

        return true;
    }


}

?>