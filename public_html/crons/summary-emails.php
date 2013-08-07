<?
// @daily php /var/www/crons/daily-summary-emails.php
/*
if (!empty($_SERVER)) {
    echo "die";
	die();
}
*/

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('FROM', 'Dahlia Wolf');
define('FROM_EMAIL', 'reminder@dahliawolf.com');

require '../1-0/config/config.php';

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

echo sprintf("USER COUNT: %s", count($users));

if (!empty($users)) {
	$Email = new Email(FROM, FROM_EMAIL);

	foreach ($users as $user) {
		$result = $Email->email('summary', $user, array('date' => $date, 'interval' => INTERVAL));
		print_r($result);
	}
}
?>