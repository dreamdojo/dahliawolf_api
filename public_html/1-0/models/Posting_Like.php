<?php
/**+
 * User: JDorado
 * Date: 09/16/13
 */
 
class Posting_Like extends _Model
{
    const TABLE = 'posting_like';
   	const PRIMARY_KEY_FIELD = 'posting_like_id';
   	const LINK_PARENT_FIELD = 'posting_id';

    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function addLike($data = array())
    {
        $error = NULL;
        $link_parent_field = self::LINK_PARENT_FIELD;
        $values = array();

        $fields = array(
            "$link_parent_field",
            "user_id",
            'viewer_user_id',
            'like_type_id',
            'created',
        );

        $data['created_at'] = date('Y-m-d h:i:s');

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
            self::$Exception_Helper->server_error_exception("Unable to save posting like.". $e->getMessage());
        }

    }

    public function deleteLike($params = array())
    {
        $error = NULL;
        $key_field = self::PRIMARY_KEY_FIELD;
        $link_parent_field = self::LINK_PARENT_FIELD;
        $user_id = 'user_id';

        if ( empty($params["$link_parent_field"]) && empty($params["$user_id"])) {

            $error = 'Invalid posting like id';
            return array('errors' => $error);
        }


        $where_sql = "{$link_parent_field} = :{$link_parent_field}
            AND  {$user_id} = :{$user_id}";

        $params = array(
            ":{$link_parent_field}" =>  $params["$link_parent_field"],
            ":{$user_id}" =>  $params["$user_id"]
        );

        $this->db_delete($where_sql, $params);

        return array(true);
    }


    public function deleteLikesByParentId($params = array())
    {
        $error = NULL;
        $link_parent_field = self::PRIMARY_KEY_FIELD;

        if (empty($params[ "$link_parent_field" ])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }

        $params['where'] = array(
            ":{$link_parent_field}" => $params[$link_parent_field]
        );

        $this->delete($this->table, $params['where'] );

        return array(true, true);
    }


}

?>