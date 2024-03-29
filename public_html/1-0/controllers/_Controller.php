<?
class _Controller {
	const MODELS_DIRECTORY = '/models/';
	
	public $default_error_message = 'Request could not be completed.';

    /** @var  $data Data */
    protected $data;
	
	protected $Validate;
    protected $use_cache = false;
    protected $object_id;
	
	public function __construct() {	
		
		$this->load('_Model', DB_API_HOST, DB_API_USER, DB_API_PASSWORD, DB_API_DATABASE);

        if($this->Model){
            $this->Model->set_static_vars(
                array(
                    'domain' => DOMAIN,
                    'site_name' => SITE_NAME
                )
            );
        }
		
		$this->load('API_Request_Log', ADMIN_API_HOST, ADMIN_API_USER, ADMIN_API_PASSWORD, ADMIN_API_DATABASE);
		
		$this->Validate = new Validate();
	}

    protected function invalidateCache()
    {
        return ($this->use_cache === true);
    }


    protected function isUsingCache()
    {
        return ($this->use_cache === true);
    }

    protected function setInvalidateCache(boolean $inv)
    {
        $this->use_cache = $inv;
    }

    protected function flushCacheObject($cache_key)
    {
        $redis = new RedisCache;
        return $redis::delete($cache_key);
    }


    protected function getCachedContent($key_params)
    {
        $cache_key = self::getCacheKey($key_params);

        $redis = new RedisCache;

        if( self::invalidateCache() )
        {
            $cached_content = null;
            $redis::delete($cache_key);

        }else{
            $cached_content = $redis::get($cache_key);
        }

        return $cached_content ? $cached_content : null;
    }

    protected function getCacheKey($key_params)
    {
        $cache_key = "";
        foreach ($key_params as $k => $v) $cache_key .= "$k/$v/";

        return trim($cache_key, '/');
    }


    protected function setObjectId($id)
    {

        $this->object_id = $id;
    }


    protected function cacheContent($cache_key_params, $content, $cache_ttl = RedisCache::TTL_HOUR )
    {
        $cache_key = self::getCacheKey($cache_key_params);
        is_array($content ) || is_object($content )? $content = json_encode($content) : null;

        //echo ("caching content with key: $cache_key: $content");

        if($content)
        {
            //echo ("caching content with key: $cache_key");
            $redis = new RedisCache;
            return $redis::save($cache_key, $content, $cache_ttl);
        }

        return false;
    }


    protected function getCacheParams($params, $action)
    {
        $cache_key_params = array();

        //remove useless params
        unset($params['endpoint']);
        unset($params['response_format']);
        unset($params['use_hmac_check']);
        unset($params['function']);
        unset($params['t']);

        if( isset( $params['invalidate_cache']) && (int) $params['invalidate_cache'] == 1 )
        {
            self::setInvalidateCache(true);
            unset($params['invalidate_cache']);
        }

        if( isset( $params['invalidate_cache_object_id']) )
        {
            $invalidate_cache =  base64_decode($params['invalidate_cache_object_id']);
            if($invalidate_cache) {
                self::flushCacheObject($invalidate_cache);
            }
            unset($params['invalidate_cache_object_id']);
        }

        self::trace(__FUNCTION__ . " cache params: ". var_export($params, true) );

        $cache_key_params['object'] = strtolower(str_ireplace('_controller', '', get_class($this)));
        $cache_key_params['action'] = $action;
        foreach ($params as $k => $v) $cache_key_params[$k] = $v;

        return $cache_key_params;
    }



    public function __destruct() {
		unset($this->Model);
	}
	
	protected function setUseCache( boolean $use_cache )
    {
        $this->use_cache = $use_cache;
    }
	
	protected function load($model, $db_host = DB_API_HOST, $db_user = DB_API_USER, $db_password = DB_API_PASSWORD, $db_name = DB_API_DATABASE) {
		$className = ltrim($model, '_');
		/*
		if (!class_exists($className)) {
			require_once DR . '/api/' . API_VERSION . self::MODELS_DIRECTORY . $model . '.php';
		}
		
		if (class_exists($className) && empty($this->$className)) {
			$this->$className = new $className($db_host, $db_user, $db_password, $db_name);
		}
		*/
		
		if (empty($this->$className)) {
			$this->$className = new $model($db_host, $db_user, $db_password, $db_name);
			
		}
	}
	
