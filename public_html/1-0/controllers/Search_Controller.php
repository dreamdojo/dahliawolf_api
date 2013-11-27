<?php
/**
 * User: JDorado
 * Date: 11/26/13
 */
 
class Search_Controller extends _Controller
{

    public function search_all( $request_params = array())
    {
        $posting = new Posting();
        $postings = $posting->getAll($request_params);

        // commerce - Add customer
        $calls = array(
            'get_products' => array(
                'q' => $request_params['q'],
            )
        );

        $products = self::commerceApiRequest('product', $calls, true);

        $user = new User();
        $users = $user->getUsersWithDetails($request_params);

        return array(
            'products'  => $products,
            'postings'  => $postings,
            'users'     => $users

        );

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