<?php
/**
 * User: JDorado
 * Date: 9/26/13
 */
 
class Boosted extends _Model
{
    const TABLE = 'imageInfo';
    const PRIMARY_KEY_FIELD = 'id';

    private $table = self::TABLE;

    public function __construct($db_host = REPO_API_HOST, $db_user = REPO_API_USER, $db_password = REPO_API_PASSWORD, $db_name = REPO_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }


    public function processImage($request_data = array())
    {
        $src_domain = $request_data['domain'];
        $image_url  = $request_data['image_url'];
        $image_caption  = $request_data['image_caption'];

       	$upload_dir = 'upload/';

       	//get contents of remote file & save
       	$imagename = sprintf("%s_%s", time(), str_replace('?','_', basename(urldecode($image_url))) );

        /*
        $replace_arr = explode('', '~!@#$%^&*()-+=' );
        foreach($replace_arr as $replace ) $imagename = str_replace("$replace", '_', $imagename);
        */

        $repo_root = "/mnt/repodata/{$upload_dir}";
        $image_repo_loc = $repo_root . $imagename;

        $image_data = @file_get_contents( $image_url );

        $errors = array();

       	if ($image_data)
        {
       		$saved = file_put_contents($image_repo_loc, $image_data);

            if($saved){

                $dimensions = @getimagesize($image_repo_loc);
                file_put_contents($image_repo_loc, $image_data);

                //$source = 'http://' . $_SERVER['HTTP_HOST'] . $upload_dir;
                $source = 'http://repository.offlinela.com/' . $upload_dir . $imagename;

                $source_base = $source = 'http://repository.offlinela.com/' . $upload_dir;


                if( $dimensions[0]*$dimensions[1] >= 900 )
                {
                    $insert_image_data = array(
                        'imagename' => $image_caption,
                        'source' => $source_base,
                        'dimensionsX' => !empty($dimensions) ? $dimensions[0] : NULL,
                        'dimensionsY' => !empty($dimensions) ? $dimensions[1] : NULL,
                        'baseurl' => $source,
                        'big_baseurl' => $source,
                        'imageURL' => $imagename,
                        'status' => "Approved",
                        'attribution_url' => $src_domain,
                    );

                    //echo " image source: $source \n";

                    $db_save = self::addImage($insert_image_data);
                }else
                {
                    $errors[] = "image is too small";
                }
            }else{
                $errors[] = "image could not be saved";
            }

        }else{
            $errors[] = "image could not be downloaded";
        }

        if(count($errors)==0){
            return array(
                "new_image_url" => $source,
                "repo_id" => $db_save ? $db_save['id'] : NULL
            );
        }else{
            return array(
                "errors" => $errors
            );
        }

    }


    public function addImage($data = array())
    {
        $error = NULL;

        $values = array();

        $fields = array(
            'status',
            'source',
            'imagename',
            'baseurl',
            'big_baseurl',
            'dimensionsX',
            'dimensionsY',
            'attribution_url',
            'imageURL'
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
            self::$Exception_Helper->server_error_exception("Unable to save image ". $e->getMessage());
        }

    }




}

?>