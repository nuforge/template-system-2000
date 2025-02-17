<?PHP
class comic_links extends webcomictv_DB_Object {
    public $primarykey = 'comic_link';


    public function initialize() {

        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['*'] = false;
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['CASE WHEN link_nsfw OR site_nsfw THEN 1 ELSE 0 END'] = 'link_nsfw';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['case when site_active AND link_active then 1 else 0 end'] = 'link_active';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_nsfw';
    }



    public function getList($order=false, $where=false, $limit=false, $offset=false, $table=false) {
        $this->joinTable('links', 'link = cl_link');
        $this->joinTable('site_links', 'sl_link = cl_link');
        $this->joinTable('sites', 'src_site = site');
        $this->joinTable('comics', 'cl_comic = comic');
        $this->joinTable('webcomics', 'comic_webcomic = webcomic');
        $table = ($table) ? $table : $this->table;

        //$query = 'SELECT * ' . SITES_FULL_SELECT . ' FROM ' . $table . ' LEFT JOIN categories ON (site_category = category) LEFT JOIN content_ratings ON (site_content_rating = content_rating) ';

        $query = $this->formatSelect('comic_links_default');
        $query .= $this->formatFrom($table);
        $query .= ($where) ? $this->formatWhere($where) : '';
        $query .= ( $order) ? $this->formatOrder($order) : '';
        $query .= ( $limit) ? ' LIMIT ' . $limit . ' ' : '';
        $query .= ( $offset) ? ' OFFSET ' . $offset . ' ' : '';
        return @pg_fetch_all(@pg_query($query . ';'));

    }


    public function load($where,$order=false,  $limit=1, $offset=false, $table=false) {
        $this->joinTable('links', 'link = cl_link');
        $this->joinTable('site_links', 'sl_link = cl_link');
        $this->joinTable('sites', 'src_site = site');
        $this->joinTable('comics', 'cl_comic = comic');
        $this->joinTable('webcomics', 'comic_webcomic = webcomic');
        $table = ($table) ? $table : $this->table;

        //$query = 'SELECT * ' . SITES_FULL_SELECT . ' FROM ' . $table . ' LEFT JOIN categories ON (site_category = category) LEFT JOIN content_ratings ON (site_content_rating = content_rating) ';

        $query = $this->formatSelect('comic_links_default');
        $query .= $this->formatFrom($table);
        $query .= ($where) ? $this->formatWhere($where) : '';
        $query .= ( $order) ? $this->formatOrder($order) : '';
        $query .= ( $limit) ? ' LIMIT ' . $limit . ' ' : '';
        $query .= ( $offset) ? ' OFFSET ' . $offset . ' ' : '';
        return @pg_fetch_all(@pg_query($query . ';'));

    }

    public function load_old ($where,$order=false,$table = false) {
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
    }

    public function deleteComicLink($f_comic_link) {
        if (empty($f_comic_link)) {
            return false;
        }

        $comic_link = $this->load($f_comic_link);

        if(empty($comic_link)) {
            return false;
        }

        $q = 'DELETE FROM comic_links WHERE comic_link = ' . $comic_link['comic_link'] . ';';


        return @pg_query($q);

    }

}
?>