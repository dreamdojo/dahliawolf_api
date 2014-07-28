<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */

class Tag_Controller  extends  _Controller
{
    public function __construct()
    {
        //self::delete_post($_GET);
    }

    public function get_tag_id($params)
    {
        $tag = new Tag();

        $data = $tag->getTagId($params);

        $response = array('data' => $data);

        return $response;
    }
    public function get_top_tags($params)
    {
        $tag = new Tag();

        $data = $tag->getTopTags($params);

        $response = array('data'=>$data);

        return $response;
    }
}

?>