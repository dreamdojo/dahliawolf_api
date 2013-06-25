<?php

class Posting_Dislike extends db {

	private $table = 'posting_dislike';

	public function __construct() { 
		parent::__construct();
	}

	public function add_post_dislike($params = array()) {
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
			return resultArray(false, NULL, 'Could not add post dislike.');
		}
		
		return resultArray(true, array('posting_dislike_id' => $insert_id));
	}
}
?>