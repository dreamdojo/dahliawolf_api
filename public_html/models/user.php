<?php

class User extends db {

	private $table = 'user_username';

	public function __construct() {
		parent::__construct();
	}

	// ?api=category&function=addcategory&params={"data":{"name":"asdf;sfcsd"}}
	public function addUser($params = array()) {
		$error = NULL;

		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$this->insert($this->table, $params['data']);

		$insert_id = $this->insert_id;

		if (empty($insert_id)) {
			return resultArray(false, NULL, 'Could not add user.');
		}

		return resultArray(true, array('user_id' => $insert_id));
	}

    public function addEmailHash($user_id) {
        $hash = md5( rand(0,1000) );
        $values = array(
            'hash'=>$hash,
            'user_id'=>$user_id
        );

    }

	// ?api=category&function=updatecategory&params={"data":{"name":"my test@"},"where":{"id":"4"}}
	public function updateUser($params = array()) {
		$error = NULL;

		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}
		else if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$res = $this->update($this->table, $params['data'], $params['where']);
		if ($res === false) {
			 return resultArray(false, NULL, 'Could not update user.');
		}

		return resultArray(true, $res);
	}

	// ?api=category&function=deletecategory&params={"where":{"id":"3"}}
	public function deleteUser($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$res = $this->delete($this->table, $params['where']);
		if ($res === false) {
			 return resultArray(false, NULL, 'Could not delete category.');
		}

		return resultArray(true, $res);
	}

	// ?api=category&function=getCategory&params={"conditions":{"id":"4"}}
	public function getUser($params = array()) {
		$error = NULL;

		/*
		$parameters = array(
			'conditions' => array(
				'id' => '3'
			)
		);
		echo '&params=' . json_encode($parameters);
		*/
		if (empty($params['conditions'])) {
			$error = 'Conditions are required.';
		}
		else if (!is_array($params['conditions'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$row = $this->get_row($this->table, $params['conditions']);
		if ($row === false) {
			 return resultArray(false, NULL, 'Could not get user.');
		}

		return resultArray(true, $row);
	}

	// ?api=category&function=allcategory
	public function allUser() {
		$rows = $this->get_all($this->table);
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get users.');
		}

		return resultArray(true, $rows);
	}

	public function get_user($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$where_str = '';
		$values = array();
		// user_id or username
		if (!empty($params['where']['user_id'])) {
			$where_str = 'user_username.user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else {
			$where_str = 'username = :username';
			$values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
		}

		$select_str = '';
		$join_str = '';
		// Optional viewer_user_id
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
			$join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

        $active_limit = (60*60*24)*30;

		$query = "SELECT
          user_username.*
            , (
                SELECT COUNT(*)
                FROM user_username AS u
                WHERE
                    u.points > user_username.points
            ) + 1 AS rank
            , (
                SELECT COUNT(*)
                FROM follow
                WHERE follow.follower_user_id = user_username.user_id
            ) AS following
            , (
                SELECT COUNT(*)
                FROM follow
                WHERE follow.user_id = user_username.user_id
            ) AS followers
            , (
                SELECT COUNT(*)
                FROM posting
                WHERE posting.user_id = user_username.user_id
            ) AS posts
            , (
                SELECT COUNT(*)
                FROM comment
                WHERE comment.user_id = user_username.user_id
            ) AS comments
            , (
                SELECT COUNT(*)
                FROM posting_like
                    INNER JOIN posting ON posting_like.posting_id = posting.posting_id
                WHERE posting.user_id = user_username.user_id
            ) AS likes
            ,(
                  SELECT
                  ml.name
                  FROM membership_level ml, user_username user
                  WHERE user.user_id = user_username.user_id
                  	AND CAST(user.points AS SIGNED)  / ml.points > 1
                  order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
                  LIMIT 1
              ) AS membership_level
              ,(
                  SELECT COUNT(*)
                  FROM posting
                  WHERE posting.user_id = user_username.user_id
                    AND posting.deleted IS NULL
                    AND UNIX_TIMESTAMP(posting.created)+2592000 < UNIX_TIMESTAMP()

              )AS posts_expired
              ,(
                  SELECT COUNT(*)
                  FROM posting
                  WHERE posting.user_id = user_username.user_id
                    AND posting.deleted IS NULL
                    AND UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP()

              ) AS posts_active
              ,(
                  SELECT COUNT(*)
                  FROM posting
                   LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
                  WHERE posting.user_id = user_username.user_id  AND like_winner.like_winner_id IS NOT NULL
              ) AS winner_posts
              ,(
                SELECT COUNT(*)
                FROM posting
                WHERE posting.user_id = user_username.user_id
                    AND posting.deleted IS NULL
            ) AS posts_total

                {$select_str}
			FROM user_username
		        {$join_str}
			WHERE {$where_str}
			LIMIT 1";


        if(isset($_GET['t']))
        {
            echo sprintf("query: \n%s\n", $query);
        }

        $result = $this->run($query, $values);
        $rows = $result->fetchAll();

        if (empty($rows)) {
            return resultArray(false, NULL, 'Could not get user.');
        }
		else {
	        $followers = self::get_followers($params);
	        if(@count($followers['data'])>0) $rows[0]['followers'] = count($followers['data']);
	        else $rows[0]['followers'] = 0;
		}

		return resultArray(true, $rows[0]);
	}

	public function get_users($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$where_str = '';
		$values = array();
		// user_id or username
		if (!empty($params['where']['user_id'])) {
			$where_str = 'user_username.user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else if (!empty($params['where']['username_like'])) {

			$where_str = 'user_username.username LIKE :username_like';
			$values[':username_like'] = '%' . $params['where']['username_like'] . '%';
		}
		else if (!empty($params['where']['username'])) {
			$where_str = 'username = :username';
			$values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
		}

		$select_str = '';
		$join_str = '';
		// Optional viewer_user_id
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
			$join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

		$query = '
			SELECT user_username.*
			, (
					SELECT COUNT(*)
					FROM user_username AS u
					WHERE
						u.points > user_username.points
				) + 1 AS rank
				, (
					SELECT COUNT(*)
					FROM follow
					WHERE follow.follower_user_id = user_username.user_id
				) AS following
				, (
					SELECT COUNT(*)
					FROM follow
					WHERE follow.user_id = user_username.user_id
				) AS followers
				' . $select_str . '
			FROM user_username
				' . $join_str . '
			' . (!empty($where_str) ? 'WHERE ' . $where_str : '') . '
			ORDER BY rank ASC, user_username.username ASC
			' . $this->generate_limit_offset_str($params) . '
		';

		$result = $this->run($query, $values);

		if (empty($result)) {
			 return resultArray(false, NULL, 'Could not get users.');
		}
		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}

	public function get_rank($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			SELECT (
				SELECT COUNT(*)
				FROM user_username AS u
				WHERE
					u.points > user_username.points
			) + 1 AS rank
			FROM user_username
			WHERE user_id = :user_id
			LIMIT 1
		';
		$values = array(
			':user_id' => $params['where']['user_id']
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get rank.');
		}

		$rows = $result->fetchAll();

		return resultArray(true, $rows[0]['rank']);
	}

	public function get_top_ranked($params = array()) {
		$params['limit'] = !empty($params['limit']) ? (int)$params['limit'] : 10;
		$params['offset'] = !empty($params['offset']) ? (int)$params['offset'] : 0;

		$query = '
			SELECT user_username.*
				, (
					SELECT COUNT(*)
					FROM user_username AS u
					WHERE
						u.points > user_username.points
				) + 1 AS rank
			FROM user_username
			ORDER BY rank ASC, username ASC
			LIMIT ' . $params['limit'] . ' OFFSET ' . $params['offset'] . '
		';

		$result = $this->run($query);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get top ranked users.');
		}

		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}

	public function get_following($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$where_str = '';
		$values = array();
		// user_id or username
		if (!empty($params['where']['user_id'])) {
			$where_str = 'follow.follower_user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else {
			$where_str = 'user.username = :username';
			$values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
		}

		$select_str = '';
		$join_str = '';
		// Optional viewer_user_id
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
			$join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

		$query = '
			SELECT user_username.*
				' . $select_str . '
			FROM follow
				INNER JOIN user_username AS user ON follow.follower_user_id = user.user_id
				INNER JOIN user_username ON follow.user_id = user_username.user_id
				' . $join_str . '
			WHERE ' . $where_str . '
			ORDER BY follow.created DESC
			' . $this->generate_limit_offset_str($params) . '
		';

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get following.');
		}

		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}



    public function getTopFollowing($params)
    {
        $error = NULL;

        if (empty($params['where'])) {
            $error = 'Where conditions are required.';
        }
        else if (!is_array($params['where'])) {
            $error = 'Invalid where conditions.';
        }

        if (!empty($error)) {
            return resultArray(false, NULL, $error);
        }

        $select_str = '';
        $join_str = '';
        $where_str = '';

        $values = array();
        // user_id or username
        if (!empty($params['where']['user_id'])) {
            $where_str = 'follow.follower_user_id = :user_id';
            $values[':user_id'] = $params['where']['user_id'];
        }
        elseif (!empty($params['where']['username'])) {
            $where_str = 'user.username = :username';
            $values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
        }else{
            $where_str = '1';
            $join_followers = " INNER JOIN user_username ON follow.user_id = user_username.user_id
                                LEFT JOIN follow AS f ON user_username.user_id = f.user_id";
        }

        // Optional viewer_user_id
        if (!empty($params['where']['viewer_user_id'])) {
            $select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
            $join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
            $values[':viewer_user_id'] = $params['where']['viewer_user_id'];

            $join_followers = " INNER JOIN user_username ON follow.user_id = user_username.user_id";
        }


        $following_query = "
            SELECT distinct
                user_username.user_username_id,
                user_username.user_id,
                user_username.username,
                user_username.points,
                user_username.location,
                user_username.fb_uid,
                user_username.avatar

                ,( select rank
                		from
                			( SELECT
                				u.user_id,
                				@row:=@row+1 as rank
                			FROM user_username AS u
                			join (SELECT @row:=0) pos
                			ORDER BY u.points DESC
                			limit 999999999999999  ) as uranks
                		where uranks.user_id = user_username.user_id
                ) as rank,

                (
                    SELECT
                    ml.name
                    FROM membership_level ml, user_username user
                    WHERE user.user_id = user_username.user_id
                        AND CAST(user.points AS SIGNED)  / ml.points > 1
                    order by ABS(CAST(ml.points AS SIGNED) - CAST(user.points AS SIGNED)) ASC
                    LIMIT 1
                ) AS membership_level

                {$select_str}

            FROM follow
                {$join_followers}
                {$join_str}
            WHERE {$where_str}
            ORDER BY rank ASC
         " .$this->generate_limit_offset_str($params). ";
         ";



        if(isset($_GET['t'])) echo sprintf("query: \n%s \nparams: %s\n", $following_query, var_export($values, true));


        $result = $this->run($following_query, $values);

        if ($result === false) {
             if(isset($_GET['t'])) echo $this->error;
             return resultArray(false, NULL, 'Could not get top following.');
        }

        $rows = $result->fetchAll();

        //if(isset($_GET['t'])) var_dump($rows);

        return resultArray(true, $rows);

    }



	public function get_followers($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$where_str = '';
		$values = array();
		// user_id or username
		if (!empty($params['where']['user_id'])) {
			$where_str = 'follow.user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else {
			$where_str = 'user.username = :username';
			$values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
		}

		$select_str = '';
		$join_str = '';
		// Optional viewer_user_id
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
			$join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

		$query = '
			SELECT DISTINCT user_username.*
				' . $select_str . '
			FROM follow
				INNER JOIN user_username AS user ON follow.user_id = user.user_id
				INNER JOIN user_username ON follow.follower_user_id = user_username.user_id
				' . $join_str . '
			WHERE ' . $where_str . '
			ORDER BY follow.created DESC
			' . $this->generate_limit_offset_str($params) . '
		';

        if(isset($_GET['t'])) echo sprintf('query: %s', $query);

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get followers.');
		}

		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}


	public function getTopfollowers($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$where_str = '';
		$values = array();
		// user_id or username
		if (!empty($params['where']['user_id'])) {
			$where_str = 'follow.user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else {
			$where_str = 'user.username = :username';
			$values[':username'] = !empty($params['where']['username']) ? $params['where']['username'] : '';
		}

		$select_str = '';
		$join_str = '';
		// Optional viewer_user_id
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(f.user_id IS NULL, 0, 1) AS is_followed';
			$join_str = 'LEFT JOIN follow AS f ON user_username.user_id = f.user_id AND f.follower_user_id = :viewer_user_id';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

		$query = '
			SELECT DISTINCT
			    user_username.*,
			    (
                        SELECT COUNT(*)
                        FROM user_username AS u
                        WHERE
                            u.points > user_username.points
                    ) + 1 AS rank
                    ' . $select_str . '
				' . $select_str . '
			FROM follow
				INNER JOIN user_username AS user ON follow.user_id = user.user_id
				INNER JOIN user_username ON follow.follower_user_id = user_username.user_id
				' . $join_str . '
			WHERE ' . $where_str . '

			ORDER BY rank ASC
			' . $this->generate_limit_offset_str($params) . '
		';

        if(isset($_GET['t'])) echo sprintf('query: %s', $query);

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get followers.');
		}

		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}

	public function follow($params = array()) {
		$error = NULL;

		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$result = $this->insert('follow', $params['data']);

		$insert_id = $this->insert_id;

		if (empty($insert_id)) {
			return resultArray(false, NULL, 'Could not follow.');
		}

		return resultArray(true, $insert_id);

	}

	public function unfollow($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$result = $this->delete('follow', $params['where']);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not unfollow.');
		}

		return resultArray(true, true);
	}

	public function get_points($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			SELECT points
			FROM user_username
			WHERE user_id = :user_id
			LIMIT 1
		';
		$values = array(
			':user_id' => $params['where']['user_id']
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get points.');
		}

		$rows = $result->fetchAll();

		if (empty($rows)) {
			 return resultArray(false, NULL, 'Could not get points.');
		}

		return resultArray(true, $rows[0]['points']);
	}

	public function get_membership_level($user_id) {
		$query = '
			SELECT user.points, user.points_threshold
				, membership_level.name
			FROM
				(
					SELECT user_username.points
						, (
							SELECT MAX(points)
							FROM membership_level
							WHERE membership_level.points <= user_username.points
							LIMIT 1
						) AS points_threshold
					FROM user_username
					WHERE user_username.user_id = :user_id
				) AS user
				INNER JOIN membership_level ON user.points_threshold = membership_level.points
		';
		$values = array(
			':user_id' => $user_id
		);
		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get user membership level.');
		}

		$rows = $result->fetchAll();
		return resultArray(true, $rows[0]);
	}

	public function get_users_by_username($usernames) {
		$in_str = implode(',', array_fill(0, count($usernames), '?'));
		$query = '
			SELECT *
			FROM user_username
			WHERE username IN (' . $in_str . ')
		';
		$values = $usernames;
		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get users.');
		}

		$rows = $result->fetchAll();
		return resultArray(true, $rows);
	}

	public function get_all_daily_counts($user_id, $date) {
		$query = '
			SELECT IFNULL(likes.likes, 0) AS likes, IFNULL(comments.comments, 0) AS comments, IFNULL(follows.follows, 0) AS follows, IFNULL(points.points, 0) AS points
			FROM user_username
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS likes
					FROM posting_like
						INNER JOIN posting ON posting_like.posting_id = posting.posting_id
					WHERE posting.user_id = :user_id
						AND DATE(posting_like.created) = :date
					LIMIT 1
				) AS likes ON user_username.user_id = likes.user_id
				LEFT JOIN (
					SELECT posting.user_id, COUNT(*) AS comments
					FROM comment
						INNER JOIN posting ON comment.posting_id = posting.posting_id
					WHERE posting.user_id = :user_id
						AND DATE(comment.created) = :date
					LIMIT 1
				) AS comments ON user_username.user_id = comments.user_id
				LEFT JOIN (
					SELECT follow.user_id, COUNT(*) AS follows
					FROM follow
					WHERE user_id = :user_id
						AND DATE(created) = :date
					LIMIT 1
				) AS follows ON user_username.user_id = follows.user_id
				LEFT JOIN (
					SELECT user_point.user_id, SUM(points) AS points
					FROM user_point
					WHERE user_id = :user_id
						AND DATE(created) = :date
					LIMIT 1
				) AS points ON user_username.user_id = points.user_id
			WHERE user_username.user_id = :user_id
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			return resultArray(false, NULL, 'Could not get daily counts.');
		}
		$rows = $result->fetchAll();

		return $rows[0];
	}
}
?>