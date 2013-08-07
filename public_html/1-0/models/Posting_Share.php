<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */
 
class Posting_Share extends _Model
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
        if (empty($params['where'])) {
            $error = 'Where conditions is required.';
        } else if (!is_array($params['where'])) {
            $error = 'Invalid where conditions.';
        }

        if (!empty($error)) {
            return resultArray(false, NULL, $error);
        }

        $this->delete($this->table, $params['where']);

        $this->audit_post_likes($params['where']['posting_share_id']);

        return resultArray(true, true);
    }

    public function deleteShareByPostingId($params = array())
    {
        $error = NULL;
        if (empty($params['where'])) {
            $error = 'Where conditions is required.';
        } else if (!is_array($params['where'])) {
            $error = 'Invalid where conditions.';
        }

        if (!empty($error)) {
            return resultArray(false, NULL, $error);
        }

        $this->delete($this->table, $params['where']);

        $this->audit_post_likes($params['where']['posting_id']);

        return resultArray(true, true);
    }


    public function getPostShares($params = array())
    {
        $error = NULL;

        if (empty($params['where'])) {
            $error = 'Where conditions are required.';
        } else if (!is_array($params['where'])) {
            $error = 'Invalid where conditions.';
        }

        if (!empty($error)) {
            return resultArray(false, NULL, $error);
        }

        $query = "
            SELECT user_username.*, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
            FROM posting_like
                INNER JOIN posting ON posting_like.posting_id = posting.posting_id
                INNER JOIN user_username ON posting_like.user_id = user_username.user_id
                LEFT JOIN user_image_map ON user_username.user_id = user_image_map.user_id AND user_image_map.avatar = 'Yes'
                LEFT JOIN image ON user_image_map.image_id = image.id
            WHERE posting_like.posting_id = :posting_id
            ORDER BY posting_like.created DESC
        ";
        $values = array(
            ':posting_id' => $params['where']['posting_id']
        );

        //$row = $this->get_row($this->table, $params['conditions']);
        $data = $this->fetch($query, $values);
        $row = $data->fetchAll();
        if ($row === false) {
            return resultArray(false, NULL, 'Could not get post shares.');
        }

        return resultArray(true, $row);
    }


    public function getPostSharesCount($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'count',
              network
            FROM posting_share
            WHERE posting_id = :posting_id
            GROUP BY network
        ";
        $values = array(
            ':posting_id' => $params['posting_id']
        );

        if(!$params['posting_id']) self::addError('invalid_posting_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            $totals = self::getTotalPostShares($params);
            return array(
                        'totals' => $data,
                        'total' => ($totals ? $totals['total'] : null)
                    );
        }

        return null;
    }


    public function getTotalPostShares($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'total'
            FROM posting_share
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