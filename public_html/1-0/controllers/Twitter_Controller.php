<?
class Twitter_Controller extends Social_Network_Controller {
	const NAME = 'Twitter';
	
	private $twitter_oauth;
	private $consumer_key = 'l7vLsE0VVUk3a6jHj5HU4Q';
	private $consumer_secret = 'iR4e2k4M5dci2Jr6vkU9wmqYpJ0GVzlLy4nmwc1Jwds';
	private $request_token_url = 'https://api.twitter.com/oauth/request_token';
	private $authorize_url = 'https://api.twitter.com/oauth/authorize';
	private $access_token_url = 'https://api.twitter.com/oauth/access_token';
	private $callback_url = NULL;
	
	public function __construct($params) {
		parent::__construct($params);
	}
	
	public function login() {
		require 'lib/php/twitter/oauth/twitteroauth/OAuth.php';
		require 'lib/php/twitter/oauth/twitteroauth/twitteroauth.php';
		
		if (!isset($_GET['oauth_token'])) {
			$this->twitter_oauth = new twitteroauth($this->consumer_key, $this->consumer_secret);
			
			$temporary_credentials = $this->twitter_oauth->getRequestToken();
			$redirect_url = $this->twitter_oauth->getAuthorizeURL($temporary_credentials);
			//var_dump($redirect_url);
			
			header('Location: ' . $redirect_url);
			die();
		}
		
		$this->twitter_oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $_GET['oauth_token'], $_GET['oauth_verifier']);
		$token_credentials = $this->twitter_oauth->getAccessToken();
		
		$this->twitter_oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $token_credentials['oauth_token'], $token_credentials['oauth_token_secret']);
		$credentials = $this->twitter_oauth->get('account/verify_credentials');
		//$settings = $this->twitter_oauth->get('account/settings');
		
		$name_parts = explode(' ', $credentials->name);
		$first_name = $name_parts[0];
		unset($name_parts[0]);
		$last_name = implode(' ', $name_parts);
		
		$user = array(
			'first_name' => $first_name
			, 'last_name' => $last_name
			, 'username' => $credentials->screen_name
			, 'email' => $credentials->screen_name . '@twitter.com'
			
			//, 'logout_url' => $logout_url
		);
		parent::login($user);
	}
}
?>
	