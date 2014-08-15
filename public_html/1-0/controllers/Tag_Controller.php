<?php
/**
 * User: JDorado
 * Date: 7/17/13
 */

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

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
        $blop = getallheaders();
        var_dump($blop);

        $tag = new Tag();

        $data = $tag->getTopTags($params);

        $response = array('data'=>$data);

        return $response;
    }
}

?>