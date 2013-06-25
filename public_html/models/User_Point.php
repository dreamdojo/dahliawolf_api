<?php

class User_Point extends db {

	private $table = 'user_point';

	public function __construct() { 
		parent::__construct();
	}
	
	public function add_user_point($params = array()) {
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
			return resultArray(false, NULL, 'Could not add user points.');
		}
		
		// Audit user points
		$this->audit_user_points($params['data']['user_id']);
		
		return resultArray(true, $insert_id);
	}
	
	public function delete_user_point($params = array()) {
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
		
		$this->delete($this->table, $params['where']);
		
		// Audit user points
		$this->audit_user_points($params['where']['user_id']);
		
		return resultArray(true, true);
	}
	
	public function audit_user_points($user_id) {
		$query = '
			UPDATE user_username
			SET points = (
				SELECT IFNULL(SUM(points), 0) AS points
				FROM user_point
				WHERE user_point.user_id = user_username.user_id
			)
			WHERE user_username.user_id = :user_id
		';
		$values = array(
			':user_id' => $user_id
		);
		$result = $this->run($query, $values);
		
		if ($result === false) {
			return resultArray(false, NULL, 'Could not audit user points.');
		}
		
		return true;
	}
	
	private function sum_user_points($user_id) {
		$query = '
			SELECT IFNULL(SUM(points), 0) AS points
			FROM user_point
			WHERE user_id = :user_id
		';
		$values = array(
			':user_id' => $user_id
		);
		
		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		
		return $rows[0]['points'];
	}
	
	public function get_user_points_by_day($user_id, $date, $point_id) {
		$query = '
			SELECT *
			FROM user_point
			WHERE user_id = :user_id
				AND DATE(created) = :date
				AND point_id = :point_id
			LIMIT 1
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
			, ':point_id' => $point_id
		);
		
		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		
		return $rows;
	}
	
	public function get_daily_count($user_id, $date) {
		$query = '
			SELECT SUM(points) AS count
			FROM user_point
			WHERE user_id = :user_id
				AND DATE(created) = :date
			LIMIT 1
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
		);
		
		$result = $this->run($query, $values);
		
		if ($result === false) {
			return resultArray(false, NULL, 'Could not daily points count.');
		}
		$rows = $result->fetchAll();
		
		return $rows[0]['count'];
	}
}
?>