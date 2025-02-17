<?PHP
class commentaries extends webcomictv_DB_Object {
	public $primarykey = 'commentary';

        public function convertCommentaries () {
            $q = "SELECT * FROM comics;";
            $results = @pg_fetch_all(@pg_query($q));
            $commentary['commentary_member'] = 1;

            foreach ($results as $k => $v) {
                if (!empty($v['comic_commentary'])) {
                    $v['comic'];
                    $commentary['commentary_comic'] = $v['comic'];
                    $commentary['commentary_stamp'] = $v['comic_stamp'];
                    $commentary['commentary_body'] = $v['comic_commentary'];
                    $this->insert($commentary);
                    
                }
            }

        }


        public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN members ON (commentary_member = member)  LEFT JOIN avatars ON (mem_avatar = avatar) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}


}
?>