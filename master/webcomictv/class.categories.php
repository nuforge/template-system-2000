<?PHP
class categories extends webcomictv_DB_Object {
	public $primarykey = 'category';

	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT * FROM ' . $table;
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
}
?>