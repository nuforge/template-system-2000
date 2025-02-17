<?PHP
class conversation_folders extends sitemanager_DB_Object {

	public $primarykey = 'conversation_folder';


        public function removeFromFolder ($f_member,$f_conversation,$f_folder) {
            $load['cf_member'] = $f_member;
            $load['cf_conversation'] = $f_conversation;
            $load['cf_folder'] = $f_folder;
            $conversation_folder = $this->load($load);
            //var_dump($conversation_folder);
            if(empty($conversation_folder)) { return false;}

            $query = 'delete from conversation_folders WHERE conversation_folder = '. $conversation_folder['conversation_folder'] . ' AND cf_member = ' . $conversation_folder['cf_member'] . ' and cf_conversation = ' . $conversation_folder['cf_conversation'] . " and cf_folder = '" . $conversation_folder['cf_folder'] . "';";
            
            return @pg_query($query);

        }
	public function getFolderList ($column,$order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT ' . $column . ' FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);} else {$query .= ' ORDER BY ' . $this->primarykey;}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$res = @pg_query ($query . ';');
		while ($a = @pg_fetch_assoc($res)) {
			$arr[$a[$column]] = true;
		}
		return $arr;
	}

	
}
?>