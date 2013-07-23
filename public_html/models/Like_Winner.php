<?php

class Like_Winner extends db {

	private $table = 'like_winner';

	public function __construct() {
		parent::__construct();
	}

	public function add_like_winner($params = array()) {
		$error = NULL;
		if (empty($params['data'])) {
			$error = 'Data is required.';
		}
		else if (!is_array($params['data'])) {
			$error = 'Invalid data.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$this->insert($this->table, $params['data']);

		$insert_id = $this->insert_id;

		if (empty($insert_id)) {
			return resultArray(false, NULL, 'Could not add like winner.');
		}

		return resultArray(true, $insert_id);
	}

	public function delete_like_winner($params = array()) {
		$error = NULL;
		if (empty($params['where'])) {
			$error = 'Where conditions is required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid where conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$result = $this->delete($this->table, $params['where']);

		return resultArray(true, $result);
	}

	public function get_primary_product_winner($like_winner_id) {
		$query = '
			SELECT all_winners.*
				, posting.user_id
			FROM like_winner
				INNER JOIN posting_product ON like_winner.posting_id = posting_product.posting_id
				INNER JOIN like_winner AS all_winners ON posting_product.posting_id = all_winners.posting_id
				INNER JOIN posting ON all_winners.posting_id = posting.posting_id
			WHERE like_winner.like_winner_id = :like_winner_id
			ORDER BY all_winners.created
			LIMIT 1
		';
		$values = array(
			':like_winner_id' => $like_winner_id
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			return NULL;
		}
		$rows = $result->fetchAll();

		return $rows[0];
	}
}
?>