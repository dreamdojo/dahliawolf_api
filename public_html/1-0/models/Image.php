<?php
/**
 * User: JDorado
 * Date: 12/13/13
 */
 
class Image extends _Model{

    const TABLE = 'image';
   	const PRIMARY_KEY_FIELD = 'id';
	private $table = 'image';

    protected $fields = array(
        'repo_image_id',
        'imagename',
        'source',
        'dimensionsX',
        'dimensionsY',
        'pid', 'domain',
        'attribution_url'
    );

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

	public function addImage($params = array())
    {

        $insert_id =  $this->save($params);

		if (empty($insert_id)) {
			return array('error' => 'Could not add image.');
		}

		return $insert_id;
	}


    public function getImage($params = array())
    {
   		$query = '
   			SELECT *
   			FROM image
   			WHERE image.repo_image_id = :repo_image_id
   		';

   		$values = array(
   			':repo_image_id' => $params['repo_image_id']
   		);

   		$result = $this->query($query, $values);
   		if (empty($result)) {
   			 return array('error' => 'Could not get post repo image.');
   		}

   		return $result[0];
   	}


	public function getBankImageByPostingId($params = array())
    {
		$query = '
			SELECT image.repo_image_id
			FROM image
				INNER JOIN posting ON image.id = posting.image_id
			WHERE posting.posting_id = :posting_id
		';

		$values = array(
			':posting_id' => $params['posting_id']
		);

        $result = $this->get_row($query, $values);
        if (empty($result)) {
             return array('error' => 'Could not get post repo image.');
        }

        return $result;
	}

}
?>