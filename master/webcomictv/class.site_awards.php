<?PHP
class site_awards extends webcomictv_DB_Object {

	public $primarykey = 'site_award';


	public function removeAward ($f_siteaward) {

            $q = 'DELETE FROM site_awards WHERE site_award = ' . $f_siteaward . ';';
            return @pg_query($q);

        }

	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN awards ON (sa_award = award) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}

	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN awards ON (sa_award = award) ';
		$query .= $this->formatWhere($where);
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
}
?>