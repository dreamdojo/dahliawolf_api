<?php
header('Content-Type: application/json');
//error_reporting(E_ERROR|E_WARNING|E_DEPRECATED|E_COMPILE_ERROR|E_STRICT|E_PARSE|E_ALL);
error_reporting(E_ERROR| E_WARNING | E_DEPRECATED|E_COMPILE_ERROR|E_STRICT|E_PARSE);
ini_set('display_errors', '1');
session_start();


require_once 'includes/config.php';
//require_once 'models/users.php';
require_once 'models/Posting.php';
require_once 'models/Image.php';
require_once 'models/user.php';
require_once 'models/Posting_Like.php';
require_once 'models/Posting_Vote.php';
require_once 'models/Posting_Product.php';
require_once 'models/Like_Winner.php';
require_once 'models/Vote_Period.php';
require_once 'models/Vote_Winner.php';
require_once 'models/Point.php';
require_once 'models/User_Point.php';


require DR . '/lib/php/class.phpmailer.php';
require DR . '/lib/php/email.php';
require_once 'models/Email.php';
require_once 'includes/php/json_functions.php';

define('APP_PATH', sprintf("%s/", realpath('./') ));
$include_paths = explode(":", get_include_path());
$include_paths[] = sprintf("%s/", realpath('./lib/jk07'));
$include_paths[] = sprintf("%s/", realpath('./lib/mandrill'));
$include_paths[] = sprintf("%s/", realpath('./lib/mailchimp'));
$include_paths[] = sprintf("%s/", realpath('./'));
set_include_path(implode(":", $include_paths));


if(isset($_GET['t'])){
    var_dump($_GET);
}


function add_user_point($data) {
	// Look up points value if not set
	if (!isset($data['points'])) {
		$Point = new Point();
		unset($Point);
		$Point = new Point();
		$point = $Point->get_row('point', array('point_id' => $data['point_id']));

		$data['points'] = $point[0]['points'];
		unset($Point);
	}

	$User_Point = new User_Point();
	unset($User_Point);
	$User_Point = new User_Point();
	$params = array(
		'data' => $data
	);
	$User_Point->add_user_point($params);
	unset($User_Point);

	return $data['points'];
}
function delete_user_point($data) {
	$User_Point = new User_Point();
	unset($User_Point);
	$User_Point = new User_Point();
	$params = array(
		'where' => $data
	);
	$User_Point->delete_user_point($params);
	unset($User_Point);
}

function check_required($keys) {
	if (!empty($keys)) {
		$errors = array();

		foreach ($keys as $key) {
			if (!isset($_REQUEST[$key]) || $_REQUEST[$key] ==  '') {
				array_push($errors, ucwords($key) . ' is required.');
			}
		}

		if (!empty($errors)) {
			echo json_pretty(json_encode((resultArray(false, NULL, $errors))));
			die();
		}
	}
}


function log_activity($user_id, $activity_id, $note, $entity = NULL, $entity_id = NULL) {

	$calls = array(
		'log_activity' => array(
			'user_id' => $user_id
			, 'activity_id' => $activity_id
			, 'note' => $note
			, 'api_website_id' => API_WEBSITE_ID
			, 'entity' => $entity
			, 'entity_id' => $entity_id
		)
	);


    $data = api_request('activity_log', $calls, true);

	return $data;
}



function post_tag_notice($message, $posting_id) {
	preg_match_all('/\B@([\S]+)/', $message, $matches);

	if (!empty($matches) && !empty($matches[1])) {
		$usernames = array_values(array_unique($matches[1]));

		$User = new User();
		$users = $User->get_users_by_username($usernames);
		unset($User);

		if (!empty($users)) {
			$Email = new Email();
			foreach ($users['data'] as $i => $user) {
				$Email->email('tagged_in_post', $user, array('posting_id' => $posting_id));
			}
		}
	}
}
function add_repo_search_term($request) {
	if (!empty($request['instagram_username']) || !empty($request['pinterest_username'])) {
		$params = array(
			'data' => array(
				'user_id' => $request['user_id']
				, 'type' => 'username'
				, 'active' => '1'
				, 'auto_approve' => '1'
			)
		);
		$Search = new Search();
		if (!empty($request['instagram_username'])) {
			$params['data']['keyword'] = trim($request['instagram_username']);
			$Search_user = $Search->add_username($params);

			if ($Search_user['success']) {
				$site = $Search->get_site('instagram', 'username');
				$Search->add_site_link($Search_user['data']['search_id'], $site);

				search_term_cron_curl($request['instagram_username'], 'instagram');
			}
		}
		if (!empty($request['pinterest_username'])) {
			$params['data']['keyword'] = trim($request['pinterest_username']);
			$Search_user = $Search->add_username($params);

			if ($Search_user['success']) {
				$site = $Search->get_site('pinterest', 'username');
				$Search->add_site_link($Search_user['data']['search_id'], $site);

				search_term_cron_curl($request['pinterest_username'], 'pinterest');
			}
		}

		unset($Search);
	}
}
function search_term_cron_curl($username, $domain_keyword = NULL) {
	$url = 'http://repository.offlinela.com/cron.php';

	$fields = array(
		/*'domain_keyword' => $domain_keyword
		, */'username' => $username
	);
	if (!empty($domain_keyword)) {
		$fields['domain_keyword'] = $domain_keyword;
	}

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);

	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);
}


function register_default_follows($user_id)
{
    //follow default
    $follow_these = array(658, 1375, 790, 1385, 3797, 2763, 3584, 2776, 3577, 2736);

    foreach($follow_these as $ftk => $fthisone)
    {
        $calls = array(
            'follow' => array(
                'user_id' => $fthisone,
                'follower_user_id' => $user_id
            )
        );

        try{
            $follow_user_response = api_request('user', $calls, true);
        }catch (Exception $e ) {

        }
        return $follow_user_response;
    }

};

