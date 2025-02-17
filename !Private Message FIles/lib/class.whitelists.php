<?PHP
class whitelists extends sitemanager_DB_Object {

	public $primarykey = 'whitelist';

        public function clearWhitelist ($f_member) {
		$t_mem = $this->loadClass('members');
		$member = $t_mem->load($f_member);
                if(empty($member)) {return false;}

                $query = 'DELETE FROM whitelists WHERE wl_member = ' .$member['member'] .';';
                return @pg_query($query);
        }


        public function checkForUser ($f_member, $f_to_test) {
		$t_mem = $this->loadClass('members');
		$member = $t_mem->load($f_member);
		$to_test = $t_mem->load($f_to_test);

                $query = "SELECT count(whitelist) FROM whitelists WHERE wl_member = " . $member['member'] . " AND (wl_safe = " . $to_test['member'] . " OR lower(wl_username) = '" . $to_test['member_unique'] . "');";

                $result = @pg_fetch_assoc(@pg_query($query));

                return ($result['count']) ? true : false;

        }

    public function getUsernameList($order=false, $where=false, $limit=false, $offset=false, $group=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = "SELECT coalesce(member_username,wl_username) as wl_username
                    FROM " . $table . "

                LEFT JOIN members ON (wl_safe = member)

                ";
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        if ($group) {
            $query .= $this->formatGroup($group);
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
$result = @pg_query($query . ';');
        while($a =  @pg_fetch_assoc($result) ) {
            $return[] = $a['wl_username'];
        }
        return $return;
    }
	
}
?>