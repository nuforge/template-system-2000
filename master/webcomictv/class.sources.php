<?PHP
class sources extends webcomictv_DB_Object {
	public $primarykey = 'source';

	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *, case when src_active then 1 else 0 end as src_active, case when src_nsfw then 1 else 0 end as src_nsfw FROM ' . $table . ' LEFT JOIN comic_sources ON (cs_source = source) LEFT JOIN sites ON (src_site = site) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT *, case when src_active then 1 else 0 end as src_active, case when src_nsfw then 1 else 0 end as src_nsfw FROM ' . $table . ' LEFT JOIN comic_sources ON (cs_source = source) LEFT JOIN sites ON (src_site = site)  ';
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
}
?>