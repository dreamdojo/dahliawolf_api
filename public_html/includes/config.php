<?php
error_reporting(E_ERROR|E_WARNING|E_DEPRECATED|E_COMPILE_ERROR|E_STRICT);
ini_set('display_errors', '0');

//define('API_KEY_DEVELOPER', '4fcd8fd1bd0f8b3e99074fa393ce76a6');
//define('PRIVATE_KEY_DEVELOPER', 'a885d0a41c3daa7e8bdb73984771d696');
define('API_KEY_DEVELOPER', 'b968a167feba0990b283f0cd65757a60');
define('PRIVATE_KEY_DEVELOPER', '796323f65ce5f0178dc15e8181c17247');

define('DR', $_SERVER['DOCUMENT_ROOT']);
define('FROM', 'Dahlia Wolf');
define('FROM_EMAIL', 'reminder@dahliawolf.com');
define('FROM_EMAIL_INVITE', 'reminder@dahliawolf.com');
define('TO_EMAIL_FEEDBACK', 'solomon@zyonnetworks.com');
define('WEBSITE_URL', 'http://dev.dahliawolf.com');

$config = array(
	'Database' => array(
		'driver' =>	'mysql'
		,'persistent'	=> false
		//,'host' => '10.51.98.70'
		//,'host' => '10.48.113.8'
		,'host' => '127.0.0.1'
		//,'login' => 'dahlia'
		,'login' => 'offlineadmin'
		,'password'	=> '9w8^^^qFtwCD7N^N^'
		,'database' => 'dahliawolf_v1_2013'
		,'prefix'	=> ''
		,'encoding'	=> 'utf-8'
		)
	,'JsonFile' => array(
		'auctions' =>'models/tmp/dahlia.json'
		)
	,'APIServer' => array(
		'host' => 'http://api.dahliawolf.com'
		,'version' =>''
		,'auction' =>'/api.php'
		),

		'App'=>array(
					'encoding'               => 'utf-8',
					'timezone'               => 'America/Los_Angeles',
					'currency'               => 'USD',		
					'noCents'        		 => true, // false = show prices in European format (,01c), true = show prices in American format 
					'debug' => 0
			)
);
			
/* database config values goes here.*/
 $dbhost	= $config['Database']['host'];
 $dbname	= $config['Database']['database'];
 $dbuser	= $config['Database']['login'];
 $dbpass	= $config['Database']['password'];
 
 define('DB_HOST', $dbhost);
 define('DB_NAME', $dbname);
 define('DB_USER', $dbuser);
 define('DB_PASS', $dbpass);

/*
define('DW_API_HOST', '127.0.0.1');
define('DW_API_USER', 'offlineadmin');
define('DW_API_PASSWORD', '9w8^^^qFtwCD7N^N^');
define('DW_API_DATABASE', 'dahliawolf_v1_2013');
*/

// API db
//define('DB_API_HOST', '10.48.113.8');
define('DB_API_HOST', '127.0.0.1');
define('DB_API_USER', 'off_admin');
define('DB_API_PASSWORD', 'EYCs5HhdwWbBKpvc');
define('DB_API_DATABASE', 'offline_commerce_v1_2013');

define('ADMIN_API_HOST', '127.0.0.1');
define('ADMIN_API_USER', 'off_admin');
define('ADMIN_API_PASSWORD', 'EYCs5HhdwWbBKpvc');
define('ADMIN_API_DATABASE', 'admin_offline_v1_2013');

define('DW_API_HOST', '127.0.0.1');
define('DW_API_USER', 'off_admin');
define('DW_API_PASSWORD', 'EYCs5HhdwWbBKpvc');
define('DW_API_DATABASE', 'dahliawolf_v1_2013');
 
 define('DB_NAME_REPOSITORY', 'dahliawolf_repository');
 
 include_once $_SERVER['DOCUMENT_ROOT'] . '/models/function.php';
 
spl_autoload_register(function($class_name)
{
	$class_dirs = array(
		$_SERVER['DOCUMENT_ROOT'] . '/models/',
		$_SERVER['DOCUMENT_ROOT'] . '/lib/php',
		$_SERVER['DOCUMENT_ROOT'] . '/lib/jk07',
		//$_SERVER['DOCUMENT_ROOT'] . '/lib/mailchimp',
		$_SERVER['DOCUMENT_ROOT'] . '/lib/mandrill',
		$_SERVER['DOCUMENT_ROOT'] . '/1-0/models',
		$_SERVER['DOCUMENT_ROOT'] . '/includes/php/classes',
	);

     error_log("OLD Fke API.php - spl_autoload_register info: trying to load file: $class_name" );


     try{
        foreach ($class_dirs as $class_dir)
        {
            // Search through directories recursively
            $Directory = new RecursiveDirectoryIterator($class_dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $Iterator = new RecursiveIteratorIterator($Directory, RecursiveIteratorIterator::SELF_FIRST);
            $pattern = '/\/' . preg_quote($class_name) . '\.php/i';
            $Regex = new RegexIterator($Iterator, $pattern);
            $matches = iterator_to_array($Regex, false);
            $Iterator = iterator_to_array($Iterator, false);

            if (!empty($matches)) {
                $file = $matches[0]->getPathname();
                require $file;
                return true;
            }else
            {
                error_log("trying to load class file: no matches found, $class_name file doesnt exist");
            }

            $file = $class_dir . '/' . $class_name . '.php';

            if (file_exists($file)) {
                require $file;
                return true;
            }else
            {
                error_log("OLD Fke API.php - spl_autoload_register warning: failed to load file: $file, file doesnt exist");
            }

        }
    }catch(ErrorException $e)
    {
        error_log("OLD Fke API.php - spl_autoload_register exception: " . $e->getMessage());
    }

	$result = resultArray(
		false
		, NULL
		, array(
			'Unable to load API.'
		)
	);
	
	echo json_encode($result);
	
	die();
	
});

define('API_WEBSITE_ID', 2);


include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/php/functions-api.php';

?>
