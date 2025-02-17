<?PHP
class site_tags extends webcomictv_DB_Object {

	public $primarykey = 'site_tag';

    public function initialize() {
        // case when site_card then 1 else 0 end as site_card
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['*'] = false;
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['initcap(st_tag)'] = 'st_display';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['coalesce(site_hidden_url, site_url)'] = 'site_hidden_url';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['queries']['case when site_added >= NOW() - INTERVAL \'2 days\' THEN 1 ELSE 0 END'] = 'site_new';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_ended';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_recommended';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_active';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_paysite';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_adultwebcomic';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_nsfw';
        $this->SELECT_STATMENTS[$this->getTableName() . '_default']['booleans'][] = 'site_card';
    }


        public function deleteSiteTags($f_site) {

            if(empty($f_site) || !is_numeric($f_site)) { return false;}

            $q = 'DELETE FROM site_tags WHERE st_site = ' . $f_site . ';';

            return @pg_query($q);

        }


    public function getCount($column=false, $where=false, $group=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        if (empty($column) && !is_array($this->primarykey)) {
            $column = $this->primarykey;
        } elseif (empty($column) && is_array($this->primarykey)) {
            $column = 0;
        }
        $query = 'SELECT count(' . $column . ') as count FROM ' . $table . ' LEFT JOIN sites ON (st_site = site)  LEFT JOIN categories ON (site_category = category) LEFT JOIN content_ratings ON (site_content_rating = content_rating) ';
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        if ($group) {
            $query .= $this->formatGroup($group);
        }
        $ret = pg_fetch_assoc(pg_query($query . ';'));
        return $ret['count'];
    }



    public function getList($order=false, $where=false, $limit=false, $offset=false, $table=false) {
        $this->joinTable('sites', 'site = st_site');
        $this->joinTable('categories', 'site_category = category');
        $this->joinTable('content_ratings', 'site_content_rating = content_rating');
        $this->joinTable('languages', 'site_language = language');

        $table = ($table) ? $table : $this->table;

        $query = $this->formatSelect('site_tags_default');
        $query .= $this->formatFrom($table);
        $query .= ($where) ? $this->formatWhere($where) : '';
        $query .= ( $order) ? $this->formatOrder($order) : '';
        $query .= ( $limit) ? ' LIMIT ' . $limit . ' ' : '';
        $query .= ( $offset) ? ' OFFSET ' . $offset . ' ' : '';
        return @pg_fetch_all(@pg_query($query . ';'));

    }


    public function getList_old ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT *, initcap(st_tag) as st_display, coalesce(site_hidden_url, site_url) as site_hidden_url, case when site_added >= NOW() - INTERVAL \'2 days\' THEN 1 ELSE 0 END as site_new, case when site_ended THEN 1 ELSE 0 END as site_ended, case when site_recommended THEN 1 ELSE 0 END as site_recommended, case when site_active THEN 1 ELSE 0 END as site_active, case when site_paysite THEN 1 ELSE 0 END as site_paysite, case when site_adult THEN 1 ELSE 0 END as site_adult, case when site_content_rating = 6 then 1 ELSE 0 END as site_mature, case when site_card then 1 else 0 end as site_card  FROM ' . $table . ' LEFT JOIN sites ON (st_site = site)  LEFT JOIN categories ON (site_category = category) LEFT JOIN content_ratings ON (site_content_rating = content_rating) ';
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


    public function load($where, $order=false, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = 'SELECT *, initcap(st_tag) as st_display FROM ' . $table;
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        $query .= ' LIMIT 1 ';
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }

}
?>