<?
class Country extends _Model {
	const TABLE = 'country';
	const PRIMARY_KEY_FIELD = 'country_id';
	
	protected $fields = array(
		'name'
		, 'code'
	);
	
	public function get_countries() {
		$query = '
			SELECT name, code
			FROM country
			ORDER BY name ASC
		';
		
		return self::$dbs[$this->db_host][$this->db_name]->exec($query);
	}
}
?>