<?PHP 
class metacategories {

	public function getChildren ($parent=false, $max=false, $depth=1) {
		($parent !== false) ? $query ='SELECT * FROM metacategories WHERE parent = ' . $parent . ';' : $query ='SELECT * FROM metacategories WHERE parent IS NULL;';
		$res = @pg_query($query);
		while (($arr = @pg_fetch_assoc($res)) && ($depth < $max || $max === false)) {
			$ret[$arr['id']]['info'] = $arr;
			if ($child = $this->getChildren($arr['id'], $max, ($depth+1))) {
				$ret[$arr['id']]['children'] = $child;
			}
		}
		if (!empty($ret)) {	return $ret; } else { return false;}
	}
}


?>