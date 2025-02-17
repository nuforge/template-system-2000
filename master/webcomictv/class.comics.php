<?PHP

class comics extends webcomictv_DB_Object {

    public $primarykey = 'comic';

    public function initialize() {

        $this->SELECT_STATMENTS['comics_default']['queries']['*'] = false;
        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic = 1 THEN 1 else 0 end'] = 'is_first_comic';
        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic_number = 1 THEN 1 else 0 end'] = 'is_first_webcomic';

        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic >= (Select max(comic) from comics WHERE comic_stamp <= NOW()) THEN 1 else 0 end'] = 'is_last_comic';
        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic_number >= (Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic and comic_stamp <= NOW())  THEN 1 else 0 end'] = 'is_last_webcomic';

        $this->SELECT_STATMENTS['comics_default']['queries']['(Select max(comic) from comics WHERE comic_stamp <= NOW())'] = 'last_comic';
        $this->SELECT_STATMENTS['comics_default']['queries']['(Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic AND comic_stamp <= NOW())'] = 'last_webcomic';

        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic > 1 THEN (comic -1) ELSE 0 end'] = 'previous_comic';
        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic_number > 1 THEN (comic_number -1) ELSE 0 end'] = 'previous_webcomic';

        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic < (Select max(comic) from comics WHERE comic_stamp <= NOW()) THEN (comic +1) ELSE 0 end'] = 'next_comic';
        $this->SELECT_STATMENTS['comics_default']['queries']['case when comic_number < (Select max(comic_number) from comics WHERE comic_stamp <= NOW()) THEN (comic_number +1) ELSE 0 end'] = 'next_webcomic';

        $this->SELECT_STATMENTS['comics_default']['booleans'][] = 'comic_show_ads';

    }

    public function getNextFreeComicNumber($f_webcomic) {


        $query = 'SELECT coalesce(max(comic_number),0) +1 as next from comics where comic_webcomic = ' . $f_webcomic;

        $results = @pg_fetch_assoc(@pg_query($query));
        return $results['next'];
    }

    public function updateTextSearch($f_comic = false) {
        $where =  ($f_comic == false) ? ';' :  " WHERE comic = " . $f_comic . ";";
        $query = "UPDATE comics SET comic_tsvector =
                setweight(to_tsvector(coalesce(comic_title,'')), 'A') ||
                setweight(to_tsvector(coalesce(comic_transcription,'')), 'A') ||
                setweight(to_tsvector(coalesce(comic_description,'')), 'B') ||
                setweight(to_tsvector(coalesce(comic_alt,'')), 'B') ||
                setweight(to_tsvector(coalesce((SELECT commentary_body FROM commentaries WHERE commentary_comic = comic),'')), 'B') " . $where;
       
        return @pg_query($query);
    }

    public function getLatestUpdates($f_limit=4, $f_filter_adult = false) {
        if($f_filter_adult) {
            $adult = ' AND wc_adult = false';
        }
        $query = "SELECT * from (SELECT DISTINCT ON (comic_webcomic) * from comics where comic_stamp <= NOW() order by comic_webcomic, comic_stamp desc) as updates LEFT JOIN webcomics ON (comic_webcomic = webcomic) WHERE wc_status <> 5 " . $adult . " order by comic_stamp desc lIMIT " . $f_limit . ";";
        return @pg_fetch_all(@pg_query($query));
    }

    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        $this->joinTable('webcomics', 'comic_webcomic = webcomic');

        $query = $this->formatQuery('comics_default', $f_order, $f_where, $f_limit, $f_offset, $f_table);

        return @pg_fetch_all(@pg_query($query));
    }

    public function getSearchList($f_query, $limit=false, $offset=false, $table=false) {
        $f_query = $this->escapeData($f_query);
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT *,
		case when comic = 1 THEN 1 else 0 end as is_first_comic,
		case when comic_number = 1 THEN 1 else 0 end as is_first_webcomic,

		case when comic >= (Select max(comic) from comics WHERE comic_stamp <= NOW()) THEN 1 else 0 end as is_last_comic,
		case when comic_number >= (Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic and comic_stamp <= NOW())  THEN 1 else 0 end as is_last_webcomic,

		(Select max(comic) from comics WHERE comic_stamp <= NOW()) as last_comic,
		(Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic AND comic_stamp <= NOW()) as last_webcomic,

		case when comic > 1 THEN (comic -1) ELSE 0 end as previous_comic,
		case when comic_number > 1 THEN (comic_number -1) ELSE 0 end as previous_webcomic,

		case when comic < (Select max(comic) from comics WHERE comic_stamp <= NOW()) THEN (comic +1) ELSE 0 end as next_comic,
		case when comic_number < (Select max(comic_number) from comics WHERE comic_stamp <= NOW()) THEN (comic_number +1) ELSE 0 end as next_webcomic,
		ts_rank_cd(comic_tsvector, query) AS ts_rank

		FROM ' . $table . ' LEFT JOIN webcomics ON (comic_webcomic = webcomic) 
                 , plainto_tsquery(\'' . $f_query . '\') query ';
        $query .= " WHERE query @@ comic_tsvector ";
        $query .= " ORDER BY ts_rank DESC, comic_title ";
        if ($limit) {
            $query .= ' LIMIT ' . $limit . ' ';
        }
        if ($offset) {
            $query .= ' OFFSET ' . $offset . ' ';
        }
        echo $query;
        return @pg_fetch_all(@pg_query($query . ';'));
    }

    public function load($f_where, $f_order=false, $f_table = false) {
        $t_csrc = new comic_sources();
        $this->joinTable('webcomics', 'comic_webcomic = webcomic');

        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = $this->formatQuery('comics_default', $f_order, $f_where, $f_limit, $f_offset, $f_table);
        if ($array = @pg_fetch_assoc(@pg_query($query))) {
            $array['sources'] = $t_csrc->getList('src_title asc', array('cs_comic' => $array['comic'], 'cs_active' => 'true'));
        }
        //echo $query;
        return $array;
    }

    public function creatorLoad($where, $order=false, $table = false) {
        $t_csrc = new comic_sources();
        if (!$table) {
            $table = $this->table;
        }
        //if (!is_array($where)){$where = array($this->primarykey => $where);}
        $query = 'SELECT  *,
		case when comic = 1 THEN 1 else 0 end as is_first_comic,
		case when comic_number = 1 THEN 1 else 0 end as is_first_webcomic,

		case when comic = (Select max(comic) from comics) THEN 1 else 0 end as is_last_comic,
		case when comic_number = (Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic) THEN 1 else 0 end as is_last_webcomic,

		(Select max(comic) from comics  ) as last_comic,
		(Select max(comic_number) from comics WHERE comic_webcomic = webcomics.webcomic  ) as last_webcomic,

		case when comic > 1 THEN (comic -1) ELSE 0 end as previous_comic,
		case when comic_number > 1 THEN (comic_number -1) ELSE 0 end as previous_webcomic,

		case when comic < (Select max(comic) from comics ) THEN (comic +1) ELSE 0 end as next_comic,
		case when comic_number < (Select max(comic_number) from comics ) THEN (comic_number +1) ELSE 0 end as next_webcomic

		FROM ' . $table . ' LEFT JOIN webcomics ON (comic_webcomic = webcomic) ';
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        if ($array = @pg_fetch_assoc(@pg_query($query . ';'))) {
            $array['sources'] = $t_csrc->getList('src_title asc', array('cs_comic' => $array['comic'], 'cs_active' => 'true'));
        }
        //echo $query;
        return $array;
    }

}

?>