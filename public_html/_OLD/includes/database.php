<?php
include_once 'config.php';

class Database {
	public $isAlive = FALSE;
	private $Conn	= NULL;
	private $databaseInfo;

	private function Connect() {
		global $config;
		$this -> databaseInfo = $config['Database'];
		$this -> Conn = new mysqli($this -> databaseInfo['host'], $this -> databaseInfo['login'], $this -> databaseInfo['password'], $this -> databaseInfo['database']);
		if ($this -> Conn -> connect_errno) {
			echo "Failed to connect to Mysql:(" . $this -> Conn -> connect_errno . ")" . $this -> Conn -> connect_error;
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	private function resultArray($success, $result) {
		$ret = array('success' => ($success == TRUE ? TRUE : FALSE), 'data' => $result);
		return $ret;
	}
	
	public function Select($tablename, $sql) {
		if ($this -> Connect()) {
			$result = $this -> Conn -> query($sql);
			
			if (!$result) {
    			throw new Exception("Database Error [{$this->database->errno}] {$this->database->error}");
			} else {
			
				$ret = array();
				while ($row = $result->fetch_assoc()) {
					$ret[] = ($row);
				}
				$result->close();
				return $this -> resultArray(TRUE, $ret);
			} 
		}
		else {
				return $this -> resultArray(FALSE, NULL);
			}

	}
	

	

}
?>