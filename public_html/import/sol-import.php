<?
die();
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

require '../includes/config.php';
require DR . '/models/db.php';
require DR . '/lib/php/API.php';

$db = new db('mysql:host=127.0.0.1;dbname=admin_offline_v1_2013', 'offlineadmin', '9w8^^^qFtwCD7N^N^');

$query = '
	SELECT *
	FROM old_members
	WHERE verified = 0
	ORDER BY USERID ASC
';
$result = $db->run($query);

echo '<pre>';
if (!empty($result)) {
	$rows = $result->fetchAll();
	if (!empty($rows)) {
		foreach ($rows as $i => $member) {
			$gender = $member['gender'] == 2 ? 'Female' : ($member['gender'] == 1 ? 'Male' : NULL);
			
			// Register with basic info
			$user_params = array(
				'first_name' => $member['fname']
				, 'last_name' => $member['lname']
				, 'email' => $member['email']
				, 'username' => $member['username']
				, 'password' => $member['pwd']
				, 'gender' => $gender
				, 'api_website_id' => API_WEBSITE_ID
			);
			$data = api_call('user', 'register', $user_params, true);
			
			if (empty($data['success'])) {
				echo "\nError registering " . $member['email'] . ":\n";
				echo '<span style="color: #999;">';
				print_r($data['errors']);
				echo '</span>';
			}
			else {
				$user_id = $data['data']['user']['user_id'];
				echo "\nRegistered " . $member['email'] . ' [' . $user_id . ']';
				
				// Update with additional details
				$user_params = array(
					'user_id' => $user_id
					
					, 'avatar' => 'http://www.dahliawolf.com/avatar.php?user_id=' . $user_id
					
					, 'about' => $member['description']
					, 'location' => $member['location']
					, 'website' => $member['website']
					, 'facebook_post' => $member['post_fb']
					, 'pinterest_username' => $member['pinterest_user']
					, 'comment_notifications' => $member['mail_com']
					, 'like_notifications' => $member['mail_like']
					
					, 'member_id' => $member['USERID']
				);
				$data = api_call('user', 'update_user_optional', $user_params, true);
				if (empty($data['success'])) {
					echo "\nError updating " . $member['email'] . ' [' . $user_id . "]:\n";
					print_r($data['errors']);
				}
			}
		}
	}
}
echo '</pre>';






die();
require('lib/php/API.php');

$con = mysql_connect("127.0.0.1","offlineadmin","9w8^^^qFtwCD7N^N^") or die(mysql_error());
mysql_select_db("admin_offline_v1_2013") or die(mysql_error());

echo "Import";
$query = mysql_query("SELECT * FROM old_members WHERE verified=1 ") OR die(mysql_error());
while($row = mysql_fetch_array($query)) {
	
	$user_params = array(
		'user_id' 			=> $row['USERID']
		, 'first_name' 		=> $row['fname']
		, 'last_name' 		=> $row['lname']
		, 'email' 			=> $row['email']
		, 'username' 		=> $row['username']
		, 'password' 		=> $row['pwd']
		, 'api_website_id' => 2
	);
	$data = api_call('user', 'register', $user_params, true);
	
	if (!empty($data['errors'])) {
		echo "Error: ".$row['email']."<br>";
	}
	
	$user_id = $data['data']['user']['user_id'];

	// Save to local db
	$user_params = array(
		'user_id' 			=> $user_id
		, 'username' 		=> $row['username']
		, 'email_address' 	=> $row['email']
		, 'first_name' 		=> $row['fname']
		, 'last_name' 		=> $row['lname']
		, 'gender' 			=> $row['gender']
		, 'about' 			=> $row['description']
		, 'location' 		=> $row['location']
		, 'website' 		=> $row['website']
		
		, 'facebook_post' 			=> $row['post_fb']
		//, 'instagram_import' 		=> $row['instagram_import']
		//, 'instagram_username' 		=> $row['instagram_username']
		, 'pinterest_username' 		=> $row['pinterest_user']
		, 'comment_notifications' 	=> $row['mail_com']
		, 'like_notifications' 		=> $row['mail_like']
	);
	$data = api_call('user', 'update_user', $user_params);

}
?>