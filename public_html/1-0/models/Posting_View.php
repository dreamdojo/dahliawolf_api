<?php
/**
 * User: JDorado
 * Date: 08/05/13
 */
 
class Posting_View extends _Model
{
    const TABLE = 'posting_view';
   	const PRIMARY_KEY_FIELD = 'posting_view_id';
   	const LINK_PARENT_FIELD = 'posting_id';

    private $table = 'posting_share';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function addView($data = array())
    {
        $error = NULL;
        $link_parent_field = self::LINK_PARENT_FIELD;
        $values = array();

        $fields = array(
            "$link_parent_field",
            'user_id',
            'viewer_user_id',
            'created_at',
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
            self::$Exception_Helper->server_error_exception("Unable to save posting view.". $e->getMessage());
        }

    }

    public function deleteView($params = array())
    {
        $error = NULL;
        $key_field = self::PRIMARY_KEY_FIELD;
        if (empty($params["$key_field"])) {

            $error = 'Invalid posting view id';
            return array('errors' => $error);
        }

        $params['where'] = array(
            ":{$key_field}" =>  $params["$key_field"]
        );

        $this->db_delete($this->table, $params['where']);


        return array( "$key_field" => $params['where'][ "$key_field" ]);
    }

    public function deleteViewsByParentId($params = array())
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


    public function getViews($params = array())
    {
        $error = NULL;
        $link_parent_field = self::LINK_PARENT_FIELD;
        $key_field = self::PRIMARY_KEY_FIELD;


        if (empty($params["$link_parent_field"])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }

        $query = " SELECT
                    *
                    FROM {$this->table}
                    WHERE {$link_parent_field} = :{$link_parent_field}
        ";

        $values = array(
            ":{$link_parent_field}" => $params["$link_parent_field"]
        );

        $data = $this->fetch($query, $values);

        if ($data === false) {
            return array('error' => 'Could not get post shares.');
        }

        return array( "{$key_field}s" => $data);
    }


    public function getCount($params = array())
    {
        $link_parent_field = self::LINK_PARENT_FIELD;
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'count',
              network
            FROM {$this->table}
            WHERE {$link_parent_field} = :$link_parent_field
            GROUP BY network
        ";
        $values = array(
            ":{$link_parent_field}" => $params["$link_parent_field"]
        );

        if(!$params[ "$link_parent_field" ]) self::addError('invalid_posting_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            $totals = self::getTotal($params);
            return array(
                        'totals' => $data,
                        'total' => ($totals ? $totals['total'] : null)
                    );
        }

        return null;
    }


    public function getTotal($params = array())
    {
        $link_parent_field = self::LINK_PARENT_FIELD;
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'total'
            FROM {$this->table}
            WHERE {$link_parent_field} = :{$link_parent_field}
        ";

        $values = array(
            ":{$link_parent_field}" => $params[ "$link_parent_field" ]
        );

        if(!$params[ $link_parent_field ]) self::addError('invalid_posting_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            return array(
                        'total' => $data[0]['total']
                    );
        }

    }

}

?>