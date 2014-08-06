<?php
/**
 * User: JDorado
 * Date: 12/12/13
 */
 
class Commission extends _Model
{

    const TABLE = 'commission';
   	const PRIMARY_KEY_FIELD = 'id';

	private $table = 'imageInfo';

    public function __construct($db_host = REPO_API_HOST, $db_user = REPO_API_USER, $db_password = REPO_API_PASSWORD, $db_name = REPO_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function add_user_commission($user_id, $amount) {
        $query = '
			INSERT INTO offline_commerce_v1_2013.commission (user_id, commission, note) VALUES (:user_id, :amount, :noote)
		';

        $values = array(
            ':user_id' => $user_id,
            ':amount' => $amount,
            ':noote' => 'Member referral'
        );

        try {
            $total_credits = $this->query($query, $values);

            return $total_credits;
        } catch (Exception $e) {
            self::$Exception_Helper->server_error_exception('Unable to get total user store credits.');
        }
    }
}
