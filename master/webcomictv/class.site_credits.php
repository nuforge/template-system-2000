<?PHP
class site_credits extends webcomictv_DB_Object {
	public $primarykey = 'site_credit';



	public function removeSiteCredit ($f_site_credit) {

            $q = 'DELETE FROM site_credits WHERE site_credit = ' . $f_site_credit . ';';
            return @pg_query($q);

        }

	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *, case when sc_privileges THEN 1 ELSE 0 END as sc_privileges FROM ' . $table . ' LEFT JOIN sites ON (sc_site = site) LEFT JOIN members ON (sc_member = member) LEFT JOIN categories ON (site_category = category) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT *, case when sc_privileges THEN 1 ELSE 0 END as sc_privileges FROM ' . $table . ' LEFT JOIN sites ON (sc_site = site) LEFT JOIN members ON (sc_member = member) LEFT JOIN categories ON (site_category = category)  ';
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
}
?>