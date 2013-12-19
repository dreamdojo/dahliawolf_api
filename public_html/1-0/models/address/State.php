<?
class State extends _Model {
	const TABLE = 'state';
	const PRIMARY_KEY_FIELD = 'state_id';
	
	protected $fields = array(
		'name'
		, 'code'
	);
	
	public function get_states() {
		$query = '
			SELECT name, code
			FROM state
			ORDER BY name ASC
		';
		
		return self::$dbs[$this->db_host][$this->db_name]->exec($query);
	}
}
?>