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




    public function getLovers($params = array())
    {
        $order_by_str = 'main.created DESC';
        $order_by_columns = array(
			'created',
            'total_likes',
            'total_votes',
            'total_shares',
            'total_views',
		);

        if (!empty($params['order_by'])) {
			if (in_array($params['order_by'], $order_by_columns)) {
				$order_by_str = "main.{$params['order_by']} DESC";
			}
		}


		$values = array();

        $user_id = $params['user_id'];
        $posting_id = $params['posting_id'];

        $offset_limit = '';
        if (!empty($params['limit'])) {
            $offset_limit .= ' LIMIT ' . (int)$params['limit'];
        }
        if (!empty($params['offset'])) {
            $offset_limit .= ' OFFSET ' . (int)$params['offset'];
        }

        //// limit the restult set to failsafe 300,
        if(count($values) == 0 && empty($inner_offset_limit)) $inner_offset_limit = ' LIMIT 999';

        $where_str = 'main.posting_id = :posting_id';
        $values[':posting_id'] = $posting_id;


        if($params['viewer_user_id'])
        {
            $select_str = ", IF(follow.user_id IS NULL, 0, 1) AS is_followed
                           , DATE_FORMAT(follow.created, '%c/%e/%Y') AS loved_date ";
            $join_str = 'LEFT JOIN follow ON (user_username.user_id = follow.user_id
                                                    AND follow.follower_user_id = :viewer_user_id)';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $query = "
                SELECT
                user_username.user_id,
                user_username.username, user_username.location, user_username.avatar
                {$select_str}

                FROM posting_like main
                    INNER JOIN user_username ON main.user_id = user_username.user_id
                    {$join_str}
                WHERE
                {$where_str}
                ORDER BY {$order_by_str}
                {$offset_limit}
      			";

        if (isset($_GET['t'])) {
			print_r($params);
			echo "$query\n";
			print_r($values);
            //die();
		}

        try {
            $data = $this->fetch($query, $values);
            return array('lovers' => $data );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }

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


    public function promotePost($request_data = array())
    {
        $promote = new Posting_Promote();
        return $promote->create($request_data);
    }


}

?>