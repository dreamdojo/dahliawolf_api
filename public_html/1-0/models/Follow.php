<?php
/**
 * User: JDorado
 * Date: 8/2/13
 */

class Follow extends _Model {
	const TABLE = 'follow';
	const PRIMARY_KEY_FIELD = 'follow_id';

	protected $fields = array(
		'created',
		 'user_id',
		 'follower_user_id'
	);

    public function followUser($data = array())
    {
        $error = NULL;

        $values = array();

        $fields = array(
            'user_id',
            'follower_user_id',
        );


        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = $data[$field];
            }
        }

        try {
            $insert_id = $this->do_db_save($values, $data);
            return array(
                    strtolower( self::PRIMARY_KEY_FIELD) => $insert_id,
                    //'model_data' => $data
                    );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("Unable to follow users.". $e->getMessage());
        }

    }

}
?>