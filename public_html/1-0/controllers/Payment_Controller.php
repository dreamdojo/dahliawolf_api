<?
class Payment_Controller extends _Controller {
	public $months = array(
		'01'
		, '02'
		, '03'
		, '04'
		, '05'
		, '06'
		, '07'
		, '08'
		, '09'
		, '10'
		, '11'
		, '12'
	);
	
	public $years = array();
	
	public function __construct() {
		parent::__construct();
		
		$cur_year = date('Y');
		
		for ($i = $cur_year; $i < $cur_year + 11; $i++) {
			array_push($this->years, $i);
		}
	}
	
	public function get_months() {
		$data = $this->months;
		
		return static::wrap_result(true, $data);
	}
	
	public function get_years() {
		$data = $this->years;
		
		return static::wrap_result(true, $data);
	}
	
	public function get_payment_methods($params = array()) {
		$this->load('Payment_Method');
		
		$data = $this->Payment_Method->get_rows(
			array(
				'active' => '1'
			)
			, array(
				'order_by_field' => 'name'
				, 'order_by_desc' => false
			)
		);
		
		return static::wrap_result(true, $data);
	}
	
	public function process_credit_card($params = array()) {
		$this->load('Config');
		
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'name' => NULL
			, 'number' => NULL
			, 'exp_month' => NULL
			, 'exp_year' => NULL
			, 'cvv' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'name' => array(
				'label' => 'Name on Card'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'number' => array(
				'label' => 'Card Number'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'exp_month' => array(
				'label' => 'Card Expiration Month'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_in' => $this->months
				)
			)
			, 'exp_year' => array(
				'label' => 'Card Expiration Year'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
			, 'cvv' => array(
				'label' => 'Card CVV'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_int' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		if (empty($params['address']) || !is_array($params['address'])) {
			_Model::$Exception_Helper->bad_request_exception('Address info is empty.');
		}
		
		$now = _Model::$date_time;
		
		// Process with Authorize.net
		$payment_info = array(
			'card_num' => $params['number']
			, 'card_code' => $params['cvv']
			, 'exp_date' => $params['exp_month'] . '/' . $params['exp_year']
			, 'amount' => $params['amount'] <= 0 ? .01 : $params['amount']
			, 'description' => !empty($params['description']) ? $params['description'] : '' 
		);
		
		$api_login_id = $this->Config->get_value('Authorize.net API Login ID');
		$transaction_key = $this->Config->get_value('Authorize.net Transaction Key');
		$use_sandbox_config = $this->Config->get_value('Authorize.net Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		$transactionType = $this->Config->get_value('Payment Transaction Type');
		$paymentAction = ($transactionType == 'Capture') ? 'Sale' : 'Authorization';
		$authorizePayment = ($paymentAction == 'Authorization') ? true : false;
		
		$auth = new AuthNetAIM($api_login_id, $transaction_key, $use_sandbox);
		
		// Set address fields
		$address = array(
			"first_name" => $params['address']['first_name'],
			"last_name"	=> $params['address']['last_name'],
			"address" => $params['address']['street'],
			"city" => $params['address']['city'],
			"state" => $params['address']['state'],
			"zip" => $params['address']['zip'],
			"country" => $params['address']['country']
		);
		
		$auth->setBillingAddress($address);
		
		if (!empty($params['shipping_address'])) {
			$address = array(
				"first_name" => $params['shipping_address']['first_name'],
				"last_name"	=> $params['shipping_address']['last_name'],
				"address" => $params['shipping_address']['street'],
				"city" => $params['shipping_address']['city'],
				"state" => $params['shipping_address']['state'],
				"zip" => $params['shipping_address']['zip'],
				"country" => $params['shipping_address']['country']
			);
			
			$auth->setShippingAddress($address);
		}
		
		if ($authorizePayment) {
			$data = $auth->authorizeOnly($payment_info);
			$data->is_authorization = true;
		}
		else {
			$data = $auth->authorizeCapture($payment_info);
			$data->is_authorization = false;
		}
		
		if (!$data->approved) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $data->response_reason_text);
		}
		
		return static::wrap_result(true, $data);
	}
	
	public function capture_credit_card($params = array()) {
		$this->load('Config');
		
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'authorization_transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'authorization_transaction_id' => array(
				'label' => 'Authorization Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$api_login_id = $this->Config->get_value('Authorize.net API Login ID');
		$transaction_key = $this->Config->get_value('Authorize.net Transaction Key');
		$use_sandbox_config = $this->Config->get_value('Authorize.net Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		$auth = new AuthNetAIM($api_login_id, $transaction_key, $use_sandbox);
		
		$data = $auth->capturePriorAuthorization($params['authorization_transaction_id'], $params['amount']);
		
		if (!$data->approved) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $data->response_reason_text);
		}
		
		return static::wrap_result(true, $data);
	}
	
	public function void_credit_card($params = array()) {
		$this->load('Config');
		
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'authorization_transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'authorization_transaction_id' => array(
				'label' => 'Authorization Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$now = _Model::$date_time;
		
		$api_login_id = $this->Config->get_value('Authorize.net API Login ID');
		$transaction_key = $this->Config->get_value('Authorize.net Transaction Key');
		$use_sandbox_config = $this->Config->get_value('Authorize.net Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		$auth = new AuthNetAIM($api_login_id, $transaction_key, $use_sandbox);
		
		$data = $auth->voidTransaction($params['authorization_transaction_id']);
		
		if (!$data->approved) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $data->response_reason_text);
		}
		
