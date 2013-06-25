<?
class Instagram_Controller extends Social_Network_Controller {
	const NAME = 'Instagram';
	
	private $twitter_oauth;
	private $client_id = '7da5bfd83d704e0e888abb1bcaf5bd87';
	private $client_secret = '6f6eb01882be4a6cb65d9b769c547ef6';
	//private $website_url = 'http://api.dahliawolf.com';
	//private $redirect_uri = 'http://api.dahliawolf.com/social-login.php?social_network=instagram';
	
	private $config = array();
	
	public function __construct($params) {
		parent::__construct($params);
		
		$this->config = array(
            "base_url" => "http://api.dahliawolf.com/lib/php/instagram/oauth/",
            "providers" => array (
                    "Instagram" => array (
                            "enabled" => true,
                            "keys"    => array ( "id" => $this->client_id, "secret" => $this->client_secret )
                    ),
            ),
        );
	}
	
	public function login() {
		require 'lib/php/instagram/oauth/Hybrid/Auth.php';
		require 'lib/php/instagram/oauth/Hybrid/Endpoint.php';
		
		$hybridauth = new Hybrid_Auth( $this->config );
		$instagram = $hybridauth->authenticate( "Instagram" );
	
		// get the user profile
		$user = $instagram->getUserProfile();
		
		$name_parts = explode(' ', $user->displayName);
		$first_name = $name_parts[0];
		unset($name_parts[0]);
		$last_name = implode(' ', $name_parts);
		
		$user = array(
			'first_name' => $first_name
			, 'last_name' => $last_name
			, 'username' => $user->username
			, 'instagram_username' => $user->username
			, 'email' => $user->username . '@instagram.com'
			
			//, 'logout_url' => $logout_url
		);
		parent::login($user);
	}
}
?>
	