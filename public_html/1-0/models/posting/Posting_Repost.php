<?php
/**
 * User: JDorado
 * Date: 08/05/13
 */
 
class Posting_Repost extends _Model
{
    const TABLE = 'posting_repost';
   	const PRIMARY_KEY_FIELD = 'posting_repost_id';
   	const LINK_PARENT_FIELD = 'posting_id';

    private $table = 'posting_share';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function addRepost($data = array())
    {
        $error = NULL;
        $link_parent_field = self::LINK_PARENT_FIELD;
        $values = array();

        $fields = array(
            "$link_parent_field",
            'posting_id',
            'repost_user_id',
            'repost_interval',
            'created_at',
        );

        $data['created_at'] = date('Y-m-d h:i:s');
        $repost_interval = 15;//allow every 15 days for same user, same post only
        $dotm = intval(date('d')) > 30? 30 : intval(date('d'));
        $data['repost_interval'] = date('Y-m') ."-".  str_pad($repost_interval*floor($dotm/$repost_interval), 2, '0', STR_PAD_LEFT);

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        try {
            $insert_id = $this->do_db_save($values, $data);
            self::logActivity($data['og_id'], 33, 'reposted your post', 'posting_repost', $insert_id );
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                    //'model_data' => $data
                    );

        } catch(Exception $e) {
            $error = stripos($e->getMessage(), 'duplicate') > -1? "You are only allowed to repost same post every {$repost_interval} days." : "Unable to save posting repost.";
            self::$Exception_Helper->server_error_exception( $error);
        }
    }

    public function deleteRepost($params = array())
    {
        $error = NULL;
        $key_field = self::PRIMARY_KEY_FIELD;
        if (empty($params["$key_field"])) {
            $error = 'Invalid posting view id';
            return array('errors' => $error);
        }

        $values = array(
            ":posting_repost_id" =>  $params['posting_repost_id']
        );

        //$this->db_delete('posting_repost', $params['where']);
        print_r($values);
        $q = "
            DELETE FROM posting_repost
            WHERE posting_repost.posting_repost_id = :posting_repost_id
            LIMIT 1
        ";

        $data = $this->query($q, $values);
        return $data;
    }

    public function deleteRepostByParentId($params = array())
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


    public function getReposts($params = array())
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
            return array('error' => 'Could not get post reposts.');
        }

        return array( "{$key_field}s" => $data);
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