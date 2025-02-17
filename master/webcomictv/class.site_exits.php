<?PHP
class site_exits extends webcomictv_DB_Object {

	public $primarykey = 'site_exit';

        public function getSiteExits ($f_limit=false,$f_start_date=false,$f_end_date=false) {
            $limit = false;
            $where = false;
            if($f_limit) {
                $limit = 'LIMIT ' . $f_limit;
            }
            if($f_start_date || $f_end_date) {
                if($f_start_date) {
                    $where_array[] = " se_stamp >= '" . date('Y-m-d',strtotime($f_start_date)) . "' ";
                }
                if($f_end_date) {
                    $where_array[] = " se_stamp <= '" . date('Y-m-d',strtotime($f_end_date)) . "' ";
                }
                $where = 'WHERE ' . implode (' AND ', $where_array);
            }
            $query = 'SELECT site, site_title, site_unique, count(site_exit) as exits, count(DISTINCT se_ip) as unique_exits FROM site_exits LEFT JOIN sites ON (se_site = site) ' . $where . ' group by site, site_title, site_unique ORDER by exits desc ' . $limit . ';';
            return @pg_fetch_all(@pg_query($query));
        }
	
	
}
?>