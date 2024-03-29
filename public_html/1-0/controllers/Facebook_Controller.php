<?
class Facebook_Controller extends Social_Network_Controller {
	const NAME = 'Facebook';
	
	private $facebook;
	private $app_id = '552515884776900';
	private $secret = 'c58c0788e84750f288831a6725ad9565';
	
	public function __construct($params) {
		parent::__construct($params);
		
		require '1-0/lib/php/facebook/facebook.php';
	
		$this->facebook = new Facebook(
			array(
				'appId' => $this->app_id
				, 'secret' => $this->secret
			)
		);
	}
	
	public function login($params = array()) {
		$user = $this->facebook->getUser();

        $logger = new Jk_Logger(APP_PATH.'logs/facebook.log');
        $logger->LogInfo("FB LOGIN INIT");

		// If user isn't logged in through Facebook, redirect them to do so
		if (!$user) {
			$login_url = $this->facebook->getLoginUrl(
				array(
					'scope' => 'email,publish_stream'
					, 'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . '/social-login.php?social_network=facebook&logout_redirect_url=' . $_GET['logout_redirect_url']
				)
			);

            //$logger->LogInfo("FB LOGIN REDIRECT >> $login_url");
			header('Location: ' . $login_url);
			die();
		}
		// If user is logged in, get their profile and logout_url
		else {
			$logout_url = $this->facebook->getLogoutUrl(
				array(
					'next' => 'http://' . $_SERVER['SERVER_NAME'] . '/social-login.php?social_network=facebook&logout=1&logout_redirect_url=' . $_GET['logout_redirect_url']
				)
			);
			
			try {
				//$user_profile = $this->facebook->api('/me?fields=name,first_name,last_name,link,username,location,gender,email,timezone,locale,updated_time,picture');
				$user_profile = $this->facebook->api(
					array(
    					'method' => 'fql.query',
    					'query' => 'SELECT uid, name, first_name, last_name, username, current_location, sex, email, pic_big, birthday_date
    						FROM user
    						WHERE uid = me()
    					'
					)
				);
				$user_profile = $user_profile[0];

                $user = array(
              			'first_name' => $user_profile['first_name'],
              			'last_name' => $user_profile['last_name'],
              			'username' => trim((string) $user_profile['username']) !='' ? (string) $user_profile['username'] : strtolower("{$user_profile['first_name']}.{$user_profile['last_name']}"),
              			'email' => $user_profile['email'],
              			'fb_uid' => $user_profile['uid'],
              			'social_network_id' => $user_profile['uid'],
              			'logout_url' => $logout_url,
                );
			} catch (FacebookApiException $e) {
				$user = null;
				// Do something here
                return false;
				die();
			}
		}
		


        //$logger->LogInfo("FB USER DATA: " . var_export($user, true) );

		// Gender
		if (!empty($user_profile['sex'])) {
			$user['gender'] = ucwords($user_profile['sex']);
		}
		// Location
		if (!empty($user_profile['current_location'])) {
			$user['location'] = $user_profile['current_location']['name'];
		}
		// Avatar
		if (!empty($user_profile['pic_big'])) {
			$user['avatar'] = 'http://graph.facebook.com/' . $user_profile['username'] . '/picture?type=large';
		}
		// Avatar
		if (!empty($user_profile['birthday_date'])) {
			$user['date_of_birth'] = date('Y-m-d', strtotime($user_profile['birthday_date']));
		}

        $offline_user = new User($db_host = ADMIN_API_HOST, $db_user = ADMIN_API_USER, $db_password = ADMIN_API_PASSWORD, $db_name = ADMIN_API_DATABASE);
        $local_user = $offline_user->get_user($user['email']);

        $link_data = array(
            'user_id' => $local_user['user_id'],
            'social_network_id' => $this->social_network_id,
            'token' => $this->facebook->getAccessToken(),
            'token_secret' => null,
        );

        $social_link = new User_Social_Network_Link();
        $social_link->save($link_data);


        $logger->LogInfo("FB USER LINK: " . var_export($link_data, true) );

        $logger->LogInfo("FB USER DATA: " . var_export($user, true) );

		parent::login($user);
	}
}
?>
	