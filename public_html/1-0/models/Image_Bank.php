<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Image_Bank extends _Model
{

    const TABLE = 'country';
   	const PRIMARY_KEY_FIELD = 'country_id';

	private $table = 'imageInfo';

    public function __construct($db_host = REPO_API_HOST, $db_user = REPO_API_USER, $db_password = REPO_API_PASSWORD, $db_name = REPO_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

	public function getFeed($params = array()) {
		$where_sql = '';
		$values = array();

        $where_sql .= ' AND status = :status';
        $values[':status'] = 'Approved';
		/*
        if (!empty($params['status'])) {
		}*/

		// Type (deprecated)
		$valid_types = array(
			'instagram' => 'distilleryimage',
			'pinterest' => 'pinterest.com'
		);
		if (!empty($params['type']) && !empty($valid_types[$params['type']])) {
			$where_sql .= ' AND baseurl LIKE :baseurl';
			$values[':baseurl'] = '%' . $valid_types[$params['type']]. '%';
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
				'instagram',
				'pinterest'
			);
			$join_str = 'INNER JOIN dahliawolf_v1_2013.user_username ON (search.user_id = user_username.user_id'
				. (!empty($params['domain_keyword']) && in_array($params['domain_keyword'], $valid_domain_keywords) ? ' AND user_username.' . $params['domain_keyword'] . '_username = search.keyword' : '') . ')'
			;
			$where_sql .= ' AND user_username.user_id = :user_id';
			//$where_sql .= ' AND search.user_id = :user_id';
			$values[':user_id'] = $params['user_id'];
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

		$order_by = 'created DESC, id DESC';
		if (!empty($params['order_by'])) {
			if ($params['order_by'] == 'rand') {
				$order_by = 'RAND()';
			}
		}

        $order_by = 'RAND()';


        $sql = "SELECT
                *
                FROM
                    (SELECT
                        imageInfo.*,
                            CONCAT('upload/', imageURL) AS src,
                            IF(bigImageURL = '', NULL, CONCAT('upload/', bigImageURL)) AS big_src,
                            imagename AS alt,
                            imageInfo.keyword AS keywords,
                            #site.domain,
                            site.domain_keyword
                    FROM
                        imageInfo
                    LEFT JOIN search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                    LEFT JOIN search ON search_site_link.search_id = search.search_id
                    LEFT JOIN site ON search_site_link.site_id = site.site_id
                    $join_str
                    WHERE
                        imageURL IS NOT NULL AND imageURL != ''
                        {$where_sql}
                    ORDER BY id DESC
                    LIMIT 3000 ) as images
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
         }

        self::trace(__FUNCTION__ . " $sql");

        try{
            $data = $this->fetch($sql, $values);

            if (empty($data)) {
                 return array('error' => 'Could not get feed images.');
            }
        }catch (Exception $e ) {
            $data = array();

            self::trace($e->getMessage());

        }

        foreach($data as &$image)
        {
            if( strpos($image['imageURL'], '?') > -1 )
            {
                $image['imageURL'] = "image.php?imagename={$image['imageURL']}";
            }
        }

		return $data;
	}

	public function get_feed_image($params = array())
    {
		$error = NULL;

		if (empty($params)) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params)) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$row = $this->get_row($this->table, $params);

		if (empty($row)) {
            return array('error' => 'Could not get feed image.');
		}

		return $row;
	}

	public function update_feed_image($params = array()) {
		$error = NULL;

		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}
		else if (empty($params)) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params)) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$ok = $this->update($this->table, $params['data'], $params);
		if ($ok === false) {
            return array('error' => 'Could not update feed image.');
		}

		return $ok;
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

        //$result = $this->run($query, $values);

        $data = $this->get_row($query, $values);

		if ($data) {
			if ($data) {
				return $data['id'];
			}
		}

		return NULL;
	}

	function get_next_feed_image($id, $params)
    {
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

        //$data = $this->query($query, $values);
        $data = $this->get_row($query, $values);

		if ($data){
            return $data['id'];
		}

		return NULL;
	}
}
