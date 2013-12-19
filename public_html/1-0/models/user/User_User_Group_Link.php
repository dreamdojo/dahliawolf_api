<?
class User_User_Group_Link extends _Model {
	const TABLE = 'user_user_group_link';
	const PRIMARY_KEY_FIELD = 'user_user_group_link_id';
	
	protected $fields = array(
		'user_id'
		, 'user_group_id'
		, 'user_group_portal_id'
	);
}
?>