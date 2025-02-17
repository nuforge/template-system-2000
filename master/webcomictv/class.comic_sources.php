<?PHP
class comic_sources extends webcomictv_DB_Object {
    public $primarykey = 'comic_source';



    public function globalQueries() {
        $this->addSelectBoolean('src_nsfw');
        $this->addSelectBoolean('cs_active');
    }

    public function globalJoins() {
        $this->joinTable('sources', 'cs_source = source');
        $this->joinTable('sites', 'src_site = site');
        $this->joinTable('comics', 'cs_comic = comic');
        $this->joinTable('webcomics', 'comic_webcomic = webcomic');
    }


    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        return @pg_fetch_all(@pg_query($this->formatQuery($this->defaultQuery, $f_order, $f_where, $f_limit, $f_offset, $f_table)));
    }

    public function deleteComicSource($f_comic_source) {
        if (empty($f_comic_source)) {
            return false;
        }

        $comic_source = $this->load($f_comic_source);

        if(empty($comic_source)) {
            return false;
        }

        $q = 'DELETE FROM comic_sources WHERE comic_source = ' . $comic_source['comic_source'] . ';';
        

        return @pg_query($q);

    }

    /*
    public function getList_old ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT *, case when src_nsfw then 1 else 0 end as src_nsfw, case when site_active then 1 else 0 end as cs_active FROM ' . $table . ' LEFT JOIN sources ON (cs_source = source) LEFT JOIN sites ON (src_site = site) LEFT JOIN comics ON (cs_comic = comic) LEFT JOIN webcomics ON (comic_webcomic = webcomic) ';
        if ($where) {
            $query .= $this->formatWhere($where);
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
        return @pg_fetch_all(@pg_query ($query . ';'));
    }

    public function load ($where,$order=false,$table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = 'SELECT *, case when src_nsfw then 1 else 0 end as src_nsfw, case when cs_active then 1 else 0 end as cs_active FROM ' . $table . ' LEFT JOIN sources ON (cs_source = source) LEFT JOIN sites ON (src_site = site) ';
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }*/


}
?>