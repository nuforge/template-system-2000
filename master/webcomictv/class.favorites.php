<?PHP
class favorites extends webcomictv_DB_Object {
	public $primarykey = 'favorite';


	public function removeFavorite ($f_favorite) {

            $q = 'DELETE FROM favorites WHERE favorite = ' . $f_favorite . ';';
            return @pg_query($q);

        }


	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN sites ON (favorite_site = site) LEFT JOIN members ON (favorite_member = member) LEFT JOIN categories ON (site_category = category)';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN sites ON (favorite_site = site) LEFT JOIN members ON (favorite_member = member) LEFT JOIN categories ON (site_category = category) ';
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
}
?>