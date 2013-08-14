<?
class Activity_Log_Controller extends _Controller {
	public function get_log($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$user_id = $params['user_id'];
		$api_website_id = !empty($params['api_website_id']) ? $params['api_website_id'] : NULL;
		$data = $this->Activity_Log->get_log($user_id, $api_website_id);

		return static::wrap_result(true, $data);
	}

	public function get_grouped_log($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
        $activity_log = new Activity_Log();
        

		$user_id = $params['user_id'];
		$api_website_id = !empty($params['api_website_id']) ? $params['api_website_id'] : NULL;

		// Like winners
		$posts = $activity_log->get_like_winners_log($user_id, $api_website_id);

		// Comments
		$comments = $activity_log->get_commented_posts_log($user_id, $api_website_id);

		// Likes
		$likes = $activity_log->get_liked_posts_log($user_id, $api_website_id);

		// Followers
		$followers = $activity_log->get_followers_log($user_id, $api_website_id);

        // Messages
        $messages = $activity_log->get_messages_log($user_id, $api_website_id, 39);

		$data = array(
			'posts' => $posts,
			'comments' => $comments,
			'likes' => $likes,
			'followers' => $followers,
			'messages' => $messages,
		);

		return static::wrap_result(true, $data);

	}

	public function mark_previewed($params = array()) {
		$activity_id_map = array(
			19 => 'posts'
			, 32 => 'comments'
			, 9 => 'likes'
			, 34 => 'followers'
		);

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'type' => array(
				'label' => 'Type'
				, 'rules' => array(
					'is_in' => $activity_id_map
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$activity_id = NULL;
		if (!empty($params['type'])) {
			$activity_id = array_search($params['type'], $activity_id_map);
		}

		$this->load('Activity_Log');
		$data = $this->Activity_Log->mark_previewed($params['user_id'], _Model::$date_time, $activity_id);

		return static::wrap_result(true, $data);
	}

	public function mark_read($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'activity_log_id' => array(
				'label' => 'Activity Log ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$data = $this->Activity_Log->mark_read($params, _Model::$date_time);

		return static::wrap_result(true, $data);
	}

	public function mark_unread($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'activity_log_id' => array(
				'label' => 'Activity Log ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$data = $this->Activity_Log->mark_unread($params['activity_log_id'], $params['user_id']);

		return static::wrap_result(true, $data);
	}

	public function get_num_unread($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$data = $this->Activity_Log->get_num_unread($params['user_id'], $params['api_website_id']);

		return static::wrap_result(true, $data);
	}

	public function get_num_grouped_unread($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$user_id = $params['user_id'];
		$api_website_id = !empty($params['api_website_id']) ? $params['api_website_id'] : NULL;

		// Like winners
		$num_posts = $this->Activity_Log->get_like_winners_log_count($user_id, $api_website_id);

		// Comments
		$num_comments = $this->Activity_Log->get_commented_posts_log_count($user_id, $api_website_id);

		// Likes
		$num_likes = $this->Activity_Log->get_liked_posts_log_count($user_id, $api_website_id);

		// Followers
		$num_followers = $this->Activity_Log->get_followers_log_count($user_id, $api_website_id);

		$data = $num_posts[0]['count'] + $num_comments[0]['count'] + $num_likes[0]['count'] + $num_followers[0]['count'];

		return static::wrap_result(true, $data);
	}

	public function get_num_grouped_unpreviewed($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('Activity_Log');
		$user_id = $params['user_id'];
		$api_website_id = !empty($params['api_website_id']) ? $params['api_website_id'] : NULL;

		// Like winners
		$num_posts = $this->Activity_Log->get_like_winners_log_count($user_id, $api_website_id, true);

		// Comments
		$num_comments = $this->Activity_Log->get_commented_posts_log_count($user_id, $api_website_id, true);

		// Likes
		$num_likes = $this->Activity_Log->get_liked_posts_log_count($user_id, $api_website_id, true);

		// Followers
		$num_followers = $this->Activity_Log->get_followers_log_count($user_id, $api_website_id, true);

		$data = $num_posts[0]['count'] + $num_comments[0]['count'] + $num_likes[0]['count'] + $num_followers[0]['count'];

		return static::wrap_result(true, $data);
	}

    /****** old activity func */
    /*
    public function log_activity($params = array()) {
    		// Validations
    		$input_validations = array(
    			'user_id' => array(
    				'label' => 'User ID'
    				, 'rules' => array(
    					'is_set' => NULL
    					, 'is_int' => NULL
    				)
    			)
    			, 'activity_id' => array(
    				'label' => 'Activity ID'
    				, 'rules' => array(
    					'is_set' => NULL
    					, 'is_int' => NULL
    				)
    			)
    			, 'note' => array(
    				'label' => 'Note'
    				, 'rules' => array(
    					'is_set' => NULL
    				)
    			)
    			, 'api_website_id' => array(
    				'label' => 'API Website ID'
    				, 'rules' => array(
    					'is_int' => NULL
    				)
    			)
    		);
    		$this->Validate->add_many($input_validations, $params, true);
    		$this->Validate->run();

    		$this->load('Activity_Log');
    		$activity = array(
    			'user_id' => $params['user_id']
    			, 'api_website_id' => !empty($params['api_website_id']) ? $params['api_website_id'] : NULL
    			, 'activity_id' => $params['activity_id']
    			, 'note' => $params['note']
    			, 'entity' => !empty($params['entity']) ? $params['entity'] : NULL
    			, 'entity_id' => !empty($params['entity_id']) ? $params['entity_id'] : NULL
    		);
    		$data = $this->Activity_Log->save($activity);

    		return static::wrap_result(true, $data);
    	}

    */


	public function log_activity($params = array()) {
        $this->load('Activity_Log');
        $data = Activity_Log::saveActivity($params);
		return static::wrap_result(true, $data);
	}
}
?>