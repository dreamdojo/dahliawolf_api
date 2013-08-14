<?
die();
error_reporting(E_ERROR|E_WARNING|E_DEPRECATED|E_COMPILE_ERROR|E_STRICT|E_PARSE);
ini_set('display_errors', '0');
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/controllers/_Controller.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/controllers/API_Website_Controller.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/controllers/API_Credential_Controller.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/models/_Model.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/models/API_Credential.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/models/API_Website.php';
require $_SERVER['DOCUMENT_ROOT'] . '/1-0/models/API_Website_Domain.php';

$API_Website_Controller = new API_Website_Controller();

$API_Website_Controller->save_api_website(
	array(
		'api_website' => array(
			'customer_id' => 1
			, 'name' => 'DHRUV'
		)
	)
);
?>