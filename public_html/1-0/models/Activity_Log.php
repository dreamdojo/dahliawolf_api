<?
class Activity_Log extends _Model {
	const TABLE = 'activity_log';
	const PRIMARY_KEY_FIELD = 'activity_log_id';

	protected $fields = array(
		'user_id'
		, 'api_website_id'
		, 'activity_id'
		, 'note'
		, 'entity'
		, 'entity_id'
	);

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

	public function get_log($user_id, $api_website_id = NULL, $activity_id = NULL) {
		// Get rows
		$query = '
			SELECT activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read
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

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_like_winners_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false) {
		if (!$unread_count) {
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read
				, like_winner.posting_id, like_winner.likes
				, CONCAT(\'http://www.dahliawolf.com/post/\', like_winner.posting_id) AS post_url
				, CONCAT(image.source, image.imagename) AS image_url
			';
		}
		else {
			$select_str = 'COUNT(*) AS count';
			if (!$unpreviewed_count) {
				$where_str = 'AND activity_log.read IS NULL';
			}
			else {
				$where_str = 'AND activity_log.previewed IS NULL';
			}
		}

		// Get rows
		$query = '
			SELECT ' . $select_str . '
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.like_winner ON activity_log.entity_id = like_winner.like_winner_id
				INNER JOIN dahliawolf_v1_2013.posting ON like_winner.posting_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				' . (!empty($where_str) ? $where_str : '') . '
			ORDER BY activity_log.created DESC
		';
		$values = array(
			':user_id' => $user_id
			, ':api_website_id' => $api_website_id
			, ':activity_id' => 19
			, ':entity' => 'like_winner'
		);

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			echo $e->getMessage();
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_commented_posts_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false) {
		if (!$unread_count) {
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read
				, comment.posting_id, comment.comment
				, user_username.user_id, user_username.username
				, CONCAT(\'http://www.dahliawolf.com/post/\', comment.posting_id) AS post_url
				, CONCAT(image.source, image.imagename) AS image_url
			';
		}
		else {
			$select_str = 'COUNT(*) AS count';
			if (!$unpreviewed_count) {
				$where_str = 'AND activity_log.read IS NULL';
			}
			else {
				$where_str = 'AND activity_log.previewed IS NULL';
			}
		}

		// Get rows
		$query = '
			SELECT ' . $select_str . '
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.comment ON activity_log.entity_id = comment.comment_id
				INNER JOIN dahliawolf_v1_2013.user_username ON comment.user_id = user_username.user_id
				INNER JOIN dahliawolf_v1_2013.posting ON comment.posting_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				' . (!empty($where_str) ? $where_str : '') . '
			ORDER BY activity_log.created DESC
		';
		$values = array(
			':user_id' => $user_id
			, ':api_website_id' => $api_website_id
			, ':activity_id' => 32
			, ':entity' => 'comment'
		);

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_liked_posts_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false) {
		if (!$unread_count) {
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read
				, posting_like.posting_id
				, user_username.user_id, user_username.username
				, CONCAT(\'http://www.dahliawolf.com/post/\', posting_like.posting_id) AS post_url
				, CONCAT(image.source, image.imagename) AS image_url
			';
		}
		else {
			$select_str = 'COUNT(*) AS count';
			if (!$unpreviewed_count) {
				$where_str = 'AND activity_log.read IS NULL';
			}
			else {
				$where_str = 'AND activity_log.previewed IS NULL';
			}
		}

		// Get rows
		$query = '
			SELECT ' . $select_str . '
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.posting_like ON activity_log.entity_id = posting_like.posting_like_id
				INNER JOIN dahliawolf_v1_2013.user_username ON posting_like.user_id = user_username.user_id
				INNER JOIN dahliawolf_v1_2013.posting ON posting_like.posting_id = posting.posting_id
				INNER JOIN dahliawolf_v1_2013.image ON posting.image_id = image.id
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				' . (!empty($where_str) ? $where_str : '') . '
			ORDER BY activity_log.created DESC
		';
		$values = array(
			':user_id' => $user_id
			, ':api_website_id' => $api_website_id
			, ':activity_id' => 9
			, ':entity' => 'posting_like'
		);

		try {
			$activities = self::$dbs[$this->db_host][$this->db_name]->exec($query, $values);
			return $activities;

		} catch(Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to get activity log.');
		}
	}

	public function get_followers_log($user_id, $api_website_id, $unread_count = false, $unpreviewed_count = false) {
		if (!$unread_count) {
			$select_str = '
				activity_log.activity_log_id, activity_log.created, activity_log.note, activity_log.entity, activity_log.entity_id, activity_log.read
				, user_username.user_id, user_username.username
			';
		}
		else {
			$select_str = 'COUNT(*) AS count';
			if (!$unpreviewed_count) {
				$where_str = 'AND activity_log.read IS NULL';
			}
			else {
				$where_str = 'AND activity_log.previewed IS NULL';
			}
		}

		// Get rows
		$query = '
			SELECT ' . $select_str . '
			FROM activity_log
				INNER JOIN api_website ON activity_log.api_website_id = api_website.api_website_id
				INNER JOIN dahliawolf_v1_2013.follow ON activity_log.entity_id = follow.follow_id
				INNER JOIN dahliawolf_v1_2013.user_username ON follow.follower_user_id = user_username.user_id
			WHERE activity_log.user_id = :user_id
				AND activity_log.api_website_id = :api_website_id
				AND activity_log.activity_id = :activity_id
				AND activity_log.entity = :entity
				' . (!empty($where_str) ? $where_str : '') . '
			ORDER BY activity_log.created DESC
		';
		$values = array(
			':user_id' => $user_id
			, ':api_website_id' => $api_website_id
			, ':activity_id' => 34
			, ':entity' => 'follow'
		);

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

	public function mark_read($activity_log_id, $user_id, $datetime) {
		$fields = array(
			'read' => $datetime
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
}
?>