<?php

class Posting_Vote extends db {

	private $table = 'posting_vote';

	public function __construct() { 
		parent::__construct();
	}

	public function add_post_vote($params = array()) {
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
			return resultArray(false, NULL, 'Could not add post vote.');
		}
		
		$this->audit_post_votes($params['data']['posting_id']);
		
		return resultArray(true, array('posting_vote_id' => $insert_id));
	}
	
	public function delete_post_vote($params = array()) {
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
		
		$this->audit_post_votes($params['where']['posting_id']);
		
		return resultArray(true, true);
	}

	private function audit_post_votes($posting_id) {
		$query = '
			UPDATE posting
			SET total_votes = (
				SELECT COUNT(*)
				FROM posting_vote
				WHERE posting_vote.posting_id = posting.posting_id
			)
			WHERE posting.posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $posting_id
		);
		$result = $this->run($query, $values);
		
		if ($result === false) {
			return resultArray(false, NULL, 'Could not audit post votes.');
		}
		
		return true;
	}
	
	public function get_top_voted_posts($params = array()) {
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
			SELECT posting.*, IFNULL(COUNT(posting_vote.posting_vote_id), 0) AS votes
			FROM posting
				INNER JOIN posting_product ON posting.posting_id = posting_product.posting_id
				LEFT JOIN posting_vote ON posting.posting_id = posting_vote.posting_id
			WHERE posting_product.vote_period_id = :vote_period_id
			GROUP BY posting.posting_id
			ORDER BY votes DESC
			' . $this->generate_limit_offset_str($params) . '
		';
		$values = array(
			':vote_period_id' => $params['where']['vote_period_id']
		);
		
		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get posts.');
		}
		
		return resultArray(true, $rows);
	}
	
	public function get_num_post_votes($params = array()) {
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
			FROM posting_vote
			WHERE posting_id = :posting_id
				AND vote_period_id = :vote_period_id
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
			, ':vote_period_id' => $params['where']['vote_period_id']
		);
		
		$data = $this->run($query, $values);
		$row = $data->fetchAll();
		if ($row === false) {
			 return resultArray(false, NULL, 'Could not get num post votes.');
		}
		
		return resultArray(true, $row[0]['count']);
	}
}
?>