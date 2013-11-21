<?php

class Email extends db {
	private $from;
	private $from_email;

	public function __construct($from, $from_email) {
		parent::__construct();

		$this->from = $from;
		$this->from_email = $from_email;
	}

	public function email($type, $user, $optional_params = NULL) {
		$function = 'get_' . $type . '_params';
		$params = $this->$function($user, $optional_params);

		$data = array();
		if (!empty($params)) {
			$full_name = $user['first_name'] . ' ' . $user['last_name'];

			$result = email($this->from, $this->from_email, $full_name, $user['email'], $params['subject'], $params['html_body']);
			$data[$user['email']] = $result;
		}
		return $data;
	}

	public function get_summary_params($user, $params) {
		$date = date('m/d/Y', strtotime($params['date']));
		$subject = 'Dahlia Wolf ' . $params['interval'] . ' Summary';

		ob_start();
		require ( isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "" ? $_SERVER['DOCUMENT_ROOT'] : ( defined('DR')? DR : "") ) . '/emails/custom/notifications-email.php';
		$html_body = ob_get_contents();
		ob_end_clean();

		return array(
			'subject' => $subject
			, 'html_body' => $html_body
		);
	}

	private function get_email_footer() {
		return '
            Thanks,
            Dahlia
        ';
	}
}

?>