<?php
class Search extends _Model
{
    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function findMembers($params = array())
    {
        $values[':q'] = '%' . $params['q'] . '%';

        $relevantJoin = '';
        $relevantWhere = '';
        $from = "";
        if(isset($params['user_id'])) {
            $values[':userId'] = $params['user_id'];
            $relevantJoin = "LEFT JOIN follow ON follower_user_id = :userId";
            $relevantWhere = "";
            $from = "
                SELECT user_username.user_id, 1 AS is_followed
                FROM follow
                  LEFT JOIN user_username ON user_username.user_id = follow.user_id
                  AND user_username.username LIKE :q OR user_username.first_name LIKE :q
                WHERE follow.follower_user_id = :userId AND user_username.username IS NOT NULL
                GROUP BY user_username.user_id
                ";
        } else {
            $from = "SELECT user_username.user_id
                FROM user_username
                WHERE user_username.username LIKE :q OR user_username.first_name LIKE :q AND user_username.username IS NOT NULL
                GROUP BY user_username.user_id
                ";
        }
        $offset_limit = $this->generateLimitOffset($params, true);

        $q = "
            SELECT u.*, user_username.username, user_username.first_name, user_username.last_name, user_username.avatar
            , (SELECT COUNT(*)
                FROM follow
                WHERE follow.follower_user_id = user_username.user_id
            ) AS following
            , (
              SELECT COUNT(*)
              FROM follow
              WHERE follow.user_id = user_username.user_id
            ) AS followers
            FROM (
                {$from}
                {$offset_limit}
            )AS u
            LEFT JOIN user_username ON user_username.user_id = u.user_id
            ORDER BY followers DESC
        ";

        try {
            $data = $this->fetch($q, $values);

            $dw_user = new User($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE);

            foreach($data as $x=>$user) {
                $data[$x]['itemCount'] = $dw_user->getItemCount($user['user_id']);
            }
            return $data;
        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get search results". $e->getMessage());
        }

    }

    protected function generateLimitOffset($params, $offset=true)
    {
        $limit_offset_str = '';
        if (!empty($params['limit'])) {
            $limit_offset_str .= ' LIMIT ' . (int)$params['limit'];
        }
        if ($offset && !empty($params['offset'])) {
            $limit_offset_str .= ' OFFSET ' . (int)$params['offset'];
        }

        return $limit_offset_str;
    }
}
?>