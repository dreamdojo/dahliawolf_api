<?php
/**
 * User: JDorado
 * Date: 8/27/13
 */
 
class Posting extends _Model
{
    const TABLE = 'posting';
    const PRIMARY_KEY_FIELD = 'posting_id';

    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


    public function deletePost($request_data = array())
    {
        $error = NULL;

        $deleted_ts = date('Y-m-d H:i:s');
        // Update quantity
        $update = array(
            'deleted' => $deleted_ts
        );

        $where_values = array(
            'posting_id' => $request_data['posting_id']
        );

        try {
            $insert_id = $this->db_update($update, 'posting_id = :posting_id', $where_values, false);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $request_data['posting_id'],
                    'deleted_date' => date('Y-m-d H:i:s')
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to delete posting.". $e->getMessage());
        }

    }

}

?>