	public static function wrap_result($success, $data = NULL, $status_code = NULL, $errors = NULL) {
		if (!empty($errors) && !is_array($errors)) {
			$errors = array($errors);
		}

        if(!_Model::$Status_Code) _Model::$Status_Code = new Status_Code();
		
		return array(
			'success' => $success === true ? true : false
			, 'errors' => !empty($errors) ? $errors : NULL
			, 'status_code' => !empty($status_code) ? $status_code : _Model::$Status_Code->get_status_code_ok()
			, 'data' => isset($data) ? $data : NULL
		);
	}
	
	public function convert_null_value(&$item, $key) {
		if (empty($item) && trim($item) == '') {
			$item = NULL;
		}
	}
	
	public function get_status_code($info, $data, $key_field) {
		$status_code = empty($info[$key_field]) && is_numeric($data[$key_field]) ? _Model::$Status_Code->get_status_code_created() : NULL;
		
		return $status_code;
	}


    protected function checkHMAC($calls, $request)
    {
        // Authentication
		$api_key = !empty($request['api_key']) ? $request['api_key'] : NULL;


		$API_Credential = new API_Credential();
		$api_credential = $API_Credential->get_api_credential_by_api_key($api_key);


		$private_key = !empty($api_credential) ? $api_credential['private_key'] : NULL;

		$API = new API($api_key, $private_key);


        $client_hmac = !empty($request['hmac']) ? $request['hmac'] : NULL;
        $Status_Code = new Status_Code();

        $server_hmac = $API->get_hmac($calls);

        // Authorization Failed
		if (empty($api_credential) || $api_credential['active'] != '1' || ($client_hmac != $server_hmac)) { // Invalid API Key
            return false;
		}

        return true;
    }
	
	public function process_request($request) {
		$Status_Code = new Status_Code();

		
		$calls = $request['calls'];
		if (is_string($calls)) {
			$calls = json_decode($calls, true);
		}

        if( isset($request['use_hmac_check']) && (bool)$request['use_hmac_check'] === false )
        {
            $hmac_ok = true;
        }else{
            $hmac_ok = self::checkHMAC($calls, $request);
        }


        /// hack to use GET vars as model data,and use function as the action.. legacy "API"
        if( (!$calls || count($calls) ==0) && $request['function'] )
        {
            $calls[$request['function']] = $_GET;
            unset($request['function']);
        }

        if(!$hmac_ok)
        {
            return static::wrap_result(false, NULL, $Status_Code->get_status_code_unauthorized(), array('Invalid API Key'));
        }

		// Authorized, Do Calls
		else {
			$results = array();
            if(is_array($calls)) foreach ($calls as $function => $params)
            {
                /// hack to use GET vars
                if(!is_array($params)) $params = $_GET;

				if ($params != '' && !is_array($params)) {
					$results[$function] = static::wrap_result(false, NULL, $Status_Code->get_status_code_bad_request(), array('Invalid parameters.'));
					continue;
				}

				//if (function_exists($function) || 1) {
				if (method_exists($this, $function)) {
					if (!is_array($params)) {
						$params = array();
					}
					
					if (empty($params) || is_assoc($params)) {
						try {
							array_walk_recursive($params, array($this, 'convert_null_value'));
							//$results[$function] = $this->$function($params);


                            //params for cache
                            $cache_key_params =  self::getCacheParams($params, $function);

                            if(self::isUsingCache() && ($cached_content = self::getCachedContent($cache_key_params)) )
                            {
                                $results[$function] = $cached_content;
                            }else
                            {
                                $sub_results = $this->$function($params);
                                $results[$function] = $sub_results;

                                if(self::isUsingCache() )
                                {
                                    self::cacheContent($cache_key_params, $sub_results);
                                }
                            }


						} catch (Exception $e) {
							$errors = method_exists($e, 'get_errors') ? $e->get_errors() : $e->getMessage();
							$status_code = method_exists($e, 'get_status_code') ? $e->get_status_code() : NULL;
							$results[$function] = static::wrap_result(false, NULL, $status_code, $errors);
						}
					}
					else {
						$results[$function] = array();
						
						foreach ($params as $sub_params) {
							try {
								array_walk_recursive($sub_params, array($this, 'convert_null_value'));


                                //params for cache
                                $cache_key_params =  self::getCacheParams($sub_params, $function);

                                if(self::isUsingCache() && ($cached_content = self::getCachedContent($cache_key_params)) )
                                {
                                    $results[$function] = $cached_content;
                                }
                                else
                                {
                                    $sub_results = $this->$function($sub_params);
                                }

							} catch (Exception $e) {
								$errors = method_exists($e, 'get_errors') ? $e->get_errors() : $e->getMessage();
								$status_code = method_exists($e, 'get_status_code') ? $e->get_status_code() : NULL;
								$sub_results = static::wrap_result(false, NULL, $status_code, $errors);
							}
							
							array_push($results[$function], $sub_results);

                            if(self::isUsingCache() )
                            {
                                self::cacheContent($cache_key_params, json_encode($sub_results));
                            }
						}
					}
				}
				// API function does not exist
				else {
					$results[$function] = static::wrap_result(false, NULL, $Status_Code->get_status_code_not_found(), array('Invalid API Call'));
				}
			}
		
			$result = static::wrap_result(true, $results);
		}


        ///// do we really need this now???
        /*
		// Log API Request
		$response_format = !empty($_GET['response_format']) ? $_GET['response_format'] : NULL;
		$request_methods = get_request_methods();
		$protocol = !empty($request_methods[$response_format]) ? $request_methods[$response_format] : NULL;
		$this->log_api_request($_SERVER['REMOTE_ADDR'], $_GET['endpoint'], $protocol, $request, $result);
		*/
		// Done
		return $result;
	}

