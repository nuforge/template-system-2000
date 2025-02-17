<?PHP
class webcomics extends webcomictv_DB_Object {
	public $primarykey = 'webcomic';
	
	
	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		$t_csrc = new comic_sources();
		if (!$table) {$table = $this->table;}
		$query = 'SELECT  *, case WHEN wc_status =1 THEN 1 ELSE 0 END as wc_active, 
		(SELECT max(comic) FROM comics WHERE comic_webcomic = webcomic) as wc_latest,
		(SELECT count(character) FROM characters WHERE character_webcomic = webcomic) as wc_cast
		FROM ' . $table . ' LEFT JOIN statuses ON (wc_status = status) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query($query . ';'));
		/*while ($array = @pg_fetch_assoc($result)) {
			$array['sources'] = $t_csrc->getList('src_title asc',array('cs_comic'=>$array['comic']));
			
			$return[] = $array;
		}
		return $return;*/
	}
	
	public function load ($where,$order=false,$table = false) {
		$t_csrc = new comic_sources();
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT  *, case WHEN wc_status =1 THEN 1 ELSE 0 END as wc_active, 
		(SELECT max(comic) FROM comics WHERE comic_webcomic = webcomic) as wc_latest,
		(SELECT count(character) FROM characters WHERE character_webcomic = webcomic) as wc_cast
		FROM ' . $table . ' LEFT JOIN statuses ON (wc_status = status) ';
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}

                //$this->debugOut($query);
		if($array= @pg_fetch_assoc(@pg_query($query . ';'))) {
			//$array['sources'] = $t_csrc->getList('src_title asc',array('cs_comic'=>$array['comic']));
		}
		return $array;
	}
}
?>