<?PHP

class webcomic_credits extends webcomictv_DB_Object {

    public $primarykey = 'webcomic_credit';

    public function getList($order=false, $where=false, $limit=false, $offset=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT *, case when wcc_privileges THEN 1 ELSE 0 END as wcc_privileges FROM ' . $table . ' LEFT JOIN webcomics ON (wcc_webcomic = webcomic) LEFT JOIN members ON (wcc_member = member) ';
        if ($where) {
            $query .= $this->formatWhere($where) . ' AND wc_status <> 5';
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
        return @pg_fetch_all(@pg_query($query . ';'));
    }

    public function load($where, $order=false, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = 'SELECT *, case when wcc_privileges THEN 1 ELSE 0 END as wcc_privileges FROM ' . $table . ' LEFT JOIN webcomics ON (wcc_webcomic = webcomic) LEFT JOIN members ON (wcc_member = member) ';
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }

    public function getSimpleList($f_column, $f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {

        $this->joinTable('webcomics', 'wcc_webcomic = webcomic');
        $this->joinTable('members', 'wcc_member = member');

        $query = $this->formatQuery($this->primarykey . ', ' . $f_column, $f_order, $f_where, $f_limit, $f_offset, $f_table);

        $results = @pg_query($query);
        while ($a = @pg_fetch_assoc($results)) {
            $returnSimpleList[$a[$this->primarykey]] = $a[$f_column];
        }
        return $returnSimpleList;
    }


    public function getWebcomicSimpleList($f_column, $f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {

        $this->joinTable('webcomics', 'wcc_webcomic = webcomic');
        $this->joinTable('members', 'wcc_member = member');

        $query = $this->formatQuery('webcomic, ' . $f_column, $f_order, $f_where, $f_limit, $f_offset, $f_table);

        $results = @pg_query($query);
        while ($a = @pg_fetch_assoc($results)) {
            $returnSimpleList[$a['webcomic']] = $a[$f_column];
        }
        return $returnSimpleList;
    }


}

?>