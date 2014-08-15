<?php
 
class Promos extends _Model
{

    protected $fields = array(
        'promo_name',
        'start_date',
        'end_date',
        'active'
    );

    const TABLE = 'promo';
    const PRIMARY_KEY_FIELD = 'promo_id';

    private $table = self::TABLE;

    public function get() {
        $values = array();

        $query = "
            SELECT *
            FROM dahliawolf_v1_2013.promo
            WHERE active = 1
        ";

        try {
            $data = $this->fetch($query, $values);
            return array('promos' => $data );

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }
    }
}

?>