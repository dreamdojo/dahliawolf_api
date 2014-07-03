<?
class Tag extends _Model {
	const TABLE = 'tags';
	const PRIMARY_KEY_FIELD = 'tag_id';

    protected $fields = array(
        'value',
        'created'
    );

    private $table = self::TABLE;

    public function __construct($db_host = DW_API_HOST, $db_user = DW_API_USER, $db_password = DW_API_PASSWORD, $db_name = DW_API_DATABASE)
    {
        parent::__construct($db_host, $db_user, $db_password, $db_name );
    }

    private function getNewId($value) {
        $params = Array('value' => $value);

        $data = $this->save($params);

        return $data;
    }

    public function getTagId($params = Array()) {
        $id = null;
        $value = $params['value'];
        $values = Array();
        $values[':value'] = $value;
        $q = "
            SELECT tag_id
            FROM dahliawolf_v1_2013.tags
            WHERE tags.value = :value
        ";

        try {
            $id = $this->fetch($q, $values);

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }

        $retVal = '';
        if(!isset($id[0]['tag_id'])) {
            $retVal =  $this->getNewId($value);
        } else {
            $retVal =  $id[0]['tag_id'];
        }

        return Array('tag_id' => $retVal);
    }

    public function addTagToPost($posting_id, $tag_id) {
        $values = Array();
        $values[':pid'] = $posting_id;
        $values[':tid'] = $tag_id;

        $q = "
            INSERT INTO posting_tags (posting_id, tag_id)
            VALUES (:pid, :tid)
        ";
        $newId = "SELECT MAX(id) AS id FROM posting_tags";

        try {
            $this->fetch($q, $values);
            $data = $this->fetch($newId, Array());
            return $data[0];

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }
    }

    public function delPostTag ($params = Array()) {
        $values = Array();
        $values[':tid'] = $params['tag_id'];

        $q = "
          DELETE FROM posting_tags
          WHERE id = :tid
          LIMIT 1
        ";

        try {
            $data = $this->fetch($q, $values);
            return $data;

        } catch(Exception $e) {
            self::$Exception_Helper->server_error_exception("can not get posting lovers". $e->getMessage());
        }
    }
}
?>