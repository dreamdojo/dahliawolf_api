<?php

class Vote_Period extends db {

	private $table = 'vote_period';

	public function __construct() { 
		parent::__construct();
	}

	public function add_vote_period($params = array()) {
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
			return resultArray(false, NULL, 'Could not add vote period.');
		}
		
		return resultArray(true, $insert_id);
	}
	
	public function get_current_vote_period() {
		$sql = '
			SELECT vote_period.*
			FROM vote_period
			WHERE (end IS NULL OR DATE(end) >= CURDATE())
				AND DATE(start) <= CURDATE()
			ORDER BY start ASC
			LIMIT 1
		';
		$result = $this->run($sql);
		$rows = $result->fetchAll();
		
		if ($rows === false) {
			return resultArray(false, NULL, 'Could not get current vote period.');
		}
		
		return resultArray(true, $rows[0]);
	}
}
?>