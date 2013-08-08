<?
class Social_Network_Controller extends _Controller {
	protected $social_network_id;
	
	public function __construct($params = array()) {
		parent::__construct();
		
		// Validations
		$input_validations = array(
			'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					/*'is_set' => NULL
					, */'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();
		
		if (!empty($params['api_website_id'])) {
			$api_website_id = $params['api_website_id'];
			
			// Check that api_website_id is valid
			$this->load('API_Website');
			$api_website = $this->API_Website->get_row(
				array(
					'api_website_id' => $api_website_id
				)
			);
			if (empty($api_website)) {
				_Model::$Exception_Helper->request_failed_exception('Invalid API website ID.');
			}
		}
		
		$called_class = get_called_class();
		if (defined("$called_class::NAME")) {
			$this->load('Social_Network');
			
			$this->social_network_id = $this->Social_Network->get_primary_key_id_by_field_value('name', $called_class::NAME);
			if (empty($this->social_network_id)) {
				_Model::$Exception_Helper->request_failed_exception('Social network does not exist.');
			}
		}
	}
	
	public function login($params = array())
    {
        $logger = new Jk_Logger(APP_PATH.'logs/facebook.log');

		require_once DR . '/includes/php/functions-api.php';
		
		if (!isset($params['social_network_id'])) {
			if (isset($params['social_network'])) {
				$this->load('Social_Network');
				$this->social_network_id = $this->Social_Network->get_primary_key_id_by_field_value('name', $params['social_network']);
			}
			
			$params['social_network_id'] = $this->social_network_id;
		}
		if (!isset($params['api_website_id'])) {
			$params['api_website_id'] = $_SESSION['api_website_id'];
		}
		
		// Validations
		$input_validations = array(
			'first_name' => array(
				'label' => 'First Name'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'last_name' => array(
				'label' => 'Last Name'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'date_of_birth' => array(
				'label' => 'Date of Birth'
				, 'rules' => array(
					/*'is_set' => NULL
					, */'is_date' => NULL
				)
			)
			, 'email' => array(
				'label' => 'Email Address'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_email' => NULL
				)
			)
			, 'username' => array(
				'label' => 'Username'
				, 'rules' => array(
					'is_alpha_num_sym' => NULL
				)
			)
			, 'password' => array(
				'label' => 'Password'
			)
			, 'password_old' => array(
				'label' => 'Old Password'
			)
			, 'api_website_id' => array(
				'label' => 'API Website ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'social_network_id' => array(
				'label' => 'Social Network ID'
				, 'rules'  => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();
		
		// Check if email already exists
		$this->load('User');
		$existing_user = $this->User->check_social_network_email_exists($params['email'], $params['social_network_id']);
        $logger->LogInfo("IS EXISTING check_social_network ?: " . var_export($existing_user, true));

		$logout_url = !empty($params['logout_url']) ? $params['logout_url'] : '';

		// Get user info
		$user = $this->User->get_user($params['email']);

        $logger->LogInfo("LOCAL USER INFO: " . var_export($user, true));
        $logger->LogInfo("DO WE HAVE THIS ACTIVE EMAIL REGISTERED ?: " .  (strtolower(trim($user['email'])) == strtolower(trim($params['email'])) ? "TRUE" : "FALSE") );

        /////////////////////////////
        /////// PREPARE USER DATA ///
        $user_params = array(
            'user_id' => $user['user_id'],
            'username' => $params['username'],
            'email_address' => $params['email'],
            'first_name' => $params['first_name'],
            'last_name' => $params['last_name'],
            'fb_uid' => $params['fb_uid'],
        );

        $optional_params = array(
            'instagram_username',
            'pinterest_username',
            'gender',
            'location',
            'avatar',
            'date_of_birth',
            'fb_uid',
        );

        foreach ($optional_params as $param) {
            if (isset($params[$param])) {
                $user_params[$param] = $params[$param];
            }
        }
        /////////////////////////////


		// Check that dahliawolf user exists
		if ($existing_user || strtolower(trim($user[email])) == strtolower(trim($user_params['email_address'])) ) {
			// Scrape username
			$dw_params = array(
				'user_id' => $user['user_id']
			);
			$dw_user = api_call('user', 'get_user', $dw_params, true);

            $logger->LogInfo("dw_user ?: " . var_export($dw_user, true));

			
			if (!empty($dw_user['data']['pinterest_username'])) {
				$dw_params = array(
					'user_id' => $dw_user['data']['user_id']
					, 'username' => $dw_user['data']['pinterest_username']
				);
				api_call('feed_image', 'scrape_username', $dw_params, true);
			}
			if (!empty($dw_user['data']['instagram_username'])) {
				if (empty($dw_user['data']['pinterest_username']) || $dw_user['data']['instagram_username'] != $dw_user['data']['pinterest_username']) {
					$dw_params = array(
						'user_id' => $dw_user['data']['user_id'],
						'username' => $dw_user['data']['instagram_username']
					);
					$test = api_call('feed_image', 'scrape_username', $dw_params, true);
				}
			}

            if(!$dw_user['data']['fb_uid'] || trim($dw_user['data']['fb_uid']) == '')
            {
                //update fb_id
                $update_data = api_call('user', 'update_user_optional', $user_params);
            }

			// Generate token & insert login instance
			return $this->authen($existing_user, $logout_url);
		}
        else {
            $logger->LogInfo("register new user passed data:" . var_export($params, true));
            // Else register the user
            // if username is taken, should redirect back to register page to choose

			$this->load('User_Social_Network_Link');
			
			// Username exists
			if (!empty($params['username'])) {
				$existing_username = $this->User->get_row(
					array(
						'username' => $params['username']
					)
				);
				
				if (!empty($existing_username)) {
					// Get longest matched username
					$longest_match = $this->User->get_regexp_username($params['username']);
					
					// Generate new username
					$params['username'] = $longest_match['username'] . rand(0, 9);
					
					//_Model::$Exception_Helper->request_failed_exception('This username already exists. Please choose another.');
				}
			}

			// Email exists
			$existing_email = $this->User->get_row(
				array(
					'email' => $params['email']
				)
			);
			// If existing email, then merge
			if (!empty($existing_email)) {
				
				// Insert social_network_email_link
				$link = array(
				    'user_id' => $user['user_id'],
					'social_network_id' => $this->social_network_id
				);
				$this->User_Social_Network_Link->save($link);

                $update_data = api_call('user', 'update_user_optional', $user_params);
				
				// Authen login the user
				return $this->authen($user, $logout_url, true);
				
				//_Model::$Exception_Helper->request_failed_exception('This email already exists. Please use another.');
			}

            ////////////// USER DOES NOT EXIST CONTINUE, CREATE NEW ////////////.
			// first, last, username, email
			// Add user
            $user = array_merge($params,
                array(
                    'active' => 1,
                    'hash' => NULL,
                    'api_website_id' => $params['api_website_id'],
                    'fb_uid' => $params['fb_uid'],
                )
            );


            //offline_admin DB - save user
			$user['user_id'] = $this->User->save($user);

			
			// Add user user_group link
			$this->load('User_User_Group_Link');
			$link = array(
				'user_id' => $user['user_id']
				, 'user_group_id' => 2
				, 'user_group_portal_id' => 2
			);
			$this->User_User_Group_Link->save($link);


			// Add user social network link
			$link = array(
				'user_id' => $user['user_id'],
				'social_network_id' => $this->social_network_id
			);
			$this->User_Social_Network_Link->save($link);


            // commerce - Add customer
            $calls = array(
                'save_customer' => array(
                    'user_id' => $user['user_id'],
                    'firstname' => $params['first_name'],
                    'lastname' => $params['last_name'],
                    'email' => $params['email'],
                    'username' => $params['username'],
                    'fb_uid' => $params['fb_uid'],
                )
            );
            $data = commerce_api_request('customer', $calls, true);


            // dahliawolf -- add user

			$data = api_call('user', 'add_user', $user_params);
			
			return $this->authen($user, $logout_url, true);
		}
	}

	public function authen($user, $logout_url, $is_register = false) {
		require_once DR . '/1-0/lib/php/functions.php';
		require_once DR . '/1-0/lib/php/mysql-v8.php';
		require_once DR . '/1-0/lib/php/login-v4.php';
		
		global $_mysql;
		$_mysql = new mysql();
		
		$login = new login(array(), array());
		$error_code = NULL;
		
		//print_r($user);
		
		$authen = $login->social_authen($user);
			
		// Success: return user info and token
		if (!$authen) {
			$error_code_map = array(
				'The login information was incorrect.'
				, 'The login account has been deactivated.'
				, 'You have exceeded the maximum allowed number of login attempts. Please click the "Forgot Password" link to reset your password.'
			);
			if (empty($error_code)) {
				$error_code = 0;
			}
			$error = $error_code_map[$error_code];
			
			return static::wrap_result(true, NULL, $error);
		}
		else {
			// Unset hash
			unset($user['hash']);
			
			$data['user'] = $user;
			$data['token'] = $authen;
			$data['logout_url'] = $logout_url;
			
			$data_str = http_build_query($data);
			
			if (isset($_SESSION['redirect_url'])) {
				header('Location: ' . $_SESSION['redirect_url'] . '?authen=1' . ($is_register ? '&register=1' : '') . '&' . $data_str);
				die();
			}
			else {
				return static::wrap_result(true, $data);
			}
		}
	}
}
?>
	