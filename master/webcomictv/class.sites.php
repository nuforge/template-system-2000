<?PHP

class sites extends webcomictv_DB_Object {

    public $primarykey = 'site';

    public function globalQueries() {
        
        $this->addSelectStatement('coalesce(site_title_short, site_title)','site_title_short');
        $this->addSelectStatement('coalesce(site_hidden_url, site_url)','site_hidden_url');
        $this->addSelectStatement('case when site_content_rating = 6 then 1 ELSE 0 END ','site_mature');
        $this->addSelectStatement("case when site_added >= NOW() - INTERVAL '2 days' THEN 1 ELSE 0 END",'site_new');
        $this->addSelectStatement("(SELECT count(favorite) FROM favorites WHERE favorite_site = site)",'site_favorites');
        $this->addSelectStatement("(SELECT count(site_exit) FROM site_exits WHERE se_site = site  AND se_stamp >= NOW() - INTERVAL '1 week')",'site_exits');

        $this->addSelectBoolean('site_nsfw');
        $this->addSelectBoolean('site_adultwebcomic');
        $this->addSelectBoolean('site_adult_only');
        $this->addSelectBoolean('site_ended');
        $this->addSelectBoolean('site_recommended');
        $this->addSelectBoolean('site_active');
        $this->addSelectBoolean('site_paysite');
        $this->addSelectBoolean('site_card');
        $this->addSelectBoolean('site_force_awc');
        $this->addSelectBoolean('site_requires_login');
        $this->addSelectBoolean('site_updates_monday');
        $this->addSelectBoolean('site_updates_tuesday');
        $this->addSelectBoolean('site_updates_wednesday');
        $this->addSelectBoolean('site_updates_thursday');
        $this->addSelectBoolean('site_updates_friday');
        $this->addSelectBoolean('site_updates_saturday');
        $this->addSelectBoolean('site_updates_sunday');
    }


    public function getLetterPageNumbers($f_letter, $f_show_per_page, $f_where=false) {
        $t_where = ($f_where) ? ' AND ' . $f_where : false;
        $q = "SELECT FLOOR(count(site)/ " . $f_show_per_page .") + 1  as page_number from sites WHERE site_unique <= '" . $f_letter . "' " . $t_where . ";";
        
        $result = @pg_fetch_assoc(@pg_query($q));
        return $result['page_number'];
    }

    public function updateUnique() {
        $sites = $this->getList();

        foreach ($sites as $k => $site) {
            $arr['site_unique'] = $this->encodeString($site['site_title']);

            $this->update($arr, array('site' => $site['site']));
        }
    }

    public function getReferenceList($f_site) {
        $site = $this->load($f_site);
        $q = "SELECT *
                , case when site_nsfw THEN 1 ELSE 0 END as site_nsfw
                , case when site_adultwebcomic THEN 1 ELSE 0 END as site_adultwebcomic
                , case when site_adult THEN 1 ELSE 0 END as site_adult

                FROM sites LEFT JOIN sources ON (src_site = site) LEFT JOIN comic_sources ON (cs_source = source) LEFT JOIN comics ON (cs_comic = comic) LEFT JOIN webcomics ON (comic_webcomic = webcomic) LEFT JOIN categories ON (site_category = category) WHERE site = " . $site['site'] . " and comic_stamp <= NOW() AND source IS NOT NULL;";
        return @pg_fetch_all(@pg_query($q));
    }

    public function encodeString($string, $replace='-', $lower=true) {
        $text = preg_replace("[\-$]", '', preg_replace("[\W+]", $replace, $string));
        if ($lower) {
            return strtolower($text);
        }
        return $text;
    }

    public function updateTextSearch($f_site = false) {
        if ($f_site == false) {
            $query = "UPDATE sites SET site_tsvector =
                setweight(to_tsvector(coalesce(site_title,'')), 'A') ||
                setweight(to_tsvector(coalesce(site_description,'')), 'B') ||
                setweight(to_tsvector(coalesce(site_description_brief,'')), 'B') ||
                setweight(to_tsvector(coalesce(site_about,'')), 'B');";
            return @pg_query($query);
        } else {
            if (!$this->load($f_site)) {
                return false;
            }
            $query = "UPDATE sites SET site_tsvector =
                setweight(to_tsvector(coalesce(site_title,'')), 'A') ||
                setweight(to_tsvector(coalesce(site_description,'')), 'B') ||
                setweight(to_tsvector(coalesce(site_description_brief,'')), 'B') ||
                setweight(to_tsvector(coalesce(site_about,'')), 'B');";
            $query .= " WHERE site = " . $f_site . ";";

            return @pg_query($query);
        }
    }

    public function getSearchList($f_order=false, $f_query=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        $this->joinTable('categories', 'site_category = category');
        $this->joinTable('content_ratings', 'site_content_rating = content_rating');
        $this->joinTable('languages', 'site_language = language');

        $f_table = ( $f_table) ? $f_table : $this->table;

        $query = $this->formatSelect($this->defaultQuery) . ',ts_rank_cd(site_tsvector, query) AS ts_rank ';
        $query .= $this->formatFrom($f_table) . ', plainto_tsquery(\'' . $f_query . '\') query';
        $query .= $this->formatWhere($f_where);
        $query .= $this->formatOrder($f_order) ;
        $query .= $this->formatLimit($f_limit) ;
        $query .= $this->formatOffset($f_offset) ;

        return @pg_fetch_all(@pg_query($query . ';'));
    }

    public function load($f_where, $f_order=false, $f_table = false) {
        $this->joinTable('categories', 'site_category = category');
        $this->joinTable('content_ratings', 'site_content_rating = content_rating');
        $this->joinTable('languages', 'site_language = language');
        
        $f_where = (!is_array($f_where)) ? array($this->primarykey => $f_where) : $f_where;

        $query = $this->formatQuery($this->defaultQuery, $f_order, $f_where , $f_table);

        return @pg_fetch_assoc(@pg_query($query));
    }

    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        $this->joinTable('categories', 'site_category = category');
        $this->joinTable('content_ratings', 'site_content_rating = content_rating');
        $this->joinTable('languages', 'site_language = language');

        $query = $this->formatQuery($this->defaultQuery, $f_order, $f_where, $f_limit, $f_offset, $f_table);
        return @pg_fetch_all(@pg_query($query));
    }


    public function getSimpleUniqueList($column, $order=false, $where=false, $limit=false, $offset=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT site_unique, ' . $column . ' FROM ' . $table;
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        if ($order) {
            $query .= $this->formatOrder($order);
        } else {
            $query .= ' ORDER BY site_unique';
        }
        if ($limit) {
            $query .= ' LIMIT ' . $limit . ' ';
        }
        if ($offset) {
            $query .= ' OFFSET ' . $offset . ' ';
        }
        $res = @pg_query($query . ';');
        while ($a = @pg_fetch_assoc($res)) {
            $arr[$a['site_unique']] = $a[$column];
        }
        return $arr;
    }

    public function load_old($where, $order=false, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = 'SELECT * ' . SITES_FULL_SELECT . ' FROM ' . $table . ' LEFT JOIN categories ON (site_category = category) LEFT JOIN content_ratings ON (site_content_rating = content_rating)  ';
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }

}

?>