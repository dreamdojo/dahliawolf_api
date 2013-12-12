<?
class User_Social_Network_Link extends _Model {
	const TABLE = 'user_social_network_link';

    private $table = 'user_social_network_link';
	const PRIMARY_KEY_FIELD = 'user_social_network_link_id';
	
	protected $fields = array(
		'user_id',
		'social_network_id',
		'token',
		'token_secret',
	);
	
	public function check_email_exists($email, $social_network_id) {
		$query = '
			SELECT user.user_id
			FROM user
				INNER JOIN user_social_network_link ON user.user_id = user_social_network_link.user_id
			WHERE user.email = :email
				AND user_social_network_link.social_network_id = :social_network_id
		';
		$values = array(
			':email' => $email,
			':social_network_id' => $social_network_id
		);
		
		try {
			$user = self::$dbs[$this->db_host][$this->db_name]->select_single($query, $values);
			
			return $user;
		} catch (Exception $e) {
			self::$Exception_Helper->server_error_exception('Unable to check user social network email.');
		}
	}


    public function save($data)
    {
        try{
            $data = parent::save($data);
            return $data;
        }catch (Exception $e)
        {
            $called_class = get_called_class();
            $key_field = self::getPrimaryField() ? self::getPrimaryField() : $called_class::PRIMARY_KEY_FIELD;

            if( stripos($e->getMessage(), 'duplicate') > -1)
            {
                $key_field = self::getPrimaryField() ? self::getPrimaryField() : $called_class::PRIMARY_KEY_FIELD;

                $where_values = array(
                    ':user_id'  => $data['user_id'],
                    ':social_network_id'  => $data['social_network_id'],
                );

                $where_sql = "user_id = :user_id AND social_network_id = :social_network_id";

                $data = $this->db_update($data, $where_sql, $where_values);
                return $data;
            };
        }

        return null;
    }


    public function getAll( $params = array() )
    {
        $error = NULL;
        $values = array();
        if(!$params['to_user_id']) self::addError('invalid_user_id', 'invalid user id');

        $bind_fields = array(
            'user_id',
        );

        foreach ($bind_fields as $field) {
            if (array_key_exists($field, $params)) {
                $values[$field] = $params[$field];
            }
        }

        $where_sql = "";
        $query = "
            SELECT mt.*
            FROM {$this->table} as mt
            WHERE mt.user_id = :user_id
            {$where_sql}
        ";

        try {
            $data = $this->fetch($query, $values);
            self::trace( sprintf("$query\nQUERY RETURNED: %s results", count($data) ) );

            return array('social_links' => $data );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get user social_links". $e->getMessage());
        }

    }
}
?>