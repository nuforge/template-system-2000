<?PHP
class zips extends datingsite_DB_Object {

	public $primarykey = 'zip';
	
	public function getDistance($f_zip1, $f_zip2) {
		
		$query = "SELECT get_distance('" . $f_zip1 . "','" . $f_zip2 ."') as distance;";
		
		$ret = pg_fetch_assoc(pg_query($query));
		return floor($ret['distance']);
		
	}


    public function load($where, $order=false, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = "SELECT * FROM " . $table . "

                LEFT JOIN cities ON (zip_city_id = city)  LEFT JOIN regions ON region_code = city_region AND region_country = city_country LEFT JOIN countries ON city_country = country_iso
                ";
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        $query .= ' LIMIT 1 ';
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }


    public function getList($order=false, $where=false, $limit=false, $offset=false, $group=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = "SELECT * FROM " . $table . "

                LEFT JOIN cities ON (zip_city_id = city)  LEFT JOIN regions ON region_code = city_region AND region_country = city_country LEFT JOIN countries ON city_country = country_iso
                ";
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        if ($group) {
            $query .= $this->formatGroup($group);
        }
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        if ($limit) {
            $query .= ' LIMIT ' . $limit . ' ';
        }
        if ($offset) {
            $query .= ' OFFSET ' . $offset . ' ';
        }
        //echo $query;
        return @pg_fetch_all(@pg_query($query . ';'));
    }
	
}
?>