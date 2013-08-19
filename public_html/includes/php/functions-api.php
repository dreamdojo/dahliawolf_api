<?
function api_request($service, $calls, $return_array = false)
{
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

function api_call($endpoint, $function, $parameters = NULL, $return_array = false) {

	$query_string = empty($parameters) ? '' : http_build_query($parameters);

    $api_domain = strpos($_SERVER['SERVER_NAME'], 'dev')>-1? "dev.api.dahliawolf.com" : "api.dahliawolf.com";

    error_log( __FUNCTION__ . " api domain: $api_domain");

	$url = "http://{$api_domain}/api.php?api=" . $endpoint . '&function=' . $function . '&' . $query_string;

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec ($ch);
    curl_close ($ch);
	
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

function api_errors_to_array($result, $api_call) {
	$errors = array();
	
	if (!empty($result['errors'])) {
		$errors = $result['errors'];
	}
	else if (!empty($result['data'][$api_call]['errors'])) {
		foreach ($result['data'][$api_call]['errors'] as $index => $error) {
			if ($index === 'input_validation') {
				foreach ($error as $field => $info) {
					array_push($errors, $info['label'] . ' ' . (implode(' and ', $info['errors'])) . '.');
				}
			}
			else {
				array_push($errors, $error);
			}
		}
	}
	return $errors;
}

function commerce_api_request($service, $calls, $return_array = false) { 
	if (!class_exists('Commerce_API', false)) {
		require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/Commerce_API.php';
	}
	
	// Instantiate library helper
	$api = new Commerce_API(API_KEY_DEVELOPER, PRIVATE_KEY_DEVELOPER);
	
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
?>