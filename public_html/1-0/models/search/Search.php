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

    public function findProducts($params = array()) {
        $values = Array();
        $values[':q'] = '%' . $params['q'] . '%';

        $terms = explode(' ', $params['q']);
        $searchString = '';
        foreach($terms as $x=>$term) {
            if(!$x)
                $searchString .= '\'%' . $term . '%\'';
            else
                echo '';//$searchString .= ' OR tags.value LIKE '.'\'%' . $term . '%\'';
        }

        $cat_where = "";
        if (!empty($params['category_id'])) {
            $cat_where .= "AND category_product.id_category = ".$params['category_id'];
        }

        $offset_limit = $this->generateLimitOffset($params, true);

        $q = "
              SELECT DISTINCT product.*, product_lang.name as product_name, user_username.username, user_username.location, user_username.avatar
              , category_product.id_category
              FROM offline_commerce_v1_2013.product
                INNER JOIN offline_commerce_v1_2013.product_lang ON product_lang.id_product = product.id_product
                INNER JOIN user_username ON product.user_id = user_username.user_id
                INNER JOIN offline_commerce_v1_2013.category_product ON category_product.id_product = product.id_product
              WHERE CONCAT(design_description, product_lang.name, user_username.username) LIKE {$searchString}
                AND product.active = 1
                {$cat_where}
              GROUP BY product_lang.id_product
              {$offset_limit}
        ";

        try {
            $data = $this->fetch($q, $values);
            foreach($data as $x=>$product) {
                $values = array(
                    ':idProduct'=>$product['id_product']
                );
                $q = "
                    SELECT *
                    FROM offline_commerce_v1_2013.product_file
                    WHERE product_id = :idProduct
                ";
                $data[$x]['product_images'] = $this->fetch($q, $values);
            }

            return Array('products'=>$data);
        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get search results". $e->getMessage());
        }
    }

    public function fastPosts($params = Array()) {
        $values = Array();
        $values[':q'] = '%' . $params['q'] . '%';

        $terms = explode(' ', $params['q']);
        $searchString = '';
        foreach($terms as $x=>$term) {
            if(!$x)
                $searchString .= '\'%' . $term . '%\'';
            else
                echo '';//$searchString .= ' OR tags.value LIKE '.'\'%' . $term . '%\'';
        }

        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked
                , IF(follow.follow_id IS NULL, 0, 1) AS is_following';
            $sub_join_str = '
                LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id
                    AND posting_like.user_id = :viewer_user_id
                LEFT JOIN follow ON (posting.user_id = follow.user_id AND follow.follower_user_id = :viewer_user_id)
            ';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $offset_limit = $this->generateLimitOffset($params, true);

        $q = "
            SELECT post.*, posting.*
            , user_username.username, user_username.first_name, user_username.last_name, user_username.avatar
            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
            , (SELECT COUNT(*) FROM dahliawolf_v1_2013.comment WHERE post.posting_id = comment.posting_id) AS comments
            , (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
            , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
            , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posting.posting_id) AS `total_reposts`
            , 0 as 'is_repost'
            {$select_str}
            FROM (
                SELECT tags.tag_id, posting_tags.posting_id, tags.value
                FROM tags
                  INNER JOIN posting_tags ON tags.tag_id = posting_tags.tag_id
                WHERE tags.value LIKE {$searchString}
                GROUP BY posting_id
            ) AS post
            INNER JOIN posting ON post.posting_id = posting.posting_id
            LEFT JOIN user_username ON user_username.user_id = posting.user_id
            LEFT JOIN image ON posting.image_id = image.id
            {$sub_join_str}
            WHERE posting.deleted IS NULL
            ORDER BY total_likes DESC
            {$offset_limit}
        ";

        try {
            $data = $this->fetch($q, $values);

            return Array('posts'=>$data);
        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get search results". $e->getMessage());
        }
    }

    public function addSearchRecord($params) {
        if( isset($params['q']) ) {
            $values = Array();

            $values[':tag'] = $params['q'];
            $values[':userId'] = isset($params['viewer_user_id']) ? $params['viewer_user_id'] : '';

            $q = "
                  INSERT INTO product_searched (user_id, term)
                  VALUES (:userId, :tag)
            ";

            try {
                $data = $this->fetch($q, $values);
                return $data;

            } catch(Exception $e) {
                self::$Exception_Helper->server_error_exception("can not add tag". $e->getMessage());
            }
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