    public function getData()
    {
        if(!$this->data) $this->data = new Data();
        return $this->data->getData();
    }


    protected function addData($key, $val)
    {
        if(!$this->data) $this->data = new Data();
        $this->data->addData($key, $val);
    }


	private function log_api_request($ip_address, $endpoint, $protocol, $request, $result)
    {
		
		
		$api_key = $request['api_key'];
		$hmac = $request['hmac'];
		$calls = $request['calls'];
		if (is_array($calls)) {
			$calls = json_encode($calls);
		}
		
		$status_codes = array(
			'status_code' => $result['status_code']
			, 'data' => NULL
		);
		
		if (!empty($result['data'])) {
			foreach ($result['data'] as $api_service => $sub_result) {
				if (empty($sub_result)) {
					$status_codes['data'][$api_service]['status_code'] = NULL;
					continue;
				}
				
				if (is_assoc($sub_result)) {
					$status_codes['data'][$api_service]['status_code'] = $sub_result['status_code'];
				}
				else {
					$status_codes['data'][$api_service] = array();
					
					foreach ($sub_result as $sub_sub_result) {
						array_push(
							$status_codes['data'][$api_service]
							, array(
								'status_code' => $sub_sub_result['status_code']
							)
						);
					}
				}
			}
		}
		
		$status_codes = json_encode($status_codes);
		
		$info = array(
			'api_key' => $api_key
			, 'ip_address' => $ip_address
			, 'endpoint' => $endpoint
			, 'protocol' => $protocol
			, 'calls' => $calls
			, 'hmac' => $hmac
			, 'status_codes' => $status_codes
		);
		
		try {
			$insert_id = ($this->API_Request_Log ? $this->API_Request_Log->save($info) : NULL );
			
		} catch(Exception $e) {
			//self::$Exception_Helper->server_error_exception('Unable to log api request.');
		}
	}
	
	protected function validate_login_instance($user_id, $token) {
		$this->load('Login_Instance');
		
		$where_params = array(
			'user_id' => $user_id
			, 'token' => $token
			, 'logout' => NULL
		);
		$login_instance = $this->Login_Instance->get_row($where_params);
		
		if (empty($login_instance)) {
			_Model::$Exception_Helper->request_failed_exception('Invalid token.');
		}
	}


    protected function trace($m, $general_log=false)
    {
        $m = ( is_array($m) || is_object($m) ?  json_encode($m) : "$m");
        if($this->logger==null) $this->logger = new Jk_Logger(APP_PATH . sprintf('logs/%s.log', ($general_log?'user_log':strtolower(get_class($this))) ), Jk_Logger::DEBUG);

        $this->logger->LogInfo($m);
    }
}
?>