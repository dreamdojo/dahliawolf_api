<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Store_Credit extends _Model
{

    const TABLE = 'store_credit';
   	const PRIMARY_KEY_FIELD = 'id';

	private $table = 'imageInfo';

    public function __construct($db_host = REPO_API_HOST, $db_user = REPO_API_USER, $db_password = REPO_API_PASSWORD, $db_name = REPO_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function add_user_credit($user_id, $amount) {
        $query = '
			INSERT INTO offline_commerce_v1_2013.store_credit (user_id, amount) VALUES (:user_id, :amount)
		';

        $values = array(
            ':user_id' => $user_id,
            ':amount' => $amount
        );

        try {
            $total_credits = $this->query($query, $values);

            return $total_credits;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to get total user store credits.');
        }
    }
}
