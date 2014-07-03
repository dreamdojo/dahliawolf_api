<?php
/**
 * User: JDorado
 * Date: 11/26/13
 */
 
class Search_Controller extends _Controller
{

    public function search_all( $request_params = array())
    {

        // commerce - Add customer
        $calls = array(
            'get_products' => array(
                'use_hmac_check' => 0,
                'q' => $request_params['q'],
            )
        );
        $products = self::commerceApiRequest('product', $calls, true);

        $posting = new Posting();
        $postings = $posting->getAll($request_params);




        $user = new User();
        $users = $user->getUsersWithDetails($request_params);

        return array(
            'products'  => $products,
            'posts'  => $postings,
            'users'     => $users

        );

    }

    public function img_search($params = Array()) {
        $search = new Search();
        $data = $search->fastPosts($params);
        return $data;
    }

    public function find_members($request_params = array()) {
        $input_validations = array(
            'user_id' => array(
                'label' => 'User Id',
                'rules' => array(
                    'is_int' => NULL
                )
            )
        );

        $this->Validate->add_many($input_validations, $request_params, true);
        $this->Validate->run();

        $search = new Search();

        $data = $search->findMembers($request_params);

        return static::wrap_result( ($search->hasError()? false:true), $data, 200, $search->getErrors() );
    }


    protected  function commerceApiRequest($service, $calls, $return_array = false)
    {
    	if (!class_exists('Commerce_API', false)) {
    		require $_SERVER['DOCUMENT_ROOT'] . '/lib/php/Commerce_API.php';
    	}

    	// Instantiate library helper
    	$api = new Commerce_API(API_KEY_DEVELOPER, PRIVATE_KEY_DEVELOPER);

    	$result = $api->rest_api_request($service, $calls);

    	if (!$return_array) {
    		return $result;
    	}

    	$decoded = json_decode($result, true);
    	if ($decoded) {
    		return $decoded;
    	}
    	echo $result;
    	return;
    }


}

?>