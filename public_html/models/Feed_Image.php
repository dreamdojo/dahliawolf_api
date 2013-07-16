<?php

class Feed_Image extends db {

	private $table = 'imageInfo';

	public function __construct() {
		parent::__construct('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME_REPOSITORY);
	}

	public function get_feed_images($params = array()) {
		$where_sql = '';
		$values = array();
		
		if (!empty($params['where']['status'])) {
			$where_sql .= ' AND status = :status';
			$values[':status'] = $params['where']['status'];
		}
		
		// Type (deprecated)
		$valid_types = array(
			'instagram' => 'distilleryimage'
			, 'pinterest' => 'pinterest.com'
		);
		if (!empty($params['where']['type']) && !empty($valid_types[$params['where']['type']])) {
			$where_sql .= ' AND baseurl LIKE :baseurl';
			$values[':baseurl'] = '%' . $valid_types[$params['where']['type']]. '%';
		}
		
		// Domain keyword
		if (!empty($params['where']['domain_keyword'])) {
			$where_sql .= ' AND site.domain_keyword = :domain_keyword';
			$values[':domain_keyword'] = $params['where']['domain_keyword'];
		}
		
		// User id
		$join_str = '';
		if (!empty($params['where']['user_id'])) {
			$valid_domain_keywords = array(
				'instagram'
				, 'pinterest'
			);
			$join_str = 'INNER JOIN dahliawolf_v1_2013.user_username ON (search.user_id = user_username.user_id'
				. (!empty($params['where']['domain_keyword']) && in_array($params['where']['domain_keyword'], $valid_domain_keywords) ? ' AND user_username.' . $params['where']['domain_keyword'] . '_username = search.keyword' : '') . ')'
			;
			$where_sql .= ' AND user_username.user_id = :user_id';
			//$where_sql .= ' AND search.user_id = :user_id';
			$values[':user_id'] = $params['where']['user_id'];
		}
		else {
			$where_sql .= ' AND search.user_id IS NULL';
		}
		
		$limit_sql = '';
		if (!empty($params['limit'])) {
			$limit_sql .= ' LIMIT ' . $params['limit'];
		}
		if (!empty($params['offset'])) {
			$limit_sql .= ' OFFSET ' . $params['offset'];
		}
		
		$order_by = 'imageInfo.created DESC, imageInfo.id DESC';
		if (!empty($params['order_by'])) {
			if ($params['order_by'] == 'rand') {
				$order_by = 'RAND()';
			}
		}

        /*
		$sql = '
			SELECT imageInfo.*
				, CONCAT("upload/", imageURL) AS src
				, IF(bigImageURL = "", NULL, CONCAT("upload/", bigImageURL)) AS big_src
				, imagename AS alt
				, imageInfo.keyword AS keywords
				, site.domain, site.domain_keyword
			FROM imageInfo
				LEFT JOIN search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
				LEFT JOIN search ON search_site_link.search_id = search.search_id
				LEFT JOIN site ON search_site_link.site_id = site.site_id
				' . $join_str . '
			WHERE imageURL IS NOT NULL AND imageURL != ""
			' . (!empty($where_sql) ? $where_sql : '') . '
			ORDER BY ' . $order_by . '
			' . $limit_sql . '
		';
        */

        $sql = "SELECT
                *
                FROM
                    (SELECT
                        imageInfo . *,
                            CONCAT('upload/', imageURL) AS src,
                            IF(bigImageURL = '', NULL, CONCAT('upload/', bigImageURL)) AS big_src,
                            imagename AS alt,
                            imageInfo.keyword AS keywords,
                            site.domain,
                            site.domain_keyword
                    FROM
                        imageInfo
                    LEFT JOIN search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                    LEFT JOIN search ON search_site_link.search_id = search.search_id
                    LEFT JOIN site ON search_site_link.site_id = site.site_id
                    $join_str
                    WHERE
                        imageURL IS NOT NULL AND imageURL != '' ".
                        (!empty($where_sql) ? $where_sql : '')
                        #ORDER BY id DESC
                    ."
                    LIMIT 1000 ) as images
                ORDER BY $order_by
                $limit_sql
                ";
		/*
		 * LEFT JOIN search ON imageInfo.search_id = search.search_id
				LEFT JOIN search_site_link ON search.search_id = search_site_link.search_id
				LEFT JOIN site ON search_site_link.site_id = site.site_id
		 */
		 if (isset($_GET['t'])) {
		 	echo $sql;
		 	print_r($values);
            //die();
		 }

        try{

            $result = $this->run($sql, $values);

            if (empty($result)) {
                 return resultArray(false, NULL, 'Could not get feed images.');
            }
            $rows = $result->fetchAll();
        }catch (Exception $e ) {$rows = array();}

		return resultArray(true, $rows);
	}
	
	public function get_feed_image($params = array()) {
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
		
		$row = $this->get_row($this->table, $params['where']);
		
		if (empty($row)) {
			 return resultArray(false, NULL, 'Could not get feed image.');
		}
		
		return resultArray(true, $row[0]);
	}
	
	public function update_feed_image($params = array()) {
		$error = NULL;
		
		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}
		else if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid conditions.';
		}
		
		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}
		
		$res = $this->update($this->table, $params['data'], $params['where']);
		if ($res === false) {
			 return resultArray(false, NULL, 'Could not update feed image.');
		}
		
		return resultArray(true, $res);
	}
	
	// domain_keyword, user_id
	function get_previous_feed_image($id, $params = array()) {
		$values = array(
			':id' => $id
		);
		
		$where_sql = '';
		
		// Status
		if (!empty($params['status'])) {
			$where_sql .= ' AND status = :status';
			$values[':status'] = $params['status'];
		}
		
		// Domain keyword
		if (!empty($params['domain_keyword'])) {
			$where_sql .= ' AND site.domain_keyword = :domain_keyword';
			$values[':domain_keyword'] = $params['domain_keyword'];
		}
		
		// User id
		$join_str = '';
		if (!empty($params['user_id'])) {
			$valid_domain_keywords = array(
				'instagram'
				, 'pinterest'
			);
			$join_str = 'INNER JOIN dahliawolf_v1_2013.user_username ON (search.user_id = user_username.user_id'
				. (in_array($params['domain_keyword'], $valid_domain_keywords) ? ' AND user_username.' . $params['domain_keyword'] . '_username = search.keyword' : '') . ')'
			;
			$where_sql .= ' AND user_username.user_id = :user_id';
			$values[':user_id'] = $params['user_id'];
		}
		else {
			$where_sql .= ' AND search.user_id IS NULL';
		}
		
		$query = '
			SELECT imageInfo.id
			FROM imageInfo
				INNER JOIN search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
				LEFT JOIN search ON search_site_link.search_id = search.search_id
				INNER JOIN site ON search_site_link.site_id = site.site_id
				' . $join_str . '
			WHERE imageInfo.id < :id
				' . $where_sql . '
			ORDER BY imageInfo.id DESC
			LIMIT 1
		';
		$result = $this->run($query, $values);
		
		if ($result) {
			$rows = $result->fetchAll();
			
			if ($rows) {
				return $rows[0]['id'];
			}
		}
		return NULL;
	}
	
	function get_next_feed_image($id, $params) {
		$values = array(
			':id' => $id
		);
		
		$where_sql = '';
		
		// Status
		if (!empty($params['status'])) {
			$where_sql .= ' AND status = :status';
			$values[':status'] = $params['status'];
		}

		// Domain keyword
		if (!empty($params['domain_keyword'])) {
			$where_sql .= ' AND site.domain_keyword = :domain_keyword';
			$values[':domain_keyword'] = $params['domain_keyword'];
		}
		
		// User id
		$join_str = '';
		if (!empty($params['user_id'])) {
			$valid_domain_keywords = array(
				'instagram'
				, 'pinterest'
			);
			$join_str = 'INNER JOIN dahliawolf_v1_2013.user_username ON (search.user_id = user_username.user_id'
				. (in_array($params['domain_keyword'], $valid_domain_keywords) ? ' AND user_username.' . $params['domain_keyword'] . '_username = search.keyword' : '') . ')'
			;
			$where_sql .= ' AND user_username.user_id = :user_id';
			$values[':user_id'] = $params['user_id'];
		}
		else {
			$where_sql .= ' AND search.user_id IS NULL';
		}
		
		$query = '
			SELECT imageInfo.id
			FROM imageInfo
				INNER JOIN search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
				LEFT JOIN search ON search_site_link.search_id = search.search_id
				INNER JOIN site ON search_site_link.site_id = site.site_id
				' . $join_str . '
			WHERE imageInfo.id > :id
				' . $where_sql . '
			ORDER BY imageInfo.id ASC
			LIMIT 1
		';
		$result = $this->run($query, $values);
		
		if ($result) {
			$rows = $result->fetchAll();
			
			if ($rows) {
				return $rows[0]['id'];
			}
		}
		return NULL;
	}
}
?>