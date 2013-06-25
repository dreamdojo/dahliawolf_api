<?
class User_Social_Network_Link extends _Model {
	const TABLE = 'user_social_network_link';
	const PRIMARY_KEY_FIELD = 'user_social_network_link_id';
	
	protected $fields = array(
		'user_id'
		, 'social_network_id'
		, 'token'
	);
	
	public function check_email_exists($email, $social_network_id) {
		$query = '
			SELECT user.user_id
			FROM user
				INNER JOIN user_social_network_link ON user.user_id = user_social_network_link.user_id
			WHERE user.email = :email
				AND user_social_network_link.social_network_id = :social_network_id
		';
		$values = array(
			':email' => $email
			, ':social_network_id' => $social_network_id
		);
		
		try {
			$user = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);
			
			return $user;
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to check user social network email.');
		}
	}
}
?>