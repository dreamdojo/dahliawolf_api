<?php

class Like_Winner extends db {

	private $table = 'like_winner';

	public function __construct() { 
		parent::__construct();
	}

	public function add_like_winner($params = array()) {
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
			return resultArray(false, NULL, 'Could not add like winner.');
		}
		
		return resultArray(true, $insert_id);
	}
}
?>