<?
class Address_Controller extends _Controller {

	public function get_user_address($params = array()) {
		$this->load('Address');

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'address_id' => array(
				'label' => 'Address ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$data = $this->Address->get_row(
			array(
				'user_id' => $params['user_id']
				, 'address_id' => $params['address_id']
			)
		);

		return static::wrap_result(true, $data);
	}

	public function get_user_shipping_addresses($params = array()) {
		$this->load('Address');

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$data = $this->Address->get_rows(
			array(
				'user_id' => $params['user_id']
				, 'type' => 'Shipping'
			)
		);

		return static::wrap_result(true, $data);

	}

	public function get_user_billing_addresses($params = array()) {
		$this->load('Address');

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User ID'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, true);
		$this->Validate->run();

		$data = $this->Address->get_rows(
			array(
				'user_id' => $params['user_id']
				, 'type' => 'Billing'
			)
		);

		return static::wrap_result(true, $data);

	}

	public function create_billing_address($params = array()) {
		//unset($params['address_id']);
		$params['type'] = 'Billing';

		return $this->save_address($params);
	}

	public function create_shipping_address($params = array()) {
		//unset($params['address_id']);
		$params['type'] = 'Shipping';

		return $this->save_address($params);
	}

	public function save_address($params = array()) {
		$this->load('Address');
		$this->load('State');
		$this->load('Country');

		// User authentication: check login_instance
		$is_user_edit = array_key_exists('token', $params);
		if ($is_user_edit) {
			$this->validate_login_instance($params['user_id'], $params['token']);
		}

		$data = array();

		$is_insert = !empty($params['address_id']) && is_numeric($params['address_id']) ? false : true;

		// Validations
		$input_validations = array(
			'user_id' => array(
				'label' => 'User Id'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'first_name' => array(
				'label' => 'First Name'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'last_name' => array(
				'label' => 'Last Name'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'street' => array(
				'label' => 'Street'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'city' => array(
				'label' => 'City'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'state' => array(
				'label' => 'State'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'zip' => array(
				'label' => 'Zip Code'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'country' => array(
				'label' => 'Country'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'phone' => array(
				'label' => 'Phone'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		$this->Validate->add_many($input_validations, $params, $is_insert);
		$this->Validate->run();

		// Validate state
		if (!empty($params['state']) && !empty($params['country']) && $params['country'] == 'US') {
			$state = $this->State->get_row(
				array(
					'code' => $params['state']
				)
			);

			if (empty($state)) {
				_Model::$Exception_Helper->request_failed_exception('State not found.');
			}
		}

		if (!empty($params['country'])) {
			$country = $this->Country->get_row(
				array(
					'code' => $params['country']
				)
			);

			if (empty($country)) {
				_Model::$Exception_Helper->request_failed_exception('Country not found.');
			}
		}

		if ($params['state'] == 'N/A') {
			$params['state'] = NULL;
		}

		// Addresses
		$field_map = array(
			'user_id' => 'user_id'
			, 'type' => 'type'
			, 'first_name' => 'first_name'
			, 'last_name' => 'last_name'
			, 'street' => 'street'
			, 'street_2' => 'street_2'
			, 'city' => 'city'
			, 'zip' => 'zip'
			, 'state' => 'state'
			, 'country' => 'country'
			, 'company' => 'company'
			, 'phone' => 'phone'
		);
		$address = array();
		foreach ($field_map as $field => $param) {
			if (array_key_exists($param, $params)) {
				$address[$field] = $params[$param] != '' ? $params[$param] : NULL;
			}
		}

		if (!$is_insert) {
			$address['address_id'] = $params['address_id'];
		}

		$data['address_id'] = $this->Address->save($address);

		return static::wrap_result(true, $data);
	}

	public function get_countries() {
		$this->load('Country');

		$data = $this->Country->get_countries();

		return static::wrap_result(true, $data);
	}

	public function get_states() {
		$this->load('State');

		$data = $this->State->get_states();

		return static::wrap_result(true, $data);
	}

}

?>