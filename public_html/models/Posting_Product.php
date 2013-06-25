<?php
class Posting_Product extends db {

	private $table = 'posting_product';

	public function __construct() {
		parent::__construct();
	}

	public function add_post_product($params = array()) {
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
			return resultArray(false, NULL, 'Could not add post product.');
		}

		return resultArray(true, array('posting_product_id' => $insert_id));
	}

	public function get_product($params = array()) {
		$error = NULL;

		if (empty($params['where'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['where'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			SELECT posting_product.*
				, user_username.user_id, user_username.username
			FROM posting_product
				INNER JOIN posting ON posting_product.posting_id = posting.posting_id
				INNER JOIN user_username ON posting.user_id = user_username.user_id
			WHERE posting_product.posting_id = :posting_id
			LIMIT 1
		';
		$values = array(
			':posting_id' => $params['where']['posting_id']
		);

		//$row = $this->get_row($this->table, $params['where']);
		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get posting product.');
		}
		$rows = $result->fetchAll();

		return resultArray(true, $rows);
	}

	/*public function deactivate_post_product($params = array()) {
		$error = NULL;

		if (empty($params['conditions'])) {
			$error = 'Where conditions are required.';
		}
		else if (!is_array($params['conditions'])) {
			$error = 'Invalid conditions.';
		}

		if (!empty($error)) {
			return resultArray(false, NULL, $error);
		}

		$query = '
			UPDATE posting_product
			SET active = 0
			WHERE posting_id = :posting_id
		';
		$values = array(
			':posting_id' => $params['conditions']['posting_id']
		);
		$res = $this->run($query, $values);

		if ($res === false) {
			 return resultArray(false, NULL, 'Could not deactivate post_product.');
		}

		return resultArray(true, $res);
	}*/

	public function get_product_posts($product_id) {
		$query = '
			SELECT posting_product.*
			FROM posting_product
			WHERE posting_product.product_id = :product_id
		';
		$values = array(
			':product_id' => $product_id
		);

		$result = $this->run($query, $values);

		if ($result === false) {
			 return resultArray(false, NULL, 'Could not get product posts.');
		}
		$rows = $result->fetchAll();

		//return resultArray(true, $rows);
		return $rows;
	}

}
?>