<?
class Follow extends db {

	private $table = 'follow';

	public function __construct() { 
		parent::__construct();
	}
	
	public function get_daily_count($user_id, $date) {
		$query = '
			SELECT COUNT(*) AS count
			FROM follow
			WHERE user_id = :user_id
				AND DATE(created) = :date
			LIMIT 1
		';
		$values = array(
			':user_id' => $user_id
			, ':date' => $date
		);
		
		$result = $this->run($query, $values);
		
		if ($result === false) {
			return resultArray(false, NULL, 'Could not get daily followers count.');
		}
		$rows = $result->fetchAll();
		
		return $rows[0]['count'];
	}
	
}