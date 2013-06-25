<?
// @daily php /var/www/crons/daily-summary-emails.php
if (!empty($_SERVER)) {
	die();
}

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'dahliawolf_v1_2013');
define('DB_USER', 'offlineadmin');
define('DB_PASS', '9w8^^^qFtwCD7N^N^');

define('FROM', 'Dahlia Wolf');
define('FROM_EMAIL', 'reminder@dahliawolf.com');

$dbhost = DB_HOST;
$dbname = DB_NAME;
$dbuser = DB_USER;
$dbpass = DB_PASS;

require 'lib/php/class.phpmailer.php';
require 'lib/php/email.php';
require 'models/db.php';
require 'models/User.php';
require 'models/Email.php';

$User = new User();

$date = date('Y-m-d');
$users = $User->get_daily_summary_users($date);
unset($User);

if (!empty($users)) {
	$Email = new Email(FROM, FROM_EMAIL);
	
	foreach ($users as $user) {
		$result = $Email->email('daily_summary', $user, array('date' => $date));
		print_r($result);
	}
}
?>
