<?
class User_Group_Portal extends _Model {
	const TABLE = 'user_group_portal';
	const PRIMARY_KEY_FIELD = 'user_group_portal_id';
	
	protected $fields = array(
		'name'
	);
	
	public function get_public_user_group_portal_id() {
		$public_user_group_portal_id = $this->get_primary_key_id_by_field_value('name', 'Public');
		
		if (empty($public_user_group_portal_id)) {
			self::$Exception_Helper->server_error_exception('Public user group portal does not exist.');
		}
		
		return $public_user_group_portal_id;
	}
}
?>