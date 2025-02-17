<?PHP
class avatars extends webcomictv_DB_Object {

	public $primarykey = 'avatar';
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT *, case when avatar_public then 1 else 0 end as avatar_public, case when avatar_approved then 1 else 0 end as avatar_approved FROM ' . $table . ' LEFT JOIN webcomics ON (avatar_webcomic = webcomic) ';
		$query .= $this->formatWhere($where);
		if ($order) {$query .= $this->formatOrder($order);}
		
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *, case when avatar_public then 1 else 0 end as avatar_public, case when avatar_approved then 1 else 0 end as avatar_approved FROM ' . $table . ' LEFT JOIN webcomics ON (avatar_webcomic = webcomic) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
}
?>