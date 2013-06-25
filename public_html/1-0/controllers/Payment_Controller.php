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
		
		$now = _Model::$date_time;
		
		// Process with Authorize.net
		$payment_info = array(
			'card_num' => $params['number']
			, 'card_code' => $params['cvv']
			, 'exp_date' => $params['exp_month'] . '/' . $params['exp_year']
			, 'amount' => $params['amount']
			, 'description' => !empty($params['description']) ? $params['description'] : '' 
		);
		
		$api_login_id = $this->Config->get_value('Authorize.net API Login ID');
		$transaction_key = $this->Config->get_value('Authorize.net Transaction Key');
		$use_sandbox_config = $this->Config->get_value('Authorize.net Use Sandbox');
		$use_sandbox = $use_sandbox_config == '1' ? true : false;
		$auth = new AuthNetAIM($api_login_id, $transaction_key, $use_sandbox);
		$data = $auth->authorizeCapture($payment_info);
		
		return static::wrap_result(true, $data);
	}
	
	
}

?>