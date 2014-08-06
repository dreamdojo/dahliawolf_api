<?php
class Referral extends _Model
{
    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function getTotalReferrals($user_id) {
        $values = Array(
            ':userId' => $user_id
        );

        $q = "
            SELECT COUNT(*) as total
            FROM dahliawolf_v1_2013.referral
            WHERE user_id = :userId
        ";

        try {
            $data = $this->fetch($q, $values);

            return intval($data[0]['total']);

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }
    }

    public function addReferral($params = array())
    {
        $params = array(
            ':userId' => $params['user_id'],
            ':newMemberId' => $params['new_member_id']
        );

        $query = "
                INSERT INTO dahliawolf_v1_2013.referral (user_id, new_member_id)
                VALUES (:userId, :newMemberId);
      			";

        if (isset($_GET['t'])) {
            print_r($params);
            echo "$query\n";
            print_r($params);
            //die();
        }

        try {
            $data = $this->fetch($query, $params);
            if($data) {

            }
            return $data[0];

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }

    }
    public function getReferrals($params = array())
    {
        $params = array(
            ':userId' => $params['user_id'],
        );

        $query = "
                SELECT user_username.username, referral.*
                FROM dahliawolf_v1_2013.referral
                INNER JOIN dahliawolf_v1_2013.user_username ON user_username.user_id = referral.new_member_id
                WHERE referral.user_id = :userId
      			";

        if (isset($_GET['t'])) {
            print_r($params);
            echo "$query\n";
            print_r($params);
            //die();
        }

        try {
            $data = $this->fetch($query, $params);
            return $data;

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }

    }
}
?>