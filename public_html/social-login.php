<?
error_reporting(E_WARNING| E_ERROR | E_COMPILE_WARNING | E_COMPILE_ERROR | E_STRICT);
ini_set('display_errors', '0');
require '1-0/config/config.php';


define('APP_PATH', sprintf("%s/", realpath($_SERVER['DOCUMENT_ROOT'])) );
$include_paths = explode(":", get_include_path());
$include_paths[] = realpath(APP_PATH.'lib/jk07');
set_include_path(implode(":", $include_paths));


require DR . '/lib/jk07/Jk_Root.php';
require DR . '/lib/jk07/Jk_Base.php';
require DR . '/lib/jk07/Jk_Logger.php';
require DR . '/lib/jk07/utils/Error_Handler.php';

$error_handler = new Error_Handler();
$error_handler->registerShutdownHandler();
$error_handler->registerErrorHandler();

// Referrer to know where to send user back to
if (!empty($_GET['redirect_url'])) {
	$_SESSION['redirect_url'] = $_GET['redirect_url'];
}
if (empty($_SESSION['redirect_url']) && empty($_GET['logout_redirect_url'])) {
	die();
}

// Log out
if (isset($_GET['logout']) && isset($_GET['logout_redirect_url'])) {
	session_destroy();
	header('Location: ' . $_GET['logout_redirect_url']);
	die();
}

$social_network = !empty($_GET['social_network']) ? strtolower($_GET['social_network']) : NULL;

$social_networks = array(
	'facebook',
	'twitter',
	'instagram',
);

// If valid social network
if (in_array($social_network, $social_networks)) {
	// API Website ID for registration
	if (!empty($_GET['api_website_id'])) {
		$_SESSION['api_website_id'] = $_GET['api_website_id'];
	}
	if (empty($_SESSION['api_website_id'])) {
		die();
	}
	
	$controller_name = ucwords($social_network) . '_Controller';
	
	$controller = new $controller_name(
		array(
			'api_website_id' => $_SESSION['api_website_id']
		)
	);
	
	// Call specific social network's login (which will reference base login)
	$controller->login();
	
	die();
}
?>