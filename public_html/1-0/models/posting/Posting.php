<?php
 
class Posting extends _Model
{

    const  ACTIVITY_ENTITY_ID = 6;
    const  ACTIVITY_ID_POSTED_IMAGE = 6;

    protected $points_earned=0;

    protected $fields = array(
        'created',
        'user_id',
        'image_id',
        'description',
        'deleted',
    );

    const TABLE = 'posting';
    const PRIMARY_KEY_FIELD = 'posting_id';

    private $table = self::TABLE;


    public function getPointsEarned()
    {
        return $this->points_earned;
    }

    protected function setPointsEarned($points)
    {
        $this->points_earned= (int ) $points;
    }

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


    /**
     * @Alias for addPost
     */

    public function addPostingFromBankImage($params)
    {
        return self::addPost($params);
    }


    public function addPost($params = array())
    {
        self::trace("saving posting data:: " . var_export($params, true) );
        $insert_id =  $this->save($params);

   		if (empty($insert_id)) {
   			return array('error' => 'Could not add posting.');
   		}

        self::addUserPoint(array( 'user_id' => $params['user_id'], 'new_posting_id' => $insert_id ));

        // Log activity
        //log_activity($params['user_id'], 6, 'Posted an image', 'posting', $new_post_data['data']['posting_id']);
        self::logActivity($params['user_id'], $insert_id, $note="Posted an image",  $entity = 'posting', $activity_id=self::ACTIVITY_ID_POSTED_IMAGE);

   		return $insert_id;
   	}


    protected function addUserPoint($params)
    {
        // Credit user points
        $user_point = new User_Point();
        $point_data = array(
            'user_id' => $params['user_id'],
            'point_id' => 3,
            'posting_id' => $params['new_posting_id'],
        );

        $user_point->addPoint($point_data);
        $points_earned = $user_point->getPointsEarned();

        self::setPointsEarned($points_earned);

        return $points_earned;
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
                main.created,
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

    public function getReposters($params = array())
    {
        $order_by_str = 'main.created_at DESC';
        $order_by_columns = array(
            'created_at',
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
                main.created_at,
                user_username.user_id,
                user_username.username, user_username.location, user_username.avatar
                {$select_str}

                FROM posting_repost main
                    INNER JOIN user_username ON main.repost_user_id = user_username.user_id
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
            return array('reposters' => $data );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }

    }

    public function getAll($params = array())
    {
        if( !empty($params['filter']) && $params['filter'] == 'loves')
        {
            return self::getLovedPosts($params);
        }

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
            /*$sub_join_str .= '
                LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id
                    AND posting_dislike.user_id = :viewer_user_id
            ';
            $sub_where_str .= ' AND posting_dislike.posting_id IS NULL';*/
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
            $sub_join_str .= 'INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id';
            $order_by_str = 'day_threshold_likes DESC';

            $values[':like_day_threshold'] = $params['like_day_threshold'];

            // Only show posts within threshold
            $sub_where_str .= ' AND posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()';
            $group_by_str = 'GROUP BY posting.posting_id';
        }

        //valid filters
        $valid_filters = array(
            'following'  => " AND follow.follower_user_id = :follower_user_id",
            'loves'      => " AND posting_like.user_id IS NOT NULL"
        );

        // Filter by following
        if ( !empty($params['filter']) && isset( $valid_filters[$params['filter']]) ) {
            $sub_join_str .= 'INNER JOIN follow ON user_username.user_id = follow.user_id';

            if( $params['filter'] == 'loves')
            $sub_join_str .= 'INNER JOIN (select *
                            FROM  posting_like
                            WHERE posting_like.user_id = 2418) AS user_loves ON user_loves.posting_id = posting
            ';

            $filter = $valid_filters[$params['filter']];
            $sub_where_str .= "$filter";

            if(!empty($params['follower_user_id']))
            {
                $values[':follower_user_id'] = $params['follower_user_id'];
            }
        }

        // Timestamp
        if (!empty($params['timestamp'])) {
            $sub_where_str .= ' AND posting.created <= :timestamp';
            $values[':timestamp'] = $params['timestamp'];
        }

        $outer_where_str = '';
        $active_limit = (60*60*24)*30;

