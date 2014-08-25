<?php
    class User_Talents extends _Model
    {
        protected $fields = array(
            'user_talent_id',
            'user_id',
            'talent_id'
        );

        const TABLE = 'user_talents';
        const PRIMARY_KEY_FIELD = 'user_talent_id';

        private $table = self::TABLE;

        public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
        {
            parent::__construct($db_host, $db_user, $db_password, $db_name );
        }
    }
?>