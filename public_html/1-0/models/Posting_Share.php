<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */
 
class Posting_Share extends Sharing_Abstract
{
    const TABLE = 'posting_share';
   	const PRIMARY_KEY_FIELD = 'posting_share_id';

    private $table = 'posting_share';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function addShare($data = array())
    {
        $error = NULL;

        $values = array();

        $fields = array(
            'posting_id',
            'sharing_user_id',
            'network',
            'posting_owner_user_id',
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
            self::$Exception_Helper->server_error_exception("Unable to save  posting share.". $e->getMessage());
        }

    }

    public function deleteShare($params = array())
    {
        $error = NULL;
        if (empty($params['posting_share_id'])) {

            $error = 'Invalid posting sharing id';
            return array('errors' => $error);
        }

        $params['where'] = array(
            ':posting_share_id' =>  $params['posting_share_id']
        );

        $this->db_delete($this->table, $params['where']);


        return array('posting_share_id' => $params['where']['posting_share_id']);
    }

    public function deleteSharesByParentId($params = array())
    {
        $error = NULL;

        if (empty($params['posting_id'])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }

        $params['where'] = array(
            ':posting_id' => $params['posting_id']
        );

        $this->delete($this->table, $params['where'] );

        return array(true, true);
    }


    public function getShares($params = array())
    {
        $error = NULL;

        if (empty($params['posting_id'])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }


        $values = array(
            ':posting_id' => $params['posting_id']
        );

        $select_str ='';
        $user_id = $params['user_id'];
        if ( $user_id && !empty($user_id))
        {
            $select_str .= ',IF(follow.follow_id IS NULL, 0, 1) AS is_following';
            //$select_str .= ',posting.*';
            $select_str .= ',user.username,user.avatar,user.location ';
            $join_str = '
                LEFT JOIN posting ON posting.posting_id = main.posting_id
                LEFT JOIN user_username user ON user.user_id = posting.user_id
                LEFT JOIN follow ON (posting.user_id = follow.user_id
                    AND follow.follower_user_id = :viewer_user_id)
            ';
            $values[':viewer_user_id'] = $user_id;

        }

        $query = " SELECT main.*
                    {$select_str}
                    FROM {$this->table} main
                    {$join_str}
                    WHERE main.posting_id = :posting_id
        ";


        $data = $this->fetch($query, $values);

        if(isset($params['t'])) echo $query;

        if ($data === false) {
            return array('error' => 'Could not get post shares.');
        }

        if(count($data) > 0)
        {
            return array('sharings' => $data);
        }

        return array('sharings' => 'share');
    }


    public function getSharesCount($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'count',
              network
            FROM {$this->table}
            WHERE posting_id = :posting_id
            GROUP BY network
        ";
        $values = array(
            ':posting_id' => $params['posting_id']
        );

        if(!$params['posting_id']) self::addError('invalid_posting_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            $totals = self::getTotalShares($params);
            return array(
                        'totals' => $data,
                        'total' => ($totals ? $totals['total'] : null)
                    );
        }

        return null;
    }


    public function getTotalShares($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'total'
            FROM {$this->table}
            WHERE posting_id = :posting_id
        ";
        $values = array(
            ':posting_id' => $params['posting_id']
        );

        if(!$params['posting_id']) self::addError('invalid_posting_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            return array(
                        'total' => $data[0]['total']
                    );
        }

    }

}




?>