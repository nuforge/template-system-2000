<?PHP
class tags extends webcomictv_DB_Object {
    public $primarykey = 'tag';



        public function deleteComicTags($f_comic) {

            if(empty($f_comic) || !is_numeric($f_comic)) { return false;}

            $q = 'DELETE FROM tags WHERE tag_comic = ' . $f_comic . ';';

            return @pg_query($q);

        }

    public function getTagList() {
        $q = 'SELECT count(tag), (count(tag))+7 as tag_size, tag_name, tag_unique FROM tags GROUP BY tag_name, tag_unique ORDER BY tag_name;';

        return @pg_fetch_all(@pg_query($q));
    }

    public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = 'SELECT * FROM ' . $table . ' LEFT JOIN comics ON (tag_comic = comic) LEFT JOIN webcomics on (comic_webcomic = webcomic) ';
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
        $query = 'SELECT * FROM ' . $table;
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }


    public function insert($values, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        $t_values = $this->pruneValues($values);
        if (!empty($t_values)) {
            $query = "INSERT INTO $table (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',',$this->cleanValues($t_values)) . ")";
            if(!@pg_query($query . ';')) {
                return false;
            }
            $re = @pg_fetch_assoc(@pg_query("select currval('tags_tag_seq');"));
            return $re['currval'];
        }
        return false;
    }

    public function deleteTag($f_tag) {
        if (empty($f_tag)) {
            return false;
        }

        $tag = $this->load($f_tag);

        if(empty($tag)) {
            return false;
        }

        $q = 'DELETE FROM tags WHERE tag = ' . $tag['tag'] . ';';


        return @pg_query($q);

    }

}
?>