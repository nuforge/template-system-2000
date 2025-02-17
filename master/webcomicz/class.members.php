<?PHP

class members extends Base_Member {

    public $primarykey = 'member';

    public $connection_info = array('user'=>'webcom99_webcomics','password'=>'T$!M*.-ixZK8','dbname'=>'webcom99_webcomicz');

    public $session_title = 'member_login';
    protected $member_info;
    protected $db_username_field = 'mem_unique';
    protected $login_username_field = 'username';
    protected $db_password_field = 'mem_password';
    protected $login_password_field = 'password';



    public function auto_login () {
        $rc4 = new rc4crypt;
        if (!empty($_SESSION[$this->session_title])) {
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION [$this->session_title], 'de'));
            $loginifno = array ($this->login_username_field=>$arr[0], $this->login_password_field=>$arr[1], 'remember'=>0);
            return $this->login($loginifno);
        } elseif (empty($_SESSION[$this->session_title]) && !empty($_COOKIE[$this->session_title])) {
            $log_info = $rc4->endecrypt('x87jm43', $_COOKIE [$this->session_title], 'de');
            $arr = explode(':', $log_info);
            $loginifno = array ($this->login_username_field=>$arr[0], $this->login_password_field=>$arr[1], 'remember'=>1);
            return $this->login ($loginifno);
        }
    }



    public function getMemberInfo() {
        return $this->member_info;
    }

    public function onlogin () {
        $set['mem_last_login'] = 'NOW()';
        $where['member'] = $this->member_info['member'];
        $this->update($set,$where);
    }


    public function checkIsUnique($f_value,$f_field,$f_caseSensitive=false) {

        $user = $this->validateValues(array('mem_'.$f_field=>$f_value));
        if($f_caseSensitive) {
            $q= "SELECT count(member) as count from members WHERE mem_" . $f_field . " = " . $user['mem_' . $f_field] . ";";
        } else {
            $q= "SELECT count(member) as count from members WHERE lower(mem_" . $f_field . ") = " . strtolower($user['mem_' . $f_field]) . ";";
        }
        $res = @pg_fetch_assoc(@pg_query($q));
        return $res['count'] ? false : true;
    }

	public function getDemographics () {
		$q="SELECT sex, extract('year' from age(birthdate)), count(member) from members group by sex, extract('year' from age(birthdate)) order by count desc;";
		return @pg_fetch_all(@pg_query($q));
	}

	public function addCredits($f_member,$f_credits) {
		$member = $this->validateValues(array('member'=>$f_member));
		if(!is_numeric($f_credits)) { return false;}
		return @pg_query("UPDATE members set credits = credits + " .  $f_credits . " WHERE member = " . $member['member'] . ";");
	}

	public function removeCredits($f_member,$f_credits) {
		$member = $this->validateValues(array('member'=>$f_member));
		if(!is_numeric($f_credits)) { return false;}
		return @pg_query("UPDATE members set credits = credits - " .  $f_credits . " WHERE member = " . $member['member'] . ";");
	}

	public function getCredits ($f_member) {
		$mem = $this->load(array('member'=>$f_member));

		return $mem['credits'];
	}
	public function changeAvatar($avatar, $member) {
		$q = "UPDATE members SET avatar = '$avatar' WHERE member = $member;";
		return @pg_query($q);
	}

	public function changePassword($member,$password) {
		$q = "UPDATE members SET password = '$password' WHERE member = $member;";
		return @pg_query($q);
	}

	public function getNewest () {
		return $this->load(array('privacy'=>'0'),array('stamp'=>'desc'));
	}

	public function getNewMembers ($limit=3) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN avatars USING (avatar) WHERE avatar IS NOT NULL AND privacy = 0 ORDER BY stamp desc LIMIT ' . $limit;
		return @pg_fetch_all(@pg_query ($query . ';'));
	}

	public function insert($f_values, $table = false) {
		if (!$table) {$table = $this->table;}
		$t_values = $this->pruneValues($f_values);
		if (!empty($t_values)) {
			$query = "INSERT INTO " . $table . " (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',',$this->cleanValues($t_values)) . ")";
			if(!@pg_query($query . ';')) {return false;}
			$re = @pg_fetch_assoc(@pg_query("select currval('members_member_seq');"));
			return $re['currval'];
		}
		return false;
	}

	public function add ($values) {
		$values[$this->primarykey] = $this->getNext();
		return $this->insert($values);
	}

	public function login ($logininfo) {
		$rc4 = new rc4crypt;
		$mip = new memberip();
		$this->member_info = $this->load (array('username' => $logininfo['username']));
		if (empty($logininfo['username']) || empty($this->member_info) || $logininfo['password'] != $this->member_info['password']) {return false;}

		$log_info = $this->member_info['username'] . ':' . $logininfo['password'] . ':' . mktime();
		$log_info = $rc4->endecrypt('x87jm43', $log_info, 'en');

		if (!empty($logininfo['remember'])) {
			$lifetime = time() + 365*24*60*60;
			setcookie($this->session_title, $log_info, $lifetime, "/" );
		}

		$_SESSION [$this->session_title] = $log_info;
		$this->update('login = NOW()', array('member'=>$this->member_info['member']));
		$mip->insert(array('member'=>$this->member_info['member'],'ip'=>$_SERVER['REMOTE_ADDR']));
		return $this->load ($this->member_info['member']);
	}

	public function pruneValues($values,$keys=true,$table=false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($values)) {return $values;}
		return ($keys) ? @array_intersect_key($values, $this->meta) : @array_intersect($values, array_keys($this->meta));
	}

	public function cleanValues($values, $table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($values)) {return $values;}

		return pg_convert($this->db_connection, $table, $values);
	}

	public function formatSet ($set) {
		if (is_string($set)) {return ' SET ' . $this->escapeData($set);}
		$t_set = $this->cleanValues($this->pruneValues($set));
		foreach ($t_set as $k=>$v) {$a[] = $k . '=' . $v;}
		return ' SET ' . implode (',', $a);
	}

	public function update($values,$where=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = "UPDATE " . $table;
		$query .= $this->formatSet($values);
		if ($where) {$query .= $this->formatWhere($where);}
		//echo $query;
		return @pg_query($query . ';');
	}

	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT *,case when suspend THEN 1 ELSE 0 END as suspend, coalesce(mem_display_username,username) as d_username, extract(\'year\' from age(birthdate)) as age, case when (age(birthdate) < INTERVAL \'1 week\') then 1 else 0 end as birthday FROM ' . $table . ' LEFT JOIN avatars USING (avatar) LEFT JOIN moods USING (mood) ';
		$query .= $this->formatWhere($where);
		if ($order) {$query .= $this->formatOrder($order);}
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}

	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *,case when suspend THEN 1 ELSE 0 END as suspend, coalesce(mem_display_username,username) as d_username, extract(\'year\' from age(birthdate)) as age, case when (age(birthdate) < INTERVAL \'1 week\') then 1 else 0 end as birthday  FROM ' . $table . ' LEFT JOIN avatars USING (avatar) LEFT JOIN moods USING (mood)  ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}


}
?>