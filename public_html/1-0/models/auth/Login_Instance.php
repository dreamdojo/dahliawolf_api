<?
class Login_Instance extends _Model {
	const TABLE = 'login_instance';
	const PRIMARY_KEY_FIELD = 'login_instance_id';
	
	protected $fields = array(
		'user_id'
		, 'token'
		, 'last_login'
	);
}
?>