if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'user') {
	if (isset($_REQUEST['function'])) {
		$user = new User();
		if ($_REQUEST['function'] == 'get-user') {

			if (isset($_REQUEST["user_id"]) ) {
			echo $user -> getUser($_REQUEST["user_id"]);
			}
			return;
		}
		else if ($_REQUEST['function'] == 'login') {
			require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/API.php';
			//require $_SERVER['DOCUMENT_ROOT'] . '/includes/php/functions-api.php';

			// Admin API call
			$calls = array(
				'login' => array(
					'email' => $_REQUEST['email'],
					'password' => $_REQUEST['password']
				)
			);
			$data = api_request('user', $calls, true);

			if (!empty($data['errors'])) {
				$errors = $data['errors'];
			}
			else if (!empty($data['data']['login']['errors'])) {
				$errors = $data['data']['login']['errors'];
			}

			if (!empty($errors)) {
				echo json_pretty(json_encode((resultArray(false, NULL, $errors))));
				die();
			}

			$api_user = $data['data']['login']['data']['user'];
			$token = $data['data']['login']['data']['token'];

			// If admin login successful, check that dahliawolf user exists
			$params = array(
				'where' => array(
					'user_id' => $api_user['user_id']
				)
			);
			$dw_user = $user->get_user($params);

			if (empty($dw_user)) {
				echo json_pretty(json_encode((resultArray(false, NULL, 'Dahlia Wolf user does not exist.'))));
				die();
			}

			// Credit user points
			unset($user);
			$User_Point = new User_Point();
			$point_id = 2;

			// limit once per day
			$user_points = $User_Point->get_user_points_by_day($api_user['user_id'], date('Y-m-d'), $point_id);
			$points_earned = 0;
			if (empty($user_points)) {
				$points_earned = add_user_point(
					array(
						'user_id' => $api_user['user_id']
						, 'point_id' => $point_id
					)
				);
			}

			// Log activity
			//log_activity($api_user['user_id'], 1, 'Logged in', 'user_username', $api_user['user_id']);


			// Scrape social usernames
			unset($User_Point);
			$Search = new Search();
			if (!empty($dw_user['data']['pinterest_username'])) {
				$Search->unindex($dw_user['data']['pinterest_username'], $dw_user['data']['user_id']);
			}
			if (!empty($dw_user['data']['instagram_username'])) {
				if (empty($dw_user['data']['pinterest_username']) || $dw_user['data']['instagram_username'] != $dw_user['data']['pinterest_username']) {
					$Search->unindex($dw_user['data']['instagram_username'], $dw_user['data']['user_id']);
				}
			}
			unset($Search);

			echo json_pretty(json_encode((resultArray(true, array('user' => $api_user, 'token' => $token, 'points_earned' => $points_earned), NULL))));
			die();
		}
		else if ($_REQUEST['function'] == 'token_login') {
			check_required(
				array(
					/*'user_id'*/
					'token'
				)
			);

			$calls = array(
				'token_login' => array(
					'user_id' => $_REQUEST['user_id']
					, 'token' => $_REQUEST['token']
				)
			);
			$data = api_request('user', $calls, true);

			if (empty($data['success']) || empty($data['data']['token_login']['data'])) {
				echo json_pretty(json_encode((resultArray(false, NULL, 'Invalid token.'))));
			}
			else {
				$api_user = $data['data']['token_login']['data'];
				echo json_pretty(json_encode((resultArray(true, array('user' => $api_user, 'token' => $_REQUEST['token']), NULL))));
			}
			die();
		}
		else if ($_REQUEST['function'] == 'social_login') {
			check_required(
				array(
					'first_name'
					, 'last_name'
					, 'email'
					, 'username'
					, 'api_website_id'
					, 'social_network'
				)
			);

			$social_networks = array(
				'facebook'
				, 'twitter'
				, 'instagram'
			);

			$social_network = strtolower($_REQUEST['social_network']);
			if (!in_array($social_network, $social_networks)) {
				echo json_pretty(json_encode((resultArray(false, NULL, 'Invalid social network.'))));
				die();
			}

			// Admin API call
			$calls = array(
				'login' => array(
					'first_name' => $_REQUEST['first_name']
					, 'last_name' => $_REQUEST['last_name']
					, 'email' => $_REQUEST['email']
					, 'username' => $_REQUEST['username']
					, 'api_website_id' => $_REQUEST['api_website_id']
					, 'social_network' => $_REQUEST['social_network']
				)
			);
			$optional_params = array(
				'instagram_username'
				, 'pinterest_username'
				, 'gender'
				, 'location'
				, 'avatar'
			);
			foreach ($optional_params as $param) {
				if (isset($_REQUEST[$param])) {
					$calls['login'][$param] = $params[$param];
				}
			}
			$data = api_request('social_network', $calls, true);

			if (!empty($data['success'])) {
				$return_data = resultArray(true, $data['data']['login']['data']);
			}
			else {
				$return_data = resultArray(false, NULL, $data['data']['login']['errors']);
			}

			echo json_pretty(json_encode(($return_data)));
			return;
		}
		else if ($_REQUEST['function'] == 'register') {
			check_required(
				array(
					/*'first_name'
					, 'last_name'
					, */'email'
					, 'username'
					, 'password'
					, 'api_website_id'
				)
			);

			require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/API.php';

			// Admin API call
			$calls = array(
				'save_user' => array(
					'first_name' => !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : NULL
					, 'last_name' => !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : NULL
					, 'email' => $_REQUEST['email']
					, 'username' => $_REQUEST['username']
					, 'password' => $_REQUEST['password']
					, 'gender' => !empty($_REQUEST['gender']) ? $_REQUEST['gender'] : NULL
					, 'api_website_id' => $_REQUEST['api_website_id']
				)
			);
			$data = api_request('user', $calls, true);

			if (!empty($data['errors'])) {
				echo json_pretty(json_encode((resultArray(false, NULL, $data['errors']))));
				die();
			}
			else if (!empty($data['data']['save_user']['errors'])) {
				echo json_pretty(json_encode((resultArray(false, NULL, $data['data']['save_user']['errors']))));
				die();
			}

			// Set user_id
			$user_id = $data['data']['save_user']['data']['user_id'];

			// Create customer
			$calls = array(
				'save_customer' => array(
					'user_id' => $user_id
					, 'firstname' => !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : NULL
					, 'lastname' => !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : NULL
					, 'email' => $_REQUEST['email']
					, 'username' => $_REQUEST['username']
				)
			);
			$data = commerce_api_request('customer', $calls, true);

			if (!empty($data['errors'])) {
				echo json_pretty(json_encode((resultArray(false, NULL, $data['errors']))));
				die();
			}
			else if (!empty($data['data']['save_customer']['errors'])) {
				echo json_pretty(json_encode((resultArray(false, NULL, $data['data']['save_customer']['errors']))));
				die();
			}

			$point_id = 1;

			// Get # points
			$Point = new Point();
			$point = $Point->get_row('point', array('point_id' => $point_id));
			$point = $point[0];

			$User = new User();
			$user_params = array(
				'data' => array(
					'user_id' => $user_id
					, 'username' => $_REQUEST['username']
					, 'email_address' => $_REQUEST['email']
					, 'first_name' => !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : NULL
					, 'last_name' => !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : NULL
					, 'gender' => !empty($_REQUEST['gender']) ? $_REQUEST['gender'] : NULL
				)
			);
			$user = $User->addUser($user_params);

			// Credit user points
			$points_earned = add_user_point(
				array(
					'user_id' => $user_id
					, 'point_id' => $point_id
					, 'points' => $point['points']
				)
			);
			$user['data']['points_earned'] = $points_earned;



			// Log the user in
			$calls = array(
				'login' => array(
					'email' => $_REQUEST['email'],
					'password' => $_REQUEST['password']
				)
			);
			$data = api_request('user', $calls, true);

			$user['data']['user'] = $data['data']['login']['data']['user'];
			$user['data']['token'] = $data['data']['login']['data']['token'];



			// Send email
			unset($User);
			$email_template = new Email();
            $email_template->setVar('user_name', "{$_REQUEST['username']}" );
            $email_template->email('signup', $user_id );

            /*
            //schedule :)
            $send_time = gmdate('Y-m-d H:i:s', time()+ (60*5));
			$email_template->email('signup_scheduled', $user_id, null, $send_time );
            */

            //// follow default users
            register_default_follows($user_id);

            // Log activity
            log_activity($user_id, 4, 'Registered', 'user_username', $user_id);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'add_user') {
			check_required(
				array(
					'user_id',
					'first_name',
					'last_name',
					'email_address',
					'username',
				)
			);

			$point_id = 1;

			// Get # points
			$Point = new Point();
			$point = $Point->get_row('point', array('point_id' => $point_id));
			$point = $point[0];

			$User = new User();
			$user_params = array(
				'data' => array(
					'user_id' => intval($_REQUEST['user_id']),
					'username' => $_REQUEST['username'],
					'email_address' => $_REQUEST['email_address'],
					'first_name' => $_REQUEST['first_name'],
					'last_name' => $_REQUEST['last_name'],
                    'created_at' => date("Y-m-d H:i:s")

				)
			);
			$optional_params = array(
				'instagram_username'
				, 'pinterest_username'
				, 'gender'
				, 'location'
				, 'avatar'
				, 'date_of_birth'
			);
			foreach ($optional_params as $param) {
				if (isset($_REQUEST[$param])) {
					$user_params['data'][$param] = $_REQUEST[$param];
				}
			}
			$user = $User->addUser($user_params);

			// Credit user points
			$points_earned = add_user_point(
				array(
					'user_id' => $_REQUEST['user_id']
					, 'point_id' => $point_id
					, 'points' => $point['points']
				)
			);
			$user['data']['points_earned'] = $points_earned;

            ///register default follows
            register_default_follows($user_id);


			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'update_user_optional') {
			check_required(
				array(
					'user_id'
				)
			);

			$optional_params = array(
				'gender'
				, 'about'
				, 'location'
				, 'website'
				, 'facebook_post'
                , 'fb_uid'
				, 'instagram_username'
				, 'pinterest_username'
				//, 'comment_notifications'
				//, 'like_notifications'
				, 'notification_interval'

				// temp for import
				, 'member_id'
			);
			$user_params = array(
				'data' => array(),
				'where' => array(
					'user_id' => $_REQUEST['user_id']
				)
			);
			foreach ($optional_params as $param) {
				if (!empty($_REQUEST[$param])) {
					$user_params['data'][$param] = $_REQUEST[$param];
				}
			}

			$user_data = $user->updateUser($user_params);
			unset($user);

			// Add repo search term for instagram/pinterest username
			add_repo_search_term($_REQUEST);

			echo json_pretty(json_encode(($user_data)));
			return;
		}
		else if ($_REQUEST['function'] == 'update_user') {
			$user_params = array(
				'data' => array()
				, 'where' => array(
					'user_id' => $_REQUEST['user_id']
				)
			);

			$fields = array(
				'username'
				, 'email_address'
				, 'first_name'
				, 'last_name'
				, 'date_of_birth'
				, 'gender'
				, 'active'
				, 'about'
				, 'location'
				, 'website'
				, 'facebook_post'
				, 'instagram_import'
				, 'instagram_username'
				, 'pinterest_username'
				, 'comment_notifications'
				//, 'like_notifications'
				//, 'comment_notifications'
				, 'notification_interval'
			);

			foreach ($fields as $field) {
				if (array_key_exists($field, $_REQUEST)) {
					$user_params['data'][$field] = $_REQUEST[$field];
				}
			}

			if (!empty($_REQUEST['avatar'])) {
				$user_params['data']['avatar'] = $_REQUEST['avatar'];
			}
			if (!empty($_REQUEST['date_of_birth'])) {
				$user_params['data']['date_of_birth'] = date('Y-m-d', strtotime($_REQUEST['date_of_birth']));
			}

			$user_data = NULL;

			if (!empty($user_params['data'])) {
				$user_data = $user->updateUser($user_params);
			}

			//unset($user);

			// Add repo search term for instagram/pinterest username
			add_repo_search_term($_REQUEST);

			//echo json_pretty(json_encode(($user_data)));
            /***** */
            $params = array(
                'where' => array(
                    'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL,
                    'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL,
                    'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL,
                )
            );
            $user_data = $user->get_user($params);


            echo json_pretty(json_encode(($user_data)));
            return;

			return;
		}
		else if ($_REQUEST['function'] == 'get_user') {
			$params = array(
				'where' => array(
					'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL,
					'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL,
					'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL,
				)
			);
			$user = $user->get_user($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_users') {
			$params = array(
				'where' => array(
					'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL
					, 'username_like' => !empty($_REQUEST['username_like']) ? $_REQUEST['username_like'] : NULL
					, 'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL
					, 'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			$users = $user->get_users($params);

			echo json_pretty(json_encode(($users)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_points') {
			check_required(
				array(
					'user_id'
				)
			);

			$params = array(
				'where' => array(
					'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL
				)
			);
			$user = $user->get_points($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_membership_level') {
			check_required(
				array(
					'user_id'
				)
			);

			$user = $user->get_membership_level($_REQUEST['user_id']);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_following') {
			$params = array(
				'where' => array(
					'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL
					, 'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL
					, 'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			$user = $user->get_following($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_followers') {
			$params = array(
				'where' => array(
                    'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL,
                    'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL,
                    'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			$user = $user->get_followers($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'follow') {
			$params = array(
				'data' => array(
					'user_id' => $_REQUEST['user_id']
					, 'follower_user_id' => $_REQUEST['follower_user_id']
				)
			);
			$user = $user->follow($params);

			// Log activity
			log_activity($_REQUEST['user_id'], 34, 'Started Following you', 'follow', $user['data']);

			// Log activity
			log_activity($_REQUEST['follower_user_id'], 37, 'Followed another user', 'follow', $user['data']);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'unfollow') {
			$params = array(
				'where' => array(
					'user_id' => $_REQUEST['user_id']
					, 'follower_user_id' => $_REQUEST['follower_user_id']
				)
			);
			$user = $user->unfollow($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_rank') {
			$params = array(
				'where' => array(
					'user_id' =>  $_REQUEST['user_id']
				)
			);
			$user = $user->get_rank($params);

			echo json_pretty(json_encode(($user)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_top_ranked') {
			$params = array(
				'limit' => !empty($_REQUEST['limit']) ? $_REQUEST['limit'] : NULL
				, 'offset' => !empty($_REQUEST['offset']) ? $_REQUEST['offset'] : NULL
			);
			$users = $user->get_top_ranked($params);

			echo json_pretty(json_encode(($users)));
			return;
        }
        else if ($_REQUEST['function'] == 'get_top_following')
        {
            $params = array(
                'where' => array(
                    'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL,
                    'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL,
                    'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL
                )
            );

            $params['limit'] = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 5;

            if (!empty($_REQUEST['offset'])) {
                $params['offset'] = $_REQUEST['offset'];
            }


            $user_data = $user->getTopFollowing($params);

            if($_REQUEST['t'])
            {
                //var_dump($user_data);
            }

            if(is_array($user_data['data']))
            {
                //var_dump($user_data);

                $posts_params = array();
                foreach($user_data['data'] as $udkey => $u_data)
                {
                    //var_dump($u_data);

                    $where_params = array(
                        'user_id' =>  $u_data['user_id']
                    );

                    if (!empty($_REQUEST['posts_offset'])) $posts_params['offset'] = $_REQUEST['posts_offset'];

                    //query limits
                    if (!empty($_REQUEST['posts_limit']))  $posts_params['limit'] = $_REQUEST['posts_limit'];
                    else $posts_params['limit'] = 5;

                    $params['where'] = array(
                        'user_id' ,
                        'username',
                        'viewer_user_id'
                    );

                    $posts_params['where'] = $where_params;

                    //$logger = new Jk_Logger( APP_PATH . 'logs/posting.log');
                    //echo ( sprintf("get posts with data %s", var_export($posts_params,true) ));


                    unset($posting);
                    new Posting();

                    $posting  = new Posting();
                    $user_posts =  $posting->allPosts($posts_params);

                    if($user_posts['data']) $u_data['posts'] = $user_posts['data'];

                    $user_data['data'][$udkey] =  $u_data;
                    //var_dump($u_data['posts']);
                }

            }


            echo json_pretty(json_encode(($user_data)));
            return;

		}
        else if ($_REQUEST['function'] == 'get_top_followers')
        {
            $params = array(
                'where' => array(
                    'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL,
                    'username' => !empty($_REQUEST['username']) ? $_REQUEST['username'] : NULL,
                    'viewer_user_id' => !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL
                )
            );

            $params['limit'] = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 5;

            if (!empty($_REQUEST['offset'])) {
                $params['offset'] = $_REQUEST['offset'];
            }


            $user_data = $user->getTopfollowers($params);

            if($_REQUEST['t'])
            {
                //var_dump($user_data);
            }

            if(is_array($user_data['data']))
            {
                $fetch_posts = !( isset($_REQUEST['no_posts']) && (bool)$_REQUEST['no_posts'] === true );
                //var_dump($user_data);
                if( $fetch_posts ) {

                    $posts_params = array();
                    foreach($user_data['data'] as $udkey => $u_data)
                    {
                        //var_dump($u_data);

                        $where_params = array(
                            'user_id' =>  $u_data['user_id']
                        );

                        if (!empty($_REQUEST['posts_offset'])) $posts_params['offset'] = $_REQUEST['posts_offset'];

                        //query limits
                        if (!empty($_REQUEST['posts_limit']))  $posts_params['limit'] = $_REQUEST['posts_limit'];
                        else $posts_params['limit'] = 5;

                        $params['where'] = array(
                            'user_id' ,
                            'username',
                            'viewer_user_id'
                        );

                        $posts_params['where'] = $where_params;

                        //$logger = new Jk_Logger( APP_PATH . 'logs/posting.log');
                        //echo ( sprintf("get posts with data %s", var_export($posts_params,true) ));


                        unset($posting);
                        new Posting();

                        $posting  = new Posting();
                        $user_posts =  $posting->allPosts($posts_params);

                        if($user_posts['data']) $u_data['posts'] = $user_posts['data'];

                        $user_data['data'][$udkey] =  $u_data;
                        //var_dump($u_data['posts']);
                    }
                }

            }


            echo json_pretty(json_encode(($user_data)));
            return;

        }else {
			resultArray(FALSE, "Function doesn't exist!");
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'posting') {
	if (isset($_REQUEST['function'])) {
		$Posting = new Posting();
		if ($_REQUEST['function'] == 'all_posts') {
			$params = array(
				'where' => array()
			);

			if (!empty($_REQUEST['order_by'])) {
				$params['order_by'] = $_REQUEST['order_by'];
			}
			if (!empty($_REQUEST['filter_by']) && !empty($_REQUEST['follower_user_id'])) {
				$params['filter_by'] = $_REQUEST['filter_by'];
				$params['follower_user_id'] = $_REQUEST['follower_user_id'];
			}
			if (!empty($_REQUEST['like_day_threshold'])) {
				$params['like_day_threshold'] = $_REQUEST['like_day_threshold'];
			}
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			if (!empty($_REQUEST['timestamp'])) {
				$params['timestamp'] = $_REQUEST['timestamp'];
			}
            if (!empty($_REQUEST['filter'])) {
				$params['filter'] = $_REQUEST['filter'];
			}

			$where_params = array(
				'user_id',
				'username',
				'viewer_user_id',
				'q',
				'since_posting_id',
			);
			foreach ($where_params as $param) {
				if (isset($_REQUEST[$param])) {
					$params['where'][$param] = $_REQUEST[$param];
				}
			}
			$params['host'] = $_SERVER['HTTP_HOST'];
			echo json_pretty(json_encode(($Posting->allPosts($params))));
			return;
		}
		else if ($_REQUEST['function'] == 'get_liked_posts') {
			$params = array(
				'where' => array()
			);

			if (!empty($_REQUEST['order_by'])) {
				$params['order_by'] = $_REQUEST['order_by'];
			}
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}

			$where_params = array(
				'user_id'
				, 'username'
				, 'viewer_user_id'
			);
			foreach ($where_params as $param) {
				if (isset($_REQUEST[$param])) {
					$params['where'][$param] = $_REQUEST[$param];
				}
			}

			unset($Posting);
			$Posting_Like = new Posting_Like();
			echo json_pretty(json_pretty(json_encode((($Posting_Like->get_liked_posts($params))))));
			return;
		}
		else if ($_REQUEST['function'] == 'add_post') {
			$data = $Posting->addPost($params);

			echo json_pretty(json_encode($data));
			return;
		}
		else if ($_REQUEST['function'] == 'add_post_image') {
			check_required(
				array(
					'user_id'
				)
			);

			unset($Posting);

			// Save image first
			$Image = new Image();
			$image_params = array(
				'data' => array(
					/*'created' => $_REQUEST['created']
					, */
					'repo_image_id' => !empty($_REQUEST['repo_image_id']) ? $_REQUEST['repo_image_id'] : NULL
					, 'imagename' => $_REQUEST['imagename']
					, 'source' => $_REQUEST['source']
					, 'dimensionsX' => $_REQUEST['dimensionsX']
					, 'dimensionsY' => $_REQUEST['dimensionsY']
					, 'domain' => !empty($_REQUEST['domain']) ? $_REQUEST['domain'] : NULL
					, 'attribution_url' => !empty($_REQUEST['attribution_url']) ? $_REQUEST['attribution_url'] : NULL
				)
			);
			$image = $Image->add_image($image_params);

			$image_id = $image['data'];

			// Then save post
			unset($Image);
			$Posting = new Posting();
			$post_params = array(
				'data' => array(
					 /*'created' => $_REQUEST['created'],
					 */
                     'image_id' => $image_id ,
					 'user_id' => $_REQUEST['user_id'],
					 'description' => !empty($_REQUEST['description']) ? $_REQUEST['description'] : ''
				)
			);
			$post = $Posting->addPost($post_params);
			unset($Posting);

			// Credit user points
			$points_earned = add_user_point(
				array(
					'user_id' => $_REQUEST['user_id']
					, 'point_id' => 3
					, 'posting_id' => $post['data']['posting_id']
				)
			);
			$post['data']['points_earned'] = $points_earned;
			$post['data']['new_image_url'] = sprintf("%s/%s", trim($_REQUEST['source'], '/'), trim($_REQUEST['imagename'], '/'));

			// Update feed image (set status to 'Posted')
			if (!empty($_REQUEST['repo_image_id'])) {
				$Feed_Image = new Feed_Image();
				$params = array(
					'data' => array(
						'status' => 'Posted'
					)
					, 'where' => array(
						'id' => $_REQUEST['repo_image_id']
					)
				);
				$image = $Feed_Image->update_feed_image($params);
			}

			// Log activity
			log_activity($_REQUEST['user_id'], 6, 'Posted an image', 'posting', $post['data']['posting_id']);

			// Check post description for tag
			if (!empty($_REQUEST['description'])) {
				post_tag_notice($_REQUEST['description'], $post['data']['posting_id']);
			}

			echo json_pretty(json_encode(($post)));
			return;
		}
		else if ($_REQUEST['function'] == 'update_post') {
			check_required(
				array(
					'user_id'
					, 'posting_id'
				)
			);

			$params = array(
				'data' => array(
					'description' => !empty($_REQUEST['description']) ? $_REQUEST['description'] : ''
				)
				, 'where' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			$post = $Posting->update_post($params);

			echo json_pretty(json_encode(($post)));
			return;
		}
		else if ($_REQUEST['function'] == 'delete_post') {
			check_required(
				array(
					'user_id'
					, 'posting_id'
				)
			);

			$params = array(
				'where' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			$post = $Posting->soft_delete_post($params);
			unset($Posting);

			if (!empty($post['success']) && !empty($post['data']['row_count'])) {
				// Check if post was from repo
				$Image = new Image();
				$image = $Image->get_post_repo_image($params);
				unset($Image);

				// Un-"Posted" the repo image
				if (!empty($image)) {
					$Feed_Image = new Feed_Image();
					$Feed_Image->update('imageInfo', array('status' => 'Approved'), array('id' => $image['data']['repo_image_id']));
				}

				// Uncredit user points
				delete_user_point(
					array(
						'user_id' => $_REQUEST['user_id']
						, 'point_id' => 3
						, 'posting_id' => $_REQUEST['posting_id']
					)
				);
			}

			echo json_pretty(json_encode(($post)));
			return;
		}
		else if ($_REQUEST['function'] == 'add_post_dislike') {
			check_required(
				array(
					'user_id'
					, 'posting_id'
				)
			);

			unset($Posting);

			// Add post like
			$params = array(
				'data' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);
			$Posting_Dislike = new Posting_Dislike();
			$posting_dislike = $Posting_Dislike->add_post_dislike($params);
			unset($Posting_Dislike);

			echo json_pretty(json_encode($posting_dislike));
			return;
		}
		else if ($_REQUEST['function'] == 'add_post_like') {
			unset($Posting);

			// Add post like
			$params = array(
				'data' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
					, 'like_type_id' => !empty($_REQUEST['like_type_id']) ? $_REQUEST['like_type_id'] : 1
				)
			);
			$Posting_Like = new Posting_Like();
			$posting_like = $Posting_Like->add_post_like($params);
			unset($Posting_Like);

			if ($posting_like['success']) {
				// Credit post user
				$Posting = new Posting();
				$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
				if (empty($post)) {
					echo json_pretty(json_encode(resultArray(false, NULL, 'Post does not exist.')));
					die();
				}
				$post_user_id = $post[0]['user_id'];
				add_user_point(
					array(
						'user_id' => $post_user_id
						, 'point_id' => 5
						, 'posting_id' => $_REQUEST['posting_id']
					)
				);

				// Credit user points
				$points_earned = add_user_point(
					array(
						'user_id' => $_REQUEST['user_id']
						, 'point_id' => 4
						, 'posting_id' => $_REQUEST['posting_id']
					)
				);

				// Also return number of points earned
				$posting_like['data']['points_earned'] = $points_earned;

				// Log activity
				log_activity($post_user_id, 9, 'Received a like on an image', 'posting_like', $posting_like['data']['posting_like_id']);

				// Log activity
				log_activity($_REQUEST['user_id'], 10, 'Liked an image', 'posting_like', $posting_like['data']['posting_like_id']);

				// Send email
				unset($Posting);
				/*$Email = new Email();
				$Email->email('liked', $post_user_id, array('posting_id' => $_REQUEST['posting_id']));*/
			}

			echo json_pretty(json_encode($posting_like));
			return;
		}
		else if ($_REQUEST['function'] == 'delete_post_like') {
			unset($Posting);

			// Delete post like
			$params = array(
				'where' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
					, 'like_type_id' => !empty($_REQUEST['like_type_id']) ? $_REQUEST['like_type_id'] : 1
				)
			);
			$Posting_Like = new Posting_Like();
			$posting_like = $Posting_Like->delete_post_like($params);
			unset($Posting_Like);

			// Uncredit post user
			$Posting = new Posting();
			$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
			$post_user_id = $post[0]['user_id'];
			delete_user_point(
				array(
					'user_id' => $post_user_id
					, 'point_id' => 5
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			// Uncredit user points
			delete_user_point(
				array(
					'user_id' => $_REQUEST['user_id']
					, 'point_id' => 4
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			echo json_pretty(json_encode($posting_like));
			return;
		}
		else if ($_REQUEST['function'] == 'add_post_vote') {
			unset($Posting);

			if(!empty($_REQUEST['vote_period_id'])) {
				$vote_period_id = $_REQUEST['vote_period_id'];
			}
			// If vote_period_id is not set, then grab most current one
			else {
				$Vote_Period = new Vote_Period();

				$current_vote_period = $Vote_Period->get_current_vote_period();
				$vote_period_id = $current_vote_period['data']['vote_period_id'];

				unset($Vote_Period);
			}

			// Add post vote
			$params = array(
				'data' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $vote_period_id
				)
			);
			$Posting_Vote = new Posting_Vote();
			$posting_vote = $Posting_Vote->add_post_vote($params);
			unset($Posting_Vote);

			if ($posting_vote['success']) {
				// Credit post user
				$Posting = new Posting();
				$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
				$post_user_id = $post[0]['user_id'];
				add_user_point(
					array(
						'user_id' => $post_user_id
						, 'point_id' => 14
						, 'posting_id' => $_REQUEST['posting_id']
					)
				);

				// Credit user points
				$points_earned = add_user_point(
					array(
						'user_id' => $_REQUEST['user_id']
						, 'point_id' => 13
						, 'posting_id' => $_REQUEST['posting_id']
					)
				);
				$posting_vote['data']['points_earned'] = $points_earned;
			}

			// Log activity
			log_activity($post_user_id, 13, 'Received a vote on an image', 'posting', $_REQUEST['posting_id']);

			// Log activity
			log_activity($_REQUEST['user_id'], 16, 'Voted an image', 'posting', $_REQUEST['posting_id']);

			echo json_pretty(json_pretty(json_encode((($posting_vote)))));
			return;
		}
		else if ($_REQUEST['function'] == 'delete_post_vote') {
			unset($Posting);

			// Delete post vote
			$params = array(
				'where' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
				)
			);
			$Posting_Vote = new Posting_Vote();
			$posting_vote = $Posting_Vote->delete_post_vote($params);
			unset($Posting_Vote);

			// Uncredit post user
			$Posting = new Posting();
			$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
			$post_user_id = $post[0]['user_id'];
			delete_user_point(
				array(
					'user_id' => $post_user_id
					, 'point_id' => 14
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			// Uncredit user points
			delete_user_point(
				array(
					'user_id' => $_REQUEST['user_id']
					, 'point_id' => 13
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			echo json_pretty(json_pretty(json_encode((($posting_vote)))));
			return;
		}
		else if ($_REQUEST['function'] == 'get_post') {
			$posting_id = !empty($_REQUEST['posting_id']) ? $_REQUEST['posting_id'] : NULL;
			$viewer_user_id = !empty($_REQUEST['viewer_user_id']) ? $_REQUEST['viewer_user_id'] : NULL;
			$params = array(
				'where' => array(
					'posting_id' => $posting_id,
					'viewer_user_id' => $viewer_user_id
				)
			);


            if(!$posting_id){
                // should always return the top most liked in last 30 days
                $next = $Posting->get_next_posting_id(null, $post['data']['created'], $post['data']['total_likes'], $viewer_user_id  );

                $params['where']['posting_id'] = $next;
                $post = $Posting->getPostDetails($params);

            }else{
                $post = $Posting->getPostDetails($params);
            }

			// Also return previous and next
			if (!empty($post) && !empty($post['data'])) {
				$previous   = $Posting->get_previous_posting_id($post['data']['posting_id'], $post['data']['created'], $post['data']['likes'], $viewer_user_id );
				$next       = $Posting->get_next_posting_id($post['data']['posting_id'], $post['data']['created'], $post['data']['likes'], $viewer_user_id  );

				$post['data']['previous_posting_id'] = (!$previous ? $post['data']['posting_id'] : $previous);
				$post['data']['next_posting_id'] = $next;
			}

			echo json_pretty(json_pretty(json_encode((($post)))));
			return;
		}
		else if ($_REQUEST['function'] == 'get_post_likes') {
			check_required(
				array(
					'posting_id'
				)
			);

			unset($Posting);
			$Posting_Like = new Posting_Like();

			$params = array(
				'where' => array(
					'posting_id' => $_REQUEST['posting_id']
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			$post_likes = $Posting_Like->get_post_likes($params);

			echo json_pretty(json_pretty(json_encode((($post_likes)))));
			return;
		}
		else if ($_REQUEST['function'] == 'get_num_post_likes') {
			unset($Posting);
			$Posting_Like = new Posting_Like();

			$params = array(
				'where' => array(
					'posting_id' => $_REQUEST['posting_id']
				)
			);
			$num_post_likes = $Posting_Like->get_num_post_likes($params);

			echo json_pretty(json_encode(($num_post_likes)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_num_post_votes') {
			unset($Posting);
			$Posting_Vote = new Posting_Vote();

			$params = array(
				'where' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
				)
			);
			$num_post_votes = $Posting_Vote->get_num_post_votes($params);

			echo json_pretty(json_encode(($num_post_votes)));
			return;
		}
		else if ($_REQUEST['function'] == 'add_like_winner') {
			// Get number of likes
			$params = array(
				'conditions' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'date' => !empty($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d')
				)
			);
			$post = $Posting->get_num_post_likes_by_day($params);
			$num_likes = (int)$post['data']['likes'];

			// Log like_winner
			unset($Posting);
			$Like_Winner = new Like_Winner();

			$params = array(
				'data' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'likes' => $num_likes
				)
			);
			$like_winner = $Like_Winner->add_like_winner($params);

			// Log activity
			log_activity($post['data']['user_id'], 31, 'Product won by likes', 'like_winner', $like_winner['data']);

			echo json_pretty(json_encode(($like_winner)));
			return;
		}
		else if ($_REQUEST['function'] == 'add_vote_winner') {
			// Get number of votes
			$params = array(
				'conditions' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
				)
			);
			$post = $Posting->get_num_votes_by_period($params);
			$num_votes = $post['data']['votes'];

			// Log vote_winner
			unset($Posting);
			$Vote_Winner = new Vote_Winner();

			$params = array(
				'data' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
					, 'votes' => $num_votes
				)
			);
			$vote_winner = $Vote_Winner->add_vote_winner($params);

			// Log activity
			log_activity($post['data']['user_id'], 19, 'Product won a vote', 'vote_winner', $vote_winner['data']);

			unset($Vote_Winner);
			// Get user info
			$User = new User();
			$user = $User->get_user(array(
					'where' => array(
						'user_id' => $post['data']['user_id']
					)
				)
			);
			$user = $user['data'];
			unset($User);

			// Send email
			$email_template = new Email();
			$email_template->email('add_vote_winner', $post['data']['user_id'], array(
					'rank' => $user['rank']
					, 'points' => $user['points']
					, 'comments' => $user['comments']
					, 'posts' => $user['posts']
					, 'likes' => $user['likes']
					, 'followers' => $user['followers']
				)
			);

			echo json_pretty(json_encode(($vote_winner)));
			return;
		}
		else if ($_REQUEST['function'] == 'add_product') {
			check_required(
				array(
					'posting_id'
					, 'vote_period_id'
					, 'product_id'
					//, 'image_url'
				)
			);

			unset($Posting);

			$Posting_Product = new Posting_Product();

			// Check if product has any posts
			$product_posts = $Posting_Product->get_product_posts($_REQUEST['product_id']);
			$is_primary = empty($product_posts);

			// Add to voting pool
			$params = array(
				'data' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
					, 'product_id' => $_REQUEST['product_id']
					//, 'image_url' => $_REQUEST['image_url']
				)
			);
			$post_product = $Posting_Product->add_post_product($params);

			unset($Posting_Product);

			// Credit post user
			$Posting = new Posting();
			$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
			$post_user_id = $post[0]['user_id'];
			$points_earned = add_user_point(
				array(
					'user_id' => $post_user_id
					, 'point_id' => 8
				)
			);
			$post_product['data']['points_earned'] = $points_earned;

			// Log activity
			log_activity($post[0]['user_id'], 22, 'Product created', 'posting_product', $_REQUEST['product_id']);

			// Send email
			unset($Posting);
			$email_template = new Email();
			$email_template->email('add_product', $post_user_id, array(
					'is_primary' => $is_primary
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);

			echo json_pretty(json_encode(($post_product)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_product') {
			unset($Posting);
			$params = array(
				'where' => array(
					'posting_id' => $_REQUEST['posting_id']
				)
			);

			$Posting_Product = new Posting_Product();
			$posting_product = $Posting_Product->get_product($params);

			echo json_pretty(json_encode(($posting_product)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_vote_posts') {
			if(!empty($_REQUEST['vote_period_id'])) {
				$vote_period_id = $_REQUEST['vote_period_id'];
			}
			// If vote_period_id is not set, then grab most current one
			else {
				unset($Posting);
				$Vote_Period = new Vote_Period();

				$current_vote_period = $Vote_Period->get_current_vote_period();
				$vote_period_id = $current_vote_period['data']['vote_period_id'];

				unset($Vote_Period);
				$Posting = new Posting();
			}

			$params = array(
				'where' => array(
					'vote_period_id' => $vote_period_id
				)
			);
			$where_params = array(
				'viewer_user_id'
			);
			foreach ($where_params as $param) {
				if (!empty($_REQUEST[$param])) {
					$params['where'][$param] = $_REQUEST[$param];
				}
			}
			$params['host'] = $_SERVER['HTTP_HOST'];
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			echo json_pretty(json_encode(($Posting->get_vote_posts($params))));
			return;
		}
		else if ($_REQUEST['function'] == 'get_top_liked_posts_by_day') {
			$params = array(
				'conditions' => array(
					'date' => !empty($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d')
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}

			echo json_pretty(json_encode(($Posting->get_top_liked_posts_by_day($params))));
			return;
		}
		else if ($_REQUEST['function'] == 'activate_product') {
			$params = array(
				'data' => array(
					'posting_id' => $_REQUEST['posting_id']
				)
			);
			$product = $Posting->activate_product($params);

			$product_id = $product['data'];
			$product['data'] = array(
				'product_id' => $product_id
			);

			unset($Posting);
			if ($product['success']) {
				// Get number of votes
				$params = array(
					'conditions' => array(
						'posting_id' => $_REQUEST['posting_id']
						, 'vote_period_Id' => $_REQUEST['vote_period_id']
					)
				);
				$Posting = new Posting();
				$post = $Posting->get_num_votes_by_period($params);
				$num_votes = $post['data']['votes'];

				// Log vote_winner
				unset($Posting);
				$Vote_Winner = new Vote_Winner();

				$params = array(
					'data' => array(
						'posting_id' => $_REQUEST['posting_id']
						, 'votes' => $num_votes
					)
				);
				$vote_winner = $Vote_Winner->add_vote_winner($params);

				// Credit post user
				$Posting = new Posting();
				$post = $Posting->get_row('posting', array('posting_id' => $product_id));
				$post_user_id = $post[0]['user_id'];
				add_user_point(
					array(
						'user_id' => $post_user_id
						, 'point_id' => 9
					)
				);
			}

			echo json_pretty(json_encode(($product)));
			return;
		}
	}

}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'vote_period') {
	if (isset($_REQUEST['function'])) {
		$Vote_Period = new Vote_Period();

		if ($_REQUEST['function'] == 'add_vote_period') {
			$params = array(
				'data' => array(
					'start' => !empty($_REQUEST['start']) ? $_REQUEST['start'] : NULL
					, 'end' => !empty($_REQUEST['end']) ? $_REQUEST['end'] : NULL
				)
			);

			$vote_period = $Vote_Period->add_vote_period($params);

			echo json_pretty(json_encode(($vote_period)));
			return;
		}
		else if ($_REQUEST['function'] == 'get_current_vote_period') {
			$vote_period = $Vote_Period->get_current_vote_period();

			echo json_pretty(json_encode(($vote_period)));
			return;
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'posting_vote') {
	if (isset($_REQUEST['function'])) {
		$Posting_Vote = new Posting_Vote();

		if ($_REQUEST['function'] == 'get_top_voted_posts') {
			$params = array(
				'where' => array(
					'vote_period_id' => $_REQUEST['vote_period_id']
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}

			$votes = $Posting_Vote->get_top_voted_posts($params);

			echo json_pretty(json_encode(($votes)));
			return;
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'comment') {
	if (isset($_REQUEST['function'])) {
		$Comment = new Comment();
		if ($_REQUEST['function'] == 'get_post_comments') {
			check_required(
				array(
					'posting_id'
				)
			);

			$params = array(
				'where' => array(
					'posting_id' => $_REQUEST['posting_id']
				)
			);
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}

			echo json_pretty(json_encode(($Comment->get_post_comments($params))));
			return;
		}
		else if ($_REQUEST['function'] == 'add_comment') {
			check_required(
				array(
					'user_id'
					, 'posting_id'
					, 'comment'
				)
			);

			$params = array(
				'data' => array(
					'user_id' => $_REQUEST['user_id']
					, 'posting_id' => $_REQUEST['posting_id']
					, 'comment' => $_REQUEST['comment']
				)
			);
			$optional_data_params = array(
				'parent_comment_id'
				, 'product_id'
			);
			foreach ($optional_data_params as $param) {
				if (!empty($_REQUEST[$param])) {
					$params['data'][$param] = $_REQUEST[$param];
				}
			}
			$comment = $Comment->add_comment($params);

			// Credit user points
			$points_earned = add_user_point(
				array(
					'user_id' => $_REQUEST['user_id']
					, 'point_id' => 6
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);
			$comment['data']['points_earned'] = $points_earned;

			// Check comment for tag
			if (!empty($_REQUEST['comment'])) {
				post_tag_notice($_REQUEST['comment'], $_REQUEST['posting_id']);
			}

			// Send email
			unset($Comment);
			/*$Posting = new Posting();
			$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
			$post_user_id = $post[0]['user_id'];
			unset($Posting);
			$Email = new Email();
			$Email->email('commented', $post_user_id, array('posting_id' => $_REQUEST['posting_id']));*/

			// Log activity
			log_activity($post_user_id, 32, 'Received a comment on an image', 'comment', $comment['data']['comment_id']);

			// Log activity
			log_activity($_REQUEST['user_id'], 25, 'Commented on an image', 'comment', $comment['data']['comment_id']);

			echo json_pretty(json_encode(($comment)));
			return;
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'feed_image') {
	if (isset($_REQUEST['function'])) {
		$Feed_Image = new Feed_Image();
		if ($_REQUEST['function'] == 'get_feed_images') {

            $start = microtime(true);
            error_log("api_call start: ". $start );

			$params = array(
				'where' => array()
			);

			$where_params = array(
				'status',
				'domain_keyword',
				'user_id',
			);
			foreach ($where_params as $param) {
				if (!empty($_REQUEST[$param])) {
					$params['where'][$param] = $_REQUEST[$param];
				}
			}
			if (!empty($_REQUEST['limit'])) {
				$params['limit'] = $_REQUEST['limit'];
			}
			if (!empty($_REQUEST['offset'])) {
				$params['offset'] = $_REQUEST['offset'];
			}
			if (empty($_REQUEST['user_id'])) {
				$params['order_by'] = 'rand';
			}

            //error_log( "api_call before Feed_Image->get_feed_images(): ". (microtime(true)-$start) );
			echo json_pretty(json_encode(($Feed_Image->get_feed_images($params))));
            $start = microtime(true);
            //error_log( "api_call end: ". (microtime(true)-$start) );
			return;
		}
		else if ($_REQUEST['function'] == 'get_feed_image') {
			$params = array(
				'where' => array(
					'id' => $_REQUEST['id']
				)
			);
			$image = $Feed_Image->get_feed_image($params);

			// Also return previous and next
			if (!empty($image) && !empty($image['data'])) {
				$params = array(
					'domain_keyword' => !empty($_REQUEST['domain_keyword']) ? $_REQUEST['domain_keyword'] : NULL
					, 'user_id' => !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : NULL
					, 'status' => !empty($_REQUEST['status']) ? $_REQUEST['status'] : NULL
				);
				$previous = $Feed_Image->get_previous_feed_image($image['data']['id'], $params);
				$next = $Feed_Image->get_next_feed_image($image['data']['id'], $params);

				$image['data']['previous_id'] = $previous;
				$image['data']['next_id'] = $next;
			}

			echo json_pretty(json_encode(($image)));
			return;
		}
		else if ($_REQUEST['function'] == 'update_feed_image') {
			$params = array(
				'data' => array(
					'status' => $_REQUEST['status']
				)
				, 'where' => array(
					'id' => $_REQUEST['id']
				)
			);
			$image = $Feed_Image->update_feed_image($params);

			echo json_pretty(json_encode(($image)));
			return;
		}
		else if ($_REQUEST['function'] == 'scrape_username') {
			check_required(
				array(
					'user_id'
					, 'username'
				)
			);

			// Unindex
			unset($Feed_Image);
			$Search = new Search();

			$Search->unindex($_REQUEST['username'], $_REQUEST['user_id']);

			// Scrape
			$domain_keyword = !empty($_REQUEST['domain_keyword']) ? $_REQUEST['domain_keyword'] : NULL;

			//search_term_cron_curl($_REQUEST['username'], $domain_keyword);
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'repo') {
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'add_username') {
			add_repo_search_term($_REQUEST, true);
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'social') {
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'invite') {
			check_required(
				array(
					'user_id'
					, 'emails'
				)
			);

			$email_template = new Email();

			$message = !empty($_REQUEST['message']) ? $_REQUEST['message'] : NULL;
			echo $email_template->invite_emails($_REQUEST['user_id'], $_REQUEST['emails'], $message);

			die();
		}
		else if ($_REQUEST['function'] == 'feedback') {
			check_required(
				array(
					'user_id'
					, 'message'
				)
			);

			// Get user email
			$User = new User();
			$user = $User->get_row('user_username', array('user_id' => $_REQUEST['user_id']));

			if (empty($user)) {
				echo json_pretty(json_encode((resultArray(false, NULL, 'User does not exist.'))));
				die();
			}

			$username = $user[0]['username'];
			$user_email = $user[0]['email_address'];

			// Send email
			require DR . '/lib/php/class.phpmailer.php';
			require DR . '/lib/php/email.php';

			$from = FROM;
			$from_email = TO_EMAIL_FEEDBACK;
			$to = FROM;
			$to_email = TO_EMAIL_FEEDBACK;
			$subject = 'User Feedback';

			$html_body = $user_email . ' said:<br /><br />' . $_REQUEST['message'];
			$data = email($from, $from_email, $to, $to_email, $subject, $html_body, '', '', '', $user_email);

			// Credit user points
			$points_earned = 0;
			if ($data['sent']) {
				$points_earned = add_user_point(
					array(
						'user_id' => $_REQUEST['user_id']
						, 'point_id' => 10
					)
				);
			}

			// Log activity
			log_activity($_REQUEST['user_id'], 28, 'Gave feedback');

			echo json_pretty(json_encode((resultArray(true, array('sent' => $data, 'points_earned' => $points_earned)))));
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'email') {
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'add_vote_winner') {
			check_required(
				array(
					'user_id'
				)
			);

			$email_template = new Email();
			echo $email_template->email($_REQUEST['function'], $_REQUEST['user_id']);
			die();
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'activity_log') {
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'get_log') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'get_log' => array(
					'user_id' => $_REQUEST['user_id'],
					'api_website_id' => API_WEBSITE_ID
				)
			);
			$data = api_request('activity_log', $calls, true);

			$activity_log = array();
			if (!empty($data['success']) && !empty($data['data']['get_log']['success']) && !empty($data['data']['get_log']['data'])) {
				$activity_log = $data['data']['get_log']['data'];
				// Remove user_username entries
				foreach ($activity_log as $i => $row) {
					if ($row['entity'] == 'user_username') {
						unset($activity_log[$i]);
					}
				}
				$activity_log = array_values($activity_log);
			}

			echo json_pretty(json_encode((resultArray(true, $activity_log))));
			die();
		}
		else if ($_REQUEST['function'] == 'get_grouped_log') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'get_grouped_log' => array(
					'user_id' => $_REQUEST['user_id']
					, 'api_website_id' => API_WEBSITE_ID
				)
			);

			$data = api_request('activity_log', $calls, true);


			$activity_log = array();
			if (!empty($data['success']) && !empty($data['data']['get_grouped_log']['success']) && !empty($data['data']['get_grouped_log']['data'])) {
				$activity_log = $data['data']['get_grouped_log']['data'];
				// Remove user_username entries
				/*foreach ($activity_log as $i => $row) {
					if ($row['entity'] == 'user_username') {
						unset($activity_log[$i]);
					}
				}
				$activity_log = array_values($activity_log);*/
			}

			echo json_pretty(json_encode((resultArray(true, $activity_log))));
			die();
		}
		else if ($_REQUEST['function'] == 'get_num_unread') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'get_num_unread' => array(
					'user_id' => $_REQUEST['user_id']
					, 'api_website_id' => API_WEBSITE_ID
				)
			);
			$data = api_request('activity_log', $calls, true);

			echo json_pretty(json_encode((resultArray(true, $data['data']['get_num_unread']['data']))));
			die();
		}
		else if ($_REQUEST['function'] == 'get_num_grouped_unread') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'get_num_grouped_unread' => array(
					'user_id' => $_REQUEST['user_id']
					, 'api_website_id' => API_WEBSITE_ID
				)
			);
			$data = api_request('activity_log', $calls, true);

			echo json_pretty(json_encode((resultArray(true, $data['data']['get_num_grouped_unread']['data']))));
			die();
		}
		else if ($_REQUEST['function'] == 'get_num_grouped_unpreviewed') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'get_num_grouped_unpreviewed' => array(
					'user_id' => $_REQUEST['user_id']
					, 'api_website_id' => API_WEBSITE_ID
				)
			);
			if (!empty($_REQUEST['type'])) {
				$calls['entity'] = $_REQUEST['type'];
			}
			$data = api_request('activity_log', $calls, true);

			echo json_pretty(json_encode((resultArray(true, $data['data']['get_num_grouped_unpreviewed']['data']))));
			die();
		}
		else if ($_REQUEST['function'] == 'mark_previewed') {
			check_required(
				array(
					'user_id'
				)
			);

			$calls = array(
				'mark_previewed' => array(
					'user_id' => $_REQUEST['user_id']
				)
			);
			if (!empty($_REQUEST['type'])) {
				$calls['mark_previewed']['type'] = $_REQUEST['type'];
			}
			$data = api_request('activity_log', $calls, true);

			if (!empty($data['success']) && !empty($data['data']['mark_previewed']['success'])) {
				echo json_pretty(json_encode((resultArray(true, $calls['mark_previewed']))));
				die();
			}

			$errors = !empty($data['errors']) ? $data['errors'] : $data['data']['mark_previewed']['errors'];
			echo json_pretty(json_encode((resultArray(false, $errors))));
			die();
		}
		else if ($_REQUEST['function'] == 'mark_read') {
			check_required(
				array(
					'user_id'
					, 'activity_log_id'
				)
			);

			$calls = array(
				'mark_read' => array(
					'user_id' => $_REQUEST['user_id']
					, 'activity_log_id' => $_REQUEST['activity_log_id']
				)
			);
			$data = api_request('activity_log', $calls, true);

			if (!empty($data['success']) && !empty($data['data']['mark_read']['success'])) {
				echo json_pretty(json_encode((resultArray(true, $calls['mark_read']))));
				die();
			}

			$errors = !empty($data['errors']) ? $data['errors'] : $data['data']['mark_read']['errors'];
			echo json_pretty(json_encode((resultArray(false, $errors))));
			die();
		}
		else if ($_REQUEST['function'] == 'mark_unread') {
			check_required(
				array(
					'user_id'
					, 'activity_log_id'
				)
			);

			$calls = array(
				'mark_unread' => array(
					'user_id' => $_REQUEST['user_id']
					, 'activity_log_id' => $_REQUEST['activity_log_id']
				)
			);
			$data = api_request('activity_log', $calls, true);

			if (!empty($data['success']) && !empty($data['data']['mark_unread']['success'])) {
				echo json_pretty(json_encode((resultArray(true, $calls['mark_unread']))));
				die();
			}

			$errors = !empty($data['errors']) ? $data['errors'] : $data['data']['mark_unread']['errors'];
			echo json_pretty(json_encode((resultArray(false, $errors))));
			die();
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'daily_summary') {
	if (isset($_REQUEST['function'])) {
		check_required(
			array(
				'user_id'
			)
		);

		$date = !empty($_REQUEST['date']) ? date('Y-m-d', strtotime($_REQUEST['date'])) : date('Y-m-d');
		if ($_REQUEST['function'] == 'likes') {
			$Posting_Like = new Posting_Like();
			$likes = $Posting_Like->get_daily_count($_REQUEST['user_id'], $date);

			echo json_pretty(json_encode((resultArray(true, array('user_id' => $_REQUEST['user_id'], 'date' => $date, 'likes' => $likes)))));
		}
		else if ($_REQUEST['function'] == 'comments') {
			$Comment = new Comment();
			$comments = $Comment->get_daily_count($_REQUEST['user_id'], $date);

			echo json_pretty(json_encode((resultArray(true, array('user_id' => $_REQUEST['user_id'], 'date' => $date, 'comments' => $comments)))));
		}
		else if ($_REQUEST['function'] == 'follows') {
			$Follow = new Follow();
			$follows = $Follow->get_daily_count($_REQUEST['user_id'], $date);

			echo json_pretty(json_encode((resultArray(true, array('user_id' => $_REQUEST['user_id'], 'date' => $date, 'follows' => $follows)))));
		}
		else if ($_REQUEST['function'] == 'points') {
			$User_Point = new User_Point();
			$points = $User_Point->get_daily_count($_REQUEST['user_id'], $date);

			echo json_pretty(json_encode((resultArray(true, array('user_id' => $_REQUEST['user_id'], 'date' => $date, 'points' => $points)))));
		}
		else if ($_REQUEST['function'] == 'all') {
			$User = new User();
			$all = $User->get_all_daily_counts($_REQUEST['user_id'], $date);

			echo json_pretty(json_encode((resultArray(true, array_merge(array('user_id' => $_REQUEST['user_id'], 'date' => $date), $all)))));
		}
		die();
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'sharing') {
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'add_share') {
			require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/API.php';

			// Admin API call
			$calls = array(
				'add_share' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'sharing_user_id' => $_REQUEST['sharing_user_id']
					, 'network' => $_REQUEST['network']
					, 'posting_owner_user_id' => $_REQUEST['posting_owner_user_id']
					, 'created_at' => $_REQUEST['created_at']
				)
			);
			$data = api_request('sharing', $calls, true);

			if (!empty($data['errors'])) {
				$errors = $data['errors'];
			}
			else if (!empty($data['data']['add_share']['errors'])) {
				$errors = $data['data']['add_share']['errors'];
			}

			if (!empty($errors)) {
				echo json_encode(resultArray(false, NULL, $errors));
			}
			else {
				echo json_encode(resultArray(true, $data['data']['add_share']['data']));
			}

			die();
		}
	}
}
else if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'test') {
	die();
	if (isset($_REQUEST['function'])) {
		if ($_REQUEST['function'] == 'test') {
			die();
			$description = '@test9 world @test8 ff@fff @asdf hi @dev.zyon';
			//preg_match_all('/\s@([\S]+)/', $description, $matches);
			preg_match_all('/\B@([\S]+)/i', $description, $matches);

			if (!empty($matches) && !empty($matches[1])) {
				$usernames = array_values(array_unique($matches[1]));

				$User = new User();
				$users = $User->get_users_by_username($usernames);
				unset($User);

				if (!empty($users)) {
					$email_template = new Email();
					foreach ($users['data'] as $i => $user) {
						$email_template->email('tagged_in_post', $user, array('posting_id' => 102));
					}
				}
			}

			die();
			$email_template = new Email();
			echo $email_template->email('add_vote_winner', 669);
			die();
		}
		// User import (add pinterest usernames)
		else if ($_REQUEST['function'] == 'social_usernames') {
			// Get users
			$User = new User();
			$result = $User->run('SELECT user_id, pinterest_username FROM user_username WHERE pinterest_username != "" AND pinterest_username IS NOT NULL AND user_id != 657');
			$rows = $result->fetchAll();
			unset($User);

			foreach ($rows as $i => $user) {
				$request = array(
					'user_id' => $user['user_id']
					, 'pinterest_username' => $user['pinterest_username']
				);
				add_repo_search_term($request);
			}

			die();
		}
		else if ($_REQUEST['function'] == 'audit_points') {
			die();
			// Get users
			$User = new User();
			$result = $User->run('SELECT user_id FROM user_username');
			$rows = $result->fetchAll();
			unset($User);

			// Audit points
			$User_Point = new User_Point();
			unset($User_Point);
			$User_Point = new User_Point();
			foreach ($rows as $i => $user) {
				$User_Point->audit_user_points($user['user_id']);
			}
			die();
		}
		else if ($_REQUEST['function'] == 'add_post_image') {
			$Image = new Image();
			$image_params = array(
				'data' => array(
					/*'created' => $_REQUEST['created']
					, */
					'repo_image_id' => !empty($_REQUEST['repo_image_id']) ? $_REQUEST['repo_image_id'] : NULL
					, 'imagename' => $_REQUEST['imagename']
					, 'source' => $_REQUEST['source']
					, 'dimensionsX' => $_REQUEST['dimensionsX']
					, 'dimensionsY' => $_REQUEST['dimensionsY']
					, 'domain' => !empty($_REQUEST['domain']) ? $_REQUEST['domain'] : NULL
					, 'attribution_url' => !empty($_REQUEST['attribution_url']) ? $_REQUEST['attribution_url'] : NULL
				)
			);
			$image = $Image->add_image($image_params);
			print_r($image);
		}
		else if ($_REQUEST['function'] == 'add_product') {
			check_required(
				array(
					'posting_id'
					, 'vote_period_id'
					, 'product_id'
					//, 'image_url'
				)
			);

			unset($Posting);
			// Add to voting pool
			$Posting_Product = new Posting_Product();

			// Check if product has any posts
			$product_posts = $Posting_Product->get_product_posts($_REQUEST['product_id']);
			$is_primary = empty($product_posts);

			$params = array(
				'data' => array(
					'posting_id' => $_REQUEST['posting_id']
					, 'vote_period_id' => $_REQUEST['vote_period_id']
					, 'product_id' => $_REQUEST['product_id']
					//, 'image_url' => $_REQUEST['image_url']
				)
			);
			//$post_product = $Posting_Product->add_post_product($params);

			unset($Posting_Product);

			// Credit post user
			$Posting = new Posting();
			$post = $Posting->get_row('posting', array('posting_id' => $_REQUEST['posting_id']));
			//$post_user_id = $post[0]['user_id'];
			$post_user_id = 3672;

			// Send email
			unset($Posting);
			$User = new User();
			$user = $User->get_user(array(
					'where' => array(
						'user_id' => 657
					)
				)
			);
			$user = $user['data'];
			unset($User);
			$email_template = new Email();
			/*$Email->email('add_product', $post_user_id, array(
					'is_primary' => $is_primary
					, 'posting_id' => $_REQUEST['posting_id']
				)
			);*/
			$email_template->email('add_vote_winner', $post_user_id, array(
					'rank' => $user['rank']
					, 'points' => $user['points']
					, 'comments' => $user['comments']
					, 'posts' => $user['posts']
					, 'likes' => $user['likes']
					, 'followers' => $user['followers']
				)
			);

			//echo json_pretty(json_encode(($post_product)));
			return;
		}
	}
} else {
    json_pretty(json_encode(
		false
		, NULL
		, array(
			'Invalid function call.'
		)
	));
}
die();
?>

