<?
class Address extends _Model {
	const TABLE = 'address';
	const PRIMARY_KEY_FIELD = 'address_id';
	
	protected $fields = array(
		'user_id'
		, 'type'
		, 'first_name'
		, 'last_name'
		, 'street'
		, 'street_2'
		, 'city'
		, 'state'
		, 'zip'
		, 'country'
		, 'company'
		, 'phone'
	);
}
?>