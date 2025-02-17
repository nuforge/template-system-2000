<?Php
class DB_Connection {

	public $myLink;

	public function __construct ($connection_array = array()) {
		$this->myLink = $this->connect($connection_array);
	}
	
	public function __destruct () {
		if (!empty($this->myLink)) {pg_close($this->myLink);}
	}	

	public function connect ($connection_array = array())  {
		foreach ($connection_array as $k => $v) {
			$conn[] = $k .'='.$v;
		}
		$this->myLink = pg_connect(implode (' ',$conn));
		return $this->myLink;
	}

	public function getConnection () {
		return $this->myLink;
	}

	public function escapeData ($data){
		if (ini_get('magic_quotes_gpc')) {$data = stripslashes($data);}
		return pg_escape_string($data);
	}

}
?>