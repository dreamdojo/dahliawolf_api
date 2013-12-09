<?
class Activity_Log extends _Model {
	const TABLE = 'activity_log';
	const PRIMARY_KEY_FIELD = 'activity_log_id';




    protected $user_last_log_time = null;

    public function __construct($db_host = ADMIN_API_HOST, $db_user = ADMIN_API_USER, $db_password = ADMIN_API_PASSWORD, $db_name = ADMIN_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


	protected $fields = array(
		'user_id'
		, 'api_website_id'
		, 'activity_id'
		, 'note'
		, 'entity'
		, 'entity_id'
	);


	public function get_log($user_id, $api_website_id = NULL, $activity_id = NULL) {
		// Get rows
		$query = '
			SELECT  activity_log.activity_log_id,
			        activity_log.created,
			        activity_log.note,
			        activity_log.entity,
			        activity_log.entity_id,
			        activity_log.read
			FROM activity_log
				LEFT JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
			WHERE activity_log.entity IS NOT NULL
				AND user_id = :user_id
				' . (!empty($api_website_id) ? 'AND activity_log.api_website_id = :api_website_id' : '') . '
				' . (!empty($activity_id) ? 'AND activity_log.activity_id = :activity_id' : '') . '
			ORDER BY activity_log.created DESC
		';
		$values = array(
			':user_id' => $user_id
		);
		if (!empty($api_website_id)) {
			$values[':api_website_id'] = $api_website_id;
		}
		if (!empty($activity_id)) {
			$values[':activity_id'] = $activity_id;
		}

        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
            echo $query;
            var_dump($values);
        }


		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

    public function addUserLast($user_id)
    {
        if( !$this->user_last_log_time )
        {
            $user_log_last = new Activity_Log_User_Last();
            $this->user_last_log_time =  $user_log_last->getUserLast( array('user_id' => $user_id) );
        }

        $where_str = " AND UNIX_TIMESTAMP(activity_log.created) > {$this->user_last_log_time}";
        return $where_str;
    }


	public function get_like_winners_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false, $request_params =  array())
    {
        $where_str = '';

        if ($unread_count) {
            $select_str = 'COUNT(*) AS count';
            if ($unpreviewed_count==false) {
                $where_str = " AND activity_log.read IS NULL \n";
            }
            else {
                $where_str = ' AND activity_log.previewed IS NULL';
            }

		}
		else {
            $select_str = '
                activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read, UNIX_TIMESTAMP(activity_log.created) AS time
                , like_winner.posting_id, like_winner.likes
                , CONCAT(\'http://www.dahliawolf.com/post/\', posting.posting_id) AS post_url
                , CONCAT(image.source, image.imagename) AS image_url
            ';
		}

        $viewer_user_id = $request_params['viewer_user_id'];
        if (!empty($viewer_user_id)) {
            $select_str .= ', IF(follow.follow_id IS NULL, 0, 1) AS is_followed';
            $join_str = '
                LEFT JOIN dahliawolf_v1_2013.follow AS follow ON (follow.user_id = :viewer_user_id
                    AND follow.follower_user_id = activity_log.user_id )
            ';
        }

        if( $request_params['new_only'] ) $where_str .= self::addUserLast($user_id);

		// Get rows
		$query = "
			SELECT {$select_str}
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				LEFT JOIN dahliawolf_v1_2013.like_winner ON activity_log.entity_id = like_winner.like_winner_id
				INNER JOIN dahliawolf_v1_2013.posting ON activity_log.entity_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
                {$join_str}
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				{$where_str}
			ORDER BY activity_log.created DESC
		";

		$values = array(
			':user_id' => $user_id,
			':api_website_id' => $api_website_id,
			':activity_id' => Posting_Like::ACTIVITY_ID_POST_LIKED,
			':entity' => 'like_winner',
		);

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }

        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
            echo $query;
            var_dump($values);
        }

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			//echo $e->getMessage();
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_commented_posts_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false, $request_params = array()) {

        $where_str = '';

        if ($unread_count) {
            $select_str = 'COUNT(*) AS count';
            if ($unpreviewed_count==false) {
                $where_str = " AND activity_log.read IS NULL \n";
            }
            else {
                $where_str = ' AND activity_log.previewed IS NULL';
            }

        }
		else{
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read, UNIX_TIMESTAMP(activity_log.created) AS time
				, comment.posting_id, comment.comment
				, user_username.user_id, user_username.username, user_username.avatar
				, CONCAT(\'http://www.dahliawolf.com/post/\', comment.posting_id) AS post_url
				, CONCAT(image.source, image.imagename) AS image_url
			';
		}

        $viewer_user_id = $request_params['viewer_user_id'];
        if (!empty($viewer_user_id)) {
            $select_str .= ', IF(follow.follow_id IS NULL, 0, 1) AS is_followed';
            $join_str = '
                LEFT JOIN dahliawolf_v1_2013.follow AS follow ON (follow.user_id = :viewer_user_id
                    AND follow.follower_user_id = activity_log.user_id )
            ';
        }

        if( !empty($request_params['new_only']) ) $where_str .= self::addUserLast($user_id);

		// Get rows
		$query = "
			SELECT {$select_str}
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.comment ON activity_log.entity_id = comment.comment_id
				INNER JOIN dahliawolf_v1_2013.user_username ON comment.user_id = user_username.user_id
				INNER JOIN dahliawolf_v1_2013.posting ON comment.posting_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
				{$join_str}
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				{$where_str}
			ORDER BY activity_log.created DESC
		";

		$values = array(
			':user_id' => $user_id,
			':api_website_id' => $api_website_id,
			':activity_id' => 32,
			':entity' => 'comment',
		);

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }

        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
            echo $query;
            var_dump($values);
        }

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_liked_posts_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false, $request_params = array())
    {
        $where_str = '';

        if ($unread_count) {
            $select_str = 'COUNT(*) AS count';
            if ($unpreviewed_count==false) {
                $where_str = " AND activity_log.read IS NULL \n";
            }
            else {
                $where_str = ' AND activity_log.previewed IS NULL';
            }

        }
		else{
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read, UNIX_TIMESTAMP(activity_log.created) AS time
				, posting_like.posting_id
				, user_username.user_id, user_username.username, user_username.avatar
				, CONCAT(\'http://www.dahliawolf.com/post/\', posting_like.posting_id) AS post_url
				, CONCAT(image.source, image.imagename) AS image_url
			';
		}

        $viewer_user_id = $request_params['viewer_user_id'];
        if (!empty($viewer_user_id)) {
            $select_str .= ', IF(follow.follow_id IS NULL, 0, 1) AS is_followed';
            $join_str = '
                LEFT JOIN dahliawolf_v1_2013.follow AS follow ON (follow.user_id = :viewer_user_id
                    AND follow.follower_user_id = activity_log.user_id )
            ';
        }

        if( !empty($request_params['new_only']) ) $where_str .= self::addUserLast($user_id);

		// Get rows
		$query = "
			SELECT {$select_str}
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.posting_like ON activity_log.entity_id = posting_like.posting_like_id
				INNER JOIN dahliawolf_v1_2013.user_username ON posting_like.user_id = user_username.user_id
                INNER JOIN dahliawolf_v1_2013.posting ON posting_like.posting_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
				{$join_str}
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				{$where_str}
			ORDER BY activity_log.created DESC
		";
		$values = array(
			':user_id' => $user_id,
			':api_website_id' => $api_website_id,
			':activity_id' => 9,
			':entity' => 'posting_like',
		);

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }

        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
            echo $query;
            var_dump($values);
        }

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_followers_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false, $request_params = array())
    {
        if ($unread_count)
        {
            $select_str = 'COUNT(*) AS count';
            if ($unpreviewed_count==false) {
                $where_str = " AND activity_log.read IS NULL \n";
            }
            else {
                $where_str = ' AND activity_log.previewed IS NULL';
            }

        }
		else {

			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read, UNIX_TIMESTAMP(activity_log.created) AS time
				, user_username.user_id, user_username.username, user_username.avatar
			';
		}

        $viewer_user_id = $request_params['viewer_user_id'];
        if (!empty($viewer_user_id)) {
           $select_str .= ', IF(follow.follow_id IS NULL, 0, 1) AS is_followed';
            $join_str = '
                LEFT JOIN dahliawolf_v1_2013.follow AS follow ON (follow.user_id = :viewer_user_id
                    AND follow.follower_user_id = activity_log.user_id )
            ';
        }

        if( !empty($request_params['new_only']) ) $where_str .= self::addUserLast($user_id);

		// Get rows
		$query = "
			SELECT {$select_str}
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				#INNER JOIN dahliawolf_v1_2013.follow ON activity_log.entity_id = follow.follow_id
				{$join_str}
				INNER JOIN dahliawolf_v1_2013.user_username ON follow.follower_user_id = user_username.user_id
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				{$where_str}
			ORDER BY activity_log.created DESC
		";
		$values = array(
			':user_id' => $user_id,
			':api_website_id' => $api_website_id,
			':activity_id' => 34,
			':entity' => 'follow',
		);

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }

        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
           echo $query;
           var_dump($values);
        }

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_messages_log($user_id, $api_website_id, $activity_id=39, $unread_count = false, $unpreviewed_count = false, $request_params = array())
    {
        $where_str = '';

        if ($unread_count)
        {
            $select_str = 'COUNT(*) AS count';
            if ($unpreviewed_count==false) {
                $where_str = " AND activity_log.read IS NULL \n";
            }
            else {
                $where_str = ' AND activity_log.previewed IS NULL';
            }
        }
		else{
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read, UNIX_TIMESTAMP(activity_log.created) AS time,
				user_username.user_id, user_username.username, user_username.avatar,
				message.header, message.body
			';
		}

        $viewer_user_id = $request_params['viewer_user_id'];
        if (!empty($viewer_user_id)) {
           $select_str .= ', IF(follow.follow_id IS NULL, 0, 1) AS is_followed';
           $join_str = '
                LEFT JOIN dahliawolf_v1_2013.follow AS follow ON (follow.user_id = :viewer_user_id
                    AND follow.follower_user_id = activity_log.user_id )
            ';
        }

        if( !empty($request_params['new_only']) ) $where_str .= self::addUserLast($user_id);

		// Get rows
		$query = "
			SELECT {$select_str}
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
                INNER JOIN dahliawolf_v1_2013.user_username ON activity_log.user_id = user_username.user_id
                INNER JOIN dahliawolf_v1_2013.message AS message ON message.to_user_id = activity_log.user_id
                                                                 AND message.message_id = activity_log.entity_id
                {$join_str}
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				{$where_str}
			ORDER BY activity_log.created DESC
		";
		$values = array(
			':user_id' => $user_id,
			':api_website_id' => $api_website_id,
			':activity_id' => $activity_id,
			':entity' => 'message'
		);

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }


        if(isset($_GET['t'])){
            echo "\n".__FUNCTION__ ."\n";
           echo $query;
           var_dump($values);
        }


        $logger = new Jk_Logger(APP_PATH.'logs/db_queries.log');

        $logger->LogInfo( sprintf( "messages log query:\n %s: \nparams: %s", $query, var_export($values, true)) );


		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_like_winners_log_count($user_id, $api_website_id, $previewed = false) {
		return $this->get_like_winners_log($user_id, $api_website_id, true, $previewed);
	}
	public function get_commented_posts_log_count($user_id, $api_website_id, $previewed = false) {
		return $this->get_commented_posts_log($user_id, $api_website_id, true, $previewed);
	}
	public function get_liked_posts_log_count($user_id, $api_website_id, $previewed = false) {
		return $this->get_liked_posts_log($user_id, $api_website_id, true, $previewed);
	}
	public function get_followers_log_count($user_id, $api_website_id, $previewed = false) {
		return $this->get_followers_log($user_id, $api_website_id, true, $previewed);
	}

	public function get_num_unread($user_id, $api_website_id = NULL) {
		$query = '
			SELECT COUNT(*) AS count
			FROM activity_log
				LEFT JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
			WHERE activity_log.entity IS NOT NULL
				AND activity_log.read IS NULL
				AND user_id = :user_id
				' . (!empty($api_website_id) ? 'AND activity_log.api_website_id = :api_website_id' : '') . '
		';
		$values = array(
			':user_id' => $user_id
		);
		if (!empty($api_website_id)) {
			$values[':api_website_id'] = $api_website_id;
		}

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);

			if (!empty($activities)) {
				return $activities['count'];
			}

			return 0;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function mark_read($params, $datetime) {
		$fields = array(
			'read' => $datetime
		);

		$where_sql = 'activity_log_id = :activity_log_id AND user_id = :user_id';
		$where_values = array(
			':activity_log_id' => $params['activity_log_id'],
			':user_id' => $params['user_id'],
		);

        if(isset($params['entity']) && strlen($params['entity'])> 2)
        {
            $where_values[":entity"] = $params['entity'];
            $where_sql = 'entity = :entity AND user_id = :user_id';

            unset($where_values[':activity_log_id']);
        }

        $logger = new Jk_Logger( APP_PATH . 'logs/activity_log.log');
        $logger->LogInfo( sprintf("MARK MESSAGES AS READ\nwith params: %s \n\nwhere values: %s" , var_export($params, true), var_export($where_values, true) ) );

		try {
			$update = $this->db_update($fields, $where_sql, $where_values);
			return $update;
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to mark activity log as read.');
		}
	}

	public function mark_unread($activity_log_id, $user_id) {
		$fields = array(
			'read' => NULL
		);

		$where_sql = 'activity_log_id = :activity_log_id AND user_id = :user_id';
		$where_values = array(
			':activity_log_id' => $activity_log_id
			, ':user_id' => $user_id
		);

		try {
			$update = $this->db_update($fields, $where_sql, $where_values);

			return $update;
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to mark activity log as unread.');
		}
	}

	public function mark_previewed($user_id, $datetime, $activity_id = NULL) {
		$fields = array(
			'previewed' => $datetime
		);

		$where_sql = 'user_id = :user_id';
		$where_values = array(
			':user_id' => $user_id
		);
		if (!empty($activity_id)) {
			$where_sql .= ' AND activity_id = :activity_id';
			$where_values[':activity_id'] = $activity_id;
		}

		try {
			$update = $this->db_update($fields, $where_sql, $where_values);

			return $update;
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to mark activity log as previewed.');
		}
	}


    public static function logActivity($user_id, $activity_id, $note, $entity = NULL, $entity_id = NULL)
    {
    	$activity = array(
            'user_id' => $user_id,
            'activity_id' => $activity_id,
            'note' => $note,
            'api_website_id' => API_WEBSITE_ID,
            'entity' => $entity,
            'entity_id' => $entity_id,

    	);
    	$data = self::saveActivity($activity);

    	return $data;
    }


    public static function saveActivity($params)
    {
        $input_validations = array(
            'user_id' => array(
                'label' => 'User ID'
                , 'rules' => array(
                    'is_set' => NULL
                    , 'is_int' => NULL
                )
            )
            , 'activity_id' => array(
                'label' => 'Activity ID'
                , 'rules' => array(
                    'is_set' => NULL
                    , 'is_int' => NULL
                )
            )
            , 'note' => array(
                'label' => 'Note'
                , 'rules' => array(
                    'is_set' => NULL
                )
            )
            , 'api_website_id' => array(
                'label' => 'API Website ID'
                , 'rules' => array(
                    'is_int' => NULL
                )
            )
        );

        $logger = new Jk_Logger(APP_PATH.'logs/activity_log.log');
        $logger->LogInfo("LOGGING ACTIVITY WITH params: ". var_export($params, true));

        $validator = new Validate();
        $validator->add_many($input_validations, $params, true);
        $validator->run();

        $activity_log = new Activity_Log();
        $activity = array(
            'user_id' => $params['user_id'],
            'api_website_id' => !empty($params['api_website_id']) ? $params['api_website_id'] : NULL,
            'activity_id' => $params['activity_id'],
            'note' => $params['note'],
            'entity' => !empty($params['entity']) ? $params['entity'] : NULL,
            'entity_id' => !empty($params['entity']) ? (int)$params['entity_id'] : NULL,
        );

        $logger->LogInfo("LOGGING ACTIVITY WITH activity data: ". var_export($activity, true));
        $data = $activity_log->save($activity);

        $logger->LogInfo("LOGGING ACTIVITY WITH activity response: ". var_export($data, true));
    	return $data;
    }

    public function markReadByType($params)
    {
        $datetime = date('Y-m-d H:i:s');
        $fields = array(
            'read' => $datetime
        );

        $where_sql = 'activity_id = :activity_id
                        AND user_id = :user_id';

        $where_values = array(
            ':activity_id'  => $params['activity_id'],
            ':user_id'      => $params['user_id'],
        );


        $logger = new Jk_Logger( APP_PATH . 'logs/activity_log.log');
        $logger->LogInfo( sprintf("MARK MESSAGES AS READ\nwith params: %s \n\nwhere values: %s\nfields: %s" , var_export($params, true), var_export($where_values, true), var_export($fields, true) ) );

        try {
            $update = $this->db_update($fields, $where_sql, $where_values);
            return $update;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to mark activity log as read.');
        }

    }

    /*
	// Get api website db, so we know where to join

		// Get possible entities for api website id
		$query = '
			SELECT DISTINCT entity
			FROM activity_log
			WHERE entity IS NOT NULL
				AND api_website_id = :api_website_id
		';
		$values = array(
			'api_website_id' => $api_website_id
		);
		try {
			$entities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log entities.');
		}
		print_r($entities);die();

		$left_join_str = '';
		if (!empty($entities)) {

		}
	*/

}
?>