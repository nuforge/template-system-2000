<?PHP
class member_privileges extends webcomicz_DB_Object {
	public $primarykey = 'member_privilege';

	public function getSingleList ($column,$order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT ' . $column . ' FROM ' . $table . ' LEFT JOIN privileges ON (mp_privilege = privilege) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);} else {$query .= ' ORDER BY ' . $this->primarykey;}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$res = @pg_query ($query . ';');
		while ($a = @pg_fetch_assoc($res)) {
			$arr[] = $a[$column];
		}
		return $arr;
	}


	public function getSelectList ($columnVisible,$columnId,$order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT ' . $columnId . ', ' . $columnVisible . ' FROM ' . $table . ' LEFT JOIN privileges ON (mp_privilege = privilege) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);} else {$query .= ' ORDER BY ' . $this->primarykey;}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$res = @pg_query ($query . ';');
		while ($a = @pg_fetch_assoc($res)) {
			$arr[$a[$columnId]] = $a[$columnVisible];
		}
		return $arr;
	}
}
?>