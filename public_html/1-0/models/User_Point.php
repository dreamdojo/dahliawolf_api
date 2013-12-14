<?php
/**
 * User: JDorado
 * Date: 12/13/13
 */

class User_Point extends _Model
{

    const TABLE = 'user_point';
    const PRIMARY_KEY_FIELD = 'user_point_id';

    private $table = 'user_point';

    private $points_earned = 0;

    protected $fields = array(
        'user_id',
        'point_id',
        'points',
        'posting_id',
        'id_order',
        'note'
    );

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name);
    }

    function addPoint($params)
    {
        $point = new Point();

        $point_count = $point->get_row('point', array('point_id' => $params['point_id']));


        $params['points'] = $point_count['points'];

        $insert_id = $this->save($params);

        if (empty($insert_id)) {
            return array('error' => 'Could not add posting.');
        }

        $this->points_earned = $point_count['points'];

        return $insert_id;
    }


    public function getPointsEarned()
    {
        return $this->points_earned;
    }

}

?> 