        $inner_offset_limit = $this->generateLimitOffset($params, true);


        //// limit the restult set to failsafe 300,
        if(count($values) == 0 && empty($inner_offset_limit)) $inner_offset_limit = ' LIMIT 999';


        $query = "
                SELECT posting.*
                    , (SELECT COUNT(*) FROM dahliawolf_v1_2013.comment WHERE posting.posting_id = comment.posting_id) AS comments
                    , imageInfo.baseurl, imageInfo.attribution_url, site.domain, site.domain_keyword
                    {$outer_select_str}
                FROM (

                    (
                        SELECT posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.description, posting.deleted
                            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
                            , user_username.username, user_username.location, user_username.avatar
                            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                            /*, IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
                            , IF(UNIX_TIMESTAMP(posting.created)+$active_limit > UNIX_TIMESTAMP(), 1, 0 ) AS is_active
                            , FROM_UNIXTIME(UNIX_TIMESTAMP(posting.created)+$active_limit, '%c/%e/%Y') AS 'expiration_date'*/
                            , (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
                            , (SELECT COUNT(*) FROM posting_tag   WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`
                            , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
                            , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posting.posting_id) AS `total_reposts`
                            , 0 as 'is_repost'
                            {$select_str}
                            {$hot_select_str}
                        FROM posting
                            INNER JOIN image ON posting.image_id = image.id
                            INNER JOIN user_username ON posting.user_id = user_username.user_id
                            /*LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id*/

                            {$sub_join_str}
                        WHERE image.imagename IS NOT NULL
                            AND posting.deleted IS NULL
                            {$sub_where_str}
                        {$group_by_str}
                    )

                    ORDER BY {$order_by_str}
                    {$inner_offset_limit}
                ) AS posting

                #LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON posting.repo_image_id = imageInfo.id
                LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id

                {$outer_where_str}
                #GROUP BY posting.posting_id
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

        if($posts)
        {
            return $posts;
        }

        return null;
    }

    public function getFollowingPosts($params = array())
    {
        $select_str = '';
        $sub_join_str = '';

        $inner_offset_limit = $this->generateLimitOffset($params, true);
        $values[':userId'] = $params['user_id'];
        $values['viewer_user_id'] = $params['user_id'];
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked';
            $sub_join_str = '
                LEFT JOIN posting_like ON posts.posting_id = posting_like.posting_id
                    AND posting_like.user_id = :viewer_user_id
            ';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $query = "

            SELECT posts.*
            , user_username.username, user_username.location, user_username.avatar, user_username.first_name, user_username.last_name
            , (SELECT COUNT(*) FROM dahliawolf_v1_2013.comment WHERE posts.posting_id = comment.posting_id) AS comments
            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
            , (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posts.posting_id) AS `total_likes`
            , (SELECT COUNT(*) FROM posting_tag   WHERE posting_tag.posting_id = posts.posting_id) AS `total_tags`
            , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posts.posting_id) AS total_shares
            , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posts.posting_id) AS `total_reposts`
            , 0 as 'is_repost'
            , IF(follow.follow_id IS NULL, 0, 1) AS is_following
            {$select_str}
            FROM
            (
                SELECT posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.deleted
                FROM posting
                  LEFT JOIN follow ON follow.follower_user_id = :userId
                WHERE posting.user_id = follow.user_id AND follow.follower_user_id = :userId AND posting.deleted IS NULL
                GROUP BY posting.posting_id
                ORDER BY posting.created DESC
                {$inner_offset_limit}
            ) AS posts
            INNER JOIN user_username ON user_username.user_id = posts.user_id
            INNER JOIN image ON posts.image_id = image.id
            LEFT JOIN follow ON (posts.user_id = follow.user_id AND follow.follower_user_id = :viewer_user_id)
            {$sub_join_str}
        ";
        //$values[':like_day_threshold'] = $params['like_day_threshold'];
        //$rows = $this->get_all($this->table);
        $posts = $this->fetch($query, $values);

        if (empty($posts)) {
            return array(
                'error' => 'Could not get posts.'
            );
        }

        if (isset($_GET['t'])) { echo sprintf("result count %s\n\n", count($posts)); }

        if($posts)
        {
            return $posts;
        }

        return null;
    }

    public function getAllTest($params = array())
    {
        $select_str = '';
        $sub_join_str = '';

        $inner_offset_limit = $this->generateLimitOffset($params, true);
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked';
            $sub_join_str = '
                LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id
                    AND posting_like.user_id = :viewer_user_id
            ';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
        }

        $query = "
            SELECT user_username.username, user_username.location, user_username.avatar, user_username.first_name, user_username.last_name
            , posting.*
            , (SELECT COUNT(*) FROM dahliawolf_v1_2013.comment WHERE posting.posting_id = comment.posting_id) AS comments
            , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
            , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
            , (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
            , (SELECT COUNT(*) FROM posting_tag   WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`
            , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
            , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posting.posting_id) AS `total_reposts`
            , 0 as 'is_repost'
            , IF(follow.follow_id IS NULL, 0, 1) AS is_following
            {$select_str}
            FROM (
                SELECT posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.deleted, IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes
                FROM dahliawolf_v1_2013.posting
                INNER JOIN dahliawolf_v1_2013.posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
                WHERE posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()
                AND posting.deleted IS NULL
                GROUP BY posting.posting_id
                ORDER BY day_threshold_likes DESC
                {$inner_offset_limit}
            ) AS posting
            INNER JOIN user_username ON posting.user_id = user_username.user_id
            INNER JOIN image ON posting.image_id = image.id
            LEFT JOIN follow ON (posting.user_id = follow.user_id AND follow.follower_user_id = :viewer_user_id)
            {$sub_join_str}
        ";
        $values[':like_day_threshold'] = $params['like_day_threshold'];
        //$rows = $this->get_all($this->table);
        $posts = $this->fetch($query, $values);

        if (empty($posts)) {
            return array(
                'error' => 'Could not get posts.'
            );
        }

        if (isset($_GET['t'])) { echo sprintf("result count %s\n\n", count($posts)); }

        if($posts)
        {
            return $posts;
        }

        return null;
    }


    public  function getLovedPosts($params)
    {
        $active_limit = (60*60*24)*30;
        $values = array();
        $viewer_user_id = $params["viewer_user_id"];
        $inner_offset_limit = $this->generateLimitOffset($params, true);

        $order_by_str = 'posting_like.created DESC';

        $inner_order_by_columns = array(
            'posting_like.created',
            'total_likes',
        );

        if (!empty($params['order_by'])) {
            if (in_array($params['order_by'], $inner_order_by_columns)) {
                $order_by_str = "{$params['order_by']} DESC";
            }
        }

        $query = "
          SELECT
              posting.posting_id, posting.created, posting.user_id, posting.image_id, posting.description, posting.deleted
              , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
              , user_username.username, user_username.location, user_username.avatar
              , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
              , IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
              , IF(UNIX_TIMESTAMP(posting.created)+$active_limit > UNIX_TIMESTAMP(), 1, 0 ) AS is_active
              , FROM_UNIXTIME(UNIX_TIMESTAMP(posting.created)+$active_limit, '%c/%e/%Y') AS 'expiration_date'
              , (SELECT COUNT(*) FROM posting_like WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
              , (SELECT COUNT(*) FROM posting_tag WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`
              , IF(posting_like.user_id IS NULL, 0, 1) AS is_liked

          FROM posting

                INNER JOIN image ON posting.image_id = image.id
                INNER JOIN user_username ON posting.user_id = user_username.user_id
                LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id

                INNER JOIN (select *
                    from  posting_like
                    where posting_like.user_id = :viewer_user_id
                ) AS posting_like ON posting_like.posting_id = posting.posting_id

                LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id
        						AND posting_dislike.user_id = :viewer_user_id

          WHERE image.imagename IS NOT NULL
        	  AND posting.deleted IS NULL
              AND posting_dislike.posting_id IS NULL

              AND posting_like.user_id IS NOT NULL

        ORDER BY {$order_by_str}
        {$inner_offset_limit}
        ";

        $values[':viewer_user_id'] = $viewer_user_id;

        if (isset($_GET['t'])) {
            print_r($params);
            echo "\n$query\n";
            print_r($values);
            if(isset($_GET['die']))die();
        }


        $posts = $this->fetch($query, $values);

        if ($posts) {
            return $posts;

            return array(
                        'posts' => $posts
            );

        }

        return null;

    }


    public function getByIdsArray($params = array())
    {
        if (isset($_GET['t'])) { print_r($params); }

        $ids_array  = $params['posting_ids'];
        $user_id    = $params['user_id'];

        if (!is_array($ids_array) || count($ids_array) == 0) {
            return array('error' => 'posting ids are required');
        }

        $outer_where_str = "";

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
        $sub_where_str = sprintf('AND posting.posting_id IN (%s)', implode(",", $ids_array ));



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


        // Timestamp
        if (!empty($params['timestamp'])) {
            $sub_where_str .= ' AND posting.created <= :timestamp';
            $values[':timestamp'] = $params['timestamp'];
        }

        //$outer_where_str = '';
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
                            , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
                            , (SELECT COUNT(*) FROM posting_view WHERE posting_view.posting_id = posting.posting_id) AS total_views
                            , (SELECT COUNT(*) FROM posting_tag WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`


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


    // ?api=category&function=getCategory&params={"conditions":{"id":"4"}}
    public function getPostDetails($params = array())
    {
        $posting_id     = $params['posting_id'];
        $viewer_user_id = $params['viewer_user_id'];

        if(!$posting_id){
            // should always return the top most liked in last 30 days
            $next = $this->getNextId(null, null, null, $viewer_user_id  );

            $params['posting_id'] = $next;
            $post = $this->_getPostDetails($params);

        }else{
            $post = $this->_getPostDetails($params);
        }

        // Also return previous and next
        /*if ($post)
        {
            $previous   = $this->getPreviousId($post['posting_id'], $post['created'], $post['likes'], $viewer_user_id );
            $next       = $this->getNextId($post['posting_id'], $post['created'], $post['likes'], $viewer_user_id, $previous  );

            $post['previous_posting_id'] = $previous;
            $post['next_posting_id'] = $next;
        }*/

        return $post;
    }


    protected function _getPostDetails($params)
    {
        $posting_id     = $params['posting_id'];

        $values = array();

        $from_prefix = 'posting';
        $posting_id = null;
        if (!empty($params['posting_id'])) {
            $values[':posting_id'] = $params['posting_id'];
        }

        $posting_id = $params['posting_id'];

        $select_str = '';
        $join_str = '';
        // Viewer (show if posts are liked/voted in relation)
        // Also show if user is following post user
        $viewer_user_id = null;
        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked,IF(posting_repost.repost_user_id IS NULL, 0, 1) AS is_repost, IF(follow.follow_id IS NULL, 0, 1) AS is_following';
            $join_str = '
                LEFT JOIN posting_like ON (posting.posting_id = posting_like.posting_id
                    AND posting_like.user_id = :viewer_user_id)
                LEFT JOIN posting_repost ON (posting.posting_id = posting_repost.posting_id
                    AND posting_repost.repost_user_id = :viewer_user_id)
                LEFT JOIN follow ON (posting.user_id = follow.user_id
                    AND follow.follower_user_id = :viewer_user_id)
            ';
            $values[':viewer_user_id'] = $params['viewer_user_id'];
            $viewer_user_id = $params['viewer_user_id'];
        }

        $query = "
            SELECT posting.*,
                image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height,
                IFNULL(image.attribution_url, imageInfo.attribution_url) AS image_attribution_url,
                image.domain AS image_attribution_domain
                , user_username.username, user_username.avatar, user_username.location, user_username.verified
                , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                , IFNULL(COUNT(pl.posting_like_id), 0) AS likes
                , imageInfo.baseurl
                , site.domain_keyword
                , IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
                , product.id_product AS product_id, product.status, product.price, product.wholesale_price
                , (SELECT COUNT(*) FROM comment WHERE comment.posting_id = posting.posting_id) AS comments
                , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
                , (SELECT COUNT(*) FROM posting_view WHERE posting_view.posting_id = posting.posting_id) AS total_views
                , (SELECT COUNT(*) FROM posting_tag WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`
                , image.repo_image_id
                " . $select_str . "
            FROM " . $from_prefix . "
                INNER JOIN image ON posting.image_id = image.id
                INNER JOIN user_username ON posting.user_id = user_username.user_id
                LEFT JOIN posting_product as posting_product ON posting.posting_id = posting_product.posting_id
                LEFT JOIN offline_commerce_v1_2013.product AS product ON posting_product.product_id = product.id_product
                LEFT JOIN posting_like AS pl ON posting.posting_id = pl.posting_id
                LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON image.repo_image_id = imageInfo.id
                LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id
                LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
                " . $join_str
             . (!empty($params['posting_id']) ? 'WHERE posting.posting_id = :posting_id' : '') . "
        ";
        if (isset($_GET['t'])) {
            echo $query;
            print_r($values);
        }


        $data = $this->query($query, $values);

        $post = $data[0];

        if ($data !== false)
        {
            return $post;
        }

        return null;
    }


    public function getPostingEntity($params)
    {
        $posting_id     = $params['posting_id'];
        $posting_id = $params['posting_id'];
        $values = array(':posting_id' => $posting_id);

        $table_alias = 'posting';

        $query = "
            SELECT {$table_alias}.*
            FROM {$table_alias}

            WHERE posting.posting_id = :posting_id
        ";

        if (isset($_GET['t'])) {
            echo $query;
            print_r($values);
        }


        $data = $this->fetch($query, $values);
        $post = $data[0];

        if ($data !== false)
        {
            return $post;
        }

        return null;
    }

    public function getByUser($params = array())
    {
        if (isset($_GET['t'])) { print_r($params); }

        $user_id = $params['user_id'];

        if (!$user_id || empty($user_id)) {
            return array('error' => 'user id is required');
        }

        $select_str = '';
        $sub_join_str = '';
        $values = array();
        $sub_where_str = '';
        $post_img_info = ', image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height';
        $post_img_join = 'LEFT JOIN image ON id = posting.image_id';
        $fast_count_select ="
                           , (SELECT COUNT(*) FROM posting_like WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
                           , (SELECT COUNT(*) FROM posting_share WHERE posting_id = posting.posting_id) AS `total_shares`
                           , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posting.posting_id) AS `total_reposts`
                           ";

        $sub_where_str .= ' AND posting.user_id = :user_id';
        $values[':user_id'] = $user_id;

        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked, IF(follow.follow_id IS NULL, 0, 1) AS is_following';
            $sub_join_str = '
      				LEFT JOIN posting_like ON (posting.posting_id = posting_like.posting_id
      					AND posting_like.user_id = :viewer_user_id)
      				LEFT JOIN follow ON (posting.user_id = follow.user_id
      					AND follow.follower_user_id = :viewer_user_id)
      			';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
            $viewer_user_id = $params['viewer_user_id'];
        }

        $inner_offset_limit = $this->generateLimitOffset($params, true);

        $query = "
                    SELECT posting.*
                    , user_username.avatar, user_username.username, user_username.first_name, user_username.last_name
                    , IFNULL(COUNT(comment.comment_id), 0) AS comments
                    , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                    {$select_str}
                    {$post_img_info}
                    {$fast_count_select}
                    FROM
                    (
                        (
                          SELECT posting.*
                          , 0 as 'is_repost'
                          FROM posting
                          WHERE user_id = :user_id
                            AND posting.posting_id IS NOT NULL
                            AND posting.deleted IS NULL
                          ORDER BY posting.created DESC
                        )
                        UNION ALL
                        (
                          SELECT posting.*
                          , 1 as 'is_repost'
                          FROM posting_repost
                            LEFT JOIN posting ON posting.posting_id = posting_repost.posting_id
                          WHERE repost_user_id = :user_id
                            AND posting_repost.posting_repost_id IS NOT NULL
                            AND posting.deleted IS NULL
                          ORDER BY posting.created DESC
                        )
                        ORDER BY created DESC
                        {$inner_offset_limit}
                    ) AS posting
                        {$post_img_join}
                        {$sub_join_str}
                        LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                        LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = posting.user_id
                    GROUP BY posting.posting_id
                    ORDER BY posting.created DESC
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

    public function getLovesByUser($params = array())
    {
        if (isset($_GET['t'])) { print_r($params); }

        $user_id = $params['user_id'];

        if (!$user_id || empty($user_id)) {
            return array('error' => 'user id is required');
        }

        $select_str = '';
        $sub_join_str = '';
        $values = array();
        $sub_where_str = '';
        $post_img_info = ', image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height';
        $post_img_join = 'LEFT JOIN image ON id = posting.image_id';
        $fast_count_select ="
                           , (SELECT COUNT(*) FROM posting_like WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`
                           , (SELECT COUNT(*) FROM posting_share WHERE posting_id = posting.posting_id) AS `total_shares`
                           , (SELECT COUNT(*) FROM posting_repost WHERE posting_repost.posting_id = posting.posting_id) AS `total_reposts`
                           ";

        $sub_where_str .= ' AND posting.user_id = :user_id';
        $values[':user_id'] = $user_id;

        if (!empty($params['viewer_user_id'])) {
            $select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked, IF(follow.follow_id IS NULL, 0, 1) AS is_following';
            $sub_join_str = '
      				LEFT JOIN posting_like ON (posting.posting_id = posting_like.posting_id
      					AND posting_like.user_id = :viewer_user_id)
      				LEFT JOIN follow ON (posting.user_id = follow.user_id
      					AND follow.follower_user_id = :viewer_user_id)
      			';

            $values[':viewer_user_id'] = $params['viewer_user_id'];
            $viewer_user_id = $params['viewer_user_id'];
        }

        $inner_offset_limit = $this->generateLimitOffset($params, true);

        $query = "
                    SELECT posting.*
                    {$select_str}
                    {$fast_count_select}
                    {$post_img_info}
                    , user_username.avatar, user_username.username, user_username.first_name, user_username.last_name
                    , IFNULL(COUNT(comment.comment_id), 0) AS comments
                    , CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                    FROM
                    (
                        SELECT posting_like.posting_id
                        FROM posting_like
                        WHERE user_id = :user_id
                        ORDER BY created DESC
                        {$inner_offset_limit}
                    ) AS posts
                        INNER JOIN posting ON posting.posting_id = posts.posting_id AND posting.deleted IS NULL
                        {$post_img_join}
                        {$sub_join_str}
                        LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                        LEFT JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = posting.user_id
                    GROUP BY posting.posting_id
                    ORDER BY posting.created DESC
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
            'posting_id' => $request_data['posting_id'],
            'user_id' => $request_data['user_id']
        );

        try {
            $insert_id = $this->db_update($update, 'posting_id = :posting_id AND user_id = :user_id', $where_values, false);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $request_data['posting_id'],
                    'deleted_date' => date('Y-m-d H:i:s')
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to delete posting.". $e->getMessage());
        }

    }


    public function getPostingBankImages($request_data)
    {
        $values['user_id'] = $request_data['user_id'];
        $extra_where_sql= "";

        if( (int) $request_data['limit_per_day'] == 1)
        {
            $extra_where_sql = "AND DATE(mt.created) = DATE(NOW())";
        }

        $query = "
            SELECT mt.*
              , imageInfo.baseurl, imageInfo.attribution_url
              , image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
            FROM {$this->table} as mt
              INNER JOIN image ON mt.image_id = image.id
              LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON image.repo_image_id = imageInfo.id
            WHERE mt.user_id = :user_id
              AND image.repo_image_id IS NOT NULL
              {$extra_where_sql}
        ";

        $data = $this->fetch($query, $values);
        self::trace( sprintf("$query\nvalues: %s\nQUERY RETURNED: %s results", var_export($values,true), count($data) ) );

        return $data;
    }


    public function promotePost($request_data = array())
    {
        $promote = new Posting_Promote();
        return $promote->create($request_data);
    }

    public function favePost($request_data = array())
    {
        $fave = new Posting_Fave();
        return $fave->create($request_data);
    }


    public function removeFave($request_data)
    {
        $fave = new Posting_Fave();
       return $fave->remove($request_data);
    }

    public function getUserFaves($request_data)
    {
        $fave = new Posting_Fave();
        return $fave->getUserFaves($request_data, $with_details=true);
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



    public function getPreviousId($posting_id, $created, $total_likes, $viewer_user_id = NULL, $previous_id =null)
    {
        $query = '
            SELECT * FROM
               (SELECT posting.posting_id
                       , IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes

               FROM posting
                   ' . (!empty($viewer_user_id) ? '
                       LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id AND posting_like.user_id = :viewer_user_id
                           LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id AND posting_dislike.user_id = :viewer_user_id
                   ' : '') . '
                       INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
               WHERE posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()
                   AND posting.posting_id != :posting_id
                   AND posting.deleted IS NULL
                   ' . (!empty($viewer_user_id) ? '
                       AND posting_like.user_id IS NULL
                       AND posting_dislike.posting_id IS NULL
                   ' : '') . '
               #, posting.posting_id DESC
               GROUP BY posting.posting_id
               ORDER BY day_threshold_likes DESC, posting.created ASC
               ) AS sub_posting

            WHERE day_threshold_likes >= :total_likes
            ORDER BY day_threshold_likes ASC
            LIMIT 1
   		';

        $values = array(
            ':posting_id' => $posting_id,
            //':created' => $created,
            ':like_day_threshold' => 30,
            ':total_likes' => $total_likes
        );


        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }


        if (isset($_GET['t'])) {
            echo $query;
            print_r($values);
        }

        try {
            $result = $this->fetch($query, $values);
        }catch (Exception $e){
            //
        }

        if ($result) {
            return $result[0]['posting_id'];
        }

   		return NULL;
   	}

    public function getNextId($posting_id, $created, $total_likes, $viewer_user_id = NULL, $previous_id =null)
    {
        $notin_posting_array = array();
        $notin_posting_array[] = ":posting_id";

        $filter_likes = "";
        if ($posting_id) {
            $filter_likes = "WHERE day_threshold_likes <= :total_likes";
            $notin_posting_array[] = ":posting_id";
        }

        //for when no posting id is sent
        if(count($notin_posting_array) === 0 ){
            $notin_posting_array[] = ":posting_id";
        }

        if ($previous_id) {
            $values[':previous_id'] = $previous_id;
        }

        $notin_posting_str =  trim(implode(", ", $notin_posting_array), ',');

        $query = "
        SELECT * FROM
               (SELECT posting.posting_id
            , IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes
            , posting.created
               FROM posting
                   " . (!empty($viewer_user_id) ? "
                       LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id AND posting_like.user_id = :viewer_user_id
                       LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id AND posting_dislike.user_id = :viewer_user_id
                   " : '') . "
                   INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
               WHERE posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()
                   AND posting.posting_id NOT IN ({$notin_posting_str})
                   AND posting.deleted IS NULL
                       " . (!empty($viewer_user_id) ? "
                   AND posting_like.user_id IS NULL
                   AND posting_dislike.posting_id IS NULL
                       " : '') . "
               GROUP BY posting.posting_id
               ORDER BY day_threshold_likes DESC, posting.created ASC
               ) AS sub_posting
           {$filter_likes}
           ORDER BY day_threshold_likes DESC
           LIMIT 1
        ";


        $values = array(
            ':posting_id' => $posting_id ? $posting_id : 0,
            ':like_day_threshold' => 30,
        );

        if ($posting_id) {
            $values[':total_likes'] = $total_likes;
        }

        if ($previous_id) {
            $values[':previous_id'] = $previous_id;
        }

        if ($created) {
            //$values[':created'] = $created;
        }

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }

        //$this->debug();
        if (isset($_GET['t'])) {
            echo "$query\n\n";
            var_dump($values);
        }

        try{
            $result = $this->fetch($query, $values);
        }catch (Exception $e)
        {
            //
        }

        if ($result) {
            return $result[0]['posting_id'];
        }


        return NULL;
    }


    private function logActivity($user_id, $entity_id, $note="Posted an image", $entity = 'posting', $activity_id=self::ACTIVITY_ID_POSTED_IMAGE )
    {
        $activity = array(
            'user_id' => $user_id,
            'activity_id' => $activity_id,
            'note' => $note,
            'api_website_id' => 2,
            'entity' => $entity,
            'entity_id' => $entity_id

        );

        $activity_log = new Activity_Log();
        $data = $activity_log::saveActivity( $activity );

        return $data;
    }



}

?>