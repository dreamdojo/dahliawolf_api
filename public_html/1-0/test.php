<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

require $_SERVER['DOCUMENT_ROOT'] . '/1-0/lib/php/API.php';

define('API_KEY_DEVELOPER', 'b968a167feba0990b283f0cd65757a60');
define('PRIVATE_KEY_DEVELOPER', '796323f65ce5f0178dc15e8181c17247');

function api_request($service, $calls, $return_array = false) {
	if (!class_exists('API', false)) {
		require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/API.php';
	}

	// Instantiate library helper
	$api = new API(API_KEY_DEVELOPER, PRIVATE_KEY_DEVELOPER);

	// Make request
	$result = $api->rest_api_request($service, $calls);

	if (!$return_array) {
		return $result;
	}

	$decoded = json_decode($result, true);
	if ($decoded) {
		return $decoded;
	}
	echo $result;
	return;
}

echo '<pre>';

$calls = array(
	'get_log' => array(
		'user_id' => 657
		, 'api_website_id' => 2
	)
);
$data = api_request('activity_log', $calls, true);
print_r($data);
die();

$calls = array(
	'login' => array(
		'email' => 'example@example.com'
		, 'password' => 'example8'
	)
);
/*$calls = array(
	'save_user' => array(
		'first_name' => 'Example'
		, 'last_name' => 'Example'
		, 'email' => 'example@example.com'
		, 'username' => 'example'
		, 'password' => 'example8'
	)
);*/

$data = api_request('user', $calls, true);
print_r($data);

$calls = array(
	'add_comment' => array(
		'posting_id' => ''
		, 'user_id' => ''
		, 'comment' => ''
		, 'parent_id' => ''
	)
);
?>