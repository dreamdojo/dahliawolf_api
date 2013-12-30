<?php

class Comment extends db {

	private $table = 'comment';

	public function __construct() { 
		parent::__construct();
	}

	public function get_post_comments($params = array())
    {
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
		
		$query = '
			SELECT comment.*, user_username.*
			FROM comment
				INNER JOIN user_username ON comment.user_id = user_username.user_id
			WHERE posting_id = :posting_id
			ORDER BY comment.created_at DESC
			' . $this->generate_limit_offset_str($params) . '
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);


        if(isset($_GET['t']))
        {
            echo "query: {$query}";
        }
		
		//$rows = $this->get_all($this->table);
		$result = $this->run($query, $values);
		
		if ( empty($result) && count($result) == 0) {
			 return resultArray(false, NULL, 'Could not get post comments.');
		}
		$rows = $result->fetchAll();

        if (  count($rows) == 0) {
             return resultArray(false, NULL, 'Could not get post comments.');
        }
		
		return resultArray(true, $rows);
	}

	public function get_product_comments($params = array()) {
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
		
		$query = '
			SELECT comment.*, user.*
			FROM comment
				INNER JOIN user_username ON comment.user_id = user_username.user_id
			WHERE product_id = :product_id
		';
		$values = array(
			':product_id' => $params['where']['product_id']
		);
		
		//$rows = $this->get_all($this->table);
		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get product comments.');
		}
		
		return resultArray(true, $rows);
	}
	
	public function add_comment($params = array()) {
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
			return resultArray(false, NULL, 'Could not add comment.');
		}
		
		return resultArray(true, array('comment_id' => $insert_id));
	}
	
	public function get_daily_count($user_id, $date) {
		$query = '
			SELECT COUNT(*) AS count
			FROM comment
				INNER JOIN posting ON comment.posting_id = posting.posting_id
			WHERE posting.user_id = :user_id
				AND DATE(comment.created) = :date
			LIMIT 1
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
		);
		
		$result = $this->run($query, $values);
		
		if ($result === false) {
			return resultArray(false, NULL, 'Could not get daily comments count.');
		}
		$rows = $result->fetchAll();
		
		return $rows[0]['count'];
	}
}
?>