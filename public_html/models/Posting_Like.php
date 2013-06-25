<?php

class Posting_Like extends db {

	private $table = 'posting_like';

	public function __construct() {
		parent::__construct();
	}

	public function add_post_like($params = array()) {
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
			return resultArray(false, NULL, 'Could not add post like.');
		}

		$this->audit_post_likes($params['data']['posting_id']);

		return resultArray(true, array('posting_like_id' => $insert_id));
	}

	public function delete_post_like($params = array()) {
		$error = NULL;
		if (empty($params['where'])) {
			$error = 'Where conditions is required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$this->delete($this->table, $params['where']);

		$this->audit_post_likes($params['where']['posting_id']);

		return resultArray(true, true);
	}

	private function audit_post_likes($posting_id) {
		$query = '
			UPDATE posting
			SET total_likes = (
				SELECT COUNT(*)
				FROM posting_like
				WHERE posting_like.posting_id = posting.posting_id
			)
			WHERE posting.posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $posting_id
		);
		$result = $this->run($query, $values);

		if ($result === false) {
			return resultArray(false, NULL, 'Could not audit post likes.');
		}

		return true;
	}

	public function get_post_likes($params = array()) {
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
			SELECT user_username.*, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
			FROM posting_like
				INNER JOIN posting ON posting_like.posting_id = posting.posting_id
				INNER JOIN user_username ON posting_like.user_id = user_username.user_id
				LEFT JOIN user_image_map ON user_username.user_id = user_image_map.user_id AND user_image_map.avatar = "Yes"
				LEFT JOIN image ON user_image_map.image_id = image.id
			WHERE posting_like.posting_id = :posting_id
			ORDER BY posting_like.created DESC
			' . $this->generate_limit_offset_str($params) . '
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);

		//$row = $this->get_row($this->table, $params['conditions']);
		$data = $this->run($query, $values);
		$row = $data->fetchAll();
		if ($row === false) {
			 return resultArray(false, NULL, 'Could not get post likes.');
		}

		return resultArray(true, $row);
	}

	public function get_num_post_likes($params = array()) {
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
			SELECT COUNT(*) AS count
			FROM posting_like
			WHERE posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);

		$data = $this->run($query, $values);
		$row = $data->fetchAll();
		if ($row === false) {
			 return resultArray(false, NULL, 'Could not get num post likes.');
		}

		return resultArray(true, $row[0]['count']);
	}

	public function get_liked_posts($params = array()) {
		$order_by_str = 'created DESC';

		$order_by_columns = array(
			'created'
			, 'total_likes'
			, 'total_votes'
		);
		if (!empty($params['order_by'])) {
			if (in_array($params['order_by'], $order_by_columns)) {
				$order_by_str = $params['order_by'] . ' DESC';
			}
			else if ($params['order_by'] == 'rand') {
				$order_by_str = 'RAND()';
			}
		}

		$select_str = '';
		$join_str = '';
		$values = array();
		if (!empty($params['where'])) {
			$where_str = '';
			if (!empty($params['where']['user_id'])) {
				$where_str .= ' posting_like.user_id = :user_id';
				$values[':user_id'] = $params['where']['user_id'];
			}
			if (!empty($params['where']['username'])) {
				$where_str .= ' liker.username = :username';
				$values[':username'] = $params['where']['username'];
			}
			// Viewer (show if posts are liked/voted in relation)
			if (!empty($params['where']['viewer_user_id'])) {
				$select_str = ', IF(viewer_posting_like.user_id IS NULL, 0, 1) AS is_liked';
				$join_str = '
					LEFT JOIN posting_like AS viewer_posting_like ON posting.posting_id = viewer_posting_like.posting_id
						AND viewer_posting_like.user_id = :viewer_user_id
				';
				$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
			}
		}

		$limit_str = '';
		$offset_str = '';
		if (!empty($params['limit'])) {
			$limit_str = 'LIMIT ' . $params['limit'];
		}
		if (!empty($params['offset'])) {
			$offset_str = 'OFFSET ' . $params['offset'];
		}

		$query = '
			SELECT posting.*, image.imagename, image.source, user_username.username, image.dimensionsX AS width, image.dimensionsY AS height
				, posting_like.created AS liked
				, CONCAT(image.source, "image.php?imagename=", image.imagename) AS image_url
				, user_username.location, user_username.avatar
				, IFNULL(COUNT(comment.comment_id), 0) AS comments
				' . $select_str . '
			FROM posting_like
				INNER JOIN user_username AS liker ON posting_like.user_id = liker.user_id
				INNER JOIN posting ON posting_like.posting_id = posting.posting_id
				INNER JOIN image ON posting.image_id = image.id
				INNER JOIN user_username ON posting.user_id = user_username.user_id
				LEFT JOIN comment ON posting.posting_id = comment.posting_id
				' . $join_str . '
			WHERE posting.deleted IS NULL
			' . (!empty($where_str) ? 'AND ' . $where_str : '') . '
			GROUP BY posting.posting_id
			ORDER BY ' . $order_by_str . '
			' . $limit_str . ' ' . $offset_str . '
		';

		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get posts.');
		}

		return resultArray(true, $rows);
	}

	public function get_daily_count($user_id, $date) {
		$query = '
			SELECT COUNT(*) AS count
			FROM posting_like
				INNER JOIN posting ON posting_like.posting_id = posting.posting_id
			WHERE posting.user_id = :user_id
				AND DATE(posting_like.created) = :date
			LIMIT 1
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			return resultArray(false, NULL, 'Could not get daily likes count.');
		}
		$rows = $result->fetchAll();

		return $rows[0]['count'];
	}
}
?>