		return static::wrap_result(true, $data);
	}
	
	public function credit_credit_card($params = array()) {
		$this->load('Config');
		
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'transaction_id' => array(
				'label' => 'Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'card_number' => array(
				'label' => 'Card Number'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$now = _Model::$date_time;
		
		$api_login_id = $this->Config->get_value('Authorize.net API Login ID');
		$transaction_key = $this->Config->get_value('Authorize.net Transaction Key');
		$use_sandbox_config = $this->Config->get_value('Authorize.net Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		$auth = new AuthNetAIM($api_login_id, $transaction_key, $use_sandbox);
		
		$data = $auth->issueRefund($params['transaction_id'], $params['amount'], $params['card_number']);
		
		if (!$data->approved) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $data->response_reason_text);
		}
		
		return static::wrap_result(true, $data);
	}
	
	public function begin_paypal_purchase($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'purchase_info' => NULL
			, 'return_url' => NULL
			, 'cancel_url' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'purchase_info' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_array' => NULL
				)
			)
			, 'return_url' => array(
				'label' => 'Return Url'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
			, 'cancel_url' => array(
				'label' => 'Cancel Url'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$pm = $this->Payment_Method->get_row(
			array(
				'name' => 'PayPal'
				, 'active' => '1'
			)
		);
		if (empty($pm)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal payment method not found.');
		}
		
		$paymentArray = array(
			'amount' => $params['purchase_info']['amount']
			, 'currency' => 'USD'
			, 'item_description' => $params['purchase_info']['item_description']
			, 'item_name' => $params['purchase_info']['item_name']
		);
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$transactionType = $this->Config->get_value('Payment Transaction Type');
		$paymentAction = ($transactionType == 'Capture') ? 'Sale' : 'Authorization';
		$authorizePayment = ($paymentAction == 'Authorization') ? true : false;
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->beginPurchase($paymentArray, $params['return_url'], $params['cancel_url'], $paymentAction);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		$data['is_authorization'] = $authorizePayment ? true : false;
		$data['payment_method_id'] = $pm['payment_method_id'];
		
		return static::wrap_result(true, $data);
	}
	
	public function complete_paypal_purchase($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'token' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'token' => array(
				'label' => 'Return Url'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$transactionType = $this->Config->get_value('Payment Transaction Type');
		$paymentAction = ($transactionType == 'Capture') ? 'Sale' : 'Authorization';
		$authorizePayment = ($paymentAction == 'Authorization') ? true : false; 
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->completePurchase($params['token'], $params['amount'], $paymentAction);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		$data['is_authorization'] = $authorizePayment ? true : false;
		
		return static::wrap_result(true, $data);
	}
	public function capture_paypal_payment($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'authorization_transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'authorization_transaction_id' => array(
				'label' => 'Authorization Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->doCapture($params['authorization_transaction_id'], $params['amount']);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		
		return static::wrap_result(true, $data);
	}
	public function void_paypal_payment($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'authorization_transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'authorization_transaction_id' => array(
				'label' => 'Authorization Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->doVoid($params['authorization_transaction_id']);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		
		return static::wrap_result(true, $data);
	}
	public function return_paypal_payment($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'transaction_id' => array(
				'label' => 'Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->issueRefund(
			array(
				'transaction_id' => $params['transaction_id']
				, 'amount' => $params['amount']
			)
		);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		
		return static::wrap_result(true, $data);
	}
	
	public function return_partial_paypal_payment($params = array()) {
		$this->load('Config');
		$this->load('Payment_Method');
		$data = array();
		
		$validate_names = array(
			'amount' => NULL
			, 'transaction_id' => NULL
		);
		
		$validate_params = array_merge($validate_names, $params);
		
		// Validations
		$input_validations = array(
			'amount' => array(
				'label' => 'Amount'
				, 'rules' => array(
					'is_set' => NULL
					, 'is_decimal' => 2
					, 'is_positive' => NULL
				)
			)
			, 'transaction_id' => array(
				'label' => 'Transaction ID'
				, 'rules' => array(
					'is_set' => NULL
				)
			)
		);
		
		$this->Validate->add_many($input_validations, $validate_params, true);
		$this->Validate->run();
		
		$username = $this->Config->get_value('PayPal API Username');
		$password = $this->Config->get_value('PayPal API Password');
		$signature = $this->Config->get_value('PayPal API Signature');
		$use_sandbox_config = $this->Config->get_value('PayPal API Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		
		if (empty($username) || empty($password) || empty($signature) || !is_numeric($use_sandbox_config)) {
			_Model::$Exception_Helper->request_failed_exception('PayPal configurations are not set.');
		}
		
		$p = new PayPalExpressCheckout($use_sandbox, $username, $password, $signature);
		
		$results = $p->issuePartialRefund(
			array(
				'transaction_id' => $params['transaction_id']
				, 'amount' => $params['amount']
			)
		);
		
		if (!$results['success']) { // failed
			return static::wrap_result(false, NULL, _Model::$Status_Code->get_status_code_request_failed(), $results['errors']);
		}
		
		$data = $results['data'];
		
		return static::wrap_result(true, $data);
	}
}

?>