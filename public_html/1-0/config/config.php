<?
@session_start();

date_default_timezone_set('America/Los_Angeles');

define('TIME', time());

define('DEVELOPMENT', false);
define('CR', '');
define('DR', $_SERVER['DOCUMENT_ROOT']);
define('DOMAIN', '');
define('SITE_NAME', 'Dahlia Wolf API');
define('API_VERSION', '1-0');


// API db
//define('DB_API_HOST', '10.48.113.8');
define('DB_API_HOST', '127.0.0.1');
define('DB_API_USER', 'offlineadmin');
define('DB_API_PASSWORD', '9w8^^^qFtwCD7N^N^');
define('DB_API_DATABASE', 'admin_offline_v1_2013');
//define('DB_API_DATABASE', 'dahliawolf_v1_2013');


define('ADMIN_API_HOST', '127.0.0.1');
define('ADMIN_API_USER', 'off_admin');
define('ADMIN_API_PASSWORD', 'EYCs5HhdwWbBKpvc');
define('ADMIN_API_DATABASE', 'admin_offline_v1_2013');

define('DW_API_HOST', '127.0.0.1');
define('DW_API_USER', 'offlineadmin');
define('DW_API_PASSWORD', '9w8^^^qFtwCD7N^N^');
define('DW_API_DATABASE', 'dahliawolf_v1_2013');

define('REPO_API_HOST', '127.0.0.1');
define('REPO_API_USER', 'offlineadmin');
define('REPO_API_PASSWORD', '9w8^^^qFtwCD7N^N^');
define('REPO_API_DATABASE', 'dahliawolf_repository');


define('MYSQLHOST', DB_API_HOST);
define('MYSQLUSER', DB_API_USER);
define('MYSQLPASS', DB_API_PASSWORD);
define('MYSQLDB', DB_API_DATABASE);

// App db
/*define('DB_APP_HOST', 'localhost');
define('DB_APP_USER', 'root');
define('DB_APP_PASSWORD', '');
define('DB_APP_DATABASE', 'app.flexwurks.com');

// Client db
define('DB_CLIENT_HOST', '');
define('DB_CLIENT_USER', '');
define('DB_CLIENT_PASSWORD', '');
define('DB_CLIENT_DATABASE', '');*/

define('LOG_DIR', DR . '/logs');
define('ERROR_NOTIFICATION_EMAIL', '');

define('ERROR_SYSTEM', 1);
define('ERROR_DATABASE', 256);
define('ERROR_USER', 1024);

function default_exception_handler($exception) {
	if (method_exists($exception, 'get_errors')) {
		$errors = $exception->get_errors();
		if(method_exists($exception, 'log_error')) log_error(print_r($errors, true), 'system');
		echo 'Default Exception Handler:' . "\n";
		print_r($errors);
	}
	else {
		$error = $exception->getMessage();
        if(method_exists($exception, 'log_error')) log_error($error, 'system');
		echo 'Default Exception Handler: ' . $error;
	}
	die();
}

set_exception_handler('default_exception_handler');

/* cannot be used with __invoke. beacuse __invoke will 
	be evaluated first as a function, not as a class
	so __autoload won't be triggered
*/
spl_autoload_register(function($class_name) {
	$class_dirs = array(
		DR . '/' . API_VERSION . '/controllers/'
		, DR . '/' . API_VERSION . '/models/'
		, DR . '/includes/php/classes/'
		, DR . '/' . API_VERSION . '/lib/php/'
		, DR . '/lib/jk07/'
	);


    try{
        foreach ($class_dirs as $class_dir) {
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
            }



            // fallback
            $file = $class_dir . '/' . $class_name . '.php';

            if (file_exists($file)) {
                require $file;
                return true;
            }
        }

    }catch(ErrorException $e)
    {
        error_log("spl_autoload_register exception: " . $e->getMessage());
    }
	
	throw new Exception('Unable to load ' . $class_name);
	
});

require_once DR . '/includes/php/functions.php';
require_once DR . '/includes/php/functions-format.php';

// Commerce
define('API_KEY_DEVELOPER', 'b968a167feba0990b283f0cd65757a60');
define('PRIVATE_KEY_DEVELOPER', '796323f65ce5f0178dc15e8181c17247');
?>
