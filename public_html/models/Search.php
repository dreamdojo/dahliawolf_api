<?php

class Search extends db {

	private $table = 'search';

	public function __construct() { 
		parent::__construct('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_REPOSITORY);
	}
	
	public function add_username($params = array()) {
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
			return resultArray(false, NULL, 'Could not add search term.');
		}
		
		return resultArray(true, array('search_id' => $insert_id));
	}
	
	public function add_site_link($search_id, $site) {
		$data = array(
			'search_id' => $search_id
			, 'site_id' => $site['site_id']
			//, 'url' => str_replace('{USERNAME}', $username, $site['url'])
		);
		$this->insert('search_site_link', $data);
		
		$insert_id = $this->insert_id;
		
		if (empty($insert_id)) {
			return resultArray(false, NULL, 'Could not add search term.');
		}
		
		return resultArray(true, array('search_site_link_id' => $insert_id));
	}
	
	public function get_site($domain_keyword, $type) {
		$where = array(
			'domain_keyword' => $domain_keyword
			, 'type' => $type
		);
		$site = $this->get_row('site', $where);
		
		if (!empty($site)) {
			return $site[0];
		}
		
		return NULL;
	}
	
	public function unindex($keyword, $user_id = NULL) {
		$data = array(
			'indexed' => '0'
		);
		$where = array(
			'keyword' => $keyword
		);
		if (!empty($user_id)) {
			$where['user_id'] = $user_id;
		}
		return $this->update('search', $data, $where);
	}
}
?>