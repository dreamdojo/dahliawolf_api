<?
class User_Group extends _Model {
	const TABLE = 'user_group';
	const PRIMARY_KEY_FIELD = 'user_group_id';
	
	protected $fields = array(
		'name'
		, 'user_group_portal_id'
	);
	
	public function save_link($user_id, $user_group_id, $user_group_portal_id) {
		$values = array(
			'user_id' => $user_id
			, 'user_group_id' => $user_group_id
			, 'user_group_portal_id' => $user_group_portal_id
		);
		
		try {
			$user_group_link = self::$dbs[$this->db_host][$this->db_name]->insert('user_user_group_link', $values);
			return $this->db_last_insert_id();
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to save user group link.');
		}
	}
	
	public function get_customer_user_group_id() {
		$customer_user_group_id = $this->get_primary_key_id_by_field_value('name', 'Customer');
		
		if (empty($customer_user_group_id)) {
			self::$Exception_Helper->server_error_exception('Customer user group does not exist.');
		}
		
		return $customer_user_group_id;
	}
}
?>