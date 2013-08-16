<?
require 'lib/php/login-v4.php';
require 'lib/php/mysql-v8.php';
require 'lib/php/functions.php';

class User_Controller extends _Controller {
	private $PasswordHash;

	public function __construct() {
		parent::__construct();

		$this->PasswordHash = new PasswordHash(8, FALSE);
	}

	public function token_login($params = array()) {
		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'token' => array(
				'label' => 'Token'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('User');
		$user = $this->User->get_user_by_token($params['user_id'], $params['token']);

		return static::wrap_result(true, $user);
	}

	public function social_login($params = array()) {
		// Validations
		$input_validations = array(
			'social_network' => array(
				'label' => 'Social Network'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$this->load('User');
		$this->load('Social_Network');

		$social_network = $params['social_network'];
		$social_id = $this->Social_Network->get_primary_key_id_by_field_value('name', $social_network);
		if (empty($social_id)) {
			_Model::$Exception_Helper->request_failed_exception('Social network does not exist.');
		}

		//check to see email exists for social login
		$existing_email = $this->User->get_row(
			array(
				'email' => $params['email']
				, 'social_id' => $social_id
			)
		);

		if (!empty($existing_email)) {
			//_Model::$Exception_Helper->request_failed_exception('This email already exists. Please use another.');
			//check to see if this email is associated or linked to any existing EMAIL Account
			//OR if the email has valid token and flagged social authority to be logged in
            $login = new login(array(), array());

			// Authentication
			$authen = $login->authen($email_or_username, $password, 1, $error_code, $social_id);

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
	}
			else {
				$data['user'] = $this->User->filter_columns($login->user);
				$data['token'] = $authen;
			}

		} else {
			//check to see email exists, period
			$existing_email = $this->User->get_row(
				array(
					'email' => $params['email']
				)
			);

			//then add the social id flag
			if (!empty($existing_email)) {
				//update the social user table on $social_id
			} else {
				//register this email as a social user if there is a valid token
				$this->save_user($params = array());

				//$params['appName'] = "facebook"
				//$params['app_userid'] = 121
				//$params['app_username'] = "joe"
				//$params['firstname'] = "Joe"
				//$params['lastname'] = "Doe"
				//$params['email'] = joe@doe.com
				//$params['token'] = "adsf23kasdff"
				//$params['expire_time'] =
			}

		}
	}

	public function login($params = array()) {
		$this->load('User');
		$data = array();

		// Set parameters
		array_walk_recursive($params, array($this, 'convert_null_value'));

		// Params
		$email_or_username = !empty($params['email']) ? $params['email'] : (!empty($params['username']) ? $params['username'] : NULL);
		$password = !empty($params['password']) ? $params['password'] : NULL;

		$error = NULL;
		$data = array();

		// mysql class for login
		global $_mysql;
		$_mysql = new mysql();

		$login = new login(array(), array());
		$error_code = NULL;

		// Authentication
		$authen = $login->authen($email_or_username, $password, 1, $error_code);

		// Failed: return error message
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
		}
		// Success: return user info and token
		else {
			$data['user'] = $this->User->filter_columns($login->user);
			$data['token'] = $authen;
		}

		if (!empty($error)) {
			_Model::$Exception_Helper->request_failed_exception($error);
		}

		return static::wrap_result(true, $data);
	}

	public function logout($params = array()) {
		if (empty($params['user_id'])) {
			_Model::$Exception_Helper->bad_request_exception('User id is not set.');
		}
		else if (empty($params['token'])) {
			_Model::$Exception_Helper->bad_request_exception('Token is not set');
		}

		// mysql class for login
		global $_mysql;
		$_mysql = new mysql();

		$login = new login(array(), array());

		$error = NULL;
		$result = $login->logout($params['user_id'], $params['token'], $error);

		if (!$result) {
			_Model::$Exception_Helper->request_failed_exception($error);
		}

		return static::wrap_result(true, NULL, _Model::$Status_Code->get_status_code_no_content());
	}

	public function save_user($params = array()) {

        $logger = new Jk_Logger(APP_PATH.'logs/user.log');

        $logger->LogInfo("user save init params: " . var_export($params, true));

		$this->load('User');
		$this->load('User_Group');
		$this->load('User_Group_Portal');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		$data = array();

		$is_insert = !empty($params['user_id']) && is_numeric($params['user_id']) ? false : true;

		// Validations
		$input_validations = array(
			'first_name' => array(
				'label' => 'First Name',
				'rules' => array()

			)
			, 'last_name' => array(
				'label' => 'Last Name',
				 'rules' => array()

			)
			, 'date_of_birth' => array(
				'label' => 'Date of Birth'
				, 'rules' => array(
					/*'is_set' => NULL
					, */'is_date' => NULL
				)
			)
			, 'gender' => array(
				'label' => 'Gender'
				, 'rules' => array(
					/*'is_set' => NULL
					, */'is_in' => array('Male', 'Female')
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
		);
		if ($is_insert) {
			$input_validations['username']['rules']['is_set'] = NULL;
			$input_validations['password']['rules'] = array(
				'is_set' => NULL,
				'is_len_min' => 4
			);
		}
		$is_user_edit_password = !$is_insert && $is_user_edit && array_key_exists('password', $params);
		if ($is_user_edit_password) {
			$input_validations['password_old']['rules'] = array(
				'is_set' => NULL
			);
		}

		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();


        $logger->LogInfo("user save validation passed");


		if (isset($params['referrer'])) {
			// Get referrer_user_id
			$referrer_user_id = $this->User->get_primary_key_id_by_field_value('username', $params['referrer']);

			if (empty($referrer_user_id)) {
				_Model::$Exception_Helper->request_failed_exception('Invalid referrer.');
			}
		}

		// If updating password, validate current
		if ($is_user_edit_password) {
			$where_params = array(
				'user_id' => $params['user_id']
			);
			$user = $this->User->get_row($where_params, array('single' => true));
		}

		// Insert data
		if ($is_insert) {
			$existing_username = $this->User->get_row(
				array(
					'username' => $params['username']
				)
			);

			if (!empty($existing_username)) {
				_Model::$Exception_Helper->request_failed_exception('This username already exists. Please choose another.');
			}

			$existing_email = $this->User->get_row(
				array(
					'email' => $params['email']
				)
			);

			if (!empty($existing_email)) {
				_Model::$Exception_Helper->request_failed_exception('This email already exists. Please use another.');
			}
		}


		// User
		$field_map = array(
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'date_of_birth' => 'date_of_birth',
			'gender' => 'gender',
			'username' => 'username',
			'email' => 'email',
			'newsletter' => 'newsletter',
			'api_website_id' => 'api_website_id',
		);

		$user = array();
		foreach ($field_map as $field => $param) {
			if (array_key_exists($param, $params)) {
				$user[$field] = !empty($params[$param]) ? $params[$param] : NULL;
			}
		}
		if (array_key_exists('password', $params)) {
			$user['hash'] = $this->PasswordHash->HashPassword($params['password']);
		}

		if (!$is_insert) {
			$user['user_id'] = $params['user_id'];
		}
		else {
			$user['active'] = 1;
			if (!empty($referrer_user_id)) {
				$user['referrer_user_id'] = $referrer_user_id;
			}
		}

        $logger->LogInfo("user save insert data: \n" . var_export($user, true) );

		$data['user_id'] = $this->User->save($user);


        $logger->LogInfo("user save completed with return data: " . var_export($data, true));

		// Create user group link
		if ($is_insert) {
			// Get Customer user group and Public user group portal
			$customer_user_group_id = $this->User_Group->get_customer_user_group_id();
			$public_user_group_portal_id = $this->User_Group_Portal->get_public_user_group_portal_id();

			$user_group_link_id = $this->User_Group->save_link($data['user_id'], $customer_user_group_id, $public_user_group_portal_id);
		}

		return static::wrap_result(true, $data);
	}

	public function save_address($params = array()) {
		$this->load('Address');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		$data = array();

		$is_insert = !empty($params['address_id']) && is_numeric($params['address_id']) ? false : true;

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'first_name' => array(
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
			, 'street' => array(
				'label' => 'Street'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'city' => array(
				'label' => 'City'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'state' => array(
				'label' => 'State'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'zip' => array(
				'label' => 'Zip Code'
				, 'rules' => array(
					'is_set' => NULL
				)
			)

		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		// Addresses
		$field_map = array(
			'user_id' => 'user_id'
			, 'type' => 'type'
			, 'first_name' => 'first_name'
			, 'last_name' => 'last_name'
			, 'street' => 'street'
			, 'street_2' => 'street_2'
			, 'city' => 'city'
			, 'zip' => 'zip'
			, 'state' => 'state'
			, 'country' => 'country'
		);
		$address = array();
		foreach ($field_map as $field => $param) {
			if (array_key_exists($param, $params)) {
				$address[$field] = !empty($params[$param]) ? $params[$param] : NULL;
			}
		}

		if (!$is_insert) {
			$address['address_id'] = $params['address_id'];
		}

		$data['address_id'] = $this->Address->save($address);

		return static::wrap_result(true, $data);
	}

	public function save_phone($params = array()) {
		$this->load('Phone');

		$data = array();

		$is_insert = !empty($params['phone_id']) && is_numeric($params['phone_id']) ? false : true;

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'type' => array(
				'label' => 'Type'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'number' => array(
				'label' => 'Phone'
				, 'rules' => array(
					'is_set' => NULL
					//, 'is_phone' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$phone = array(
			'user_id' => $params['user_id']
			, 'type' => $params['type']
			, 'number' => $params['number']
		);

		if (!empty($params['phone_id'])) {
			$phone['phone_id'] = $params['phone_id'];
		}

		$data['phone_id'] = $this->Phone->save($phone);

		return static::wrap_result(true, $data);
	}

	public function get_profile($params = array()) {
		$this->load('User');
		//$this->load('Address');
		//$this->load('Phone');

		$data = array();

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$where_params = array(
			'user_id' => $params['user_id']
		);

		// User
		$data['user'] = $this->User->get_row($where_params, array('single' => true));
		if (empty($data['user'])) {
			_Model::$Exception_Helper->request_failed_exception('User could not be found.');
		}

		// Addresses
		//$data['addresses'] = $this->Address->get_rows($where_params);

		// Phone numbers
		//$data['phones'] = $this->Phone->get_rows($where_params);

		return static::wrap_result(true, $data);
	}

	public function get_user($params = array()) {
		$this->load('User');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$where_params = array(
			'user_id' => $params['user_id']
		);

		// User
		$data = $this->User->get_public_fields($where_params, array('single' => true));
		if (empty($data)) {
			_Model::$Exception_Helper->request_failed_exception('User could not be found.');
		}

		return static::wrap_result(true, $data);
	}

	public function get_addresses($params = array()) {
		$this->load('Address');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$where_params = array(
			'user_id' => $params['user_id']
		);

		// User
		$data = $this->Address->get_public_fields($where_params);

		if (!empty($data)) {
			$data = rows_to_groups($data, 'type');
		}

		return static::wrap_result(true, $data);
	}

	public function get_address($params = array()) {
		$this->load('Address');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'address_id' => array(
				'label' => 'Address Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$where_params = array(
			'user_id' => $params['user_id']
			, 'address_id' => $params['address_id']
		);

		// User
		$data = $this->Address->get_public_fields($where_params, array('single' => true));

		return static::wrap_result(true, $data);
	}

	public function save_password($params = array()) {
		$this->load('User');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'password' => array(
				'label' => 'Password'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_len_min' => 4
				)
			)
			, 'password_old' => array(
				'label' => 'Old Password'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		// mysql class for login
		global $_mysql;
		$_mysql = new mysql();

		$login = new login(array(), array());
		$error_code = NULL;

		$data = $login->updatePass($params['user_id'], $params['password'], $params['password_old']);
		if (!$data) {
			_Model::$Exception_Helper->request_failed_exception('Old Password was incorrect.');
		}

		return static::wrap_result(true, $data);
	}


	/*
	send reset password email
	*/
	public function reset_password_link($params = array()) {
		$this->load('User');
		$this->load('Config');

		if (empty($params['email'])) {
			_Model::$Exception_Helper->bad_request_exception('Email is not set.');
		}

		// mysql class for login
		global $_mysql;
		$_mysql = new mysql();

		$login = new login(array(), array());

		if (!$login->exists($params['email'], false, 'email')) {
			_Model::$Exception_Helper->request_failed_exception('Email not found.');
		}

		$user = $this->User->get_row(
			array(
				'email' => $params['email']
			)
		);

		if (empty($user)) {
			_Model::$Exception_Helper->request_failed_exception('Email not found.');
		}

		$marketing_email_prefix = $this->Config->get_value('Marketing From Email Prefix');

		// Send Email
		$emailDomain = $_SERVER['HTTP_REFERER'];
		$fromEmail = $marketing_email_prefix . '@' . $emailDomain;

		$subject = 'Password Reset Link';
		$customVariables = array(
			'email' => $user['email']
			, 'key' => md5($user['email'] . getSecretKey())
			, 'site_name' => $emailDomain
			, 'domain' => $emailDomain
		);

		$templateVariables = array(
			'first_name' => $user['first_name']
			, 'email' => $user['email']
			, 'domain' => $emailDomain
			, 'site_name' => $emailDomain
			, 'cdn_domain' => ''
		);

		$Email_Template_Helper = new Email_Template_Helper();

		$emailResults = $Email_Template_Helper->sendEmail('password-reset', $customVariables, $templateVariables, $emailDomain, $fromEmail, $user['first_name'] . ' ' . $user['last_name'], $user['email'], $subject, $fromEmail);


		return static::wrap_result(true, NULL, _Model::$Status_Code->get_status_code_no_content());
	}



    public function follow($request_data)
    {
        $this->load('Follow', DW_API_HOST, DW_API_USER, DW_API_PASSWORD, DW_API_DATABASE);
        $data  = $this->Follow->followUser($request_data);

        return static::wrap_result( ($this->Follow->hasError()? false:true), $data, 200, $this->Follow->getErrors() );
    }


	/*
	user resetpassword
	*/
	public function reset_password($params = array()) {
		$this->load('User');

		// Validations
		$input_validations = array(
			'email' => array(
				'label' => 'Email'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'password' => array(
				'label' => 'Password'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_len_min' => 4
				)
			)
			, 'key' => array(
				'label' => 'Key'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		// Verify Key
		$secretKey = getSecretKey();
		if ($params['key'] != md5($params['email'] . $secretKey)) {
			_Model::$Exception_Helper->request_failed_exception('Invalid key.');
		}

		$user = $this->User->get_row(
			array(
				'email' => $params['email']
			)
		);

		if (empty($user)) {
			_Model::$Exception_Helper->request_failed_exception('Email not found.');
		}


		// Update Password

		global $_mysql;
		$_mysql = new mysql();

		$login = new login(array(), array());

		$success = $login->updatePass($user['user_id'], $params['password']);
		if (!$success) {
			_Model::$Exception_Helper->request_failed_exception('Invalid user id.');
		}

		return static::wrap_result(true, NULL, _Model::$Status_Code->get_status_code_no_content());

	}
}
?>