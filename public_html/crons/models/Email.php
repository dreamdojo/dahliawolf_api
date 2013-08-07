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

		/*$html_body_header = 'Dear ' . $user['first_name'] . ',

Below is your ' . $params['interval'] . ' summary for ' . $date . ':

';

		$html_body_html = '<table>
	<tr>
		<th scope="row" style="text-align: right;">Posts:</th>
		<td>' . $user['posts'] . '</td>
	</tr>
	<tr>
		<th scope="row" style="text-align: right;">Likes:</th>
		<td>' . $user['likes'] . '</td>
	</tr>
	<tr>
		<th scope="row" style="text-align: right;">Comments:</th>
		<td>' . $user['comments'] . '</td>
	</tr>
	<tr>
		<th scope="row" style="text-align: right;">Follows:</th>
		<td>' . $user['follows'] . '</td>
	</tr>
	<tr>
		<th scope="row" style="text-align: right;">Points:</th>
		<td>' . $user['points'] . '</td>
	</tr>
</table>
';

		$html_body_footer = $this->get_email_footer();
		$html_body = nl2br($html_body_header) . $html_body_html . nl2br($html_body_footer);*/

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