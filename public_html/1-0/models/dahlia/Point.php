<?php

class Point extends _Model {

    const TABLE = 'point';
    const PRIMARY_KEY_FIELD = 'point_id';

	private $table = 'point';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name);
    }

}
?>