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

        $offset_limit = $this->generateLimitOffset($params, true);

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


    public function getAll($params = array())
    {

        $order_by_str = 'created DESC';
        $outer_order_by_str = 'created DESC';

        $inner_order_by_columns = array(
            'created',
            'total_likes',
            'total_votes',
            //'total_shares',
            //'total_views',
        );

        if (!empty($params['order_by'])) {
            if (in_array($params['order_by'], $inner_order_by_columns)) {
                $order_by_str = "{$params['order_by']} DESC";
            }
        }

        $outer_select_str = "";
        $select_str = '';
        $sub_join_str = '';
        $values = array();
        $sub_where_str = '';

        // Also don't show dislikes
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked';
            $sub_join_str = '
                LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id
                    AND posting_like.user_id = :viewer_user_id
            ';
            $values[':viewer_user_id'] = $params['viewer_user_id'];

            // Dislike
            $sub_join_str .= '
                LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id
                    AND posting_dislike.user_id = :viewer_user_id
            ';
            $sub_where_str .= ' AND posting_dislike.posting_id IS NULL';
        }

        // Search
        if (!empty($params['q'])) {
            $sub_where_str .= ' AND (posting.description LIKE :q OR user_username.username LIKE :q)';
            $values[':q'] = '%' . $params['q'] . '%';
        }

        // Since posting_id
        if (!empty($params['since_posting_id'])) {
            $sub_where_str .= 'AND posting.posting_id > :since_posting_id';
            $values[':since_posting_id'] = $params['where']['since_posting_id'];
        }


        // Hot (sort by likes within x days)
        if (!empty($params['like_day_threshold'])) {
            $outer_select_str = ', posting.day_threshold_likes';
            $hot_select_str = ', IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes';
            $sub_join_str .= '
                INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
            ';
            $order_by_str = 'day_threshold_likes DESC';

            $values[':like_day_threshold'] = $params['like_day_threshold'];

            // Only show posts within threshold
            $sub_where_str .= ' AND posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()';
            $group_by_str = 'GROUP BY posting.posting_id';
        }


        //valid filters
        $valid_filters = array(
            'following'     => ' AND follow.follower_user_id = :follower_user_id'
        );


        // Filter by following
        if ( !empty($params['filter_by']) && isset( $valid_filters[$params['filter_by']]) && !empty($params['follower_user_id'])) {
            $sub_join_str .= '
                INNER JOIN follow ON user_username.user_id = follow.user_id
            ';

            $filter = $valid_filters[$params['filter_by']];
            $sub_where_str .= "$filter";
            $values[':follower_user_id'] = $params['follower_user_id'];
        }

        // Timestamp
        if (!empty($params['timestamp'])) {
            $sub_where_str .= ' AND posting.created <= :timestamp';
            $values[':timestamp'] = $params['timestamp'];
        }

        $outer_where_str = '';
        $active_limit = (60*60*24)*30;

        $inner_offset_limit = $this->generateLimitOffset($params, true);

        if (!empty($params['filter']) && isset( $valid_filters[$params['filter']] )) {
            $filter = $valid_filters[$params['filter']];
            $sub_where_str .= "\n\t\t\t\t\t AND  {$filter}";

            //if($inner_order_by_str != 'created DESC') $inner_offset_limit = '';
        }

        //// limit the restult set to failsafe 300,
        if(count($values) == 0 && empty($inner_offset_limit)) $inner_offset_limit = ' LIMIT 999';


        $query = "
                SELECT posting.*
                    , IFNULL(COUNT(comment.comment_id), 0) AS comments
                    , imageInfo.baseurl, imageInfo.attribution_url, site.domain, site.domain_keyword
                    {$outer_select_str}
                FROM (
                        SELECT posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.description, posting.deleted
                            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
                            , user_username.username, user_username.location, user_username.avatar
                            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                            , IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
                            , IF(UNIX_TIMESTAMP(posting.created)+$active_limit > UNIX_TIMESTAMP(), 1, 0 ) AS is_active
                            , FROM_UNIXTIME(UNIX_TIMESTAMP(posting.created)+$active_limit, '%c/%e/%Y') AS 'expiration_date'
                            , (SELECT COUNT(*) FROM posting_like WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`

                            {$select_str}
                            {$hot_select_str}
                        FROM posting
                            INNER JOIN image ON posting.image_id = image.id
                            INNER JOIN user_username ON posting.user_id = user_username.user_id
                            LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id

                            {$sub_join_str}
                        WHERE image.imagename IS NOT NULL
                            AND posting.deleted IS NULL
                             {$sub_where_str}
                        {$group_by_str}
                        ORDER BY {$order_by_str}
                        {$inner_offset_limit}
                ) AS posting

                LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON posting.repo_image_id = imageInfo.id
                LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id

                {$outer_where_str}
                GROUP BY posting.posting_id
                ORDER BY {$order_by_str}
                ";

        if (isset($_GET['t'])) {
            print_r($params);
            echo "\n$query\n";
            print_r($values);
            if(isset($_GET['die']))die();
        }

        //$rows = $this->get_all($this->table);
        $posts = $this->fetch($query, $values);

        if (empty($posts)) {
            return array(
                        'error' => 'Could not get posts.'
            );

        }

        if (isset($_GET['t'])) { echo sprintf("result count %s\n\n", count($posts)); }



        if (empty($result)) {
            return array(
                        'posts' => $posts
            );

        }


    }



    public function getByUser($params = array())
    {
        if (isset($_GET['t'])) { print_r($params); }

        $user_id = $params['user_id'];

        if (!$user_id || empty($user_id)) {
            return array('error' => 'user id is required');
        }

        // we got user cont..
        $order_by_str = 'created DESC';
        $outer_order_by_str = 'created DESC';



        $outer_select_str = "";
        $select_str = '';
        $sub_join_str = '';
        $values = array();
        $sub_where_str = '';
        $group_by_str = '';

        $sub_where_str .= ' AND posting.user_id = :user_id';
        $values[':user_id'] = $user_id;


        {// Dislike  -- don't show dislikes
            $sub_join_str .= '
                LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id
                    AND posting_dislike.user_id = :user_id
            ';
            $sub_where_str .= ' AND posting_dislike.posting_id IS NULL';
        }

        {// filters
            $valid_filters = array(
                'is_winner'     => '(IF(like_winner.like_winner_id IS NOT NULL, 1, 0)) = 1',
                'is_not_winner' => '(IF(like_winner.like_winner_id IS NOT NULL, 1, 0)) = 0',
                'is_active'     => "(IF(UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP(), 1, 0 )) = 1",
                'is_expired'    => "(IF(UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP(), 1, 0 )) = 0",
            );

            if (!empty($params['filter']) && isset( $valid_filters[$params['filter']] )) {
                $filter = $valid_filters[$params['filter']];
                $sub_where_str .= "\n\t\t\t\t\t AND  {$filter}";
            }
        }


        $outer_where_str = '';
        $active_limit = (60*60*24)*30;

        $inner_offset_limit = $this->generateLimitOffset($params, true);


        //// limit the restult set to failsafe
        if(count($values) == 0 && empty($inner_offset_limit)) $inner_offset_limit = ' LIMIT 999';


        {//sorts
            $order_by_columns = array(
                'created',
                'total_likes',
                'total_votes',
                'total_shares',
                'total_views',
            );

            //// slow sorts
            $slow_count_select = "";
            $fast_count_select ="
                           , (SELECT COUNT(*) FROM
                                        (SELECT * FROM posting_view WHERE posting_view.user_id = :user_id) AS posting_view_tmp
                                WHERE posting_view_tmp.posting_id = posting.posting_id) AS `total_views`
                           , (SELECT COUNT(*) FROM posting_like WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
                           , (SELECT COUNT(*) FROM posting_share WHERE posting_id = posting.posting_id) AS `total_shares`";
            $slow_sorts = array(
                'total_likes',
                'total_shares',
                'total_views',
            );
            if (!empty($params['order_by']) && in_array( $params['order_by'], $slow_sorts  )) {
                $slow_count_select = $fast_count_select;
                $fast_count_select = '';
                $slow_sort = true;;
            }

            //
            if (!empty($params['order_by'])) {
                if (in_array($params['order_by'], $order_by_columns)) {
                    if($slow_sort) $order_by_str = "{$params['order_by']} DESC";
                    else  $outer_order_by_str = "{$params['order_by']} DESC";

                    $outer_order_by_str = $order_by_str;
                }
            }
        }

        $query = "
                SELECT posting.*
                    , IFNULL(COUNT(comment.comment_id), 0) AS comments
                    , imageInfo.baseurl, imageInfo.attribution_url, site.domain, site.domain_keyword
                    {$fast_count_select}
                    {$outer_select_str}
                FROM (
                        SELECT posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.description, posting.deleted
                            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
                            , user_username.username, user_username.location, user_username.avatar
                            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                            , IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
                            , IF(UNIX_TIMESTAMP(posting.created)+$active_limit > UNIX_TIMESTAMP(), 1, 0 ) AS is_active
                            , FROM_UNIXTIME(UNIX_TIMESTAMP(posting.created)+$active_limit, '%c/%e/%Y') AS 'expiration_date'
                            {$slow_count_select}
                            {$select_str}
                        FROM posting
                        INNER JOIN image ON posting.image_id = image.id
                        INNER JOIN user_username ON posting.user_id = user_username.user_id
                        LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id

                            {$sub_join_str}
                        WHERE image.imagename IS NOT NULL
                            AND posting.deleted IS NULL
                            {$sub_where_str}
                        {$group_by_str}
                        ORDER BY {$order_by_str}
                        {$inner_offset_limit}
                ) AS posting

                LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON posting.repo_image_id = imageInfo.id
                LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id

                {$outer_where_str}
                GROUP BY posting.posting_id
                ORDER BY {$outer_order_by_str}
                ";

        if (isset($_GET['t'])) {
            echo "\n<pre>$query</pre>\n";
            print_r($values);
            if(isset($_GET['die']))die();
        }

        //$rows = $this->get_all($this->table);
        $posts = $this->fetch($query, $values);

        if (isset($_GET['t'])) { echo sprintf("result count %s\n\n", count($posts)); }

        if (empty($posts)) {
            return array(
                        'error' => 'Could not get posts.'
            );
        }


        return array(
                    'posts' => $posts
        );

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