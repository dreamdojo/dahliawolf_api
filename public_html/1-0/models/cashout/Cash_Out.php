<?php
    class Cash_Out extends _Model
    {
        const TABLE = 'cash_outs';
        const PRIMARY_KEY_FIELD = 'cash_out_id';

        protected $fields = array(
            'user_id',
            'amount',
            'created'
        );

        public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }

        public function create($params = array()) {

            $data = array(
                'user_id'=>$params['user_id'],
                'amount'=>$params['amount']
            );

            try {
                $return = $this->save($data);
                return $return;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }
        }

        public function get($params=array()) {
            $where = '';
            $values = array();

            if(isset($params['user_id'])) {
                $where = 'WHERE user_id = :userId';
                $values[':userId'] = $params['user_id'];
            }

            $q = "
                SELECT *
                FROM cash_outs
                {$where}
                ORDER BY created DESC
            ";

            try {
                $return = $this->query($q, $values);
                return $return;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
            }
        }
    }
?>