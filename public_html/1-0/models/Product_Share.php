<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */
 
class Product_Share extends Sharing_Abstract
{
    const TABLE = 'product_share';
   	const PRIMARY_KEY_FIELD = 'product_share_id';

    private $table = 'product_share';

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    public function addShare($data = array())
    {
        $error = NULL;

        $values = array();

        $fields = array(
            'product_id',
            'sharing_user_id',
            'network',
            'posting_owner_user_id',
            'created_at',
        );

        $data['created_at'] = date('Y-m-d h:i:s');

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
            self::$Exception_Helper->server_error_exception("Unable to save  product share. ". $e->getMessage());
        }

    }

    public function deleteShare($params = array())
    {
        $error = NULL;
        if (empty($params['product_share_id'])) {

            $error = 'Invalid posting sharing id';
            return array('errors' => $error);
        }

        $params['where'] = array(
            ':product_share_id' =>  $params['product_share_id']
        );

        $this->db_delete($this->table, $params['where']);


        return array('product_share_id' => $params['where']['product_share_id']);
    }

    public function deleteSharesByParentId($params = array())
    {
        $error = NULL;

        if (empty($params['product_id'])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }

        $params['where'] = array(
            ':product_id' => $params['product_id']
        );

        $this->delete($this->table, $params['where'] );

        return array(true, true);
    }


    public function getShares($params = array())
    {
        $error = NULL;

        if (empty($params['product_id'])) {
            $error = 'Invalid posting id.';
            return array('error' => $error );
        }

        $query = " SELECT
                    *
                    FROM {$this->table}
                    WHERE product_id = :product_id
        ";

        if(isset($params['t'])) echo $query;

        $values = array(
            ':product_id' => $params['product_id']
        );

        $data = $this->fetch($query, $values);

        if ($data === false) {
            return array('error' => 'Could not get product shares.');
        }

        return array('sharings' => $data);
    }


    public function getSharesCount($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'count',
              network
            FROM {$this->table}
            WHERE product_id = :product_id
            GROUP BY network
        ";
        $values = array(
            ':product_id' => $params['product_id']
        );

        if(!$params['product_id']) self::addError('invalid_product_id', 'product id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            $totals = self::getTotalPostShares($params);
            return array(
                        'totals' => $data,
                        'total' => ($totals ? $totals['total'] : null)
                    );
        }

        return null;
    }


    public function getTotalShares($params = array())
    {
        $error = NULL;
        $query = "
            SELECT
              COUNT(*) AS 'total'
            FROM {$this->table}
            WHERE product_id = :product_id
        ";
        $values = array(
            ':product_id' => $params['product_id']
        );

        if(!$params['product_id']) self::addError('invalid_product_id', 'posting id is invalid');

        $data = $this->fetch($query, $values);

        if($data) {
            return array(
                        'total' => $data[0]['total']
                    );
        }

    }

}

?>