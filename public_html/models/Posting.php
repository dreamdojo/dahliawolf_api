<?php

class Posting extends db {

	private $table = 'posting';

	public function __construct() {
		parent::__construct();
	}

	// ?api=category&function=addcategory&params={"data":{"name":"asdf;sfcsd"}}
	public function addPost($params = array()) {
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
			return resultArray(false, NULL, 'Could not add post.');
		}

		return resultArray(true, array('posting_id' => $insert_id));
	}


    public function  addPostView($request_data = array())
    {
        $post_view = new Posting_View();
        $response_data = $post_view->addView($request_data);

        return $response_data;
    }

	public function addPostLike($params = array()) {
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
			 return resultArray(false, NULL, 'Could not update category.');
		}

		return resultArray(true, $res);
	}

	public function increment_total_likes($params = array()) {
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
			UPDATE posting
			SET total_likes = total_likes + 1
			WHERE posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);
		$res = $this->run($query, $values);

		if ($res === false) {
			 return resultArray(false, NULL, 'Could not increment total likes.');
		}

		return resultArray(true, $res);
	}

	public function increment_total_votes($params = array()) {
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
			UPDATE posting
			SET total_votes = total_votes + 1
			WHERE posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);
		$res = $this->run($query, $values);

		if ($res === false) {
			 return resultArray(false, NULL, 'Could not increment total likes.');
		}

		return resultArray(true, $res);
	}

	// ?api=category&function=deletecategory&params={"where":{"id":"3"}}
	public function deletePost($params = array()) {
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

		$res = $this->delete($this->table, $params['where']);
		if ($res === false) {
			 return resultArray(false, NULL, 'Could not delete category.');
		}

		return resultArray(true, $res);
	}

	public function update_post($params = array()) {
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

		$data = array(
			'description' => $params['data']['description']
		);
		$where = array(
			'posting_id' => $params['where']['posting_id']
			, 'user_id' => $params['where']['user_id']
		);

		$result = $this->update('posting', $data, $where);
		if ($result === false) {
			 return resultArray(false, NULL, 'Could not update posting.');
		}

		return resultArray(true, array('posting_id' => $params['where']['posting_id'], 'row_count' => $this->num_rows));
	}

	public function soft_delete_post($params = array()) {
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

		$data = array(
			'deleted' => date('Y-m-d H:i:s')
		);
		$where = array(
			'posting_id' => $params['where']['posting_id']
			, 'user_id' => $params['where']['user_id']
		);

		$result = $this->update('posting', $data, $where);
		if ($result === false) {
			 return resultArray(false, NULL, 'Could not soft delete posting.');
		}

		return resultArray(true, array('posting_id' => $params['where']['posting_id'], 'row_count' => $this->num_rows));
	}

	// ?api=category&function=getCategory&params={"conditions":{"id":"4"}}
	public function getPostDetails($params = array()) {
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

		$values = array(
		);
		$from_prefix = 'posting';
        $posting_id = null;
		if (!empty($params['where']['posting_id'])) {
			$values[':posting_id'] = $params['where']['posting_id'];

            $posting_id = $params['where']['posting_id'];
		}
		else {
			$from_prefix = '
				(
					SELECT MAX(posting.posting_id) AS posting_id
					FROM posting
						' . (!empty($params['where']['viewer_user_id']) ? '
							LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id AND posting_like.user_id = :viewer_user_id
							LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id AND posting_dislike.user_id = :viewer_user_id
						' : '') . '
					WHERE posting.deleted IS NULL
						' . (!empty($params['where']['viewer_user_id']) ? '
							AND posting_like.user_id IS NULL
							AND posting_dislike.posting_id IS NULL
						' : '') . '
				) AS latest INNER JOIN posting ON latest.posting_id = posting.posting_id
			';
		}



		$select_str = '';
		$join_str = '';
		// Viewer (show if posts are liked/voted in relation)
		// Also show if user is following post user
        $viewer_user_id = null;
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked, IF(follow.follow_id IS NULL, 0, 1) AS is_following';
			$join_str = '
				LEFT JOIN posting_like ON (posting.posting_id = posting_like.posting_id
					AND posting_like.user_id = :viewer_user_id)
				LEFT JOIN follow ON (posting.user_id = follow.user_id
					AND follow.follower_user_id = :viewer_user_id)
			';
			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
            $viewer_user_id = $params['where']['viewer_user_id'];
		}

		$query = "
			SELECT posting.*,
			    image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height,
			    IFNULL(image.attribution_url, imageInfo.attribution_url) AS image_attribution_url,
			    image.domain AS image_attribution_domain
				, user_username.username, user_username.avatar, user_username.location
				, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
				, IFNULL(COUNT(pl.posting_like_id), 0) AS likes
				, imageInfo.baseurl
				, site.domain_keyword
				, IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner
                , product.id_product AS product_id, product.status, product.price, product.wholesale_price
                , (SELECT COUNT(*) FROM comment WHERE comment.posting_id = posting.posting_id) AS comments
                , (SELECT COUNT(*) FROM posting_share WHERE posting_share.posting_id = posting.posting_id) AS total_shares
                , (SELECT COUNT(*) FROM posting_view WHERE posting_view.posting_id = posting.posting_id) AS total_views
                , (SELECT COUNT(*) FROM posting_tag WHERE posting_tag.posting_id = posting.posting_id) AS `total_tags`
                , image.repo_image_id
				" . $select_str . "
			FROM " . $from_prefix . "
				INNER JOIN image ON posting.image_id = image.id
				INNER JOIN user_username ON posting.user_id = user_username.user_id
                LEFT JOIN posting_product as posting_product ON posting.posting_id = posting_product.posting_id
				LEFT JOIN offline_commerce_v1_2013.product AS product ON posting_product.product_id = product.id_product
				LEFT JOIN posting_like AS pl ON posting.posting_id = pl.posting_id
				LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON image.repo_image_id = imageInfo.id
				LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
				LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id
				LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
				" . $join_str
			 . (!empty($params['where']['posting_id']) ? 'WHERE posting.posting_id = :posting_id' : '') . "
		";
		if (isset($_GET['t'])) {
			echo $query;
			print_r($values);
		}

		//$row = $this->get_row($this->table, $params['conditions']);
		$data = $this->run($query, $values);
		$row = $data ? $data->fetchAll() : false;
		if ($row === false) {
			 return resultArray(false, NULL, 'Could not get posting.');
		}

        $post = $row[0];

        if ( $post['total_shares'] == '0')
        {
            $post['total_shares'] = '6666';
        }

        //// adding post view
        $request_data = array(
            'posting_id' => $posting_id,
            'user_id' => $post["user_id"],
            'viewer_user_id' => $viewer_user_id
        );

        self::addPostView($request_data);

		return
            resultArray(true, $post);
	}

	// ?api=category&function=allcategory
	public function allPosts($params = array()) {
		$order_by_str = 'created DESC';
        $outer_order_by_str = 'created DESC';

        $inner_order_by_columns = array(
			'created',
			'total_likes',
			'total_votes',
            'total_shares',
            'total_views',
		);

        if (!empty($params['order_by'])) {
            if (in_array($params['order_by'], $inner_order_by_columns)) {
                $order_by_str = "{$params['order_by']} DESC";
            }
        }

        $outer_select_str = "";
		$select_str = '';
		$join_str = '';
		$values = array();
		$sub_where_str = '';
        $user_id = $params['where']['user_id'];

		if (!empty($params['where'])) {
			if (!empty($params['where']['user_id'])) {
				$sub_where_str .= ' AND posting.user_id = :user_id';
				$values[':user_id'] = $params['where']['user_id'];
			}
			if (!empty($params['where']['username'])) {
				$sub_where_str .= ' AND user_username.username = :username';
				$values[':username'] = $params['where']['username'];
			}
			// Viewer (show if posts are liked/voted in relation)
			// Also don't show dislikes
			if (!empty($params['where']['viewer_user_id'])) {
				$select_str = ', IF(posting_like.user_id IS NULL, 0, 1) AS is_liked';
				$join_str = '
					LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id
						AND posting_like.user_id = :viewer_user_id
				';
				$values[':viewer_user_id'] = $params['where']['viewer_user_id'];

				// Dislike
				/*$join_str .= '
					LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id
						AND posting_dislike.user_id = :viewer_user_id
				';
				$sub_where_str .= ' AND posting_dislike.posting_id IS NULL';*/
			}
			// Search
			if (!empty($params['where']['q'])) {
				$sub_where_str .= ' AND (posting.description LIKE :q OR user_username.username LIKE :q)';
				$values[':q'] = '%' . $params['where']['q'] . '%';
			}

			// Since posting_id
			if (!empty($params['where']['since_posting_id'])) {
				$sub_where_str .= 'AND posting.posting_id > :since_posting_id';
				$values[':since_posting_id'] = $params['where']['since_posting_id'];
			}
		}


		// Hot (sort by likes within x days)
		if (!empty($params['like_day_threshold'])) {
			$outer_select_str = ', posting.day_threshold_likes';
			$hot_select_str = ', IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes';
			$join_str .= '
				INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
			';
            $order_by_str = 'day_threshold_likes DESC';

			$values[':like_day_threshold'] = $params['like_day_threshold'];

			// Only show posts within threshold
			$sub_where_str .= ' AND posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()';
			$group_by_str = 'GROUP BY posting.posting_id';
		}

		// Filter by following
		if (!empty($params['filter_by']) && !empty($params['follower_user_id'])) {
			$join_str .= '
				INNER JOIN follow ON user_username.user_id = follow.user_id
			';
			$sub_where_str .= ' AND follow.follower_user_id = :follower_user_id';
			$values[':follower_user_id'] = $params['follower_user_id'];
		}

		// Timestamp
		if (!empty($params['timestamp'])) {
			$sub_where_str .= ' AND posting.created <= :timestamp';
			$values[':timestamp'] = $params['timestamp'];
		}

        $outer_where_str = '';
        $active_limit = (60*60*24)*30;

        // filters
        $valid_filters = array(
            'is_winner'     => '(IF(like_winner.like_winner_id IS NOT NULL, 1, 0)) = 1',
            'is_not_winner' => '(IF(like_winner.like_winner_id IS NOT NULL, 1, 0)) = 0',
            'is_active'     => "(IF(UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP(), 1, 0 )) = 1",
            'is_expired'    => "(IF(UNIX_TIMESTAMP(posting.created)+2592000 > UNIX_TIMESTAMP(), 1, 0 )) = 0",
        );


        $inner_offset_limit = $this->generate_limit_offset_str($params, true);

		if (!empty($params['filter']) && isset( $valid_filters[$params['filter']] )) {
            $filter = $valid_filters[$params['filter']];
            $sub_where_str .= "\n\t\t\t\t\t AND  {$filter}";

            //if($inner_order_by_str != 'created DESC') $inner_offset_limit = '';
        }

        //// limit the restult set to failsafe 300,
        if(count($values) == 0 && empty($inner_offset_limit)) $inner_offset_limit = ' LIMIT 999';

        //// run the limits on the outer query, since already filtered by userid_id
        if(empty($user_id)) {
            //$inner_offset_limit = 'LIMIT 999';
            $outer_offset_limit = 'LIMIT 999';
        }

        $query = "
                SELECT posting.*
                    , IFNULL(COUNT(comment.comment_id), 0) AS comments
                    , imageInfo.baseurl, imageInfo.attribution_url, site.domain, site.domain_keyword
                    /*, (SELECT COUNT(*) FROM posting_view WHERE posting_view.posting_id = posting.posting_id) AS `total_views`*/
                    , (SELECT COUNT(*) FROM posting_share WHERE posting_id = posting.posting_id) AS `total_shares`
                    , (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`

                    {$outer_select_str}
      			FROM (
      					SELECT posting.*
      						, image.repo_image_id, image.imagename, image.source, image.dimensionsX AS width, image.dimensionsY AS height
      						, user_username.username, user_username.location, user_username.avatar
      						, CONCAT(image.source, 'image.php?imagename=', image.imagename) AS image_url
                            /*, IF(like_winner.like_winner_id IS NOT NULL, 1, 0) AS is_winner*/
                            , IF(UNIX_TIMESTAMP(posting.created)+$active_limit > UNIX_TIMESTAMP(), 1, 0 ) AS is_active
                            , FROM_UNIXTIME(UNIX_TIMESTAMP(posting.created)+$active_limit, '%c/%e/%Y') AS 'expiration_date'
                            /*, (SELECT COUNT(*) FROM posting_like  WHERE posting_like.posting_id = posting.posting_id) AS `total_likes`*/
      						{$select_str}
      						{$hot_select_str}
      					FROM posting
      						INNER JOIN image ON posting.image_id = image.id
      						INNER JOIN user_username ON posting.user_id = user_username.user_id
                            LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id

      						{$join_str}
      					WHERE image.imagename IS NOT NULL
      						AND posting.deleted IS NULL
      						 {$sub_where_str}
      					{$group_by_str}
                        ORDER BY {$order_by_str}
                        {$inner_offset_limit}
                ) AS posting

                LEFT JOIN dahliawolf_v1_2013.comment ON posting.posting_id = comment.posting_id
                LEFT JOIN dahliawolf_repository.imageInfo AS imageInfo ON posting.repo_image_id = imageInfo.id
                LEFT JOIN dahliawolf_repository.search_site_link AS search_site_link ON imageInfo.search_site_link_id = search_site_link.search_site_link_id
                LEFT JOIN dahliawolf_repository.site AS site ON search_site_link.site_id = site.site_id

                {$outer_where_str}
                GROUP BY posting.posting_id
                ORDER BY {$order_by_str}
                ";

        if (isset($_GET['t'])) {
			echo "$query\n";
			print_r($values);
            if(isset($_GET['die']))die();
		}


		//$rows = $this->get_all($this->table);
		$result = $this->run($query, $values);

		if (empty($result)) {
			 return resultArray(false, NULL, 'Could not get posts.');
		}
		$rows = $result->fetchAll();

        if (isset($_GET['t'])) { echo sprintf("result count %s\n\n", count($rows)); }

		//temporary
		$new_row = array();
		foreach($rows AS $key => $value) {
			//echo "$key $value<br>";
			//print_r($value);
			$temp = array();
			foreach($value AS $key2 => $value2) {
                $temp[$key2] = $value2;
			}
			$new_row[$key] = $temp;
		}

		return resultArray(true, $new_row);
	}


	public function activate_product($params = array()) {
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

		// Make commerce API call to set product status to active
		$product_id = 1;

		return resultArray(true, $product_id);
	}

	public function get_vote_posts($params = array()) {
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


		$values = array(
			':vote_period_id' => $params['where']['vote_period_id']
		);

		$select_str = '';
		$join_str = '';
		// Viewer (show if posts are liked/voted in relation)
		if (!empty($params['where']['viewer_user_id'])) {
			$select_str = ', IF(posting_vote.user_id IS NULL, 0, 1) AS is_voted';
			$join_str = '
				LEFT JOIN posting_vote ON posting.posting_id = posting_vote.posting_id
					AND posting_vote.vote_period_id = :vote_period_id
					AND posting_vote.user_id = :viewer_user_id

                LEFT JOIN offline_commerce_v1_2013.favorite_product AS wishlist   ON wishlist.id_product = product.id_product  AND wishlist.id_customer = :viewer_user_id
			';

            $select_str .= "\n, IFNULL( COUNT(wishlist.id_customer) > 0, 0) AS in_wishlist";

			$values[':viewer_user_id'] = $params['where']['viewer_user_id'];
		}

		$query = "
			SELECT
			    posting.*,
			    count( (select count(*) from offline_commerce_v1_2013.favorite_product)) AS `wishlist_count`
				, image.imagename, image.source
				, user_username.username, user_username.location, user_username.avatar
				, posting_product.product_id
				, product_shop.position
				, vote_period.vote_period_id
				, IFNULL(COUNT(pv.posting_id), 0) AS votes
				" . $select_str . "
				, product_lang.name AS product_name
				, product.id_product, product.status, product.price, product.wholesale_price, product.commission, product.commission_from_date, product.commission_to_date
				, CONCAT('http://content.dahliawolf.com/shop/product/image.php?file_id=', (SELECT product_file_id FROM offline_commerce_v1_2013.product_file WHERE product_id = product_lang.id_product ORDER BY product_file_id ASC LIMIT 1)) AS image_url
				, CONCAT('http://content.dahliawolf.com/shop/product/inspirations/image.php?id_product=', product_lang.id_product) AS inspiration_image_url

				, (SELECT GROUP_CONCAT(pp.posting_id ORDER BY pp.is_primary DESC, pp.created ASC SEPARATOR '|') AS posting_ids
					FROM posting_product AS pp
					WHERE pp.product_id = posting_product.product_id
					GROUP BY pp.product_id) AS posting_ids

			FROM
				posting_product
				INNER JOIN posting ON posting_product.posting_id = posting.posting_id
				LEFT JOIN offline_commerce_v1_2013.product AS product ON posting_product.product_id = product.id_product
				INNER JOIN image ON posting.image_id = image.id
				INNER JOIN user_username ON posting.user_id = user_username.user_id
				INNER JOIN offline_commerce_v1_2013.product_lang AS product_lang ON (posting_product.product_id = product_lang.id_product AND product_lang.id_lang = 1)
				INNER JOIN vote_period ON posting_product.vote_period_id = vote_period.vote_period_id
				LEFT JOIN posting_vote AS pv ON posting.posting_id = pv.posting_id AND pv.vote_period_id = :vote_period_id
                LEFT JOIN offline_commerce_v1_2013.product_shop AS product_shop ON product_shop.id_product = posting_product.product_id

            " . $join_str . "

				LEFT JOIN offline_commerce_v1_2013.favorite_product AS favorite_product ON favorite_product.id_product = product.id_product

			WHERE vote_period.vote_period_id = :vote_period_id AND posting_product.is_primary = 1
			GROUP BY posting.posting_id
			ORDER BY product_shop.position ASC, posting_product.created DESC
			" . $this->generate_limit_offset_str($params) . "
		;";

        if($_GET['t']){
            echo "bind params: \n" . var_export($params, true) . "\n";
            echo $query;
        }

		//$rows = $this->get_all($this->table);
		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get posts.');
		}

		// Get individual postings
		// Loop through individual posting ids and grab post details
		foreach ($rows as $i => $row) {
			$posting_ids = explode('|', $row['posting_ids']);
			if (count($posting_ids) > 1 || 1) {
				$postings = array();

				foreach ($posting_ids as $posting_id) {
					$posting_params = array(
						'where' => array(
							'posting_id' => $posting_id
						)
					);
					$posting = $this->getPostDetails($posting_params);

					if (!empty($posting) && !empty($posting['data'])) {
						array_push($postings, $posting['data']);
					}
				}

				if (!empty($postings)) {
					$rows[$i]['posts'] = $postings;
				}
			}

			unset($rows[$i]['posting_ids']);
		}

		//temporary
		$new_row = array();
		foreach($rows AS $key => $value) {
			//echo "$key $value<br>";
			//print_r($value);
			$temp = array();
			foreach($value AS $key2 => $value2) {
                $temp[$key2] = $value2;
			}
			$new_row[$key] = $temp;
			$rows = $new_row;
		}

		return resultArray(true, $rows);
	}

	public function get_top_liked_posts_by_day($params = array()) {
		$error = NULL;

		if (empty($params['conditions'])) {
			$error = 'Conditions are required.';
		}
		else if (!is_array($params['conditions'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			SELECT
			    posting.*,
			    IFNULL(COUNT(posting_like.posting_like_id), 0) AS likes,
                IF(like_winner.like_winner_id IS NOT NULL, true, false) AS is_past_like_winner,
                IF(vote_winner.vote_winner_id IS NOT NULL, true, false) AS is_past_vote_winner
			FROM posting
				LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id
				LEFT JOIN like_winner ON posting.posting_id = like_winner.posting_id
				LEFT JOIN vote_winner ON posting.posting_id = vote_winner.posting_id
			WHERE DATE_FORMAT(posting_like.created, "%Y-%m-%d") = :date
			GROUP BY posting.posting_id
			ORDER BY likes DESC
			' . $this->generate_limit_offset_str($params) . '
		';
		$values = array(
			':date' => $params['conditions']['date']
		);

		$result = $this->run($query, $values);
		$rows = $result->fetchAll();
		if ($rows === false) {
			 return resultArray(false, NULL, 'Could not get posts.');
		}

		return resultArray(true, $rows);
	}

	public function get_num_post_likes_by_day($params = array()) {
		$error = NULL;

		if (empty($params['conditions'])) {
			$error = 'Conditions are required.';
		}
		else if (!is_array($params['conditions'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		/*$query = '
			SELECT posting.*
				, IFNULL(COUNT(posting_like.posting_like_id), 0) AS likes
			FROM posting
				INNER JOIN posting_like ON posting.posting_id = posting_like.posting_id
			WHERE DATE_FORMAT(posting_like.created, "%Y-%m-%d") = :date
				AND posting.posting_id = :posting_id
			GROUP BY posting_like.posting_id
		';*/
		$query = '
			SELECT posting.*
				, IFNULL(COUNT(pl.posting_like_id), 0) AS likes
			FROM posting
				LEFT JOIN (
					SELECT *
					FROM posting_like
					WHERE DATE_FORMAT(posting_like.created, "%Y-%m-%d") = :date
						AND posting_like.posting_id = :posting_id
				) AS pl ON posting.posting_id = pl.posting_id
			WHERE posting.posting_id = :posting_id
			GROUP BY posting.posting_id
		';
		$values = array(
			':date' => $params['conditions']['date']
			, ':posting_id' => $params['conditions']['posting_id']
		);

		$result = $this->run($query, $values);
		if (empty($result)) {
			 return resultArray(false, NULL, 'Could not get post likes.');
		}

		$rows = $result->fetchAll();
		if (empty($rows)) {
			$return = array(
				'posting_id' => $params['conditions']['posting_id']
				, 'likes' => 0
			);
			return resultArray(true, $return);
		}

		return resultArray(true, $rows[0]);
	}

	public function get_num_votes_by_period($params = array()) {
		$error = NULL;

		if (empty($params['conditions'])) {
			$error = 'Conditions are required.';
		}
		else if (!is_array($params['conditions'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			SELECT posting.*, IFNULL(COUNT(posting_vote.posting_vote_id), 0) AS votes
			FROM posting
				LEFT JOIN posting_vote ON posting.posting_id = posting_vote.posting_id
			WHERE (posting_vote.vote_period_id = :vote_period_id OR posting_vote.posting_vote_id IS NULL)
				AND posting.posting_id = :posting_id
			GROUP BY posting_vote.posting_id
		';
		$values = array(
			':posting_id' => $params['conditions']['posting_id']
			, ':vote_period_id' => $params['conditions']['vote_period_id']
		);

		$result = $this->run($query, $values);
		if (empty($result)) {
			 return resultArray(false, NULL, 'Could not get post votes.');
		}
		$rows = $result->fetchAll();

		return resultArray(true, $rows[0]);
	}

	public function get_previous_posting_id($posting_id, $created, $total_likes, $viewer_user_id = NULL, $next=null)
    {
        $notin_posting_array = array();
        $notin_posting_array[] = ":posting_id";

        if($next && intval($next) > 1)
        {
            $notin_posting_array[] = ":next";
        }

        $notin_posting_str =  trim(implode(", ", $notin_posting_array), ',');

		$query = "
			SELECT * FROM

                (SELECT posting.posting_id
                        , IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes

                FROM posting
                    " . (!empty($viewer_user_id) ? "
                        LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id AND posting_like.user_id = :viewer_user_id
                            LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id AND posting_dislike.user_id = :viewer_user_id
                    " : '') . "
                        INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
                WHERE posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()
                    AND posting.posting_id NOT IN ({$notin_posting_str})
                    AND posting.deleted IS NULL
                    " . (!empty($viewer_user_id) ? "
                        AND posting_like.user_id IS NULL
                        AND posting_dislike.posting_id IS NULL
                    " : '') . "
                #, posting.posting_id DESC
                GROUP BY posting.posting_id
                ORDER BY day_threshold_likes DESC, posting.created ASC
                ) AS sub_posting

            WHERE day_threshold_likes >= :total_likes
            ORDER BY day_threshold_likes ASC
            LIMIT 1
		";

		$values = array(
			':posting_id' => $posting_id,
			':created' => $created,
			':like_day_threshold' => 30,
            ':total_likes' => $total_likes
		);

        if ($next) {
            $values[':next'] = $next;
        }

        if (!empty($viewer_user_id)) {
            $values[':viewer_user_id'] = $viewer_user_id;
        }


        if (isset($_GET['t'])) {
            echo "$query \n";
            echo "get_previous_posting_id \n";
            print_r($values);
        }



		$result = $this->run($query, $values);

		if ($result) {
			$rows = $result->fetchAll();

			if ($rows) {
				return $rows[0]['posting_id'];
			}
		}
		return NULL;
	}

	public function get_next_posting_id($posting_id, $created, $total_likes, $viewer_user_id = NULL, $next =null, $limit=1 )
    {
        $notin_posting_array = array();
        $notin_posting_array[] = ":posting_id";


        $filter_likes = "";
        if($posting_id){
            $filter_likes = "WHERE day_threshold_likes <= :total_likes";
        }

        $notin_posting_array[] = ":posting_id";

        if($next && intval($next) > 1)
        {
            $notin_posting_array[] = ":next";
        }

        $notin_posting_str =  trim(implode(", ", $notin_posting_array), ',');

		$query = "
			SELECT * FROM
                (SELECT posting.posting_id
			    , IFNULL(COUNT(posting_like_hot.posting_id), 0) AS day_threshold_likes
			    , posting.created
                FROM posting
                    " . (!empty($viewer_user_id) ? "
                        LEFT JOIN posting_like ON posting.posting_id = posting_like.posting_id AND posting_like.user_id = :viewer_user_id
                        LEFT JOIN posting_dislike ON posting.posting_id = posting_dislike.posting_id AND posting_dislike.user_id = :viewer_user_id
                    " : '') . "
                    INNER JOIN posting_like AS posting_like_hot ON posting.posting_id = posting_like_hot.posting_id
                WHERE posting.created BETWEEN DATE_SUB(NOW(), INTERVAL :like_day_threshold DAY) AND NOW()
                    AND posting.posting_id NOT IN ({$notin_posting_str})
                    AND posting.deleted IS NULL
                        " . (!empty($viewer_user_id) ? "
                    AND posting_like.user_id IS NULL
                    AND posting_dislike.posting_id IS NULL
                        " : '') . "
                GROUP BY posting.posting_id
                ORDER BY day_threshold_likes DESC, posting.created ASC
                ) AS sub_posting
            {$filter_likes}
            ORDER BY day_threshold_likes DESC
            LIMIT {$limit}
		";


		$values = array(
			':posting_id' => $posting_id? $posting_id : 0,
	        ':like_day_threshold' => 30,
		);

        if($posting_id){
            $values[':total_likes'] = $total_likes;
        }

        if($created){
            $values[':created'] = $created;
        }

        if ($next) {
            $values[':next'] = $next;
        }

		if (!empty($viewer_user_id)) {
			$values[':viewer_user_id'] = $viewer_user_id;
		}

        //$this->debug();
        if (isset($_GET['t'])) {
			echo "$query\n\n";
            echo "get_next_posting_id \n";
			print_r($values);
		}

		$result = $this->run($query, $values);


		if ($result) {
			$rows = $result->fetchAll();

			if ($rows) {
				return $rows[0]['posting_id'];
			}
		}

		return NULL;
	}
}
?>