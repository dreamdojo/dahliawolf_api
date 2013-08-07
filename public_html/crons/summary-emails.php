<?
// @daily php /var/www/crons/daily-summary-emails.php

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('FROM', 'Dahlia Wolf');
define('FROM_EMAIL', 'reminder@dahliawolf.com');

/*
$temp = getenv("SCRIPT_NAME");
$localpath=realpath(basename(getenv("SCRIPT_NAME")));
*/

$absolute_path = realpath( str_replace(basename(__FILE__), '', realpath(__FILE__))."../" );

define('APP_PATH', "$absolute_path/");
define('DR', "$absolute_path/");

$include_paths = explode(":", get_include_path());
$include_paths[] = "$absolute_path/";
$include_paths[] = "$absolute_path/lib/jk07/";
$include_paths[] = "$absolute_path/crons/";
set_include_path(implode(":", $include_paths));

if(!defined('INTERVAL')) define('INTERVAL', 'Daily');

echo "WORKING PATH: $absolute_path/  \n";

/*
var_dump(APP_PATH);
var_dump(DR);
var_dump(INTERVAL);
var_dump(explode(":", get_include_path()));
*/

require_once 'Jk_Root.php';
require_once 'Jk_Base.php';
require_once 'Jk_Logger.php';
require_once 'utils/Error_Handler.php';

$error_handler = new Error_Handler();
$error_handler->registerShutdownHandler();
$error_handler->registerErrorHandler();

require_once DR . '/1-0/config/config.php';
require_once $absolute_path . '/1-0/config/config.php';

$dbhost = DB_API_HOST;
$dbname = 'dahliawolf_v1_2013';
$dbuser = DB_API_USER;
$dbpass = DB_API_PASSWORD;

require 'lib/php/class.phpmailer.php';
//require 'lib/php/email.php';
require 'models/db.php';
require 'models/User.php';
require 'models/Email.php';

$User = new User();

$date = date('Y-m-d');
$users = $User->get_summary_users(INTERVAL, $date);
unset($User);

echo sprintf("SENDING %s EMAILS....\n", INTERVAL);
echo sprintf("USER COUNT: %s", count($users). "\n\n");

if (!empty($users)) {
	$Email = new Email(FROM, FROM_EMAIL);

	foreach ($users as $user)
    {
        echo "sending email to: {$user['email']} \n";
        $result = $Email->email('summary', $user, array('date' => $date, 'interval' => INTERVAL));
		print_r($result);
	}
}
?>