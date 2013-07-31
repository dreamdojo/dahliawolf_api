<?php
class AuthNetAIM {
    
    private $api_id;
    private $transaction_key;
    private $payment_array;
    private $payment_type;
    private $transaction_id;
    private $amount;
    private $cc_number;
    private $user_domain_name;
    private $use_sandbox;
	private $billing_address = array(
	
	
	);
   
	public function __construct($authnetApiId, $authnetTransKey, $useSandbox) {
        $this->api_id = $authnetApiId;
        $this->transaction_key = $authnetTransKey;
        $this->use_sandbox = $useSandbox;
	}
	
	public function setBillingAddress($address) {
		$this->billing_address = array(
			"x_first_name" => $address['first_name'],
			"x_last_name" => $address['last_name'],
			"x_address"	=> $address['address'],
			"x_city" => $address['city'],
			"x_state" => $address['state'],
			"x_zip"	=> $address['zip'],
		);
	}
	
    private function doTransaction($transactionType) {
		$a = new AuthorizeNetAIM($this->api_id, $this->transaction_key);
        $a->setSandbox($this->use_sandbox);
        if ($transactionType == 'authorizeCapture' || $transactionType == 'authorizeOnly') {
            foreach($this->payment_array AS $name => $value) {
                $a->__set($name, $value);           
			}
			
            if ($this->payment_type == 'echeck' || $this->payment_type ==  'check') {
                $this->payment_array['method'] = 'echeck';
                $this->payment_array['echeck_type'] = 'WEB';
                }
            }
			
			if (!empty($this->billing_address)) {
				$a->setCustomFields($this->billing_address);
			}
       
        switch ($transactionType) {
            case 'authorizeCapture':
                $result = $a->authorizeAndCapture();
                break;
            case 'authorizeOnly':
                $result = $a->authorizeOnly();
                break;
            case 'capturePriorAuthorization':
                $result = $a->priorAuthCapture($this->transaction_id, $this->amount);
                break;
            case 'voidTransaction':
                $result = $a->void($this->transaction_id);
                break;
            case 'issueRefund':
                $result = $a->credit($this->transaction_id, $this->amount, $this->cc_number);
                break;
			default:
				return NULL;            
		}
       
        return $result;
	}
	
    public function authorizeCapture($paymentArray, $paymentType = 'credit card') {
        $this->payment_array = $paymentArray;
        $this->payment_type = $paymentType;
        $result = $this->doTransaction('authorizeCapture');
        return $result;
	}
	
    public function authorizeOnly($paymentArray, $paymentType = 'credit card') {
        $this->payment_array = $paymentArray;
        $this->payment_type = $paymentType;
        $result = $this->doTransaction('authorizeOnly');
        return $result;
	}
	
    public function capturePriorAuthorization($transactionId, $amount = null) {
        $this->transaction_id = $transactionId;
        $this->amount = $amount;
        $result = $this->doTransaction('capturePriorAuthorization');
        return $result;
	}
	
    public function voidTransaction($transactionId) {
        $this->transaction_id = $transactionId;
        $result = $this->doTransaction('voidTransaction');
        return $result;
	}
	
    public function issueRefund($transactionId, $amount, $ccNumber) {
        $this->transaction_id = $transactionId;
        $this->amount = $amount;
       $this->cc_number  = $ccNumber;
        $result = $this->doTransaction('issueRefund');
        return $result;
	}
}
?>