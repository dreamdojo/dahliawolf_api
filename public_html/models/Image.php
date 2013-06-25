<?php

class Image extends db {

	private $table = 'image';

	public function __construct() { 
		parent::__construct();
	}

	public function add_image($params = array()) {
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
			return resultArray(false, NULL, 'Could not add image.');
		}
		
		return resultArray(true, $insert_id);
	}
	
	public function get_post_repo_image($params = array()) {
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
		
		$query = '
			SELECT image.repo_image_id
			FROM image
				INNER JOIN posting ON image.id = posting.image_id
			WHERE posting.posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);
		
		$result = $this->run($query, $values);
		if (empty($result)) {
			 return resultArray(false, NULL, 'Could not get post repo image.');
		}
		$rows = $result->fetchAll();
		
		return resultArray(true, $rows[0]);
	}
	
}
?>